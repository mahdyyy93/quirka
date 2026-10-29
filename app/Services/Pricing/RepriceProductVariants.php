<?php

namespace App\Services\Pricing;

use App\Models\PricingSetting;
use App\Models\Product;

class RepriceProductVariants
{
    public function __construct(
        protected PricingCalculator $calculator
    ) {}

    /**
     * Recalculate and update the prices of all variants for a given product.
     *
     * @return int Number of variants updated
     */
    public function reprice(Product $product): int
    {
        $variants = $product->variants()->whereNotNull('print_cost')->get();
        if ($variants->isEmpty()) {
            return 0;
        }

        [$artistMinProfit, $artistMaxProfit] = $product->effectiveArtistProfitRange();
        $settings = PricingSetting::current();

        $costs = [];
        foreach ($variants as $variant) {
            $costs[$variant->id] = (float) $variant->print_cost;
        }

        $breakdowns = $this->calculator->calculate(
            costsByVariantId: $costs,
            artistMinProfit: $artistMinProfit,
            artistMaxProfit: $artistMaxProfit,
            platformPercent: (float) $settings->platform_percent,
            platformFixedFeePerItem: (float) $settings->platform_fixed_fee_per_item,
            roundingStep: (float) $settings->rounding_step
        );

        $updatedCount = 0;
        foreach ($variants as $variant) {
            if (isset($breakdowns[$variant->id])) {
                $variant->price = $breakdowns[$variant->id]->unitPrice;
                $variant->save();
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
