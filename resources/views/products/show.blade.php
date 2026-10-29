<x-app-layout>
    {{-- Breadcrumbs & Top Bar --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
            <nav class="flex items-center text-xs text-gray-500 gap-2">
                <a href="{{ route('shop.index') }}" class="hover:text-gray-900 transition-colors">Shop</a>
                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium truncate max-w-xs sm:max-w-md">{{ $product->title }}</span>
            </nav>
        </div>
    </div>

    @php
        $variants = $product->variants;
        $hasVariants = $variants->isNotEmpty();

        $uniqueSizes = $hasVariants ? $variants->pluck('size')->unique()->values() : collect();
        $uniqueColors = $hasVariants ? $variants->pluck('color')->unique()->values() : collect();

        if (!$hasVariants) {
            $manualSizes = $product->customizations['sizes'] ?? [];
            if (is_string($manualSizes)) $manualSizes = array_map('trim', explode(',', $manualSizes));
            $uniqueSizes = collect($manualSizes);

            $manualColors = $product->customizations['colors'] ?? [];
            if (is_string($manualColors)) $manualColors = array_map('trim', explode(',', $manualColors));
            $uniqueColors = collect($manualColors);
        }

        $variantsJson = $hasVariants ? $variants->map(fn($v) => [
            'id' => $v->id,
            'size' => $v->size,
            'color' => $v->color,
            'price' => (float) $v->price,
            'sku' => $v->sku,
        ]) : collect();

        $initialVariant = $variants->first();
        $initialSize = $initialVariant ? $initialVariant->size : ($uniqueSizes->first() ?? '');
        $initialColor = $initialVariant ? $initialVariant->color : ($uniqueColors->first() ?? '');
        $initialPrice = $initialVariant ? (float) $initialVariant->price : (float) $product->price;

        $images = $product->images;
        $imageUrls = $images->map(fn($img) => Storage::url($img->path))->values();
    @endphp

    <div
        class="py-8 sm:py-12"
        x-data="{
            images: {{ json_encode($imageUrls) }},
            activeImageIndex: 0,
            zoomOpen: false,
            variants: {{ json_encode($variantsJson) }},
            selectedSize: '{{ $initialSize }}',
            selectedColor: '{{ $initialColor }}',
            currentPrice: {{ $initialPrice }},
            variantId: {{ $initialVariant ? $initialVariant->id : 'null' }},
            quantity: 1,
            isSubmitting: false,
            feedbackMessage: '',
            feedbackType: 'success',

            updateSelection() {
                if (this.variants.length === 0) return;

                let matched = this.variants.find(v =>
                    v.size.toLowerCase() === this.selectedSize.toLowerCase() &&
                    v.color.toLowerCase() === this.selectedColor.toLowerCase()
                );

                if (!matched) {
                    matched = this.variants.find(v => v.size.toLowerCase() === this.selectedSize.toLowerCase());
                    if (matched) {
                        this.selectedColor = matched.color;
                    }
                }

                if (matched) {
                    this.variantId = matched.id;
                    this.currentPrice = matched.price;
                }
            },

            incrementQty() {
                if (this.quantity < 99) this.quantity++;
            },

            decrementQty() {
                if (this.quantity > 1) this.quantity--;
            },

            async submitAddToCart(event) {
                event.preventDefault();
                if (this.isSubmitting) return;

                this.isSubmitting = true;
                this.feedbackMessage = '';

                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('quantity', this.quantity);
                    if (this.variantId) {
                        formData.append('variant_id', this.variantId);
                    }
                    if (this.selectedSize) {
                        formData.append('size', this.selectedSize);
                    }
                    if (this.selectedColor) {
                        formData.append('color', this.selectedColor);
                    }

                    const response = await fetch('{{ route('cart.add', $product) }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Dispatch global cart updated event
                        window.dispatchEvent(new CustomEvent('cart-updated', {
                            detail: {
                                cartCount: data.cartCount,
                                item: data.item,
                                message: data.message
                            }
                        }));
                    } else {
                        this.feedbackType = 'error';
                        this.feedbackMessage = data.message || 'Could not add item to cart.';
                    }
                } catch (error) {
                    this.feedbackType = 'error';
                    this.feedbackMessage = 'Something went wrong. Please check your connection and try again.';
                } finally {
                    this.isSubmitting = false;
                }
            }
        }"
    >
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 items-start">

                {{-- LEFT COLUMN: Compact Interactive Multi-Image Showcase --}}
                <div class="md:col-span-7 lg:col-span-7 md:sticky md:top-24">
                    <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_5.5rem] gap-4 items-start">

                        {{-- Main Compact Viewport --}}
                        <div class="relative min-w-0 w-full bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group">
                            {{-- Availability Badge --}}
                            <div class="absolute top-2.5 left-2.5 z-10">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-white/95 backdrop-blur-md text-emerald-800 border border-emerald-100 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    In Stock
                                </span>
                            </div>

                            {{-- Main Image Display --}}
                            <div class="relative w-full h-[460px] sm:h-[540px] lg:h-[600px] bg-gray-50/60 overflow-hidden flex items-center justify-center cursor-zoom-in" @click="zoomOpen = true">
                                @if($imageUrls->isNotEmpty())
                                    <img
                                        :src="images[activeImageIndex]"
                                        src="{{ $imageUrls->first() }}"
                                        alt="{{ $product->title }}"
                                        class="block w-full h-full object-contain p-3 select-none transition-transform duration-500 ease-out group-hover:scale-105"
                                    >
                                @else
                                    <div class="flex flex-col items-center justify-center text-gray-300 p-8">
                                        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-xs font-medium text-gray-400">No Image Available</span>
                                    </div>
                                @endif

                                {{-- Expand Hint Button --}}
                                <div class="absolute bottom-2.5 right-2.5 bg-gray-900/70 backdrop-blur-md text-white p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Lightbox Zoom Modal (Teleported to body to avoid stacking context & z-index issues) --}}
                            <template x-teleport="body">
                                <div
                                    x-show="zoomOpen"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-md flex items-center justify-center p-4 sm:p-8"
                                    @click.self="zoomOpen = false"
                                    @keydown.escape.window="zoomOpen = false"
                                >
                                    <button
                                        type="button"
                                        @click="zoomOpen = false"
                                        class="absolute top-6 right-6 text-white/80 hover:text-white p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <img
                                        :src="images[activeImageIndex]"
                                        src="{{ $imageUrls->first() }}"
                                        alt="{{ $product->title }}"
                                        class="max-h-[90vh] max-w-[90vw] object-contain rounded-xl shadow-2xl"
                                    >
                                </div>
                            </template>
                        </div>

                        {{-- Thumbnails side strip (to the right of the main image) --}}
                        @if($imageUrls->count() > 1)
                            <div class="flex sm:flex-col gap-2.5 overflow-x-auto sm:overflow-y-auto max-h-[460px] sm:max-h-[540px] lg:max-h-[600px] pb-1 sm:pb-0 scrollbar-none shrink-0 w-full sm:w-22">
                                @foreach($imageUrls as $idx => $url)
                                    <button
                                        type="button"
                                        @click="activeImageIndex = {{ $idx }}"
                                        :class="activeImageIndex === {{ $idx }} ? 'ring-2 ring-gray-900 border-transparent shadow-sm' : 'border-gray-200 hover:border-gray-300 opacity-60 hover:opacity-100'"
                                        class="relative w-16 sm:w-20 h-20 sm:h-24 rounded-xl overflow-hidden border bg-white shrink-0 transition-all duration-200 focus:outline-none"
                                    >
                                        <img src="{{ $url }}" alt="Thumbnail {{ $idx + 1 }}" class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>

                {{-- RIGHT COLUMN: Product Info, Options & Purchase --}}
                <div class="md:col-span-5 lg:col-span-5 flex flex-col">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 shadow-sm flex flex-col">

                        {{-- Header Details --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <span class="text-xs font-semibold tracking-wider uppercase text-gray-400">
                                    {{ $product->user->name ?? 'Museum Collection' }}
                                </span>
                                <span class="text-[11px] font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md">
                                    {{ $product->sku }}
                                </span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight leading-tight">
                                {{ $product->title }}
                            </h1>
                        </div>

                        {{-- Live Price --}}
                        <div class="flex items-baseline gap-2 py-3 border-y border-gray-100 mb-6">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Price</span>
                            <span class="text-3xl font-extrabold text-gray-900">
                                <span class="text-lg font-normal text-gray-400">EGP</span>
                                <span x-text="parseFloat(currentPrice).toFixed(2)">{{ number_format($initialPrice, 2) }}</span>
                            </span>
                            <span class="text-xs text-gray-400 font-medium ml-auto">Inclusive of all local taxes</span>
                        </div>

                        {{-- Form: Variant Selection & Add to Cart --}}
                        <form @submit="submitAddToCart($event)" class="space-y-6">

                            {{-- Size Selector --}}
                            @if($uniqueSizes->isNotEmpty())
                                <div>
                                    <div class="flex items-center justify-between mb-2.5">
                                        <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Size</label>
                                        <span class="text-xs font-medium text-gray-500" x-text="selectedSize"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($uniqueSizes as $size)
                                            <button
                                                type="button"
                                                @click="selectedSize = '{{ $size }}'; updateSelection()"
                                                :class="selectedSize.toLowerCase() === '{{ strtolower($size) }}' ? 'bg-gray-900 text-white shadow-sm ring-2 ring-gray-900 ring-offset-1' : 'bg-gray-50 text-gray-700 border border-gray-200 hover:bg-gray-100'"
                                                class="px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-150 focus:outline-none"
                                            >
                                                {{ $size }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Frame Color Selector --}}
                            @if($uniqueColors->isNotEmpty())
                                <div>
                                    <div class="flex items-center justify-between mb-2.5">
                                        <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Frame Color / Finish</label>
                                        <span class="text-xs font-medium text-gray-500" x-text="selectedColor"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($uniqueColors as $color)
                                            <button
                                                type="button"
                                                @click="selectedColor = '{{ $color }}'; updateSelection()"
                                                :class="selectedColor.toLowerCase() === '{{ strtolower($color) }}' ? 'bg-gray-900 text-white shadow-sm ring-2 ring-gray-900 ring-offset-1' : 'bg-gray-50 text-gray-700 border border-gray-200 hover:bg-gray-100'"
                                                class="px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-150 focus:outline-none"
                                            >
                                                {{ $color }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Quantity & Add to Cart Action --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2.5">Quantity</label>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    {{-- Counter Box --}}
                                    <div class="inline-flex items-center border border-gray-200 rounded-xl bg-gray-50 p-1 shrink-0 self-start sm:self-auto">
                                        <button
                                            type="button"
                                            @click="decrementQty()"
                                            class="w-9 h-9 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-300 font-bold flex items-center justify-center transition-all disabled:opacity-40"
                                            :disabled="quantity <= 1"
                                        >
                                            -
                                        </button>
                                        <input
                                            type="number"
                                            x-model.number="quantity"
                                            min="1"
                                            max="99"
                                            class="w-12 text-center bg-transparent border-0 font-bold text-sm text-gray-800 focus:ring-0 p-0"
                                        >
                                        <button
                                            type="button"
                                            @click="incrementQty()"
                                            class="w-9 h-9 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-300 font-bold flex items-center justify-center transition-all disabled:opacity-40"
                                            :disabled="quantity >= 99"
                                        >
                                            +
                                        </button>
                                    </div>

                                    {{-- Primary Action Button --}}
                                    <button
                                        type="submit"
                                        :disabled="isSubmitting"
                                        class="flex-1 bg-gray-900 hover:bg-gray-800 text-white font-semibold text-sm py-3 px-6 rounded-xl transition-all duration-150 shadow-md hover:shadow-lg active:scale-[0.99] flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed"
                                    >
                                        <template x-if="isSubmitting">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        <span x-text="isSubmitting ? 'Adding...' : 'Add to Cart'">Add to Cart</span>
                                    </button>
                                </div>

                                {{-- Inline Feedback Alert --}}
                                <template x-if="feedbackMessage">
                                    <div
                                        class="mt-3 p-3 rounded-xl text-xs font-medium"
                                        :class="feedbackType === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'"
                                        x-text="feedbackMessage"
                                    ></div>
                                </template>
                            </div>
                        </form>

                        {{-- Design Story / Description --}}
                        <div class="mt-8 pt-8 border-t border-gray-100">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Design Story & Details
                            </h3>
                            <div class="prose prose-sm text-gray-600 leading-relaxed text-xs sm:text-sm">
                                @if(!empty($product->description))
                                    <p>{{ $product->description }}</p>
                                @else
                                    <p class="italic text-gray-400">Crafted with attention to artistic detail and fidelity. Printed on heavy 250gsm archival matte stock for long-lasting vibrancy.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Production & Shipping Assurances --}}
                        <div class="mt-8 pt-6 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-gray-500">
                            <div class="flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 text-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-900 block">Archival Giclée Print</span>
                                    <span>Fade-resistant mineral pigment inks</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 text-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-900 block">Safe Delivery Guarantee</span>
                                    <span>Reinforced protective packaging</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- You May Also Like / Related Works --}}
            @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
                <div class="mt-16 sm:mt-24 pt-12 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">You May Also Like</h2>
                            <p class="text-xs sm:text-sm text-gray-500 mt-1">Discover other handpicked museum-quality posters from the catalog.</p>
                        </div>
                        <a href="{{ route('shop.index') }}" class="text-xs font-semibold text-gray-900 hover:text-gray-600 transition-colors flex items-center gap-1">
                            Browse All
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($relatedProducts as $rel)
                            @php $relImg = $rel->images->first(); @endphp
                            <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                                <a href="{{ route('shop.show', $rel) }}" class="block aspect-square overflow-hidden bg-gray-50">
                                    @if($relImg)
                                        <img src="{{ Storage::url($relImg->path) }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                                <div class="p-4 flex flex-col flex-1 justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm truncate">
                                            <a href="{{ route('shop.show', $rel) }}" class="hover:text-gray-600 transition-colors">
                                                {{ $rel->title }}
                                            </a>
                                        </h4>
                                    </div>
                                    <div class="mt-2 text-sm font-bold text-gray-900">
                                        <span class="text-xs font-normal text-gray-400">EGP</span>
                                        {{ number_format($rel->price, 2) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
