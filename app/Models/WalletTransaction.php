<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'category',
        'amount',
        'balance_before',
        'balance_after',
        'pending_balance_before',
        'pending_balance_after',
        'status',
        'reference_type',
        'reference_id',
        'transfer_reference',
        'transferred_by',
        'hold_until',
        'cleared_at',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'pending_balance_before' => 'decimal:2',
        'pending_balance_after' => 'decimal:2',
        'hold_until' => 'datetime',
        'cleared_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    /**
     * Scope for pending transactions that have passed the holding window.
     */
    public function scopeEligibleForPayout(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->whereNotNull('hold_until')
            ->where('hold_until', '<=', now());
    }
}
