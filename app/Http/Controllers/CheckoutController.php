<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmed;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use App\Services\PaymentService;
use App\Services\PrintletApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Handles storefront checkout flow integrated with Printlet Fulfillment API (v1).
 * Performs Just-in-Time (JIT) quote generation and immediate order placement.
 */
class CheckoutController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PrintletApiService $printletApi
    ) {}

    /**
     * Display the checkout view with cart contents and payment methods.
     */
    public function show(): View|RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $productIds = array_keys($cart);
        $products = Product::with(['supplier', 'variants'])
            ->whereIn('id', $productIds)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        $paymentMethods = [
            'cod' => 'Cash on Delivery',
            'instapay' => 'Instapay Direct Transfer',
        ];
        $instapayConfig = config('services.instapay');
        $customer = Auth::user()?->customer;

        return view('checkout', compact('cart', 'products', 'paymentMethods', 'instapayConfig', 'customer'));
    }

    /**
     * Process checkout submission:
     * 1. Validates inputs & variant configuration.
     * 2. Executes JIT POST /quotes to Printlet API to determine shipping & fulfillment rates.
     * 3. Executes POST /orders to Printlet API immediately after obtaining quote.
     * 4. Supports 'prepaid' and 'cod' payment methods (COD orders enter 'pending_verification').
     * 5. Saves local Order & OrderItem records within a database transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $customer = $user?->customer;

        if (! $customer) {
            return redirect()->route('login')->with('error', 'Please log in to place an order.');
        }

        // Standardize payment method key
        if ($request->input('payment_method') === 'cash_on_delivery') {
            $request->merge(['payment_method' => 'cod']);
        }

        // Standardize name, email, phone, and address from request or user/customer fallbacks
        $request->merge([
            'name' => $request->input('name', $user?->name),
            'email' => $request->input('email', $user?->email),
            'phone' => $request->input('phone', $customer?->phone ?? '0000000000'),
            'address_line_1' => $request->input('address_line_1', $request->input('address', $customer?->address)),
        ]);

        // If variant_id is missing, derive it from the session cart items
        if (! $request->filled('variant_id')) {
            $cart = session()->get('cart', []);
            if (! empty($cart)) {
                $firstProductId = array_key_first($cart);
                $product = Product::with('variants')->find($firstProductId);
                if ($product) {
                    $selectedSize = $cart[$firstProductId]['size'] ?? 'M';
                    $selectedColor = $cart[$firstProductId]['color'] ?? 'Default';

                    $variant = Variant::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'size' => $selectedSize,
                            'color' => $selectedColor,
                        ],
                        [
                            'sku' => ($product->sku ?? 'SKU-'.$product->id).'-'.strtoupper(substr($selectedSize, 0, 3)).'-'.strtoupper(substr($selectedColor, 0, 3)),
                            'price' => $product->price,
                            'printlet_variant_id' => 'v_default',
                            'printlet_variant_revision' => 1,
                        ]
                    );
                    $request->merge([
                        'variant_id' => $variant->id,
                        'quantity' => $cart[$firstProductId]['quantity'] ?? 1,
                    ]);
                }
            }
        }

        $request->validate([
            'payment_method' => 'required|string|in:prepaid,cod,cash_on_delivery,instapay',
            'payment_reference' => 'nullable|string|max:255',
            'variant_id' => 'required|exists:variants,id',
            'quantity' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'address_line_1' => 'required|string|max:500',
            'city_id' => 'nullable|string|max:100|in:'.implode(',', array_keys(config('services.printlet.cities', []))),
            'country_code' => 'nullable|string|size:2',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cityId = $request->city_id ?? 'city_cairo';

        // Keep the selected city with the saved delivery address for future orders.
        $cityName = config('services.printlet.cities')[$cityId] ?? $cityId;
        $customer->update(['address' => $cityName.', '.$request->address_line_1]);

        $variant = Variant::with('product')->findOrFail($request->variant_id);
        $product = $variant->product;

        if (! $product || ! $product->is_available) {
            return redirect()->route('cart.index')->with('error', 'Selected item is unavailable.');
        }

        $quantity = (int) ($request->quantity ?? 1);
        $countryId = Cache::get('printlet:egypt_country_id', config('services.printlet.country_id', 'c1839db2-37eb-4b21-82ea-b248aeef1e97'));
        $countryCode = strtoupper($request->country_code ?? config('services.printlet.country_code', 'EG'));
        $currency = Cache::get('printlet:currency', config('services.printlet.currency', 'EGP'));
        $shippingMethodId = Cache::get('printlet:default_shipping_method_id', config('services.printlet.shipping_method_id', 'sm_standard'));
        $runId = (string) Str::uuid();

        // --------------------------------------------------------------------------
        // STEP 1: Just-in-Time Quote (POST /quotes)
        // --------------------------------------------------------------------------
        $quotePayload = [
            'merchant_quote_id' => 'quote-'.$runId,
            'market' => [
                'country_id' => $countryId,
                'currency' => $currency,
            ],
            'items' => [
                [
                    'merchant_line_id' => 'line-'.$runId,
                    'configuration' => [
                        'variant' => [
                            'id' => $variant->printlet_variant_id,
                            'revision' => (int) $variant->printlet_variant_revision,
                        ],
                        'print_areas' => [
                            [
                                'print_area' => [
                                    'id' => $variant->printlet_print_area_id ?? 'area_front',
                                    'revision' => (int) $variant->printlet_print_area_revision,
                                ],
                                'technique' => [
                                    'id' => $variant->printlet_technique_id ?? 'tech_dtg',
                                    'revision' => (int) $variant->printlet_technique_revision,
                                ],
                                'placement_preset' => $variant->placement_preset ?? 'center',
                                'artwork' => [
                                    'id' => $product->printlet_artwork_id ?? 'art_default',
                                    'revision' => (int) ($product->printlet_artwork_revision ?? 1),
                                ],
                            ],
                        ],
                        'quantity' => $quantity,
                    ],
                ],
            ],
            'shipping' => [
                'method_id' => $shippingMethodId,
                'recipient' => [
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'address_line_1' => $request->address_line_1,
                    'city_id' => $cityId,
                    'country' => $countryCode,
                ],
            ],
        ];

        try {
            $quoteResponse = $this->printletApi->createQuote($quotePayload);
            $printletQuoteId = $quoteResponse['id'] ?? null;
            $shippingCost = (float) ($quoteResponse['totals']['shipping'] ?? 0.00);
        } catch (\Exception $e) {
            Log::error('Checkout JIT Printlet quote creation failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to retrieve shipping quote from fulfillment provider: '.$e->getMessage());
        }

        // Calculate store total (item price * quantity + shipping)
        $itemTotal = (float) ($variant->price * $quantity);
        $grandTotal = $itemTotal + $shippingCost;

        // --------------------------------------------------------------------------
        // STEP 2: Immediate Order Placement (POST /orders)
        // --------------------------------------------------------------------------
        $paymentMethod = $request->payment_method;
        $orderNumber = 'ORD-'.strtoupper(Str::random(8));

        $orderPayload = [
            'merchant_order_id' => 'ord-'.$runId,
            'merchant_order_number' => $orderNumber,
            'quote_id' => $printletQuoteId,
            'end_customer_payment' => $paymentMethod === 'cod' ? [
                'method' => 'cod',
                'currency' => $currency,
                'collection_amount' => (string) number_format($grandTotal, 2, '.', ''),
                'includes_merchant_shipping' => true,
            ] : [
                'method' => 'prepaid',
                'currency' => $currency,
            ],
            'notes' => $request->notes ?? 'Order placed via e-commerce storefront checkout.',
            'metadata' => [
                'source' => 'storefront',
                'run_id' => $runId,
                'customer_id' => (string) $customer->id,
                'variant_sku' => $variant->sku,
            ],
        ];

        try {
            $orderResponse = $this->printletApi->createOrder($orderPayload);
            $printletOrderId = $orderResponse['id'] ?? null;
            $printletStatus = $orderResponse['status'] ?? ($paymentMethod === 'cod' ? 'pending_verification' : 'accepted');
        } catch (\Exception $e) {
            Log::error('Checkout Printlet order placement failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Fulfillment order creation failed: '.$e->getMessage());
        }

        // --------------------------------------------------------------------------
        // STEP 3: Store Local Database Order Record
        // --------------------------------------------------------------------------
        $order = DB::transaction(function () use (
            $customer,
            $grandTotal,
            $shippingCost,
            $paymentMethod,
            $request,
            $printletOrderId,
            $printletQuoteId,
            $printletStatus,
            $product,
            $variant,
            $quantity
        ) {
            $localStatus = match ($paymentMethod) {
                'cod' => 'pending_verification',
                'instapay' => 'pending_payment',
                default => ($printletStatus === 'accepted' ? 'processing' : 'pending'),
            };

            $paymentStatus = match ($paymentMethod) {
                'prepaid' => 'paid',
                default => 'unpaid',
            };

            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => $localStatus,
                'total_price' => $grandTotal,
                'shipping_amount' => $shippingCost,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'payment_reference' => $request->payment_reference,
                'notes' => $request->notes,
                'printlet_order_id' => $printletOrderId,
                'printlet_quote_id' => $printletQuoteId,
                'printlet_status' => $printletStatus,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $variant->price,
                'customizations' => [
                    'variant_id' => $variant->id,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'sku' => $variant->sku,
                ],
            ]);

            return $order;
        });

        // Observer handles order confirmation email automatically
        session()->forget('cart');

        $successMessage = match ($order->payment_method) {
            'instapay' => 'Order #'.$order->id.' placed successfully! Please complete your Instapay transfer below.',
            'cod' => 'Order #'.$order->id.' placed successfully! Our team will contact you to verify your order before delivery.',
            default => 'Order placed successfully! Order status: '.$order->printlet_status,
        };

        return redirect()->route('order.confirmation', $order)
            ->with('success', $successMessage);
    }

    /**
     * Display order confirmation screen.
     */
    public function confirmation(Order $order): View
    {
        abort_if($order->customer_id !== Auth::user()?->customer?->id, 403);

        $order->load('items.product', 'customer');
        $instapayConfig = config('services.instapay');

        return view('order-confirmation', compact('order', 'instapayConfig'));
    }

    /**
     * Submit or update the customer's Instapay reference after order placement.
     */
    public function updatePaymentReference(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->customer_id !== Auth::user()?->customer?->id, 403);

        $validated = $request->validate([
            'payment_reference' => 'required|string|max:255',
        ], [
            'payment_reference.required' => 'Please enter your Instapay reference number or sender name.',
        ]);

        $order->update([
            'payment_reference' => $validated['payment_reference'],
        ]);

        return redirect()->route('order.confirmation', $order)
            ->with('success', 'Your Instapay transfer reference has been received. Our team will verify it shortly.');
    }
}
