<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Top Notification --}}
        @if (session()->has('success'))
            <div class="p-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gallery-border pb-6">
            <div>
                <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold block mb-1">Treasury &amp; Settlement Control</span>
                <h1 class="font-serif text-3xl font-normal text-gallery-charcoal tracking-tight">Wallets &amp; Artist Payouts</h1>
                <p class="text-sm text-gallery-muted mt-1">Manage artist pending balances, manual transfers via Instapay, and Printlet COD remittances.</p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('pricing-limits.index') }}"
                    class="bg-white border border-gallery-border hover:border-gallery-charcoal text-gallery-charcoal px-3.5 py-2 rounded-md text-xs font-medium transition-colors shadow-sm inline-flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 text-gallery-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Pricing &amp; Clearance Settings</span>
                </a>
                <button
                    type="button"
                    wire:click="$set('showRemittanceModal', true)"
                    class="bg-white border border-gallery-border hover:border-gallery-charcoal text-gallery-charcoal px-4 py-2 rounded-md text-xs font-medium transition-colors shadow-sm"
                >
                    + Record Printlet Remittance
                </button>
            </div>
        </div>

        {{-- Treasury Overview KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            {{-- Quirka Platform Realized Revenue --}}
            <div class="bg-white rounded-xl border border-gallery-border p-5 shadow-sm">
                <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold block">Quirka Platform Profit</span>
                <div class="mt-2 text-2xl font-semibold text-gallery-accent">
                    <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                    {{ number_format($platformWallet->balance ?? 0.00, 2) }}
                </div>
                <p class="text-xs text-gallery-muted mt-1">Net platform fee + fixed fee collected from delivered orders.</p>
            </div>

            {{-- Total Escrowed Artist Funds --}}
            <div class="bg-white rounded-xl border border-gallery-border p-5 shadow-sm">
                <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold block">Total Artist Pending</span>
                <div class="mt-2 text-2xl font-semibold text-gallery-charcoal">
                    <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                    {{ number_format($totalPendingArtistFunds, 2) }}
                </div>
                <p class="text-xs text-gallery-muted mt-1">All artist profit cuts currently held in clearance escrow.</p>
            </div>

            {{-- Eligible for Manual Payout --}}
            <div class="bg-white rounded-xl border border-gallery-border p-5 shadow-sm">
                <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold block">Eligible for Transfer</span>
                <div class="mt-2 text-2xl font-semibold text-amber-700">
                    <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                    {{ number_format($totalEligibleForPayout, 2) }}
                </div>
                <p class="text-xs text-gallery-muted mt-1">Matured orders past the 10–14 day clearance window.</p>
            </div>

            {{-- Printlet COD Remittances Received --}}
            <div class="bg-white rounded-xl border border-gallery-border p-5 shadow-sm">
                <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold block">Printlet Remittances</span>
                <div class="mt-2 text-2xl font-semibold text-gallery-charcoal">
                    <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                    {{ number_format($totalPrintletRemittances, 2) }}
                </div>
                <p class="text-xs text-gallery-muted mt-1">Total COD funds remitted by Printlet to Quirka bank.</p>
            </div>
        </div>

        {{-- Artist Wallets & Payout Actions Table --}}
        <div class="bg-white rounded-xl border border-gallery-border overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gallery-border">
                <h3 class="font-medium text-lg text-gallery-charcoal">Artist Payout Roster</h3>
                <p class="text-xs text-gallery-muted mt-0.5">Review artist balances, payment accounts, and record manual transfer receipts.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gallery-cream text-gallery-charcoal uppercase tracking-wider font-semibold text-[11px] border-b border-gallery-border">
                        <tr>
                            <th class="px-6 py-3.5">Artist</th>
                            <th class="px-6 py-3.5">Payout Method &amp; Account</th>
                            <th class="px-6 py-3.5 text-right">Pending Balance</th>
                            <th class="px-6 py-3.5 text-right">Lifetime Paid Out</th>
                            <th class="px-6 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gallery-border">
                        @forelse($artistWallets as $w)
                            @php
                                $details = $w->payout_details ?? [];
                                $methodLabel = match($w->payout_method) {
                                    'instapay' => 'Instapay: ' . ($details['instapay_handle'] ?? 'Not set'),
                                    'vodafone_cash' => 'Vodafone Cash: ' . ($details['phone'] ?? 'Not set'),
                                    'bank_transfer' => 'Bank: ' . ($details['bank_name'] ?? '') . ' / IBAN: ' . ($details['iban'] ?? ''),
                                    default => 'Not set',
                                };
                            @endphp
                            <tr class="hover:bg-gallery-cream/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-sm text-gallery-charcoal">{{ $w->user?->name ?? 'Artist #'.$w->user_id }}</div>
                                    <div class="text-xs text-gallery-muted">{{ $w->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block px-2.5 py-1 rounded bg-gallery-cream text-gallery-charcoal text-xs font-mono border border-gallery-border">
                                        {{ $methodLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="font-semibold text-sm text-gallery-charcoal">
                                        EGP {{ number_format($w->pending_balance, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-xs text-gallery-muted">
                                        EGP {{ number_format($w->total_paid_out, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($w->pending_balance > 0)
                                        <button
                                            type="button"
                                            wire:click="openTransferModal({{ $w->id }})"
                                            class="bg-gallery-accent hover:bg-gallery-accent-hover text-white text-xs font-medium px-3.5 py-1.5 rounded-md transition-colors shadow-sm"
                                        >
                                            Record Transfer
                                        </button>
                                    @else
                                        <span class="text-xs text-gallery-muted">&mdash; All settled &mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gallery-muted text-sm">
                                    No active artist wallets with pending earnings yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($artistWallets->hasPages())
                <div class="p-4 border-t border-gallery-border bg-white">
                    {{ $artistWallets->links() }}
                </div>
            @endif
        </div>

        {{-- Printlet Remittances History --}}
        <div class="bg-white rounded-xl border border-gallery-border overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gallery-border">
                <h3 class="font-medium text-lg text-gallery-charcoal">Recent Printlet COD Remittances</h3>
                <p class="text-xs text-gallery-muted mt-0.5">Audit log of customer COD cash settlements remitted by Printlet into Quirka's bank account.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gallery-cream text-gallery-charcoal uppercase tracking-wider font-semibold text-[11px] border-b border-gallery-border">
                        <tr>
                            <th class="px-6 py-3.5">Remittance Date</th>
                            <th class="px-6 py-3.5">Printlet Reference / Deposit ID</th>
                            <th class="px-6 py-3.5">Recorded By</th>
                            <th class="px-6 py-3.5">Notes</th>
                            <th class="px-6 py-3.5 text-right">Amount Deposited</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gallery-border">
                        @forelse($recentRemittances as $remit)
                            <tr class="hover:bg-gallery-cream/30 transition-colors">
                                <td class="px-6 py-4 text-gallery-charcoal font-medium whitespace-nowrap">
                                    {{ $remit->remittance_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 font-mono text-gallery-charcoal whitespace-nowrap">
                                    {{ $remit->reference_number }}
                                </td>
                                <td class="px-6 py-4 text-gallery-muted whitespace-nowrap">
                                    {{ $remit->recorder?->name ?? 'Admin' }}
                                </td>
                                <td class="px-6 py-4 text-gallery-muted">
                                    {{ $remit->notes ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-sm text-emerald-700 whitespace-nowrap">
                                    + EGP {{ number_format($remit->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gallery-muted text-sm">
                                    No Printlet COD remittances recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal: Record Manual Artist Transfer --}}
        @if($showTransferModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gallery-charcoal/50 backdrop-blur-sm">
                <div class="bg-white rounded-xl border border-gallery-border shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-gallery-border pb-4">
                        <div>
                            <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Execute Payout</span>
                            <h3 class="font-serif text-xl text-gallery-charcoal">Record Transfer to {{ $selectedArtistName }}</h3>
                        </div>
                        <button type="button" wire:click="$set('showTransferModal', false)" class="text-gallery-muted hover:text-gallery-charcoal text-xl">&times;</button>
                    </div>

                    <form wire:submit.prevent="submitManualTransfer" class="space-y-4 text-xs">
                        <div class="p-3.5 bg-gallery-cream rounded-md border border-gallery-border space-y-1">
                            <span class="text-gallery-muted block">Artist Payout Destination:</span>
                            <span class="font-mono font-medium text-gallery-charcoal block">{{ $payoutDetailsSummary }}</span>
                        </div>

                        <div>
                            <label class="block uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Transfer Amount (EGP)</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="transferAmount"
                                class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3 py-2 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                            >
                            @error('transferAmount') <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Transfer Reference (e.g. Instapay Ref / Receipt #)</label>
                            <input
                                type="text"
                                wire:model="transferReference"
                                placeholder="IPA-XXXXXXXX"
                                class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3 py-2 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                            >
                            @error('transferReference') <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Internal Notes (Optional)</label>
                            <textarea
                                wire:model="transferNotes"
                                rows="2"
                                placeholder="e.g. Cleared batch for October orders"
                                class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3 py-2 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                            ></textarea>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-3 border-t border-gallery-border">
                            <button
                                type="button"
                                wire:click="$set('showTransferModal', false)"
                                class="px-4 py-2 text-gallery-muted hover:text-gallery-charcoal"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="bg-gallery-accent hover:bg-gallery-accent-hover text-white px-5 py-2 rounded-md font-semibold text-xs uppercase tracking-wider transition-colors shadow-sm"
                            >
                                Confirm Payout Transfer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Modal: Record Printlet Remittance --}}
        @if($showRemittanceModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gallery-charcoal/50 backdrop-blur-sm">
                <div class="bg-white rounded-xl border border-gallery-border shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-gallery-border pb-4">
                        <div>
                            <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Printlet COD Remittance</span>
                            <h3 class="font-serif text-xl text-gallery-charcoal">Record Deposit from Printlet</h3>
                        </div>
                        <button type="button" wire:click="$set('showRemittanceModal', false)" class="text-gallery-muted hover:text-gallery-charcoal text-xl">&times;</button>
                    </div>

                    <form wire:submit.prevent="submitPrintletRemittance" class="space-y-4 text-xs">
                        <div>
                            <label class="block uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Remittance Amount (EGP)</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="remittanceAmount"
                                placeholder="5000.00"
                                class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3 py-2 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                            >
                            @error('remittanceAmount') <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Deposit Date</label>
                            <input
                                type="date"
                                wire:model="remittanceDate"
                                class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3 py-2 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                            >
                            @error('remittanceDate') <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Printlet Settlement Ref / Bank Deposit #</label>
                            <input
                                type="text"
                                wire:model="remittanceReference"
                                placeholder="PRNT-SETTLE-2026-001"
                                class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3 py-2 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                            >
                            @error('remittanceReference') <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Notes (Optional)</label>
                            <textarea
                                wire:model="remittanceNotes"
                                rows="2"
                                placeholder="e.g. Weekly Cairo COD settlement"
                                class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3 py-2 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                            ></textarea>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-3 border-t border-gallery-border">
                            <button
                                type="button"
                                wire:click="$set('showRemittanceModal', false)"
                                class="px-4 py-2 text-gallery-muted hover:text-gallery-charcoal"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="bg-gallery-accent hover:bg-gallery-accent-hover text-white px-5 py-2 rounded-md font-semibold text-xs uppercase tracking-wider transition-colors shadow-sm"
                            >
                                Record Deposit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>
