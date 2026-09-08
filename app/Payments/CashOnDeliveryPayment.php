<?php

namespace App\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;

class CashOnDeliveryPayment implements PaymentMethodInterface
{
    public function process(Order $order): bool
    {
        // No gateway call needed — payment is collected on delivery.
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
