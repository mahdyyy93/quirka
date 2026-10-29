<?php

namespace App\Observers;

use App\Jobs\RepriceProductJob;
use App\Models\ArtistPricingProfile;

class ArtistPricingProfileObserver
{
    public function saved(ArtistPricingProfile $profile): void
    {
        $products = $profile->user?->products;
        if ($products) {
            foreach ($products as $product) {
                // If the product doesn't have an override, it depends on the artist's profile
                if ($product->artist_min_profit === null || $product->artist_max_profit === null) {
                    RepriceProductJob::dispatch($product->id);
                }
            }
        }
    }
}
