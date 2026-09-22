<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([\App\Observers\OrderObserver::class])]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
        'total_price',
        'shipping_amount',
        'payment_method',
        'payment_status',
        'notes',
        'payment_reference',
        'printlet_order_id',
        'printlet_quote_id',
        'printlet_status',
        'cod_reference',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
