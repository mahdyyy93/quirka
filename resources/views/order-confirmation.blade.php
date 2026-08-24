<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Order Confirmed</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Success banner --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="text-5xl mb-3">✅</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-1">Thank you, {{ $order->customer->name }}!</h3>
                <p class="text-gray-500">Your order <span class="font-mono font-semibold text-gray-700">#{{ $order->id }}</span> has been placed successfully.</p>

                @if($order->payment_method === 'cash_on_delivery')
                    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-amber-700 text-sm">
                        💰 Payment will be collected <strong>cash on delivery</strong>. Please have the exact amount ready.
                    </div>
                @endif
            </div>

            {{-- Order details --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h4 class="font-semibold text-gray-800">Order Details</h4>
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full
                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="divide-y divide-gray-50">
                    @foreach($order->items as $item)
                        <div class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-900">{{ $item->product->title }}</p>
                                <p class="text-xs text-gray-400">Qty: {{ $item->quantity }} × ${{ number_format($item->unit_price, 2) }}</p>
                                @if($item->customizations)
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($item->customizations as $key => $value)
                                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full capitalize">{{ $key }}: {{ $value }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <span class="font-semibold text-gray-900">${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t-2 border-gray-100 flex justify-between items-center">
                    <span class="font-semibold text-gray-700">Total</span>
                    <span class="text-xl font-bold text-gray-900">${{ number_format($order->total_price, 2) }}</span>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('shop.index') }}"
                    class="flex-1 text-center bg-gray-900 text-white font-semibold py-3 rounded-xl hover:bg-gray-700 transition-colors">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
