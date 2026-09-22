<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PrintletApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller handling store administrator actions on fulfillment orders.
 * Supports manual Cash on Delivery (COD) verification against the Printlet Fulfillment API.
 */
class AdminOrderController extends Controller
{
    public function __construct(
        protected PrintletApiService $printletApi
    ) {}

    /**
     * Manually verify a pending Cash on Delivery (COD) order.
     * Triggers the Printlet API endpoint POST /orders/{id}/cod-verification.
     */
    public function verifyCod(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        // Ensure the order uses Cash on Delivery
        if ($order->payment_method !== 'cod') {
            $message = 'Cannot verify non-COD order (Payment method: '.$order->payment_method.').';
            if ($request->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        // Check if order has a valid Printlet reference ID
        if (! $order->printlet_order_id) {
            $message = 'Order is missing an external Printlet order ID.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        // Validate optional verification reference string
        $validated = $request->validate([
            'reference' => 'nullable|string|max:255',
        ]);

        $reference = $validated['reference'] ?? ('ADMIN-VERIFY-'.time());

        try {
            Log::info('Initiating manual COD order verification by admin', [
                'order_id' => $order->id,
                'printlet_order_id' => $order->printlet_order_id,
                'reference' => $reference,
            ]);

            // Trigger Printlet API COD verification request
            $response = $this->printletApi->verifyCodOrder($order->printlet_order_id, $reference);

            $newPrintletStatus = $response['status'] ?? 'accepted';

            // Update local database order status
            $order->update([
                'printlet_status' => $newPrintletStatus,
                'cod_reference' => $reference,
                'status' => in_array($newPrintletStatus, ['accepted', 'verified']) ? 'processing' : $order->status,
                'payment_status' => in_array($newPrintletStatus, ['accepted', 'verified']) ? 'verified' : $order->payment_status,
            ]);

            Log::info('COD order verified successfully', [
                'order_id' => $order->id,
                'new_status' => $newPrintletStatus,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'COD order verified successfully.',
                    'order' => $order->fresh(),
                    'printlet_response' => $response,
                ]);
            }

            return redirect()->back()->with('success', 'COD order verified successfully! New Printlet status: '.$newPrintletStatus);
        } catch (\Exception $e) {
            Log::error('Failed to verify COD order on Printlet API', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Printlet COD verification failed: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Printlet COD verification failed: '.$e->getMessage());
        }
    }

    /**
     * Manually verify an Instapay bank transfer order after confirming received funds.
     */
    public function verifyInstapay(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        if ($order->payment_method !== 'instapay') {
            $message = 'Cannot verify non-Instapay order (Payment method: '.$order->payment_method.').';
            if ($request->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        Log::info('Instapay order payment verified by admin', [
            'order_id' => $order->id,
            'payment_reference' => $order->payment_reference,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Instapay payment verified successfully.',
                'order' => $order->fresh(),
            ]);
        }

        return redirect()->back()->with('success', 'Instapay payment verified successfully!');
    }
}
