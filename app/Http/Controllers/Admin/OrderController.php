<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Order::query();

        if ($user && ! $user->can('orders.view-all')) {
            // Artists only see orders containing their products, with only their items loaded
            $query->whereHas('items.product', fn ($q) => $q->where('user_id', $user->id))
                ->with([
                    'customer.user',
                    'items' => fn ($iq) => $iq->whereHas('product', fn ($pq) => $pq->where('user_id', $user->id))->with('product'),
                ]);
        } else {
            $query->with(['customer.user', 'items.product']);
        }

        $orders = $query
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->where(function ($query) use ($s) {
                    $query->where('id', 'like', "%{$s}%")
                        ->orWhereHas('customer', function ($cq) use ($s) {
                            $cq->where('name', 'like', "%{$s}%")
                                ->orWhere('email', 'like', "%{$s}%");
                        });
                });
            })
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $order->update(['status' => $request->status]);

        return response()->json($order->fresh(['customer.user', 'items.product']));
    }
}
