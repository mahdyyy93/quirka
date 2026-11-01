<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gallery-border pb-6">
            <div>
                <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold block mb-1">Artist Financial Hub</span>
                <h1 class="font-serif text-3xl font-normal text-gallery-charcoal tracking-tight">Artist Earnings &amp; Wallet</h1>
                <p class="text-sm text-gallery-muted mt-1">Track your art print profits, escrow clearance windows, and manual payouts from Quirka.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 bg-gallery-cream text-gallery-charcoal text-xs font-medium rounded-full border border-gallery-border">
                    Currency: EGP (Egyptian Pound)
                </span>
            </div>
        </div>

        {{-- Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            {{-- Pending Balance Card --}}
            <div class="bg-white rounded-xl border border-gallery-border p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Pending Balance</span>
                    <span class="p-2 bg-amber-50 rounded-md text-amber-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold text-gallery-charcoal">
                        <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                        {{ number_format($wallet->pending_balance ?? 0.00, 2) }}
                    </div>
                    <p class="text-xs text-gallery-muted mt-2 leading-relaxed">
                        Earnings from delivered orders currently in the 10–14 day clearance window or awaiting manual transfer.
                    </p>
                </div>
            </div>

            {{-- Eligible for Payout Card --}}
            <div class="bg-white rounded-xl border border-gallery-border p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Eligible for Payout</span>
                    <span class="p-2 bg-emerald-50 rounded-md text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold text-gallery-accent">
                        <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                        {{ number_format($eligibleBalance, 2) }}
                    </div>
                    <p class="text-xs text-gallery-muted mt-2 leading-relaxed">
                        Funds that have completed the 10–14 day holding period. Transferred manually by Quirka admin.
                    </p>
                </div>
            </div>

            {{-- Lifetime Paid Out Card --}}
            <div class="bg-white rounded-xl border border-gallery-border p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Total Paid Out</span>
                    <span class="p-2 bg-gallery-cream rounded-md text-gallery-charcoal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold text-gallery-charcoal">
                        <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                        {{ number_format($wallet->total_paid_out ?? 0.00, 2) }}
                    </div>
                    <p class="text-xs text-gallery-muted mt-2 leading-relaxed">
                        Lifetime payouts successfully transferred to your Instapay, Vodafone Cash, or bank account.
                    </p>
                </div>
            </div>
        </div>

        {{-- Payout Method Settings --}}
        <div class="bg-white rounded-xl border border-gallery-border p-6 sm:p-8 shadow-sm">
            <div class="border-b border-gallery-border pb-4 mb-6">
                <h3 class="font-medium text-lg text-gallery-charcoal">Payout Destination</h3>
                <p class="text-xs text-gallery-muted mt-1">Specify how Quirka should transfer your profits when releasing funds.</p>
            </div>

            @if (session()->has('payout_message'))
                <div class="mb-6 p-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                    {{ session('payout_message') }}
                </div>
            @endif

            <form wire:submit.prevent="savePayoutDetails" class="space-y-6">
                <div>
                    <label class="block text-xs uppercase tracking-wider font-semibold text-gallery-charcoal mb-2">Preferred Transfer Method</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center gap-3 p-3.5 rounded-lg border cursor-pointer transition-colors {{ $payout_method === 'instapay' ? 'border-gallery-accent bg-gallery-cream/40 font-medium' : 'border-gallery-border hover:bg-gallery-cream/20' }}">
                            <input type="radio" wire:model.live="payout_method" value="instapay" class="text-gallery-accent focus:ring-gallery-accent">
                            <span class="text-sm text-gallery-charcoal">Instapay (Recommended)</span>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-lg border cursor-pointer transition-colors {{ $payout_method === 'vodafone_cash' ? 'border-gallery-accent bg-gallery-cream/40 font-medium' : 'border-gallery-border hover:bg-gallery-cream/20' }}">
                            <input type="radio" wire:model.live="payout_method" value="vodafone_cash" class="text-gallery-accent focus:ring-gallery-accent">
                            <span class="text-sm text-gallery-charcoal">Vodafone Cash</span>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-lg border cursor-pointer transition-colors {{ $payout_method === 'bank_transfer' ? 'border-gallery-accent bg-gallery-cream/40 font-medium' : 'border-gallery-border hover:bg-gallery-cream/20' }}">
                            <input type="radio" wire:model.live="payout_method" value="bank_transfer" class="text-gallery-accent focus:ring-gallery-accent">
                            <span class="text-sm text-gallery-charcoal">Bank Account (IBAN)</span>
                        </label>
                    </div>
                </div>

                @if($payout_method === 'instapay')
                    <div class="space-y-1">
                        <label class="block text-xs uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Instapay Username / Address / Mobile</label>
                        <input
                            type="text"
                            wire:model="instapay_handle"
                            placeholder="e.g. yourname@instapay or 010xxxxxxxx"
                            class="w-full sm:max-w-md bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3.5 py-2.5 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                        >
                        @error('instapay_handle') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                @elseif($payout_method === 'vodafone_cash')
                    <div class="space-y-1">
                        <label class="block text-xs uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Vodafone Cash Mobile Number</label>
                        <input
                            type="text"
                            wire:model="phone"
                            placeholder="010xxxxxxxx"
                            class="w-full sm:max-w-md bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3.5 py-2.5 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                        >
                        @error('phone') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                @elseif($payout_method === 'bank_transfer')
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Bank Name</label>
                            <input type="text" wire:model="bank_name" placeholder="e.g. CIB, NBE, QNB" class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3.5 py-2.5 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent">
                            @error('bank_name') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">Account Holder Full Name</label>
                            <input type="text" wire:model="account_holder" class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3.5 py-2.5 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent">
                            @error('account_holder') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs uppercase tracking-wider font-semibold text-gallery-charcoal mb-1">IBAN</label>
                            <input type="text" wire:model="iban" placeholder="EG..." class="w-full bg-white border border-gallery-border rounded-md text-sm text-gallery-charcoal px-3.5 py-2.5 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent">
                            @error('iban') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif

                <div>
                    <button
                        type="submit"
                        class="bg-gallery-accent hover:bg-gallery-accent-hover text-white text-xs uppercase tracking-wider font-semibold px-6 py-2.5 rounded-md shadow-sm transition-colors"
                    >
                        Save Payout Details
                    </button>
                </div>
            </form>
        </div>

        {{-- Micro-Ledger Transactions Table --}}
        <div class="bg-white rounded-xl border border-gallery-border overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gallery-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-medium text-lg text-gallery-charcoal">Transaction Micro-Ledger</h3>
                    <p class="text-xs text-gallery-muted mt-0.5">Immutable audit trail of your order cuts, escrow holds, and manual transfers.</p>
                </div>

                {{-- Status Filter --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gallery-muted font-medium">Filter:</span>
                    <select wire:model.live="filter_status" class="bg-white border border-gallery-border rounded-md text-xs text-gallery-charcoal px-3 py-1.5 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent">
                        <option value="all">All Records</option>
                        <option value="pending">Pending Escrow</option>
                        <option value="completed">Completed Transfers</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gallery-cream text-gallery-charcoal uppercase tracking-wider font-semibold text-[11px] border-b border-gallery-border">
                        <tr>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5">Type &amp; Description</th>
                            <th class="px-6 py-3.5">Reference</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Maturity Date</th>
                            <th class="px-6 py-3.5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gallery-border">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-gallery-cream/30 transition-colors">
                                <td class="px-6 py-4 text-gallery-muted whitespace-nowrap">
                                    {{ $tx->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gallery-charcoal">{{ $tx->description }}</div>
                                    @if($tx->transfer_reference)
                                        <div class="text-[11px] text-gallery-muted mt-0.5 font-mono">
                                            Transfer Ref: <span class="font-semibold text-gallery-charcoal">{{ $tx->transfer_reference }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($tx->reference_id)
                                        <span class="text-gallery-charcoal font-medium">Order #{{ $tx->reference_id }}</span>
                                    @else
                                        <span class="text-gallery-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($tx->status === 'pending')
                                        @if($tx->hold_until && $tx->hold_until->isPast())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Eligible for Payout
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                In Escrow Hold
                                            </span>
                                        @endif
                                    @elseif($tx->status === 'cleared' || $tx->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            Transferred / Paid
                                        </span>
                                    @elseif($tx->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-700 border border-red-200">
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gallery-muted whitespace-nowrap">
                                    @if($tx->hold_until)
                                        {{ $tx->hold_until->format('M d, Y') }}
                                        @if($tx->status === 'pending' && ! $tx->hold_until->isPast())
                                            <span class="text-[10px] text-amber-600 block">({{ $tx->hold_until->diffForHumans() }})</span>
                                        @endif
                                    @else
                                        <span>&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <span class="font-semibold text-sm {{ $tx->type === 'credit' ? 'text-emerald-700' : 'text-gallery-charcoal' }}">
                                        {{ $tx->type === 'credit' ? '+' : '-' }} EGP {{ number_format($tx->amount, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gallery-muted text-sm">
                                    No ledger transactions recorded yet. Once your artworks are ordered and delivered, your profit cuts will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->hasPages())
                <div class="p-4 border-t border-gallery-border bg-white">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
