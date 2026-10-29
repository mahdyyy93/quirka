<?php

namespace App\Http\Requests;

use App\Models\PricingSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArtistPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $settings = PricingSetting::current();
        $floor = (float) $settings->artist_profit_floor;
        $ceiling = (float) $settings->artist_profit_ceiling;

        return [
            'artist_min_profit' => ['required', 'numeric', "min:{$floor}", "max:{$ceiling}"],
            'artist_max_profit' => ['required', 'numeric', 'gte:artist_min_profit', "max:{$ceiling}"],
        ];
    }
}
