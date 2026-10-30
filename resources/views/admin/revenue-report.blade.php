<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Revenue & Party Totals Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Party Totals Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-gray-400">
                    <p class="text-sm font-medium text-gray-500">Gross Item Sales</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totals->gross_sales ?? 0, 2) }} EGP</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-slate-500">
                    <p class="text-sm font-medium text-gray-500">Total Printlet Cost</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totals->total_print_cost ?? 0, 2) }} EGP</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-emerald-500">
                    <p class="text-sm font-medium text-emerald-600">Total Artist Profit</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($totals->total_artist_profit ?? 0, 2) }} EGP</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <p class="text-sm font-medium text-blue-600">Total Platform Cut</p>
                    <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($totals->total_platform_cut ?? 0, 2) }} EGP</p>
                </div>
            </div>

            <!-- Artist Payouts Table -->
            <div class="bg-white overflow-hidden shadow-sm rounded-none border border-stone-300 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Artist Payout Breakdown</h3>
                <div class="overflow-x-auto border border-stone-300 rounded-none">
                    <table class="min-w-full divide-y divide-stone-300">
                        <thead class="bg-stone-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-stone-700 uppercase tracking-wider">Artist</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-stone-700 uppercase tracking-wider">Items Sold</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-stone-700 uppercase tracking-wider">Total Profit Due</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-stone-200">
                            @forelse ($artistPayouts as $artist)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $artist->artist_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-stone-600">{{ $artist->items_sold }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-emerald-600">{{ number_format($artist->total_profit, 2) }} EGP</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-stone-500">No snapshot sales recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
