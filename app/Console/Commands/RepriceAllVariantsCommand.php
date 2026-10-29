<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Variant;
use App\Services\Pricing\RepriceProductVariants;
use Illuminate\Console\Command;

class RepriceAllVariantsCommand extends Command
{
    protected $signature = 'pricing:reprice-all';

    protected $description = 'Compute default print_cost for any unpriced variants and reprice all products.';

    public function handle(RepriceProductVariants $repricer): int
    {
        $this->info('Checking variants without print_cost...');

        // For variants missing print_cost, default to 100.00
        $updatedMissing = Variant::whereNull('print_cost')->update(['print_cost' => 100.00]);
        if ($updatedMissing > 0) {
            $this->info("Updated {$updatedMissing} variants with fallback print_cost.");
        }

        $products = Product::with('variants')->get();
        $totalVariantsRepriced = 0;

        foreach ($products as $product) {
            $count = $repricer->reprice($product);
            $totalVariantsRepriced += $count;
        }

        $this->info("Repricing complete! Updated prices for {$totalVariantsRepriced} variants across {$products->count()} products.");

        return self::SUCCESS;
    }
}
