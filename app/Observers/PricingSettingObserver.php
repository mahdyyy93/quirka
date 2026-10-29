<?php

namespace App\Observers;

use App\Jobs\RepriceAllProductsJob;
use App\Models\PricingSetting;

class PricingSettingObserver
{
    public function saved(PricingSetting $setting): void
    {
        RepriceAllProductsJob::dispatch();
    }
}
