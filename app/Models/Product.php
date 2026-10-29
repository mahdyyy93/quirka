<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'sku',
        'price',
        'artist_min_profit',
        'artist_max_profit',
        'is_available',
        'customizations',
        'description',
        'printlet_artwork_id',
        'printlet_artwork_revision',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'customizations' => 'array',
        'price' => 'decimal:2',
        'artist_min_profit' => 'decimal:2',
        'artist_max_profit' => 'decimal:2',
        'printlet_artwork_revision' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Product $product) {
            if (empty($product->slug) && ! empty($product->title)) {
                $product->slug = static::generateUniqueSlug($product->title);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('title') && empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->title, $product->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Resolves the effective [artist_min_profit, artist_max_profit] range for this product:
     * Product overrides take precedence; otherwise falls back to the artist's profile,
     * or the global system floor/ceiling limits.
     *
     * @return array{0: float, 1: float}
     */
    public function effectiveArtistProfitRange(): array
    {
        if ($this->artist_min_profit !== null && $this->artist_max_profit !== null) {
            return [(float) $this->artist_min_profit, (float) $this->artist_max_profit];
        }

        $artistProfile = $this->user?->artistPricingProfile;
        if ($artistProfile) {
            return [(float) $artistProfile->artist_min_profit, (float) $artistProfile->artist_max_profit];
        }

        $settings = PricingSetting::current();

        return [(float) $settings->artist_profit_floor, (float) $settings->artist_profit_ceiling];
    }
}
