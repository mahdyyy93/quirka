<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentMethodInterface
{
    /**
     * Process payment for a given order.
     * Returns true on success, false on failure.
     */
    public function process(Order $order): bool;

    /**
     * Human-readable name shown in UI / receipts.
     */
    public function label(): string;

    /**
     * Unique key used in the database and routing (e.g. 'cash_on_delivery').
     */
    public function key(): string;
}
