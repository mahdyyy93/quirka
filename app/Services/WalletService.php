<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PricingSetting;
use App\Models\PrintletRemittance;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WalletService
{
    /**
     * Hold artist earnings and platform cut in escrow when an order is delivered.
     */
    public function holdOrderEarnings(Order $order): void
    {
        if (! $order->delivered_at) {
            $order->updateQuietly(['delivered_at' => now()]);
        }

        $holdingDays = (int) (PricingSetting::current()->payout_holding_days ?? 14);
        $holdUntil = $order->delivered_at->copy()->addDays($holdingDays);

        $order->loadMissing('items');

        // 1. Process Artist Cuts
        $itemsByArtist = $order->items->whereNotNull('artist_id')->groupBy('artist_id');

        foreach ($itemsByArtist as $artistId => $items) {
            $totalArtistCut = (float) $items->sum(function ($item) {
                return (float) ($item->artist_profit ?? 0.00) * (int) ($item->quantity ?? 1);
            });

            if ($totalArtistCut <= 0.00) {
                continue;
            }

            DB::transaction(function () use ($artistId, $order, $totalArtistCut, $holdUntil, $holdingDays) {
                $wallet = Wallet::forUser($artistId);
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

                // Idempotency: Do not duplicate hold for this order
                $alreadyHeld = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('reference_type', Order::class)
                    ->where('reference_id', $order->id)
                    ->where('category', 'order_escrow_hold')
                    ->exists();

                if ($alreadyHeld) {
                    return;
                }

                $beforePending = (float) $wallet->pending_balance;
                $beforeBal = (float) $wallet->balance;

                $wallet->pending_balance = $beforePending + $totalArtistCut;
                $wallet->save();

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'category' => 'order_escrow_hold',
                    'amount' => $totalArtistCut,
                    'balance_before' => $beforeBal,
                    'balance_after' => $beforeBal,
                    'pending_balance_before' => $beforePending,
                    'pending_balance_after' => $wallet->pending_balance,
                    'status' => 'pending',
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'hold_until' => $holdUntil,
                    'description' => "Earnings hold for Order #{$order->id} ({$holdingDays}-day delivery escrow)",
                    'metadata' => [
                        'order_id' => $order->id,
                        'holding_days' => $holdingDays,
                        'items_count' => $items->count(),
                    ],
                ]);

                Log::info("WalletService: Escrowed {$totalArtistCut} EGP for Artist #{$artistId} on Order #{$order->id} (Clears: {$holdUntil->toDateString()})");
            });
        }

        // 2. Process Quirka Platform Revenue Cut
        $totalPlatformCut = (float) $order->items->sum(function ($item) {
            $cut = (float) ($item->platform_cut ?? 0.00);
            $fee = (float) ($item->platform_fixed_fee ?? 0.00);

            return ($cut + $fee) * (int) ($item->quantity ?? 1);
        });

        if ($totalPlatformCut > 0.00) {
            DB::transaction(function () use ($order, $totalPlatformCut) {
                $platformWallet = Wallet::platform();
                $platformWallet = Wallet::where('id', $platformWallet->id)->lockForUpdate()->first();

                $alreadyRecorded = WalletTransaction::where('wallet_id', $platformWallet->id)
                    ->where('reference_type', Order::class)
                    ->where('reference_id', $order->id)
                    ->where('category', 'platform_fee_collected')
                    ->exists();

                if ($alreadyRecorded) {
                    return;
                }

                $beforeBal = (float) $platformWallet->balance;
                $platformWallet->balance = $beforeBal + $totalPlatformCut;
                $platformWallet->save();

                WalletTransaction::create([
                    'wallet_id' => $platformWallet->id,
                    'type' => 'credit',
                    'category' => 'platform_fee_collected',
                    'amount' => $totalPlatformCut,
                    'balance_before' => $beforeBal,
                    'balance_after' => $platformWallet->balance,
                    'pending_balance_before' => (float) $platformWallet->pending_balance,
                    'pending_balance_after' => (float) $platformWallet->pending_balance,
                    'status' => 'completed',
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'description' => "Platform net revenue collected from delivered Order #{$order->id}",
                    'metadata' => ['order_id' => $order->id],
                ]);

                Log::info("WalletService: Recorded {$totalPlatformCut} EGP platform revenue on Order #{$order->id}");
            });
        }
    }

    /**
     * Record a manual payout transfer from Quirka to an artist (via Instapay, Vodafone Cash, or Bank).
     * Deducts the amount from the artist's pending_balance and records it as total_paid_out.
     */
    public function recordManualArtistPayout(
        Wallet $wallet,
        float $amount,
        string $transferReference,
        User $admin,
        array $orderIds = [],
        ?string $notes = null
    ): WalletTransaction {
        if ($amount <= 0.00) {
            throw new InvalidArgumentException('Payout amount must be greater than zero.');
        }

        return DB::transaction(function () use ($wallet, $amount, $transferReference, $admin, $orderIds, $notes) {
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            if ((float) $lockedWallet->pending_balance < $amount) {
                throw new InvalidArgumentException('Requested payout amount exceeds the artist pending balance.');
            }

            $beforePending = (float) $lockedWallet->pending_balance;
            $beforeBal = (float) $lockedWallet->balance;
            $beforePaidOut = (float) $lockedWallet->total_paid_out;

            $lockedWallet->pending_balance = $beforePending - $amount;
            $lockedWallet->total_paid_out = $beforePaidOut + $amount;
            $lockedWallet->save();

            // Mark matched pending hold transactions as cleared
            $pendingQuery = WalletTransaction::where('wallet_id', $lockedWallet->id)
                ->where('category', 'order_escrow_hold')
                ->where('status', 'pending');

            if (! empty($orderIds)) {
                $pendingQuery->whereIn('reference_id', $orderIds);
            }

            $pendingQuery->update([
                'status' => 'cleared',
                'cleared_at' => now(),
                'transfer_reference' => $transferReference,
            ]);

            $payoutTx = WalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'type' => 'debit',
                'category' => 'manual_artist_payout',
                'amount' => $amount,
                'balance_before' => $beforeBal,
                'balance_after' => $beforeBal,
                'pending_balance_before' => $beforePending,
                'pending_balance_after' => $lockedWallet->pending_balance,
                'status' => 'completed',
                'transfer_reference' => $transferReference,
                'transferred_by' => $admin->id,
                'cleared_at' => now(),
                'description' => 'Manual payout transfer'.($lockedWallet->payout_method ? " via {$lockedWallet->payout_method}" : '')." (Ref: {$transferReference})",
                'metadata' => [
                    'notes' => $notes,
                    'order_ids' => $orderIds,
                    'admin_name' => $admin->name,
                ],
            ]);

            Log::info("WalletService: Admin #{$admin->id} recorded {$amount} EGP payout to Artist #{$lockedWallet->user_id} (Ref: {$transferReference})");

            return $payoutTx;
        });
    }

    /**
     * Cancel escrow hold if an order is returned or cancelled during the holding period.
     */
    public function cancelOrderEscrow(Order $order): void
    {
        $pendingHolds = WalletTransaction::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('category', 'order_escrow_hold')
            ->where('status', 'pending')
            ->get();

        foreach ($pendingHolds as $hold) {
            DB::transaction(function () use ($hold, $order) {
                $wallet = Wallet::where('id', $hold->wallet_id)->lockForUpdate()->first();

                $beforePending = (float) $wallet->pending_balance;
                $wallet->pending_balance = max(0.00, $beforePending - (float) $hold->amount);
                $wallet->save();

                $hold->update([
                    'status' => 'cancelled',
                ]);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'category' => 'order_escrow_cancel',
                    'amount' => $hold->amount,
                    'balance_before' => (float) $wallet->balance,
                    'balance_after' => (float) $wallet->balance,
                    'pending_balance_before' => $beforePending,
                    'pending_balance_after' => $wallet->pending_balance,
                    'status' => 'cancelled',
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'description' => "Escrow hold cancelled due to cancellation of Order #{$order->id}",
                ]);

                Log::info("WalletService: Cancelled escrow of {$hold->amount} EGP for Wallet #{$wallet->id} on cancelled Order #{$order->id}");
            });
        }
    }

    /**
     * Record a Printlet COD remittance deposited into Quirka's bank account.
     */
    public function recordPrintletRemittance(
        float $amount,
        string $remittanceDate,
        string $referenceNumber,
        User $admin,
        ?string $notes = null
    ): PrintletRemittance {
        if ($amount <= 0.00) {
            throw new InvalidArgumentException('Remittance amount must be greater than zero.');
        }

        return PrintletRemittance::create([
            'amount' => $amount,
            'remittance_date' => $remittanceDate,
            'reference_number' => $referenceNumber,
            'status' => 'received',
            'notes' => $notes,
            'recorded_by' => $admin->id,
        ]);
    }
}
