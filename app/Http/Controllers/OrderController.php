<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /** Customer-facing: render the My Orders page (JS will fetch JSON). */
    public function myOrders(): View
    {
        return view('my-orders');
    }

    /** Customer-facing JSON: paginated orders for the authenticated customer. */
    public function index(Request $request): JsonResponse
    {
        $orders = Auth::user()->customer?->orders()
            ->with(['items.product'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    /** Customer-facing: show a single order detail page (ownership-guarded). */
    public function show(Order $order): View
    {
        abort_if($order->customer_id !== Auth::user()->customer?->id, 403);

        $order->load('items.product', 'customer');

        return view('order-confirmation', compact('order'));
    }

    /** @deprecated Use CheckoutController::store() instead. Kept for API backwards compatibility. */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'customizations' => 'nullable|array',
        ]);

        if (! Auth::check() || ! Auth::user()->customer) {
            return response()->json(['error' => 'You must be logged in as a customer to place an order.'], 403);
        }

        $product = Product::findOrFail($request->product_id);

        if (! $product->is_available) {
            return response()->json(['error' => 'Product is not available'], 400);
        }

        DB::transaction(function () use ($request, $product) {
            $customer = Auth::user()->customer;

            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'pending',
                'total_price' => $product->price * $request->quantity,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'unit_price' => $product->price,
                'customizations' => $request->customizations,
            ]);
        });

        return response()->json(['message' => 'Order placed successfully!']);
    }
}
