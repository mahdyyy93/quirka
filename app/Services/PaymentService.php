<?php

namespace App\Services;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;
use InvalidArgumentException;

class PaymentService
{
    /** @var array<string, PaymentMethodInterface> */
    protected array $drivers = [];

    public function register(PaymentMethodInterface $driver): void
    {
        $this->drivers[$driver->key()] = $driver;
    }

    public function driver(string $key): PaymentMethodInterface
    {
        if (!isset($this->drivers[$key])) {
            throw new InvalidArgumentException("Payment driver [{$key}] is not registered.");
        }

        return $this->drivers[$key];
    }

    /** @return array<string, string> key => label */
    public function available(): array
    {
        return array_map(fn ($d) => $d->label(), $this->drivers);
    }

    public function process(string $key, Order $order): bool
    {
        return $this->driver($key)->process($order);
    }
}
