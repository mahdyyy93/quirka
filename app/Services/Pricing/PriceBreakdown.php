<?php

namespace App\Services\Pricing;

class PriceBreakdown
{
    public function __construct(
        public readonly float $printCost,
        public readonly float $artistProfit,
        public readonly float $platformCut,
        public readonly float $platformFixedFee,
        public readonly float $unitPrice,
    ) {}

    public function toArray(): array
    {
        return [
            'print_cost' => $this->printCost,
            'artist_profit' => $this->artistProfit,
            'platform_cut' => $this->platformCut,
            'platform_fixed_fee' => $this->platformFixedFee,
            'unit_price' => $this->unitPrice,
        ];
    }
}
