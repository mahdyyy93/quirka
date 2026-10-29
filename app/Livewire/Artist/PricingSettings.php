<?php

namespace App\Livewire\Artist;

use App\Models\ArtistPricingProfile;
use App\Models\PricingSetting;
use App\Services\Pricing\PricingCalculator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PricingSettings extends Component
{
    public float $artist_min_profit = 20.00;

    public float $artist_max_profit = 80.00;

    public float $floor = 20.00;

    public float $ceiling = 200.00;

    public float $platform_percent = 0.10;

    public float $platform_fixed_fee = 10.00;

    public function mount(): void
    {
        $settings = PricingSetting::current();
        $this->floor = (float) $settings->artist_profit_floor;
        $this->ceiling = (float) $settings->artist_profit_ceiling;
        $this->platform_percent = (float) $settings->platform_percent;
        $this->platform_fixed_fee = (float) $settings->platform_fixed_fee_per_item;

        $user = Auth::user();
        if ($user && $user->artistPricingProfile) {
            $this->artist_min_profit = (float) $user->artistPricingProfile->artist_min_profit;
            $this->artist_max_profit = (float) $user->artistPricingProfile->artist_max_profit;
        } else {
            $this->artist_min_profit = $this->floor;
            $this->artist_max_profit = min($this->ceiling, $this->floor + 60.00);
        }
    }

    public function updated($propertyName): void
    {
        if ($this->artist_max_profit < $this->artist_min_profit) {
            $this->artist_max_profit = $this->artist_min_profit;
        }
    }

    public function save(): void
    {
        $this->validate([
            'artist_min_profit' => ['required', 'numeric', "min:{$this->floor}", "max:{$this->ceiling}"],
            'artist_max_profit' => ['required', 'numeric', 'gte:artist_min_profit', "max:{$this->ceiling}"],
        ]);

        $user = Auth::user();
        if (! $user) {
            return;
        }

        ArtistPricingProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'artist_min_profit' => $this->artist_min_profit,
                'artist_max_profit' => $this->artist_max_profit,
            ]
        );

        session()->flash('message', 'Pricing settings saved successfully!');
    }

    public function render()
    {
        $calculator = app(PricingCalculator::class);

        // Preview mock sample poster sizes with illustrative print costs
        $sampleSizes = [
            'A4 (21x30cm)' => 80.00,
            'A3 (30x42cm)' => 120.00,
            'A2 (42x59cm)' => 180.00,
            'A1 (59x84cm)' => 260.00,
        ];

        $breakdowns = $calculator->calculate(
            costsByVariantId: $sampleSizes,
            artistMinProfit: $this->artist_min_profit,
            artistMaxProfit: $this->artist_max_profit,
            platformPercent: $this->platform_percent,
            platformFixedFeePerItem: $this->platform_fixed_fee,
            roundingStep: 5.00
        );

        return view('livewire.artist.pricing-settings', [
            'breakdowns' => $breakdowns,
        ]);
    }
}
