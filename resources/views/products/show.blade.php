<x-app-layout>
    {{-- Breadcrumbs --}}
    <div class="border-b border-gallery-border bg-white">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
            <nav class="flex items-center text-xs text-gallery-muted gap-2">
                <a href="{{ route('shop.index') }}" class="hover:text-gallery-charcoal transition-colors">Art Gallery</a>
                <span class="text-gallery-muted/60">&rsaquo;</span>
                <span class="text-gallery-charcoal font-medium truncate max-w-xs sm:max-w-md">{{ $product->title }}</span>
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
        class="py-10 lg:py-14"
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
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14">

                {{-- Left: Contemporary Artwork Gallery Presentation --}}
                <div class="lg:col-span-7 space-y-5">
                    <div class="relative bg-white rounded-xl border border-gallery-border p-2 sm:p-4 lg:p-5 flex items-center justify-center min-h-[480px] sm:min-h-[620px] lg:min-h-[700px] shadow-sm">
                        <template x-if="images.length > 0">
                            <div class="relative group cursor-zoom-in w-full h-full flex items-center justify-center" @click="zoomOpen = true">
                                <img
                                    :src="images[activeImageIndex]"
                                    alt="{{ $product->title }}"
                                    class="max-h-[640px] sm:max-h-[720px] lg:max-h-[780px] w-full h-auto object-contain filter drop-shadow-[0_10px_28px_rgba(0,0,0,0.08)] transition-transform duration-500 group-hover:scale-[1.01]"
                                >
                                <div class="absolute bottom-3 right-3 bg-gallery-charcoal/80 backdrop-blur-sm text-white px-3 py-1.5 rounded-md text-xs font-medium opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    <span>High-Res Zoom</span>
                                </div>
                            </div>
                        </template>

                        <template x-if="images.length === 0">
                            <div class="text-center py-20 text-gallery-muted">
                                <svg class="w-16 h-16 mx-auto stroke-1 mb-3 text-gallery-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs uppercase tracking-wider font-medium">Fine Art Print Edition</span>
                            </div>
                        </template>
                    </div>

                    {{-- Image Thumbnails --}}
                    <template x-if="images.length > 1">
                        <div class="flex items-center gap-3 overflow-x-auto pb-2">
                            <template x-for="(img, idx) in images" :key="idx">
                                <button
                                    type="button"
                                    @click="activeImageIndex = idx"
                                    :class="activeImageIndex === idx ? 'border-gallery-charcoal ring-2 ring-gallery-charcoal' : 'border-gallery-border opacity-70 hover:opacity-100'"
                                    class="w-18 h-22 bg-[#F9F9F8] rounded-md border overflow-hidden p-1 transition-all"
                                >
                                    <img :src="img" class="w-full h-full object-contain">
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- Museum Standards Badge --}}
                    <div class="p-6 bg-white rounded-xl border border-gallery-border grid grid-cols-1 sm:grid-cols-3 gap-6 text-center sm:text-left">
                        <div>
                            <span class="text-xs uppercase tracking-wider text-gallery-charcoal font-semibold block mb-1">Archival Pigment</span>
                            <p class="text-xs text-gallery-muted leading-relaxed">12-color giclée printing rated for 100+ years without fading.</p>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-wider text-gallery-charcoal font-semibold block mb-1">Handcrafted Framing</span>
                            <p class="text-xs text-gallery-muted leading-relaxed">Solid wood moulding with anti-reflective protective glazing.</p>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-wider text-gallery-charcoal font-semibold block mb-1">Artist Provenance</span>
                            <p class="text-xs text-gallery-muted leading-relaxed">Direct artist royalty licensing supporting independent creators.</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Modern Artwork Details & Purchasing --}}
                <div class="lg:col-span-5 space-y-6">
                    {{-- Title & Artist attribution --}}
                    <div class="space-y-2.5 border-b border-gallery-border pb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Fine Art Print Edition</span>
                            <span class="text-xs font-mono text-gallery-muted bg-gallery-cream px-2 py-0.5 rounded border border-gallery-border">
                                {{ $product->sku }}
                            </span>
                        </div>
                        <h1 class="font-serif text-3xl sm:text-4xl text-gallery-charcoal leading-tight font-normal">
                            {{ $product->title }}
                        </h1>
                        <p class="text-sm text-gallery-muted">
                            By <span class="text-gallery-charcoal font-medium">{{ $product->user?->name ?? 'Quirka Studio' }}</span>
                        </p>
                    </div>

                    {{-- Price Display --}}
                    <div class="flex items-baseline gap-3">
                        <span class="text-xs uppercase tracking-wider text-gallery-muted font-medium">Price</span>
                        <div class="text-3xl font-semibold text-gallery-charcoal">
                            <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                            <span x-text="parseFloat(currentPrice).toFixed(2)">{{ number_format($initialPrice, 2) }}</span>
                        </div>
                        <span class="text-xs text-gallery-muted ml-2 bg-gallery-cream px-2.5 py-1 rounded-full border border-gallery-border">Insured Delivery</span>
                    </div>

                    {{-- Purchasing Form --}}
                    <form @submit="submitAddToCart" class="space-y-6">
                        {{-- Sizes Selector --}}
                        @if($uniqueSizes->isNotEmpty())
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs uppercase tracking-wider font-semibold text-gallery-charcoal">Select Dimensions</label>
                                    <span class="text-xs text-gallery-muted" x-text="selectedSize"></span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach($uniqueSizes as $sz)
                                        <button
                                            type="button"
                                            @click="selectedSize = '{{ $sz }}'; updateSelection()"
                                            :class="selectedSize.toLowerCase() === '{{ strtolower($sz) }}' ? 'border-gallery-charcoal bg-gallery-charcoal text-white' : 'border-gallery-border bg-white text-gallery-charcoal hover:border-gallery-muted/60'"
                                            class="py-2.5 px-3 text-xs font-medium rounded-md border text-center transition-all"
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
                                    <label class="text-xs uppercase tracking-wider font-semibold text-gallery-charcoal">Frame Finish</label>
                                    <span class="text-xs text-gallery-muted" x-text="selectedColor"></span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($uniqueColors as $cl)
                                        <button
                                            type="button"
                                            @click="selectedColor = '{{ $cl }}'; updateSelection()"
                                            :class="selectedColor.toLowerCase() === '{{ strtolower($cl) }}' ? 'border-gallery-charcoal bg-gallery-charcoal text-white' : 'border-gallery-border bg-white text-gallery-charcoal hover:border-gallery-muted/60'"
                                            class="py-2.5 px-3 text-xs font-medium rounded-md border text-center transition-all"
                                        >
                                            {{ $cl }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Quantity & Add To Cart Button --}}
                        <div class="pt-2 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center border border-gallery-border rounded-md bg-white">
                                    <button
                                        type="button"
                                        @click="decrementQty"
                                        class="px-3.5 py-3 text-gallery-muted hover:text-gallery-charcoal transition-colors"
                                        aria-label="Decrease quantity"
                                    >
                                        &minus;
                                    </button>
                                    <span class="w-8 text-center text-xs font-semibold text-gallery-charcoal" x-text="quantity"></span>
                                    <button
                                        type="button"
                                        @click="incrementQty"
                                        class="px-3.5 py-3 text-gallery-muted hover:text-gallery-charcoal transition-colors"
                                        aria-label="Increase quantity"
                                    >
                                        &plus;
                                    </button>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="flex-1 bg-gallery-accent hover:bg-gallery-accent-hover disabled:opacity-50 text-white text-xs uppercase tracking-wider font-semibold py-3.5 px-6 rounded-md transition-colors shadow-sm flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span x-show="!isSubmitting">Add Artwork to Bag</span>
                                    <span x-show="isSubmitting">Preparing Selection...</span>
                                </button>
                            </div>

                            {{-- Feedback notice --}}
                            <div
                                x-show="feedbackMessage"
                                x-transition
                                :class="feedbackType === 'success' ? 'bg-gallery-cream border-gallery-border text-gallery-charcoal' : 'bg-red-50 border-red-200 text-red-800'"
                                class="p-3.5 rounded-md border text-xs"
                                x-text="feedbackMessage"
                            ></div>
                        </div>
                    </form>

                    {{-- Description & Provenance --}}
                    @if($product->description)
                        <div class="border-t border-gallery-border pt-6 space-y-2">
                            <h3 class="text-xs uppercase tracking-wider font-semibold text-gallery-charcoal">Artwork Overview</h3>
                            <div class="text-sm text-gallery-muted leading-relaxed">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Complementary Artworks --}}
            @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
                <div class="mt-20 pt-14 border-t border-gallery-border">
                    <div class="mb-8">
                        <span class="text-xs uppercase tracking-wider text-gallery-muted font-medium block mb-1">Companion Pieces</span>
                        <h2 class="font-serif text-2xl text-gallery-charcoal font-normal">Related Artworks</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($relatedProducts as $rel)
                            @php $relImg = $rel->images->first(); @endphp
                            <div class="group flex flex-col bg-white rounded-lg border border-gallery-border hover:border-gallery-muted/40 hover:shadow-sm transition-all overflow-hidden">
                                <a href="{{ route('shop.show', $rel) }}" class="block aspect-[4/5] bg-[#F9F9F8] p-5 flex items-center justify-center overflow-hidden">
                                    @if($relImg)
                                        <img
                                            src="{{ Storage::url($relImg->path) }}"
                                            alt="{{ $rel->title }}"
                                            class="w-full h-full object-contain filter drop-shadow group-hover:scale-[1.03] transition-transform duration-500"
                                        >
                                    @else
                                        <div class="text-gallery-muted text-xs uppercase tracking-wider font-medium">Archival Print</div>
                                    @endif
                                </a>
                                <div class="p-4 flex flex-col flex-1 justify-between border-t border-gallery-border">
                                    <div>
                                        <h4 class="font-medium text-sm text-gallery-charcoal group-hover:text-gallery-accent transition-colors truncate">
                                            <a href="{{ route('shop.show', $rel) }}">
                                                {{ $rel->title }}
                                            </a>
                                        </h4>
                                        <span class="text-xs text-gallery-muted block mt-0.5">
                                            {{ $rel->user?->name ?? 'Quirka Studio' }}
                                        </span>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-gallery-border flex items-baseline justify-between">
                                        <span class="text-xs text-gallery-muted">Edition</span>
                                        <div class="text-sm font-semibold text-gallery-charcoal">
                                            <span class="text-[10px] font-normal text-gallery-muted uppercase">EGP</span> {{ number_format($rel->price, 2) }}
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
                class="fixed inset-0 z-50 flex items-center justify-center bg-gallery-charcoal/90 backdrop-blur-md p-4 sm:p-8"
            >
                <button
                    type="button"
                    @click="zoomOpen = false"
                    class="absolute top-6 right-6 text-white/80 hover:text-white p-2 text-2xl transition-colors"
                    aria-label="Close lightbox"
                >
                    &times;
                </button>
                <template x-if="images.length > 0">
                    <img
                        :src="images[activeImageIndex]"
                        alt="{{ $product->title }}"
                        class="max-w-full max-h-[90vh] object-contain shadow-2xl rounded-lg"
                    >
                </template>
            </div>
        </template>
    </div>
</x-app-layout>
