<x-app-layout>
    {{-- Editorial Breadcrumbs --}}
    <div class="border-b border-gallery-burgundy/20 bg-gallery-cream">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center text-xs uppercase tracking-[0.15em] text-gallery-muted gap-3">
                <a href="{{ route('shop.index') }}" class="hover:text-gallery-burgundy transition-colors">Art Gallery</a>
                <span class="text-gallery-burgundy/40">/</span>
                <span class="text-gallery-burgundy font-semibold truncate max-w-xs sm:max-w-md">{{ $product->title }}</span>
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
        class="py-12 lg:py-16"
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
                    const response = await fetch('{{ route('cart.add', $product) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            quantity: this.quantity,
                            variant_id: this.variantId,
                            size: this.selectedSize,
                            color: this.selectedColor
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.feedbackType = 'success';
                        this.feedbackMessage = data.message;
                        window.dispatchEvent(new CustomEvent('cart-updated', {
                            detail: {
                                cartCount: data.cartCount,
                                item: data.item,
                                message: data.message
                            }
                        }));
                    } else {
                        this.feedbackType = 'error';
                        this.feedbackMessage = data.message || 'Error adding artwork to your selection.';
                    }
                } catch (error) {
                    console.error(error);
                    this.feedbackType = 'error';
                    this.feedbackMessage = 'A network error occurred. Please try again.';
                } finally {
                    this.isSubmitting = false;
                }
            }
        }"
    >
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">

                {{-- Left: Prominent Artwork Gallery Presentation --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="relative bg-gallery-frame-backing border-2 border-gallery-burgundy/30 p-6 sm:p-12 flex items-center justify-center min-h-[480px]">
                        <template x-if="images.length > 0">
                            <div class="relative group cursor-zoom-in max-h-[640px] w-full flex items-center justify-center" @click="zoomOpen = true">
                                <img
                                    :src="images[activeImageIndex]"
                                    alt="{{ $product->title }}"
                                    class="max-h-[580px] w-auto object-contain filter drop-shadow-2xl transition-transform duration-500 group-hover:scale-[1.02]"
                                >
                                <div class="absolute bottom-4 right-4 bg-gallery-burgundy/90 backdrop-blur-sm text-white px-3 py-1.5 text-[10px] uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    <span>Inspect High-Res</span>
                                </div>
                            </div>
                        </template>

                        <template x-if="images.length === 0">
                            <div class="text-center py-20 text-gallery-burgundy">
                                <svg class="w-16 h-16 mx-auto stroke-1 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs uppercase tracking-widest font-semibold">Archival Print Exhibition</span>
                            </div>
                        </template>
                    </div>

                    {{-- Image Thumbnails --}}
                    <template x-if="images.length > 1">
                        <div class="flex items-center gap-4 overflow-x-auto pb-2">
                            <template x-for="(img, idx) in images" :key="idx">
                                <button
                                    type="button"
                                    @click="activeImageIndex = idx"
                                    :class="activeImageIndex === idx ? 'border-gallery-burgundy ring-2 ring-gallery-burgundy' : 'border-gallery-border opacity-70 hover:opacity-100'"
                                    class="w-20 h-24 bg-gallery-frame-backing border overflow-hidden p-1 transition-all"
                                >
                                    <img :src="img" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- Museum Standards Badge --}}
                    <div class="p-6 bg-gallery-cream border border-gallery-burgundy/30 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center sm:text-left">
                        <div>
                            <span class="text-[10px] uppercase tracking-[0.2em] text-gallery-burgundy font-bold block mb-1">Archival Pigment</span>
                            <p class="text-xs text-gallery-muted">12-color giclée printing rated for 100+ years without fade.</p>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-[0.2em] text-gallery-burgundy font-bold block mb-1">Solid Wood Frame</span>
                            <p class="text-xs text-gallery-muted">Hand-finished moulding with protective anti-reflective glazing.</p>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-[0.2em] text-gallery-burgundy font-bold block mb-1">Artist Provenance</span>
                            <p class="text-xs text-gallery-muted">Official gallery license directly supporting the artist.</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Editorial Artwork Details & Purchasing --}}
                <div class="lg:col-span-5 space-y-8">
                    {{-- Title & Artist attribution --}}
                    <div class="space-y-3 border-b border-gallery-burgundy/30 pb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase tracking-[0.25em] text-gallery-burgundy font-bold">Limited Print Edition</span>
                            <span class="text-xs font-mono text-gallery-burgundy bg-gallery-burgundy/5 px-2 py-0.5 border border-gallery-burgundy/20">
                                {{ $product->sku }}
                            </span>
                        </div>
                        <h1 class="font-serif text-3xl sm:text-4xl text-gallery-charcoal leading-tight">
                            {{ $product->title }}
                        </h1>
                        <p class="text-sm uppercase tracking-[0.15em] text-gallery-muted">
                            By <span class="text-gallery-burgundy font-bold">{{ $product->user?->name ?? 'Quirka Studio' }}</span>
                        </p>
                    </div>

                    {{-- Price Display --}}
                    <div class="flex items-baseline gap-3">
                        <span class="text-sm font-sans uppercase tracking-wider text-gallery-muted">Price</span>
                        <div class="font-serif text-3xl sm:text-4xl text-gallery-burgundy font-bold">
                            <span class="text-base font-sans font-normal text-gallery-burgundy/70 uppercase">EGP</span>
                            <span x-text="parseFloat(currentPrice).toFixed(2)">{{ number_format($initialPrice, 2) }}</span>
                        </div>
                        <span class="text-xs text-gallery-burgundy font-medium ml-2 border border-gallery-burgundy/20 px-2 py-0.5 bg-gallery-burgundy/5">Taxes & Delivery Included</span>
                    </div>

                    {{-- Purchasing Form --}}
                    <form @submit="submitAddToCart" class="space-y-6">
                        {{-- Sizes Selector --}}
                        @if($uniqueSizes->isNotEmpty())
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs uppercase tracking-[0.15em] font-bold text-gallery-burgundy">Select Dimensions</label>
                                    <span class="text-xs font-medium text-gallery-burgundy" x-text="selectedSize"></span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    @foreach($uniqueSizes as $sz)
                                        <button
                                            type="button"
                                            @click="selectedSize = '{{ $sz }}'; updateSelection()"
                                            :class="selectedSize.toLowerCase() === '{{ strtolower($sz) }}' ? 'border-gallery-burgundy bg-gallery-burgundy text-white shadow-sm' : 'border-gallery-burgundy/30 bg-gallery-cream text-gallery-charcoal hover:border-gallery-burgundy hover:bg-gallery-burgundy/5'"
                                            class="py-3 px-3 text-xs uppercase tracking-wider font-semibold border text-center transition-all"
                                        >
                                            {{ $sz }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Frame / Finish Selector --}}
                        @if($uniqueColors->isNotEmpty())
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs uppercase tracking-[0.15em] font-bold text-gallery-burgundy">Frame Finish</label>
                                    <span class="text-xs font-medium text-gallery-burgundy" x-text="selectedColor"></span>
                                </div>
                                <div class="grid grid-cols-2 gap-2.5">
                                    @foreach($uniqueColors as $cl)
                                        <button
                                            type="button"
                                            @click="selectedColor = '{{ $cl }}'; updateSelection()"
                                            :class="selectedColor.toLowerCase() === '{{ strtolower($cl) }}' ? 'border-gallery-burgundy bg-gallery-burgundy text-white shadow-sm' : 'border-gallery-burgundy/30 bg-gallery-cream text-gallery-charcoal hover:border-gallery-burgundy hover:bg-gallery-burgundy/5'"
                                            class="py-3 px-3 text-xs uppercase tracking-wider font-semibold border text-center transition-all"
                                        >
                                            {{ $cl }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Quantity & Add To Cart Button --}}
                        <div class="pt-4 space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center border border-gallery-burgundy/40 bg-gallery-cream">
                                    <button
                                        type="button"
                                        @click="decrementQty"
                                        class="px-3.5 py-3 text-gallery-burgundy hover:bg-gallery-burgundy/10 transition-colors"
                                    >
                                        &minus;
                                    </button>
                                    <span class="w-10 text-center text-xs font-bold text-gallery-burgundy" x-text="quantity"></span>
                                    <button
                                        type="button"
                                        @click="incrementQty"
                                        class="px-3.5 py-3 text-gallery-burgundy hover:bg-gallery-burgundy/10 transition-colors"
                                    >
                                        &plus;
                                    </button>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="flex-1 bg-gallery-burgundy hover:bg-gallery-burgundy-dark disabled:opacity-50 text-gallery-cream text-xs uppercase tracking-[0.2em] font-medium py-4 px-6 border border-gallery-burgundy transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span x-show="!isSubmitting">Acquire Artwork</span>
                                    <span x-show="isSubmitting">Preparing Selection...</span>
                                </button>
                            </div>

                            {{-- Feedback notice --}}
                            <div
                                x-show="feedbackMessage"
                                x-transition
                                :class="feedbackType === 'success' ? 'bg-gallery-burgundy/10 border-gallery-burgundy text-gallery-burgundy font-medium' : 'bg-red-50 border-red-200 text-red-800'"
                                class="p-3.5 border text-xs"
                                x-text="feedbackMessage"
                            ></div>
                        </div>
                    </form>

                    {{-- Description & Provenance --}}
                    @if($product->description)
                        <div class="border-t border-gallery-burgundy/30 pt-6 space-y-2">
                            <h3 class="text-xs uppercase tracking-[0.2em] font-bold text-gallery-burgundy">Artwork Overview</h3>
                            <div class="text-sm text-gallery-muted leading-relaxed font-light">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Curated Complementary Artworks --}}
            @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
                <div class="mt-24 pt-16 border-t border-gallery-burgundy/30">
                    <div class="mb-10">
                        <span class="text-[10px] uppercase tracking-[0.25em] text-gallery-burgundy font-bold block mb-1">Companion Pieces</span>
                        <h2 class="font-serif text-3xl text-gallery-charcoal">Related Artworks</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        @foreach($relatedProducts as $rel)
                            @php $relImg = $rel->images->first(); @endphp
                            <div class="group flex flex-col bg-gallery-cream border border-gallery-border hover:border-gallery-burgundy transition-all hover:shadow-md">
                                <a href="{{ route('shop.show', $rel) }}" class="block aspect-[4/5] bg-gallery-frame-backing p-4 flex items-center justify-center overflow-hidden">
                                    @if($relImg)
                                        <img
                                            src="{{ Storage::url($relImg->path) }}"
                                            alt="{{ $rel->title }}"
                                            class="w-full h-full object-contain filter drop-shadow group-hover:scale-102 transition-transform duration-500"
                                        >
                                    @else
                                        <div class="text-gallery-burgundy text-[10px] uppercase tracking-wider font-semibold">Archival Print</div>
                                    @endif
                                </a>
                                <div class="p-4 flex flex-col flex-1 justify-between border-t border-gallery-border">
                                    <div>
                                        <h4 class="font-serif text-base text-gallery-charcoal group-hover:text-gallery-burgundy transition-colors truncate">
                                            <a href="{{ route('shop.show', $rel) }}">
                                                {{ $rel->title }}
                                            </a>
                                        </h4>
                                        <span class="text-[11px] text-gallery-muted uppercase tracking-wider block mt-0.5">
                                            {{ $rel->user?->name ?? 'Quirka Studio' }}
                                        </span>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-gallery-burgundy/20 flex items-baseline justify-between">
                                        <span class="text-[10px] uppercase text-gallery-burgundy font-semibold">From</span>
                                        <div class="font-serif text-base font-bold text-gallery-burgundy">
                                            EGP {{ number_format($rel->price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Inspect Zoom Lightbox Modal --}}
        <template x-teleport="body">
            <div
                x-show="zoomOpen"
                x-cloak
                @keydown.escape.window="zoomOpen = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-gallery-charcoal/95 backdrop-blur-md p-4 sm:p-8"
            >
                <button
                    type="button"
                    @click="zoomOpen = false"
                    class="absolute top-6 right-6 text-white/80 hover:text-gallery-burgundy p-2 text-2xl transition-colors"
                >
                    &times;
                </button>
                <template x-if="images.length > 0">
                    <img
                        :src="images[activeImageIndex]"
                        alt="{{ $product->title }}"
                        class="max-w-full max-h-[90vh] object-contain shadow-2xl border-4 border-gallery-burgundy/30"
                    >
                </template>
            </div>
        </template>
    </div>
</x-app-layout>
