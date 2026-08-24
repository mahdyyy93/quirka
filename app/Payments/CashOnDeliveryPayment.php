<?php

namespace App\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;

class CashOnDeliveryPayment implements PaymentMethodInterface
{
    public function process(Order $order): bool
    {
        // COD requires no gateway call — order is accepted as-is.
        // Payment is collected upon delivery.
        $order->update(['payment_status' => 'pending_collection']);

        return true;
    }

    public function label(): string
    {
        return 'Cash on Delivery';
    }

    public function key(): string
    {
        return 'cash_on_delivery';
    }
}
