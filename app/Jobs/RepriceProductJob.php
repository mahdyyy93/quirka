<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\Pricing\RepriceProductVariants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RepriceProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $productId
    ) {}

    public function handle(RepriceProductVariants $repricer): void
    {
        $product = Product::find($this->productId);
        if ($product) {
            $repricer->reprice($product);
        }
    }
}
