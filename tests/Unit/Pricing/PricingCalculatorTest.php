<?php

namespace Tests\Unit\Pricing;

use App\Services\Pricing\PricingCalculator;
use PHPUnit\Framework\TestCase;

class PricingCalculatorTest extends TestCase
{
    protected PricingCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PricingCalculator;
    }

    public function test_single_variant_uses_min_profit(): void
    {
        $breakdowns = $this->calculator->calculate(
            costsByVariantId: [1 => 100.00],
            artistMinProfit: 20.00,
            artistMaxProfit: 80.00,
            platformPercent: 0.10,
            platformFixedFeePerItem: 10.00,
            roundingStep: 5.00
        );

        $this->assertCount(1, $breakdowns);
        $item = $breakdowns[1];

        $this->assertEquals(100.00, $item->printCost);
        $this->assertEquals(20.00, $item->artistProfit);
        $this->assertEquals(10.00, $item->platformFixedFee);
        // subtotal = 120, platform% = 12, fee = 10, total = 142 -> rounded up to 145 (+3)
        $this->assertEquals(145.00, $item->unitPrice);
        $this->assertEquals(25.00, $item->platformCut); // 12 + 10 + 3
        $this->assertEquals($item->unitPrice, $item->printCost + $item->artistProfit + $item->platformCut);
    }

    public function test_interpolates_profits_linearly_across_multiple_variants(): void
    {
        $breakdowns = $this->calculator->calculate(
            costsByVariantId: [
                'low' => 50.00,
                'mid' => 100.00,
                'high' => 150.00,
            ],
            artistMinProfit: 30.00,
            artistMaxProfit: 90.00,
            platformPercent: 0.10,
            platformFixedFeePerItem: 10.00,
            roundingStep: 5.00
        );

        $this->assertEquals(30.00, $breakdowns['low']->artistProfit);
        $this->assertEquals(60.00, $breakdowns['mid']->artistProfit);
        $this->assertEquals(90.00, $breakdowns['high']->artistProfit);

        foreach ($breakdowns as $item) {
            $sum = round($item->printCost + $item->artistProfit + $item->platformCut, 2);
            $this->assertEquals($item->unitPrice, $sum);
        }
    }

    public function test_when_min_equals_max_profit_all_variants_receive_that_profit(): void
    {
        $breakdowns = $this->calculator->calculate(
            costsByVariantId: [1 => 60.00, 2 => 120.00],
            artistMinProfit: 50.00,
            artistMaxProfit: 50.00,
            platformPercent: 0.15,
            platformFixedFeePerItem: 10.00,
            roundingStep: 5.00
        );

        $this->assertEquals(50.00, $breakdowns[1]->artistProfit);
        $this->assertEquals(50.00, $breakdowns[2]->artistProfit);
    }

    public function test_rounding_remainder_always_goes_to_platform_cut(): void
    {
        $breakdowns = $this->calculator->calculate(
            costsByVariantId: [1 => 101.33],
            artistMinProfit: 25.00,
            artistMaxProfit: 50.00,
            platformPercent: 0.10,
            platformFixedFeePerItem: 10.00,
            roundingStep: 5.00
        );

        $item = $breakdowns[1];
        $this->assertEquals(0, fmod($item->unitPrice, 5.00));
        $this->assertEquals($item->unitPrice, round($item->printCost + $item->artistProfit + $item->platformCut, 2));
    }
}
