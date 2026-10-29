<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'artist_id',
        'quantity',
        'unit_price',
        'print_cost',
        'artist_profit',
        'platform_cut',
        'platform_fixed_fee',
        'customizations',
    ];

    protected $casts = [
        'customizations' => 'array',
        'unit_price' => 'decimal:2',
        'print_cost' => 'decimal:2',
        'artist_profit' => 'decimal:2',
        'platform_cut' => 'decimal:2',
        'platform_fixed_fee' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
}
