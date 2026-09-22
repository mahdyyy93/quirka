<?php

namespace App\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;

class InstapayPayment implements PaymentMethodInterface
{
    public function process(Order $order): bool
    {
        // Instapay payments are initiated directly by the customer and verified manually
        // by the store admin against the provided transaction reference or sender name.
        return true;
    }

    public function label(): string
    {
        return 'Instapay Direct Transfer';
    }

    public function key(): string
    {
        return 'instapay';
    }
}
