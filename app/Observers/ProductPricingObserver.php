<?php

namespace App\Observers;

use App\Jobs\RepriceProductJob;
use App\Models\Product;

class ProductPricingObserver
{
    public function updated(Product $product): void
    {
        if ($product->isDirty(['artist_min_profit', 'artist_max_profit'])) {
            RepriceProductJob::dispatch($product->id);
        }
    }
}
