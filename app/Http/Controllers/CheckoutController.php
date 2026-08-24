<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function show(): View|RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $productIds = array_keys($cart);
        $products = Product::with('supplier')
            ->whereIn('id', $productIds)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        $paymentMethods = $this->paymentService->available();
        $customer = Auth::user()->customer;

        return view('checkout', compact('cart', 'products', 'paymentMethods', 'customer'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_method' => 'required|string',
            'address'        => 'required|string|max:500',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        $customer = Auth::user()->customer;

        if (!$customer) {
            return redirect()->route('login')->with('error', 'Please log in to place an order.');
        }

        // Update shipping address if provided
        $customer->update(['address' => $request->address]);

        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        if ($products->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'No available products in your cart.');
        }

        $total = $products->sum(fn ($p) => $p->price * ($cart[$p->id]['quantity'] ?? 1));

        $order = DB::transaction(function () use ($request, $customer, $products, $cart, $total) {
            $order = Order::create([
                'customer_id'    => $customer->id,
                'status'         => 'pending',
                'total_price'    => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'notes'          => $request->notes,
            ]);

            foreach ($products as $product) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $product->id,
                    'quantity'       => $cart[$product->id]['quantity'] ?? 1,
                    'unit_price'     => $product->price,
                    'customizations' => $product->customizations,
                ]);
            }

            return $order;
        });

        // Process payment through the registered driver
        $this->paymentService->process($request->payment_method, $order);

        session()->forget('cart');

        return redirect()->route('order.confirmation', $order)->with('success', 'Order placed successfully!');
    }

    public function confirmation(Order $order): View
    {
        // Ensure customers can only view their own orders
        abort_if($order->customer_id !== Auth::user()->customer?->id, 403);

        $order->load('items.product', 'customer');

        return view('order-confirmation', compact('order'));
    }
}
