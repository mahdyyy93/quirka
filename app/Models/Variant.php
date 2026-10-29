<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model representing a physical poster variant (e.g. Size & Frame Color combination).
 * Maps internal store SKUs and pricing to external Printlet Fulfillment API variant metadata.
 */
class Variant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'size',
        'color',
        'price',
        'print_cost',
        'sku',
        'printlet_variant_id',
        'printlet_variant_revision',
        'printlet_print_area_id',
        'printlet_print_area_revision',
        'printlet_technique_id',
        'printlet_technique_revision',
        'placement_preset',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'print_cost' => 'decimal:2',
        'printlet_variant_revision' => 'integer',
        'printlet_print_area_revision' => 'integer',
        'printlet_technique_revision' => 'integer',
    ];

    /**
     * Get the parent Product that owns this variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
