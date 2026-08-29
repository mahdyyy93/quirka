<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['customer.user', 'items.product'])
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
            'status' => ['required', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $order->update(['status' => $request->status]);

        return response()->json($order->fresh(['customer.user', 'items.product']));
    }
}
