<?php

namespace App\Livewire\Admin;

use App\Jobs\RepriceAllProductsJob;
use App\Models\PricingSetting;
use Livewire\Component;

class PricingLimits extends Component
{
    public float $artist_profit_floor;

    public float $artist_profit_ceiling;

    public float $platform_percent;

    public float $platform_fixed_fee_per_item;

    public float $rounding_step;

    public int $payout_holding_days = 14;

    public function mount(): void
    {
        $settings = PricingSetting::current();
        $this->artist_profit_floor = (float) $settings->artist_profit_floor;
        $this->artist_profit_ceiling = (float) $settings->artist_profit_ceiling;
        $this->platform_percent = (float) $settings->platform_percent * 100; // Display as percentage e.g. 10%
        $this->platform_fixed_fee_per_item = (float) $settings->platform_fixed_fee_per_item;
        $this->rounding_step = (float) $settings->rounding_step;
        $this->payout_holding_days = (int) ($settings->payout_holding_days ?? 14);
    }

    public function save(): void
    {
        $this->validate([
            'artist_profit_floor' => ['required', 'numeric', 'min:0'],
            'artist_profit_ceiling' => ['required', 'numeric', 'gte:artist_profit_floor'],
            'platform_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'platform_fixed_fee_per_item' => ['required', 'numeric', 'min:0'],
            'rounding_step' => ['required', 'numeric', 'min:0.01'],
            'payout_holding_days' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $settings = PricingSetting::current();
        $settings->update([
            'artist_profit_floor' => $this->artist_profit_floor,
            'artist_profit_ceiling' => $this->artist_profit_ceiling,
            'platform_percent' => $this->platform_percent / 100,
            'platform_fixed_fee_per_item' => $this->platform_fixed_fee_per_item,
            'rounding_step' => $this->rounding_step,
            'payout_holding_days' => $this->payout_holding_days,
        ]);

        RepriceAllProductsJob::dispatch();

        session()->flash('message', 'Platform pricing limits updated and products queued for repricing!');
    }

    public function render()
    {
        return view('livewire.admin.pricing-limits');
    }
}
