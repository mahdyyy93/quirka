<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout</h2>
            <a href="{{ route('cart.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">← Back to Cart</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @csrf

                {{-- Left column: delivery & payment --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Delivery details --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 text-lg mb-4">Delivery Details</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                            <input type="text" value="{{ auth()->user()->name }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-500 text-sm">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" value="{{ auth()->user()->email }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-500 text-sm">
                        </div>

                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Shipping Address <span class="text-red-500">*</span></label>
                            <textarea id="address" name="address" rows="3" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('address') border-red-400 @enderror"
                                placeholder="Street address, city, postal code...">{{ old('address', $customer?->address) }}</textarea>
                            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes <span class="text-gray-400">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="2"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                                placeholder="Any special instructions...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Payment method --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 text-lg mb-4">Payment Method</h3>
                        @error('payment_method') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

                        <div class="space-y-3">
                            @foreach($paymentMethods as $key => $label)
                                <label class="flex items-center gap-4 border border-gray-200 rounded-xl px-4 py-3 cursor-pointer hover:border-gray-400 transition-colors has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                                    <input type="radio" name="payment_method" value="{{ $key }}"
                                        {{ (old('payment_method', array_key_first($paymentMethods)) === $key) ? 'checked' : '' }}
                                        class="accent-gray-900">
                                    <div>
                                        <span class="font-medium text-gray-800">{{ $label }}</span>
                                        @if($key === 'cash_on_delivery')
                                            <p class="text-xs text-gray-400">Pay in cash when your order arrives.</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right column: order summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                        <h3 class="font-semibold text-gray-800 text-lg mb-4">Order Summary</h3>

                        @php $total = 0; @endphp
                        <div class="space-y-3 mb-4">
                            @foreach($cart as $productId => $item)
                                @php
                                    $product = $products->get($productId);
                                    if (!$product) continue;
                                    $subtotal = $product->price * $item['quantity'];
                                    $total += $subtotal;
                                @endphp
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 truncate mr-2">{{ $product->title }} <span class="text-gray-400">×{{ $item['quantity'] }}</span></span>
                                    <span class="font-medium text-gray-900 shrink-0">${{ number_format($subtotal, 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-100 pt-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-700">Total</span>
                                <span class="text-2xl font-bold text-gray-900">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-gray-900 text-white font-semibold py-3 rounded-xl hover:bg-gray-700 transition-colors">
                            Place Order
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
