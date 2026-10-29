<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-2">Artist Pricing Strategy</h2>
        <p class="text-sm text-gray-600 mb-6">
            Configure your minimum profit (for the smallest/cheapest variant) and maximum profit (for the largest variant). 
            Variants in between will scale their profit automatically.
        </p>

        @if (session()->has('message'))
            <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Minimum Profit (EGP)</label>
                    <span class="text-xs text-gray-500">Floor: {{ number_format($floor, 2) }} EGP</span>
                    <input type="number" step="0.5" wire:model.live="artist_min_profit" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('artist_min_profit') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Maximum Profit (EGP)</label>
                    <span class="text-xs text-gray-500">Ceiling: {{ number_format($ceiling, 2) }} EGP</span>
                    <input type="number" step="0.5" wire:model.live="artist_max_profit" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('artist_max_profit') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Pricing Settings
                </button>
            </div>
        </form>

        <div class="mt-8 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Live Price Preview by Size</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Variant Size</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Print Cost</th>
                            <th class="px-4 py-2 text-right font-medium text-emerald-600">Your Profit</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Platform Cut</th>
                            <th class="px-4 py-2 text-right font-bold text-gray-900">Retail Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($breakdowns as $size => $breakdown)
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $size }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ number_format($breakdown->printCost, 2) }} EGP</td>
                                <td class="px-4 py-2 text-right font-semibold text-emerald-600">{{ number_format($breakdown->artistProfit, 2) }} EGP</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ number_format($breakdown->platformCut, 2) }} EGP</td>
                                <td class="px-4 py-2 text-right font-bold text-gray-900">{{ number_format($breakdown->unitPrice, 2) }} EGP</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
