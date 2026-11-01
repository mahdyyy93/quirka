<?php

namespace App\Livewire\Artist;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class WalletDashboard extends Component
{
    use WithPagination;

    public string $payout_method = 'instapay';

    public string $instapay_handle = '';

    public string $phone = '';

    public string $bank_name = '';

    public string $iban = '';

    public string $account_holder = '';

    public string $filter_status = 'all';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $wallet = Wallet::forUser($user);
        $this->payout_method = $wallet->payout_method ?? 'instapay';

        $details = $wallet->payout_details ?? [];
        $this->instapay_handle = $details['instapay_handle'] ?? '';
        $this->phone = $details['phone'] ?? '';
        $this->bank_name = $details['bank_name'] ?? '';
        $this->iban = $details['iban'] ?? '';
        $this->account_holder = $details['account_holder'] ?? $user->name;
    }

    public function savePayoutDetails(): void
    {
        $this->validate([
            'payout_method' => ['required', 'in:instapay,vodafone_cash,bank_transfer'],
            'instapay_handle' => ['required_if:payout_method,instapay', 'nullable', 'string', 'max:100'],
            'phone' => ['required_if:payout_method,vodafone_cash', 'nullable', 'string', 'max:20'],
            'bank_name' => ['required_if:payout_method,bank_transfer', 'nullable', 'string', 'max:100'],
            'iban' => ['required_if:payout_method,bank_transfer', 'nullable', 'string', 'max:50'],
            'account_holder' => ['required_if:payout_method,bank_transfer', 'nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        if (! $user) {
            return;
        }

        $wallet = Wallet::forUser($user);
        $wallet->payout_method = $this->payout_method;
        $wallet->payout_details = [
            'instapay_handle' => $this->instapay_handle,
            'phone' => $this->phone,
            'bank_name' => $this->bank_name,
            'iban' => $this->iban,
            'account_holder' => $this->account_holder,
        ];
        $wallet->save();

        session()->flash('payout_message', 'Payout settings saved successfully! Quirka admin will use these details for manual transfers.');
    }

    public function render()
    {
        $user = Auth::user();
        $wallet = $user ? Wallet::forUser($user) : null;

        $eligibleBalance = 0.00;
        $transactions = collect();

        if ($wallet) {
            $eligibleBalance = (float) WalletTransaction::where('wallet_id', $wallet->id)
                ->where('status', 'pending')
                ->whereNotNull('hold_until')
                ->where('hold_until', '<=', now())
                ->sum('amount');

            $query = WalletTransaction::where('wallet_id', $wallet->id)
                ->latest();

            if ($this->filter_status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($this->filter_status === 'completed') {
                $query->whereIn('status', ['completed', 'cleared']);
            }

            $transactions = $query->paginate(15);
        }

        return view('livewire.artist.wallet-dashboard', [
            'wallet' => $wallet,
            'eligibleBalance' => $eligibleBalance,
            'transactions' => $transactions,
        ]);
    }
}
