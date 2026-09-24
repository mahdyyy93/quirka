<x-app-layout>
    @php
        $cartTotal = 0;
        foreach ($cart as $productId => $item) {
            $p = $products->get($productId);
            if ($p) {
                $variant = null;
                if (!empty($item['variant_id'])) {
                    $variant = $p->variants->firstWhere('id', $item['variant_id']);
                }
                if (!$variant && !empty($item['size'])) {
                    $variant = $p->variants->first(function ($v) use ($item) {
                        $match = strtolower($v->size) === strtolower($item['size']);
                        if (!empty($item['color'])) {
                            $match = $match && strtolower($v->color) === strtolower($item['color']);
                        }
                        return $match;
                    });
                }
                $price = $variant ? (float) $variant->price : (float) $p->price;
                $cartTotal += $price * ($item['quantity'] ?? 1);
            }
        }
    @endphp

    <div class="py-8">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @csrf

                {{-- Left column: delivery & payment --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Delivery details --}}
                    <div x-data="{ city: '{{ old('city_id', 'city_cairo') }}' }" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 text-lg mb-4">Delivery Details</h3>

                        <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">

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
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                            <input type="text" id="phone" name="phone" required
                                value="{{ old('phone', $customer?->phone ?? '01000000000') }}"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('phone') border-red-400 @enderror"
                                placeholder="+20 100 000 0000">
                            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-1">Shipping Address <span class="text-red-500">*</span></label>
                            <textarea id="address_line_1" name="address_line_1" rows="3" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('address_line_1') border-red-400 @enderror"
                                placeholder="Street address, building, apartment, postal code...">{{ old('address_line_1', old('address', $customer?->address)) }}</textarea>
                            @error('address_line_1') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="city_id" class="block text-sm font-medium text-gray-700 mb-1">City / Governorate <span class="text-red-500">*</span></label>
                            <select id="city_id" name="city_id" required x-model="city"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('city_id') border-red-400 @enderror">
                                <option value="">Select your city</option>
                                @foreach(config('services.printlet.cities', []) as $cityId => $cityName)
                                    <option value="{{ $cityId }}" @selected(old('city_id', 'city_cairo') === $cityId)>{{ $cityName }}</option>
                                @endforeach
                            </select>
                            @error('city_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-400 mt-1">Delivery fees are calculated from this location.</p>
                            <div class="mt-3 rounded-lg border border-indigo-100 bg-indigo-50/70 px-3 py-2 text-xs text-indigo-900">
                                <span class="font-semibold">Estimated delivery:</span>
                                <span x-text="['city_cairo', 'city_giza', 'city_qalyubia'].includes(city) ? '3-5 working days inside Greater Cairo' : '4-8 working days for this city'"></span>
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes <span class="text-gray-400">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="2"
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                                placeholder="Any special instructions...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Payment method section --}}
                    <div x-data="{ paymentMethod: '{{ old('payment_method', 'cod') }}' }" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-800 text-lg">Payment Method</h3>
                            <span class="text-xs text-gray-400 font-medium">Select payment option</span>
                        </div>

                        @error('payment_method') <p class="text-red-500 text-xs mb-3">{{ $message }}</p> @enderror

                        <div class="space-y-3">
                            {{-- Cash on Delivery --}}
                            <label class="flex items-start gap-4 border rounded-xl p-4 cursor-pointer transition-all duration-150"
                                :class="paymentMethod === 'cod' ? 'border-gray-900 bg-gray-50/70 shadow-sm' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="cod"
                                    x-model="paymentMethod"
                                    {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}
                                    class="mt-1 accent-gray-900">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-900 text-sm">💵 Cash on Delivery (COD)</span>
                                        <span class="text-[11px] font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded">Doorstep</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Pay in cash directly to the courier when your order arrives at your door.</p>
                                </div>
                            </label>

                            {{-- Instapay Direct Transfer --}}
                            <label class="flex items-start gap-4 border rounded-xl p-4 cursor-pointer transition-all duration-150"
                                :class="paymentMethod === 'instapay' ? 'border-indigo-600 bg-indigo-50/30 shadow-sm' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="instapay"
                                    x-model="paymentMethod"
                                    {{ old('payment_method') === 'instapay' ? 'checked' : '' }}
                                    class="mt-1 accent-indigo-600">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-900 text-sm flex items-center gap-1.5">
                                            ⚡ Instapay Direct Transfer
                                        </span>
                                        <span class="text-[11px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">Zero Extra Fees</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Instant electronic transfer via Instapay app using payment link or QR code.</p>
                                </div>
                            </label>
                        </div>

                        {{-- Instapay Reassurance Notice --}}
                        <div x-show="paymentMethod === 'instapay'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4 p-4 bg-indigo-50/80 border border-indigo-100 rounded-xl space-y-1.5 text-xs text-indigo-900">
                            <div class="flex items-center gap-2 font-bold text-indigo-950 text-sm">
                                <span>🔒</span>
                                <span>Your order will be saved first</span>
                            </div>
                            <p class="leading-relaxed text-indigo-800">
                                Click <strong>Place Order</strong> below to secure your items and shipping details immediately. On the next screen, you will get the <strong>Instapay payment link (for mobile)</strong> and <strong>QR code (for desktop)</strong> to complete your transfer without losing your form info.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Right column: order summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                        <h3 class="font-semibold text-gray-800 text-lg mb-4">Order Summary</h3>

                        <div class="space-y-3 mb-4">
                            @foreach($cart as $productId => $item)
                                @php
                                    $product = $products->get($productId);
                                    if (!$product) continue;
                                    $variant = null;
                                    if (!empty($item['variant_id'])) {
                                        $variant = $product->variants->firstWhere('id', $item['variant_id']);
                                    }
                                    if (!$variant && !empty($item['size'])) {
                                        $variant = $product->variants->first(function ($v) use ($item) {
                                            $match = strtolower($v->size) === strtolower($item['size']);
                                            if (!empty($item['color'])) {
                                                $match = $match && strtolower($v->color) === strtolower($item['color']);
                                            }
                                            return $match;
                                        });
                                    }
                                    $itemPrice = $variant ? (float) $variant->price : (float) $product->price;
                                    $subtotal = $itemPrice * $item['quantity'];
                                @endphp
                                <div class="flex justify-between text-sm">
                                    <div class="truncate mr-2">
                                        <span class="text-gray-700 font-medium">{{ $product->title }}</span>
                                        <span class="text-gray-400">×{{ $item['quantity'] }}</span>
                                        @if($variant || !empty($item['size']) || !empty($item['color']))
                                            <div class="text-[11px] text-gray-500">
                                                {{ implode(' / ', array_filter([$variant?->size ?? ($item['size'] ?? null), $variant?->color ?? ($item['color'] ?? null)])) }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="font-medium text-gray-900 shrink-0">${{ number_format($subtotal, 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-100 pt-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-700">Total</span>
                                <span class="text-2xl font-bold text-gray-900">${{ number_format($cartTotal, 2) }}</span>
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
