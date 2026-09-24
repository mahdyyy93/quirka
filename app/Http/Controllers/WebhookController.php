<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Controller to handle incoming HTTP POST webhooks from the Printlet Fulfillment platform.
 * Listens for order state lifecycle changes (e.g., order.in_production, order.shipped, order.delivered).
 */
class WebhookController extends Controller
{
    /**
     * Handle incoming Printlet webhook request.
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify webhook HMAC signature and timestamp for authenticity and replay protection
        if (! $this->verifySignature($request)) {
            Log::warning('Printlet webhook signature/timestamp verification failed', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
            ]);

            return response()->json(['error' => 'Invalid or expired webhook signature'], 401);
        }

        $payload = $request->json()->all();
        $event = $payload['event'] ?? $payload['type'] ?? null;
        $eventId = $payload['id'] ?? $payload['event_id'] ?? null;
        $data = $payload['data'] ?? $payload['resource'] ?? $payload;

        Log::info('Received Printlet webhook event', [
            'event' => $event,
            'event_id' => $eventId,
            'printlet_order_id' => $data['id'] ?? $data['order_id'] ?? null,
        ]);

        if (! $event) {
            return response()->json(['message' => 'Ignored event payload without type'], 200);
        }

        // 2. Event Deduplication using atomic Cache lock (7-day TTL)
        if ($eventId) {
            $dedupKey = 'printlet_webhook_event:'.$eventId;
            if (! Cache::add($dedupKey, true, now()->addDays(7))) {
                Log::info('Printlet webhook event already processed (deduplicated)', [
                    'event_id' => $eventId,
                    'event' => $event,
                ]);

                return response()->json(['message' => 'Event already processed', 'event_id' => $eventId], 200);
            }
        }

        // Extract printlet order ID from payload data
        $printletOrderId = $data['id'] ?? $data['order_id'] ?? $data['resource_id'] ?? null;

        if (! $printletOrderId) {
            return response()->json(['message' => 'No order identifier found in event payload'], 200);
        }

        $order = Order::where('printlet_order_id', $printletOrderId)->first();

        if (! $order) {
            Log::warning('Printlet webhook received for unknown local order', [
                'printlet_order_id' => $printletOrderId,
                'event' => $event,
            ]);

            return response()->json(['message' => 'Order record not found locally'], 200);
        }

        // Process status transitions based on event type
        $this->processOrderEvent($order, $event, $data);

        return response()->json([
            'status' => 'success',
            'event' => $event,
            'order_id' => $order->id,
        ], 200);
    }

    /**
     * Verify incoming webhook request signature against stored secret.
     *
     * @return bool True if valid or if secret is unconfigured in non-production.
     */
    public function verifySignature(Request $request): bool
    {
        $secret = config('services.printlet.webhook_secret');

        // If no webhook secret is set in config, allow request in local environment only
        if (empty($secret)) {
            return app()->isLocal();
        }

        // Printlet sends signature in X-Printlet-Signature or X-Signature header
        $signature = $request->header('X-Printlet-Signature')
            ?? $request->header('X-Signature')
            ?? $request->header('Signature');

        if (empty($signature)) {
            // Allow unsigned webhook calls during local development testing
            return app()->isLocal();
        }

        // Reject requests with expired or future timestamp if timestamp header is provided (replay protection: 5 min window)
        $timestamp = $request->header('X-Printlet-Timestamp')
            ?? $request->header('X-Timestamp')
            ?? $request->header('Timestamp');

        if ($timestamp) {
            $timestampInt = is_numeric($timestamp) ? (int) $timestamp : strtotime($timestamp);
            if ($timestampInt && abs(time() - $timestampInt) > 300) {
                Log::warning('Printlet webhook timestamp rejected (replay window exceeded)', [
                    'timestamp' => $timestamp,
                    'current_time' => time(),
                ]);

                return false;
            }
        }

        $content = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $content, $secret);

        // Normalize signature format if prefixed with sha256=
        if (str_starts_with($signature, 'sha256=')) {
            $signature = substr($signature, 7);
        }

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Reconcile local Order state based on incoming Printlet webhook event type.
     */
    protected function processOrderEvent(Order $order, string $event, array $data): void
    {
        $statusMapping = [
            'order.pending_verification' => ['printlet_status' => 'pending_verification', 'status' => 'pending_verification'],
            'order.accepted' => ['printlet_status' => 'accepted', 'status' => 'processing', 'payment_status' => 'paid'],
            'order.in_production' => ['printlet_status' => 'in_production', 'status' => 'in_production'],
            'order.shipped' => ['printlet_status' => 'shipped', 'status' => 'shipped'],
            'order.delivered' => ['printlet_status' => 'delivered', 'status' => 'completed'],
            'order.cancelled' => ['printlet_status' => 'cancelled', 'status' => 'cancelled'],
            'order.payment_required' => ['printlet_status' => 'payment_required', 'status' => 'on_hold'],
        ];

        $updateData = [];

        if (isset($statusMapping[$event])) {
            $updateData = $statusMapping[$event];
        } elseif (isset($data['status'])) {
            $updateData['printlet_status'] = $data['status'];
        }

        if (! empty($updateData)) {
            $order->update($updateData);

            Log::info('Updated local order status from Printlet webhook', [
                'order_id' => $order->id,
                'event' => $event,
                'updates' => $updateData,
            ]);
        }
    }
}
