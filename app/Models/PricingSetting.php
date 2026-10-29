<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_profit_floor',
        'artist_profit_ceiling',
        'platform_percent',
        'platform_fixed_fee_per_item',
        'rounding_step',
    ];

    protected $casts = [
        'artist_profit_floor' => 'decimal:2',
        'artist_profit_ceiling' => 'decimal:2',
        'platform_percent' => 'decimal:4',
        'platform_fixed_fee_per_item' => 'decimal:2',
        'rounding_step' => 'decimal:2',
    ];

    /**
     * Retrieve the singleton settings record, creating it with defaults if not present.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'artist_profit_floor' => 20.00,
            'artist_profit_ceiling' => 200.00,
            'platform_percent' => 0.1000,
            'platform_fixed_fee_per_item' => 10.00,
            'rounding_step' => 5.00,
        ]);
    }
}
