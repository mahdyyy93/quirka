<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Shopping Cart</h1>
                <p class="text-sm text-gray-500 mt-1">Review your selected framed poster prints before checkout.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @php $cartItems = $items; @endphp

            @if(count($cartItems) === 0)
                <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm space-y-4 max-w-xl mx-auto my-12">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto text-3xl">
                        🛒
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Your cart is empty</h3>
                    <p class="text-gray-500 text-sm">Looks like you haven't added any custom framed posters yet.</p>
                    <div>
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-800 text-white font-semibold px-6 py-3 rounded-xl text-sm transition-all shadow-sm">
                            <span>Explore Shop</span> &rarr;
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left: Cart Items List -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden divide-y divide-gray-100">
                            @foreach($cartItems as $item)
                                @php
                                    $product = $item['product'];
                                    $qty = $item['quantity'];
                                    $firstImage = $product->images->first();
                                @endphp
                                <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                                    <div class="flex items-center gap-4 flex-1">
                                        <!-- Product Image -->
                                        @if($firstImage)
                                            <img src="{{ Storage::url($firstImage->path) }}" alt="{{ $product->title }}" class="w-20 h-20 object-cover rounded-xl border border-gray-100 shrink-0">
                                        @else
                                            <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 shrink-0">
                                                🖼️
                                            </div>
                                        @endif

                                        <div>
                                            <h4 class="font-bold text-gray-900 text-base leading-snug">{{ $product->title }}</h4>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs text-gray-400 font-mono">SKU: {{ $product->sku }}</span>
                                                @if(!empty($item['size']))
                                                    <span class="text-[11px] bg-gray-100 text-gray-700 font-medium px-2 py-0.5 rounded">Size: {{ $item['size'] }}</span>
                                                @endif
                                                @if(!empty($item['color']))
                                                    <span class="text-[11px] bg-gray-100 text-gray-700 font-medium px-2 py-0.5 rounded">Color: {{ $item['color'] }}</span>
                                                @endif
                                            </div>
                                            <p class="text-sm font-semibold text-gray-900 mt-1">EGP {{ number_format($item['unit_price'], 2) }} each</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
                                        <!-- Quantity Form -->
                                        <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="variant_id" value="{{ $item['variant']?->id }}">
                                            <input type="number" name="quantity" value="{{ $qty }}" min="1" max="99" 
                                                class="w-16 border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm text-center font-medium focus:ring-2 focus:ring-gray-900">
                                            <button type="submit" class="text-xs text-gray-600 hover:text-gray-900 font-semibold px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                                Update
                                            </button>
                                        </form>

                                        <!-- Line Total -->
                                        <div class="text-right min-w-[80px]">
                                            <span class="font-bold text-gray-900 text-base">EGP {{ number_format($item['line_total'], 2) }}</span>
                                        </div>

                                        <!-- Remove Button -->
                                        <form method="POST" action="{{ route('cart.remove', $product) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="variant_id" value="{{ $item['variant']?->id }}">
                                            <button type="submit" class="text-gray-400 hover:text-red-600 p-1.5 transition-colors" title="Remove item">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between pt-2 text-sm">
                            <a href="{{ route('shop.index') }}" class="text-indigo-600 font-semibold hover:underline flex items-center gap-1">
                                &larr; Continue Shopping
                            </a>
                        </div>
                    </div>

                    <!-- Right: Order Summary Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
                            <h3 class="text-lg font-bold text-gray-900">Order Summary</h3>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal ({{ count($cartItems) }} items)</span>
                                    <span class="font-bold text-gray-900">EGP {{ number_format($subtotal, 2) }}</span>
                                </div>

                                <div class="flex justify-between text-gray-600">
                                    <span>Fulfillment & Shipping</span>
                                    <span class="text-xs text-indigo-700 font-semibold bg-indigo-50 px-2 py-0.5 rounded">Calculated at Checkout</span>
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            <div class="flex justify-between items-baseline text-base font-bold text-gray-900">
                                <span>Estimated Subtotal</span>
                                <span class="text-2xl">EGP {{ number_format($subtotal, 2) }}</span>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-xl text-xs text-gray-500 space-y-1">
                                <p class="font-semibold text-gray-700">⚡ Printlet Fulfillment Integration</p>
                                <p>Shipping costs & live quotes will be calculated instantly during checkout.</p>
                            </div>

                            <div>
                                <a href="{{ route('checkout.show') }}" class="w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition-all text-sm">
                                    <span>Proceed to Checkout</span> &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
