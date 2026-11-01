<x-app-layout>
    <div class="py-8 lg:py-14">
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Modern Hero Section --}}
            <section class="mb-16 lg:mb-20 border-b border-gallery-border pb-12 lg:pb-16">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                    {{-- Text Column --}}
                    <div class="lg:col-span-7 space-y-6">
                        <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold block">
                            Contemporary Fine Art &amp; Editions
                        </span>

                        <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-normal leading-[1.14] text-gallery-charcoal tracking-tight">
                            Curated art editions for contemporary living.
                        </h1>

                        <p class="text-base sm:text-lg text-gallery-muted max-w-xl leading-relaxed">
                            Discover original works by independent artists, thoughtfully produced as museum-grade archival prints. Handcrafted wooden framing on demand with archival precision.
                        </p>

                        <div class="pt-2 flex flex-wrap items-center gap-5">
                            <a href="#collection-grid" class="inline-flex items-center justify-center bg-gallery-accent hover:bg-gallery-accent-hover text-white text-xs uppercase tracking-wider font-semibold px-7 py-3.5 rounded-md shadow-sm transition-all duration-200">
                                Explore The Collection
                            </a>
                            <span class="text-xs text-gallery-muted font-medium">
                                Archival Standards &middot; Solid Wood Framing &middot; Cairo, Egypt
                            </span>
                        </div>
                    </div>

                    {{-- Hero Featured Artwork --}}
                    <div class="lg:col-span-5">
                        @php
                            $featuredProduct = $products->first();
                            $featuredImage = $featuredProduct ? $featuredProduct->images->first() : null;
                        @endphp
                        @if($featuredProduct)
                            <div class="group relative">
                                <div class="bg-white rounded-xl p-3 sm:p-4 border border-gallery-border shadow-sm hover:shadow-md transition-all duration-300">
                                    <a href="{{ route('shop.show', $featuredProduct) }}" class="block aspect-[4/5] rounded-lg overflow-hidden bg-[#F9F9F8] relative focus:outline-none" aria-label="View {{ $featuredProduct->title }}">
                                        @if($featuredImage)
                                            <img
                                                src="{{ Storage::url($featuredImage->path) }}"
                                                alt="{{ $featuredProduct->title }}"
                                                class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-700 ease-out"
                                            >
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center text-gallery-muted">
                                                <svg class="w-12 h-12 mb-2 stroke-1 text-gallery-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-xs uppercase tracking-wider text-gallery-muted">Featured Artwork</span>
                                            </div>
                                        @endif
                                    </a>

                                    <div class="mt-3.5 px-1 pb-1 flex items-baseline justify-between gap-4">
                                        <div class="min-w-0">
                                            <span class="text-[11px] uppercase tracking-wider text-gallery-muted font-medium block">Exhibition Highlight</span>
                                            <a href="{{ route('shop.show', $featuredProduct) }}" class="font-medium text-base text-gallery-charcoal hover:text-gallery-accent transition-colors truncate block">
                                                {{ $featuredProduct->title }}
                                            </a>
                                            <span class="block text-xs text-gallery-muted mt-0.5">
                                                {{ $featuredProduct->user?->name ?? 'Quirka Studio' }}
                                            </span>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="text-[10px] uppercase tracking-wider text-gallery-muted font-medium block">From</span>
                                            <div class="text-base font-semibold text-gallery-charcoal">
                                                <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span> {{ number_format($featuredProduct->price, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Curated Collection Header --}}
            <div id="collection-grid" class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gallery-border pb-6">
                <div>
                    <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold block mb-1">Curated Catalogue</span>
                    <h2 class="font-serif text-3xl font-normal text-gallery-charcoal tracking-tight">The Art Collection</h2>
                    <p class="text-sm text-gallery-muted mt-1">Each work is custom printed on archival paper with options for handcrafted wooden frames.</p>
                </div>
                <div class="text-xs text-gallery-muted font-medium bg-white px-3.5 py-1.5 rounded-full border border-gallery-border self-start md:self-end">
                    <span>{{ $products->total() }} Works Available</span>
                </div>
            </div>

            {{-- Gallery Grid Component with Quick-Acquisition Modal --}}
            <div
                x-data="{
                    activeProduct: null,
                    quickViewOpen: false,
                    selectedSize: '',
                    selectedColor: '',
                    currentPrice: 0,
                    variantId: null,
                    quantity: 1,
                    isSubmitting: false,

                    openQuickAcquire(prod) {
                        this.activeProduct = prod;
                        this.quantity = 1;

                        if (prod.variants && prod.variants.length > 0) {
                            let firstVar = prod.variants[0];
                            this.selectedSize = firstVar.size;
                            this.selectedColor = firstVar.color;
                            this.currentPrice = firstVar.price;
                            this.variantId = firstVar.id;
                        } else {
                            this.selectedSize = prod.sizes.length > 0 ? prod.sizes[0] : '';
                            this.selectedColor = prod.colors.length > 0 ? prod.colors[0] : '';
                            this.currentPrice = prod.base_price;
                            this.variantId = null;
                        }

                        this.quickViewOpen = true;
                    },

                    updateSelection() {
                        if (!this.activeProduct || !this.activeProduct.variants || this.activeProduct.variants.length === 0) return;

                        let matched = this.activeProduct.variants.find(v =>
                            v.size.toLowerCase() === this.selectedSize.toLowerCase() &&
                            v.color.toLowerCase() === this.selectedColor.toLowerCase()
                        );

                        if (!matched) {
                            matched = this.activeProduct.variants.find(v => v.size.toLowerCase() === this.selectedSize.toLowerCase());
                            if (matched) {
                                this.selectedColor = matched.color;
                            }
                        }

                        if (matched) {
                            this.variantId = matched.id;
                            this.currentPrice = matched.price;
                        }
                    },

                    async submitQuickAdd() {
                        if (this.isSubmitting || !this.activeProduct) return;
                        this.isSubmitting = true;

                        try {
                            const res = await fetch('/cart/' + this.activeProduct.id, {
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

                            const data = await res.json();
                            if (res.ok && data.success) {
                                window.dispatchEvent(new CustomEvent('cart-updated', {
                                    detail: {
                                        cartCount: data.cartCount,
                                        item: data.item,
                                        message: data.message
                                    }
                                }));
                                this.quickViewOpen = false;
                            } else {
                                alert(data.message || 'Unable to add artwork to cart.');
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Network error adding artwork to selection.');
                        } finally {
                            this.isSubmitting = false;
                        }
                    }
                }"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                    @forelse($products as $product)
                        @php
                            $firstImage = $product->images->first();
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
                            ])->values() : collect();

                            $prodData = [
                                'id' => $product->id,
                                'title' => $product->title,
                                'artist' => $product->user?->name ?? 'Quirka Studio',
                                'slug' => $product->slug,
                                'sku' => $product->sku,
                                'base_price' => (float) $product->price,
                                'image_url' => $firstImage ? Storage::url($firstImage->path) : null,
                                'variants' => $variantsJson,
                                'sizes' => $uniqueSizes,
                                'colors' => $uniqueColors,
                            ];
                        @endphp

                        <div class="group flex flex-col bg-white rounded-lg border border-gallery-border hover:border-gallery-muted/40 hover:shadow-sm transition-all duration-300 overflow-hidden">
                            {{-- Artwork Preview Area --}}
                            <div class="relative aspect-[4/5] bg-[#F9F9F8] overflow-hidden p-6 sm:p-7 flex items-center justify-center">
                                <a href="{{ route('shop.show', $product) }}" class="block w-full h-full relative focus:outline-none" aria-label="View {{ $product->title }}">
                                    @if($firstImage)
                                        <img
                                            src="{{ Storage::url($firstImage->path) }}"
                                            alt="{{ $product->title }}"
                                            loading="lazy"
                                            class="w-full h-full object-contain filter drop-shadow-[0_4px_16px_rgba(0,0,0,0.06)] group-hover:scale-[1.03] transition-transform duration-500 ease-out"
                                        >
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-gallery-muted">
                                            <svg class="w-10 h-10 stroke-1 mb-2 text-gallery-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-xs uppercase tracking-wider text-gallery-muted">Print Preview</span>
                                        </div>
                                    @endif
                                </a>

                                {{-- Quick View Action Overlay --}}
                                <div class="absolute bottom-3 left-3 right-3 opacity-0 translate-y-1 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200 flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="openQuickAcquire({{ json_encode($prodData) }})"
                                        class="flex-1 bg-gallery-charcoal/90 hover:bg-gallery-charcoal text-white text-xs font-medium py-2.5 px-3 rounded-md shadow-sm backdrop-blur-sm transition-colors text-center"
                                    >
                                        Quick Select
                                    </button>
                                    <a
                                        href="{{ route('shop.show', $product) }}"
                                        class="bg-white/95 hover:bg-white text-gallery-charcoal p-2.5 rounded-md border border-gallery-border transition-colors shadow-sm"
                                        title="View Details"
                                        aria-label="View details for {{ $product->title }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {{-- Metadata Area --}}
                            <div class="p-4 sm:p-5 flex flex-col flex-1 justify-between bg-white border-t border-gallery-border/60">
                                <div class="space-y-1">
                                    <h3 class="font-medium text-base text-gallery-charcoal leading-snug line-clamp-1 group-hover:text-gallery-accent transition-colors">
                                        <a href="{{ route('shop.show', $product) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gallery-muted">
                                        {{ $product->user?->name ?? 'Quirka Studio' }}
                                    </p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gallery-border/60 flex items-baseline justify-between">
                                    <div class="text-xs text-gallery-muted">
                                        @if($uniqueSizes->isNotEmpty())
                                            <span>{{ $uniqueSizes->count() }} sizes</span>
                                        @else
                                            <span>Archival Edition</span>
                                        @endif
                                    </div>
                                    <div class="text-base font-semibold text-gallery-charcoal">
                                        <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                                        {{ number_format($product->price, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-24 bg-white rounded-xl border border-gallery-border p-8">
                            <span class="text-xs uppercase tracking-wider text-gallery-muted font-medium block mb-2">Exhibition Pending</span>
                            <h3 class="font-serif text-2xl text-gallery-charcoal">No Artworks Currently Available</h3>
                            <p class="text-sm text-gallery-muted mt-2 font-normal">New curated releases are scheduled soon. Please check back shortly.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Contemporary Quick-Acquisition Modal --}}
                <template x-teleport="body">
                    <div
                        x-show="quickViewOpen"
                        x-cloak
                        class="fixed inset-0 z-50 overflow-y-auto"
                        aria-labelledby="modal-title"
                        role="dialog"
                        aria-modal="true"
                    >
                        {{-- Backdrop --}}
                        <div
                            x-show="quickViewOpen"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            @click="quickViewOpen = false"
                            class="fixed inset-0 bg-gallery-charcoal/40 backdrop-blur-sm transition-opacity"
                        ></div>

                        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                            <div
                                x-show="quickViewOpen"
                                x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                class="relative transform overflow-hidden bg-white rounded-xl border border-gallery-border text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                            >
                                <button
                                    type="button"
                                    @click="quickViewOpen = false"
                                    class="absolute top-4 right-4 text-gallery-muted hover:text-gallery-charcoal p-2 rounded-full hover:bg-gallery-cream transition-colors z-10"
                                    aria-label="Close modal"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>

                                <div class="grid grid-cols-1 md:grid-cols-2">
                                    {{-- Modal Artwork Preview --}}
                                    <div class="bg-[#F9F9F8] p-8 flex items-center justify-center border-b md:border-b-0 md:border-r border-gallery-border">
                                        <template x-if="activeProduct && activeProduct.image_url">
                                            <img :src="activeProduct.image_url" :alt="activeProduct.title" class="max-h-72 object-contain drop-shadow-md">
                                        </template>
                                        <template x-if="activeProduct && !activeProduct.image_url">
                                            <div class="text-gallery-muted text-xs uppercase tracking-wider font-medium">Archival Print</div>
                                        </template>
                                    </div>

                                    {{-- Modal Controls --}}
                                    <div class="p-6 sm:p-8 flex flex-col justify-between space-y-6">
                                        <div class="space-y-1.5">
                                            <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Direct Acquisition</span>
                                            <h3 class="font-serif text-2xl text-gallery-charcoal" x-text="activeProduct ? activeProduct.title : ''"></h3>
                                            <p class="text-xs text-gallery-muted" x-text="activeProduct ? activeProduct.artist : ''"></p>
                                        </div>

                                        {{-- Options --}}
                                        <div class="space-y-4">
                                            <template x-if="activeProduct && activeProduct.sizes && activeProduct.sizes.length > 0">
                                                <div>
                                                    <label class="block text-xs uppercase tracking-wider font-medium text-gallery-charcoal mb-1.5">Print Size</label>
                                                    <select
                                                        x-model="selectedSize"
                                                        @change="updateSelection()"
                                                        class="w-full bg-white border border-gallery-border rounded-md text-xs font-medium text-gallery-charcoal px-3 py-2.5 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                                                    >
                                                        <template x-for="sz in activeProduct.sizes" :key="sz">
                                                            <option :value="sz" x-text="sz"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>

                                            <template x-if="activeProduct && activeProduct.colors && activeProduct.colors.length > 0">
                                                <div>
                                                    <label class="block text-xs uppercase tracking-wider font-medium text-gallery-charcoal mb-1.5">Frame Finish</label>
                                                    <select
                                                        x-model="selectedColor"
                                                        @change="updateSelection()"
                                                        class="w-full bg-white border border-gallery-border rounded-md text-xs font-medium text-gallery-charcoal px-3 py-2.5 focus:border-gallery-accent focus:ring-1 focus:ring-gallery-accent"
                                                    >
                                                        <template x-for="cl in activeProduct.colors" :key="cl">
                                                            <option :value="cl" x-text="cl"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>

                                            <div class="pt-2 flex items-baseline justify-between border-t border-gallery-border">
                                                <span class="text-xs uppercase tracking-wider text-gallery-muted font-medium">Price</span>
                                                <div class="text-2xl font-semibold text-gallery-charcoal">
                                                    <span class="text-xs font-normal text-gallery-muted uppercase">EGP</span>
                                                    <span x-text="parseFloat(currentPrice).toFixed(2)"></span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Buttons --}}
                                        <div class="space-y-3 pt-2">
                                            <button
                                                type="button"
                                                @click="submitQuickAdd()"
                                                :disabled="isSubmitting"
                                                class="w-full bg-gallery-accent hover:bg-gallery-accent-hover disabled:opacity-50 text-white text-xs uppercase tracking-wider font-semibold py-3.5 rounded-md transition-colors shadow-sm"
                                            >
                                                <span x-show="!isSubmitting">Add Artwork to Bag</span>
                                                <span x-show="isSubmitting">Preparing Artwork...</span>
                                            </button>
                                            <a
                                                :href="'/products/' + (activeProduct ? activeProduct.slug : '')"
                                                class="block text-center text-xs font-medium text-gallery-charcoal hover:text-gallery-accent transition-colors py-1"
                                            >
                                                View Complete Details &amp; Provenance &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Gallery Pagination --}}
            @if($products->hasPages())
                <div class="mt-16 pt-8 border-t border-gallery-border">
                    {{ $products->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
