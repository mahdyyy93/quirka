<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Supported status update keys:
     * - 'confirmed': Initial order confirmation
     * - 'payment_verified': Instapay or COD payment verification
     * - 'shipped': Order handed to courier and dispatched
     * - 'delivered': Order successfully delivered to doorstep
     */
    public function __construct(
        public readonly Order $order,
        public readonly string $statusKey,
        public readonly ?string $trackingNumber = null,
        public readonly ?string $customMessage = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->statusKey) {
            'confirmed' => "Order #{$this->order->id} Confirmed — Thank you!",
            'payment_verified' => "Payment Verified for Order #{$this->order->id} — We're preparing your order!",
            'shipped' => "Great news! Order #{$this->order->id} has been shipped 🚚",
            'delivered' => "Order #{$this->order->id} has been delivered 🎉",
            default => "Update on your Order #{$this->order->id}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-status-updated',
            with: [
                'order' => $this->order,
                'statusKey' => $this->statusKey,
                'trackingNumber' => $this->trackingNumber,
                'customMessage' => $this->customMessage,
                'headline' => $this->getHeadline(),
                'statusBadge' => $this->getStatusBadge(),
                'subtext' => $this->getSubtext(),
            ],
        );
    }

    protected function getHeadline(): string
    {
        return match ($this->statusKey) {
            'confirmed' => "Your order is confirmed! 🎉",
            'payment_verified' => "Payment Verified & Approved! ⚡",
            'shipped' => "Your package is on its way! 🚚",
            'delivered' => "Your package has arrived! 🎁",
            default => "Order status updated",
        };
    }

    protected function getStatusBadge(): string
    {
        return match ($this->statusKey) {
            'confirmed' => "Order Placed & Confirmed",
            'payment_verified' => "Payment Confirmed — In Production",
            'shipped' => "Dispatched / Out for Delivery",
            'delivered' => "Successfully Delivered",
            default => ucfirst(str_replace('_', ' ', $this->statusKey)),
        };
    }

    protected function getSubtext(): string
    {
        return match ($this->statusKey) {
            'confirmed' => "We've received your order and our fulfillment team has been notified. We will update you as soon as your items are printed and dispatched.",
            'payment_verified' => "Your payment has been successfully confirmed. Your customized print-on-demand items are now being printed with premium quality.",
            'shipped' => "Your order has been printed, packaged, and handed over to the courier. It is now on its way to your delivery address.",
            'delivered' => "Our courier has marked your order as delivered. We hope you love your new items! If you need anything, we're always here to help.",
            default => "Your order status has been updated. You can check the current progress anytime in your account.",
        };
    }

    public function attachments(): array
    {
        return [];
    }
}
