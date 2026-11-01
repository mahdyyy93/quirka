<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'balance',
        'pending_balance',
        'total_paid_out',
        'currency',
        'payout_method',
        'payout_details',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_paid_out' => 'decimal:2',
        'payout_details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get or create the unique Quirka Platform Wallet.
     */
    public static function platform(): self
    {
        return static::firstOrCreate(
            ['type' => 'platform', 'user_id' => null],
            [
                'balance' => 0.00,
                'pending_balance' => 0.00,
                'total_paid_out' => 0.00,
                'currency' => 'EGP',
            ]
        );
    }

    /**
     * Get or create the wallet for a given artist/user.
     */
    public static function forUser(User|int $user): self
    {
        $userId = $user instanceof User ? $user->id : $user;

        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'type' => 'artist',
                'balance' => 0.00,
                'pending_balance' => 0.00,
                'total_paid_out' => 0.00,
                'currency' => 'EGP',
            ]
        );
    }

    public function isPlatform(): bool
    {
        return $this->type === 'platform';
    }

    public function isArtist(): bool
    {
        return $this->type === 'artist';
    }
}
