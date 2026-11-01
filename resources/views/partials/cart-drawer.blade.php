{{-- Slide-over Cart Drawer --}}
<div
    x-data="{
        isOpen: false,
        cartCount: {{ collect(session()->get('cart', []))->sum('quantity') }},
        lastItem: null,
        statusMessage: '',
        
        init() {
            window.addEventListener('cart-updated', (event) => {
                if (event.detail && typeof event.detail.cartCount !== 'undefined') {
                    this.cartCount = event.detail.cartCount;
                }
                if (event.detail && event.detail.item) {
                    this.lastItem = event.detail.item;
                    this.statusMessage = event.detail.message || 'Artwork added to your selection.';
                    this.isOpen = true;
                }
            });
        }
    }"
    x-cloak
    class="relative z-50"
>
    <template x-teleport="body">
        <div>
            {{-- Backdrop --}}
            <div
                x-show="isOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="isOpen = false"
                class="fixed inset-0 bg-gallery-charcoal/40 backdrop-blur-sm z-50"
            ></div>

            {{-- Drawer Panel --}}
            <div
                x-show="isOpen"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-10 z-50"
            >
                <div class="w-screen max-w-md bg-white border-l border-gallery-border shadow-2xl flex flex-col justify-between">
                    {{-- Drawer Header --}}
                    <div class="p-6 border-b border-gallery-border flex items-center justify-between bg-white">
                        <div class="space-y-0.5">
                            <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Selection Updated</span>
                            <h2 class="font-serif text-xl font-normal text-gallery-charcoal">Your Collection Bag</h2>
                        </div>
                        <button
                            @click="isOpen = false"
                            class="text-gallery-muted hover:text-gallery-charcoal p-2 rounded-full hover:bg-gallery-cream transition-colors"
                            aria-label="Close bag"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Drawer Body --}}
                    <div class="p-6 flex-1 overflow-y-auto space-y-4 bg-white">
                        <template x-if="lastItem">
                            <div class="bg-[#F9F9F8] p-4 rounded-lg border border-gallery-border flex gap-4">
                                <div class="w-20 h-24 bg-white rounded-md shrink-0 border border-gallery-border overflow-hidden flex items-center justify-center p-1">
                                    <template x-if="lastItem.image_url">
                                        <img :src="lastItem.image_url" :alt="lastItem.title" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!lastItem.image_url">
                                        <svg class="w-8 h-8 text-gallery-muted/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                    <div>
                                        <h4 class="font-medium text-sm text-gallery-charcoal truncate" x-text="lastItem.title"></h4>
                                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                                            <template x-if="lastItem.size">
                                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] uppercase tracking-wider bg-white border border-gallery-border rounded text-gallery-charcoal font-medium" x-text="lastItem.size"></span>
                                            </template>
                                            <template x-if="lastItem.color">
                                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] uppercase tracking-wider bg-white border border-gallery-border rounded text-gallery-charcoal font-medium" x-text="lastItem.color"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-gallery-border">
                                        <span class="text-xs text-gallery-muted">Qty: <span class="font-semibold text-gallery-charcoal" x-text="lastItem.quantity"></span></span>
                                        <span class="text-sm font-semibold text-gallery-charcoal">
                                            <span class="text-[10px] font-normal text-gallery-muted uppercase">EGP</span>
                                            <span x-text="(lastItem.unit_price * lastItem.quantity).toFixed(2)"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="p-4 bg-gallery-cream/60 rounded-lg border border-gallery-border text-xs text-gallery-muted leading-relaxed">
                            <strong class="font-semibold text-gallery-charcoal">Museum-Grade Standards:</strong> Archival pigment printing on acid-free cotton paper. Solid wood framing with protective glazing.
                        </div>
                    </div>

                    {{-- Drawer Footer Actions --}}
                    <div class="p-6 border-t border-gallery-border bg-white space-y-3">
                        <a
                            href="{{ route('cart.index') }}"
                            class="block w-full text-center bg-gallery-accent hover:bg-gallery-accent-hover text-white text-xs uppercase tracking-wider font-semibold py-3.5 rounded-md transition-colors shadow-sm"
                        >
                            View Cart &amp; Checkout
                        </a>
                        <button
                            type="button"
                            @click="isOpen = false"
                            class="block w-full text-center text-xs font-medium text-gallery-muted hover:text-gallery-charcoal py-2 transition-colors"
                        >
                            Continue Browsing Art
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
