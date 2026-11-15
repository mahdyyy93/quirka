<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PricingSetting;
use App\Models\Variant;
use App\Services\Cart;
use App\Services\PaymentService;
use App\Services\Pricing\PricingCalculator;
use App\Services\PrintletApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $items = Cart::items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $paymentMethods = [
            'cod' => 'Cash on Delivery',
            'instapay' => 'Instapay Direct Transfer',
        ];
        $instapayConfig = config('services.instapay');
        $customer = Auth::user()?->customer;

        return view('checkout', compact('items', 'paymentMethods', 'instapayConfig', 'customer'));
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

        $request->validate([
            'payment_method' => 'required|string|in:prepaid,cod,cash_on_delivery,instapay',
            'payment_reference' => 'nullable|string|max:255',
            'variant_id' => 'nullable|exists:variants,id',
            'quantity' => 'nullable|integer|min:1|max:99',
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

        // An explicit variant_id checks out that single variant; otherwise every line in the cart is ordered.
        $lines = $this->resolveLines($request);

        if ($lines === null) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty or contains items that need to be re-selected.');
        }

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
            'items' => $lines->values()->map(fn (array $line, int $i) => $this->quoteItem($line['variant'], $line['quantity'], $runId, $i))->all(),
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
        $itemTotal = (float) $lines->sum(fn (array $line) => $line['variant']->price * $line['quantity']);
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
                'variant_sku' => $lines->map(fn (array $line) => $line['variant']->sku.' x'.$line['quantity'])->implode(', '),
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
            $lines
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

            $settings = PricingSetting::current();
            $calculator = app(PricingCalculator::class);

            foreach ($lines as $line) {
                /** @var Variant $variant */
                $variant = $line['variant'];
                $product = $variant->product;
                $quantity = $line['quantity'];

                [$artistMinProfit, $artistMaxProfit] = $product->effectiveArtistProfitRange();

                // Use every priced sibling variant so the artist-profit interpolation
                // matches RepriceProductVariants (a lone variant would always get min profit).
                $printCost = $variant->print_cost !== null ? (float) $variant->print_cost : 100.00;
                $costs = $product->variants()
                    ->whereNotNull('print_cost')
                    ->pluck('print_cost', 'id')
                    ->map(fn ($cost) => (float) $cost)
                    ->all();
                $costs[$variant->id] = $printCost;

                $breakdown = $calculator->calculate(
                    costsByVariantId: $costs,
                    artistMinProfit: $artistMinProfit,
                    artistMaxProfit: $artistMaxProfit,
                    platformPercent: (float) $settings->platform_percent,
                    platformFixedFeePerItem: (float) $settings->platform_fixed_fee_per_item,
                    roundingStep: (float) $settings->rounding_step
                )[$variant->id] ?? null;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'artist_id' => $product->user_id,
                    'quantity' => $quantity,
                    // The customer (and Printlet COD collection) was charged $variant->price; snapshot exactly that.
                    'unit_price' => (float) $variant->price,
                    'print_cost' => $breakdown ? $breakdown->printCost : $printCost,
                    'artist_profit' => $breakdown ? $breakdown->artistProfit : 0.00,
                    'platform_cut' => $breakdown ? $breakdown->platformCut : 0.00,
                    'platform_fixed_fee' => $breakdown ? $breakdown->platformFixedFee : (float) $settings->platform_fixed_fee_per_item,
                    'customizations' => [
                        'variant_id' => $variant->id,
                        'size' => $variant->size,
                        'color' => $variant->color,
                        'sku' => $variant->sku,
                    ],
                ]);
            }

            return $order;
        });

        // Observer handles order confirmation email automatically
        Cart::clear();

        $successMessage = match ($order->payment_method) {
            'instapay' => 'Order #'.$order->id.' placed successfully! Please complete your Instapay transfer below.',
            'cod' => 'Order #'.$order->id.' placed successfully! Our team will contact you to verify your order before delivery.',
            default => 'Order placed successfully! Order status: '.$order->printlet_status,
        };

        return redirect()->route('order.confirmation', $order)
            ->with('success', $successMessage);
    }

    /**
     * Resolve the variants + quantities to order, or null when there is nothing valid to order.
     *
     * @return Collection<int, array{variant: Variant, quantity: int}>|null
     */
    protected function resolveLines(Request $request): ?Collection
    {
        if ($request->filled('variant_id')) {
            $variant = Variant::with('product')->findOrFail($request->variant_id);

            if (! $variant->product?->is_available) {
                return null;
            }

            return collect([['variant' => $variant, 'quantity' => (int) ($request->quantity ?? 1)]]);
        }

        $items = Cart::items();

        if ($items->isEmpty() || $items->contains(fn (array $item) => $item['variant'] === null)) {
            return null;
        }

        return $items->map(fn (array $item) => [
            'variant' => $item['variant']->setRelation('product', $item['product']),
            'quantity' => $item['quantity'],
        ])->values();
    }

    /**
     * Build one Printlet quote line for a variant.
     */
    protected function quoteItem(Variant $variant, int $quantity, string $runId, int $index): array
    {
        $product = $variant->product;

        return [
            'merchant_line_id' => 'line-'.$runId.'-'.$index,
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
        ];
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
