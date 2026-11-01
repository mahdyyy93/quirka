<?php

namespace App\Livewire\Admin;

use App\Models\PrintletRemittance;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class WalletManager extends Component
{
    use WithPagination;

    // Manual Transfer Form State
    public bool $showTransferModal = false;

    public ?int $selectedWalletId = null;

    public ?string $selectedArtistName = null;

    public float $transferAmount = 0.00;

    public string $transferReference = '';

    public string $payoutMethod = 'instapay';

    public ?string $payoutDetailsSummary = '';

    public ?string $transferNotes = '';

    // Printlet Remittance Form State
    public bool $showRemittanceModal = false;

    public float $remittanceAmount = 0.00;

    public string $remittanceDate = '';

    public string $remittanceReference = '';

    public ?string $remittanceNotes = '';

    public function mount(): void
    {
        $this->remittanceDate = now()->toDateString();
    }

    public function openTransferModal(int $walletId): void
    {
        $wallet = Wallet::with('user')->findOrFail($walletId);
        $this->selectedWalletId = $wallet->id;
        $this->selectedArtistName = $wallet->user?->name ?? 'Artist #'.$wallet->user_id;

        // Calculate eligible amount
        $eligible = (float) WalletTransaction::where('wallet_id', $wallet->id)
            ->where('status', 'pending')
            ->whereNotNull('hold_until')
            ->where('hold_until', '<=', now())
            ->sum('amount');

        $this->transferAmount = $eligible > 0 ? $eligible : (float) $wallet->pending_balance;
        $this->payoutMethod = $wallet->payout_method ?? 'instapay';

        $details = $wallet->payout_details ?? [];
        if ($this->payoutMethod === 'instapay') {
            $this->payoutDetailsSummary = 'Instapay: '.($details['instapay_handle'] ?? 'Not set');
        } elseif ($this->payoutMethod === 'vodafone_cash') {
            $this->payoutDetailsSummary = 'Vodafone Cash: '.($details['phone'] ?? 'Not set');
        } elseif ($this->payoutMethod === 'bank_transfer') {
            $this->payoutDetailsSummary = 'Bank: '.($details['bank_name'] ?? '').' / IBAN: '.($details['iban'] ?? '');
        } else {
            $this->payoutDetailsSummary = 'Not specified';
        }

        $this->transferReference = '';
        $this->transferNotes = '';
        $this->showTransferModal = true;
    }

    public function submitManualTransfer(): void
    {
        $this->validate([
            'selectedWalletId' => ['required', 'exists:wallets,id'],
            'transferAmount' => ['required', 'numeric', 'min:0.01'],
            'transferReference' => ['required', 'string', 'max:100'],
        ]);

        $wallet = Wallet::findOrFail($this->selectedWalletId);
        $admin = Auth::user();

        try {
            app(WalletService::class)->recordManualArtistPayout(
                wallet: $wallet,
                amount: (float) $this->transferAmount,
                transferReference: $this->transferReference,
                admin: $admin,
                notes: $this->transferNotes
            );

            $this->showTransferModal = false;
            session()->flash('success', "Successfully recorded manual payout of EGP {$this->transferAmount} to {$this->selectedArtistName} (Ref: {$this->transferReference}).");
        } catch (\Exception $e) {
            $this->addError('transferAmount', $e->getMessage());
        }
    }

    public function submitPrintletRemittance(): void
    {
        $this->validate([
            'remittanceAmount' => ['required', 'numeric', 'min:0.01'],
            'remittanceDate' => ['required', 'date'],
            'remittanceReference' => ['required', 'string', 'max:100'],
        ]);

        $admin = Auth::user();

        app(WalletService::class)->recordPrintletRemittance(
            amount: (float) $this->remittanceAmount,
            remittanceDate: $this->remittanceDate,
            referenceNumber: $this->remittanceReference,
            admin: $admin,
            notes: $this->remittanceNotes
        );

        $this->showRemittanceModal = false;
        $this->remittanceAmount = 0.00;
        $this->remittanceReference = '';
        $this->remittanceNotes = '';

        session()->flash('success', 'Printlet COD remittance successfully recorded.');
    }

    public function render()
    {
        $platformWallet = Wallet::platform();

        $totalPendingArtistFunds = (float) Wallet::where('type', 'artist')->sum('pending_balance');
        $totalPaidOutToArtists = (float) Wallet::where('type', 'artist')->sum('total_paid_out');
        $totalPrintletRemittances = (float) PrintletRemittance::sum('amount');

        // Total eligible for payout across all artists
        $totalEligibleForPayout = (float) WalletTransaction::where('status', 'pending')
            ->whereNotNull('hold_until')
            ->where('hold_until', '<=', now())
            ->whereHas('wallet', fn ($q) => $q->where('type', 'artist'))
            ->sum('amount');

        $artistWallets = Wallet::with('user')
            ->where('type', 'artist')
            ->where(function ($q) {
                $q->where('pending_balance', '>', 0)
                    ->orWhere('total_paid_out', '>', 0);
            })
            ->latest('updated_at')
            ->paginate(15);

        $recentRemittances = PrintletRemittance::with('recorder')
            ->latest('remittance_date')
            ->take(10)
            ->get();

        return view('livewire.admin.wallet-manager', [
            'platformWallet' => $platformWallet,
            'totalPendingArtistFunds' => $totalPendingArtistFunds,
            'totalPaidOutToArtists' => $totalPaidOutToArtists,
            'totalEligibleForPayout' => $totalEligibleForPayout,
            'totalPrintletRemittances' => $totalPrintletRemittances,
            'artistWallets' => $artistWallets,
            'recentRemittances' => $recentRemittances,
        ]);
    }
}
