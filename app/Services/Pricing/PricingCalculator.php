<?php

namespace App\Services\Pricing;

class PricingCalculator
{
    /**
     * Calculate price breakdowns for an array of variant costs.
     * All monetary computations are performed in integer piastres (cents)
     * to avoid floating-point inaccuracies.
     *
     * @param  array<int|string, float>  $costsByVariantId  Variant ID => print_cost
     * @param  float  $artistMinProfit  Artist minimum profit
     * @param  float  $artistMaxProfit  Artist maximum profit
     * @param  float  $platformPercent  Platform percentage (e.g. 0.10 for 10%)
     * @param  float  $platformFixedFeePerItem  Fixed fee included per item
     * @param  float  $roundingStep  Step to round unit prices up to (e.g. 5.00)
     * @return array<int|string, PriceBreakdown> Variant ID => PriceBreakdown
     */
    public function calculate(
        array $costsByVariantId,
        float $artistMinProfit,
        float $artistMaxProfit,
        float $platformPercent,
        float $platformFixedFeePerItem,
        float $roundingStep = 5.00
    ): array {
        if (empty($costsByVariantId)) {
            return [];
        }

        $minProfitPiastres = (int) round($artistMinProfit * 100);
        $maxProfitPiastres = (int) round($artistMaxProfit * 100);
        $fixedFeePiastres = (int) round($platformFixedFeePerItem * 100);
        $roundingStepPiastres = max(1, (int) round($roundingStep * 100));

        $costs = array_values($costsByVariantId);
        $minCost = min($costs);
        $maxCost = max($costs);
        $costSpread = $maxCost - $minCost;

        $results = [];

        foreach ($costsByVariantId as $key => $costFloat) {
            $costPiastres = (int) round($costFloat * 100);

            // Interpolation factor t: 0 if single variant or all costs identical
            $t = ($costSpread > 0.00001) ? ($costFloat - $minCost) / $costSpread : 0.0;
            $t = max(0.0, min(1.0, $t));

            $profitDiff = $maxProfitPiastres - $minProfitPiastres;
            $artistProfitPiastres = (int) round($minProfitPiastres + ($t * $profitDiff));

            $subtotalPiastres = $costPiastres + $artistProfitPiastres;

            // Platform percentage cut
            $percentageCutPiastres = (int) round($subtotalPiastres * $platformPercent);

            // Platform cut before rounding (percentage + fixed fee per item)
            $basePlatformCutPiastres = $percentageCutPiastres + $fixedFeePiastres;

            // Raw total before rounding up
            $rawTotalPiastres = $subtotalPiastres + $basePlatformCutPiastres;

            // Round up to nearest rounding_step
            $remainder = $rawTotalPiastres % $roundingStepPiastres;
            $roundingBonus = ($remainder === 0) ? 0 : ($roundingStepPiastres - $remainder);
            $unitPricePiastres = $rawTotalPiastres + $roundingBonus;

            // Rounding bonus goes to platform cut so: printCost + artistProfit + platformCut == unitPrice
            $finalPlatformCutPiastres = $basePlatformCutPiastres + $roundingBonus;

            $results[$key] = new PriceBreakdown(
                printCost: $costPiastres / 100,
                artistProfit: $artistProfitPiastres / 100,
                platformCut: $finalPlatformCutPiastres / 100,
                platformFixedFee: $fixedFeePiastres / 100,
                unitPrice: $unitPricePiastres / 100
            );
        }

        return $results;
    }
}
