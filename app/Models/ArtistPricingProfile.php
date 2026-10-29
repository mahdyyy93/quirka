<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistPricingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'artist_min_profit',
        'artist_max_profit',
    ];

    protected $casts = [
        'artist_min_profit' => 'decimal:2',
        'artist_max_profit' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
