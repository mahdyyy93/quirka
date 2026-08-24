<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cart & Checkout</h2>
            <a href="{{ route('shop.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">← Continue Shopping</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">{{ session('error') }}</div>
            @endif

            @if(empty($cart) || $products->isEmpty())
                {{-- Empty state --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                    <div class="text-gray-300 text-6xl mb-4">🛒</div>
                    <p class="text-gray-500 text-lg">Your cart is empty.</p>
                    <a href="{{ route('shop.index') }}" class="mt-4 inline-block bg-gray-900 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">Shop Now</a>
                </div>
            @else
                @auth
                    @php
                        $paymentService = app(\App\Services\PaymentService::class);
                        $paymentMethods = $paymentService->available();
                        $customer = auth()->user()->customer;
                    @endphp
                    <form method="POST" action="{{ route('checkout.store') }}">
                        @csrf
                @endauth

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Left: Cart items (visible to guests & auth users) --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Cart items --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h3 class="font-semibold text-gray-800 text-lg">Your Items</h3>
                            </div>

                            <div class="divide-y divide-gray-50">
                                @php $total = 0; @endphp
                                @foreach($cart as $productId => $item)
                                    @php
                                        $product = $products->get($productId);
                                        if (!$product) continue;
                                        $subtotal = $product->price * $item['quantity'];
                                        $total += $subtotal;
                                    @endphp
                                    <div class="px-6 py-4 flex items-center gap-4" id="cart-item-{{ $product->id }}">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 truncate">{{ $product->title }}</p>
                                            <p class="text-xs text-gray-400 font-mono">{{ $product->sku }}</p>
                                            @if($product->customizations)
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach($product->customizations as $key => $value)
                                                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full capitalize">{{ $key }}: {{ $value }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <span class="text-gray-600 text-sm">${{ number_format($product->price, 2) }}</span>

                                        {{-- Inline quantity update --}}
                                        <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-1">
                                            @csrf @method('PATCH')
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="99"
                                                class="w-14 border border-gray-200 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-gray-900">
                                            <button type="submit" class="text-xs text-gray-400 hover:text-gray-700 transition">Update</button>
                                        </form>

                                        <span class="font-semibold text-gray-900 w-20 text-right">${{ number_format($subtotal, 2) }}</span>

                                        {{-- Remove --}}
                                        <form method="POST" action="{{ route('cart.remove', $product) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 transition" title="Remove">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @auth
                        {{-- Delivery details --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <h3 class="font-semibold text-gray-800 text-lg mb-4">Delivery Details</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                    <input type="text" value="{{ auth()->user()->name }}" disabled
                                        class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" value="{{ auth()->user()->email }}" disabled
                                        class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-500 text-sm">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                    Shipping Address <span class="text-red-500">*</span>
                                </label>
                                <textarea id="address" name="address" rows="2" required
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('address') border-red-400 @enderror"
                                    placeholder="Street address, city, postal code...">{{ old('address', $customer?->address) }}</textarea>
                                @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Order Notes <span class="text-gray-400 font-normal">(optional)</span>
                                </label>
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
                                                <p class="text-xs text-gray-400 mt-0.5">Pay in cash when your order arrives.</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endauth
                    </div>

                    {{-- Right: Order summary --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                            <h3 class="font-semibold text-gray-800 text-lg mb-4">Order Summary</h3>

                            <div class="space-y-2 mb-4">
                                @php $summaryTotal = 0; @endphp
                                @foreach($cart as $productId => $item)
                                    @php
                                        $p = $products->get($productId);
                                        if (!$p) continue;
                                        $sub = $p->price * $item['quantity'];
                                        $summaryTotal += $sub;
                                    @endphp
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 truncate mr-2">{{ $p->title }} <span class="text-gray-400">×{{ $item['quantity'] }}</span></span>
                                        <span class="font-medium text-gray-900 shrink-0">${{ number_format($sub, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-gray-100 pt-4 mb-6">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-gray-700">Total</span>
                                    <span class="text-2xl font-bold text-gray-900">${{ number_format($summaryTotal, 2) }}</span>
                                </div>
                            </div>

                            @auth
                                <button type="submit"
                                    class="w-full bg-gray-900 text-white font-semibold py-3 rounded-xl hover:bg-gray-700 transition-colors">
                                    Place Order
                                </button>
                            @else
                                <div class="space-y-3">
                                    <p class="text-sm text-gray-500 text-center">Log in or create an account to complete your purchase.</p>
                                    <a href="{{ route('login') }}?redirect={{ urlencode(route('cart.index')) }}"
                                        class="block w-full text-center bg-gray-900 text-white font-semibold py-3 rounded-xl hover:bg-gray-700 transition-colors">
                                        Log in to Checkout
                                    </a>
                                    <a href="{{ route('register') }}"
                                        class="block w-full text-center border border-gray-200 text-gray-700 font-medium py-3 rounded-xl hover:bg-gray-50 transition-colors">
                                        Create an Account
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>

                </div>{{-- /grid --}}

                @auth
                    </form>
                @endauth
            @endif
        </div>
    </div>
</x-app-layout>

