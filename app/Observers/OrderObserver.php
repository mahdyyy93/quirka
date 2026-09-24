<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * Triggers initial order confirmation email.
     */
    public function created(Order $order): void
    {
        $this->dispatchStatusEmail($order, 'confirmed');
    }

    /**
     * Handle the Order "updated" event.
     * Listens to changes in 'status' and 'payment_status' to dispatch milestone emails.
     */
    public function updated(Order $order): void
    {
        // 1. Payment Verification milestone (COD verified or Instapay confirmed)
        if ($order->wasChanged('payment_status') && in_array($order->payment_status, ['paid', 'verified'])) {
            $this->dispatchStatusEmail($order, 'payment_verified');

            return;
        }

        // 2. Shipping milestone
        if ($order->wasChanged('status') && ($order->status === OrderStatus::SHIPPED || $order->status?->value === 'shipped')) {
            $this->dispatchStatusEmail($order, 'shipped');

            return;
        }

        // 3. Delivery milestone
        $statusValue = $order->status instanceof \BackedEnum ? $order->status->value : (string) $order->status;
        if ($order->wasChanged('status') && in_array($statusValue, ['delivered', 'completed'])) {
            $this->dispatchStatusEmail($order, 'delivered');

            return;
        }
    }

    /**
     * Helper to load relationships and safely queue the mailable.
     */
    protected function dispatchStatusEmail(Order $order, string $statusKey): void
    {
        try {
            $order->loadMissing(['customer.user', 'items.product']);

            $email = $order->customer?->email ?? $order->customer?->user?->email;

            if (empty($email)) {
                Log::warning("OrderObserver: No recipient email found for Order #{$order->id} ({$statusKey})");

                return;
            }

            Mail::to($email)->queue(new OrderStatusUpdated($order, $statusKey));

            Log::info("OrderObserver: Queued '{$statusKey}' email for Order #{$order->id} to {$email}");
        } catch (\Throwable $e) {
            Log::error("OrderObserver: Failed queuing '{$statusKey}' email for Order #{$order->id}: ".$e->getMessage());
        }
    }
}
