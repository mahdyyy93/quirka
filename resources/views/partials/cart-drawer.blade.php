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
                    this.statusMessage = event.detail.message || 'Item added to your cart.';
                    this.isOpen = true;
                }
            });
        }
    }"
    x-cloak
    class="relative z-50"
>
    {{-- Global Cart Count Synchronizer for Navigation badges --}}
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
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50"
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
                class="fixed inset-y-0 right-0 max-w-full flex pl-10 z-50"
            >
                <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between">
                    {{-- Drawer Header --}}
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Added to Cart</h2>
                        </div>
                        <button
                            @click="isOpen = false"
                            class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Drawer Body --}}
                    <div class="p-6 flex-1 overflow-y-auto">
                        <template x-if="lastItem">
                            <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-100 flex gap-4">
                                <div class="w-20 h-24 rounded-xl overflow-hidden bg-white shrink-0 border border-gray-200/60 shadow-sm flex items-center justify-center">
                                    <template x-if="lastItem.image_url">
                                        <img :src="lastItem.image_url" :alt="lastItem.title" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!lastItem.image_url">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0 flex flex-col justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm leading-snug truncate" x-text="lastItem.title"></h4>
                                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                                            <template x-if="lastItem.size">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-white border border-gray-200 text-gray-700" x-text="lastItem.size"></span>
                                            </template>
                                            <template x-if="lastItem.color">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-white border border-gray-200 text-gray-700" x-text="lastItem.color"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200/50">
                                        <span class="text-xs text-gray-500">Qty: <span class="font-bold text-gray-800" x-text="lastItem.quantity"></span></span>
                                        <span class="text-sm font-bold text-gray-900">
                                            <span class="text-[10px] font-normal text-gray-500">EGP</span>
                                            <span x-text="(lastItem.unit_price * lastItem.quantity).toFixed(2)"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Assurance Badges --}}
                        <div class="mt-6 space-y-3 pt-6 border-t border-gray-100 text-xs text-gray-500">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Printed on-demand using archival museum ink</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Fast delivery across Cairo & all Governorates</span>
                            </div>
                        </div>
                    </div>

                    {{-- Drawer Footer with Actions --}}
                    <div class="p-6 border-t border-gray-100 bg-gray-50 space-y-3">
                        <a
                            href="{{ route('cart.index') }}"
                            class="block w-full py-3 px-4 text-center rounded-xl font-semibold text-sm bg-gray-900 text-white hover:bg-gray-800 active:scale-[0.99] transition-all shadow-md"
                        >
                            View Cart (<span x-text="cartCount"></span> items)
                        </a>
                        <button
                            type="button"
                            @click="isOpen = false"
                            class="block w-full py-2.5 px-4 text-center rounded-xl font-medium text-xs text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                        >
                            Continue Shopping
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
