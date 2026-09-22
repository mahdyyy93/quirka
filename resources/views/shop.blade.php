<x-app-layout>
    <div class="py-12">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">

            {{-- Banner --}}
            <div class="mb-12 text-center">
                <h1 class="text-[40px] font-semibold tracking-[0.16em] text-gray-800 uppercase">
                    On-demand printed Museum-quality posters
                </h1>
                <br>
                <div class="flex items-center justify-center gap-4 mb-5">
                    <span class="flex-1 h-px bg-gray-200 max-w-[120px]"></span>
                    <span class="text-[10px] font-semibold tracking-[0.25em] text-gray-400 uppercase">Collection</span>
                    <span class="flex-1 h-px bg-gray-200 max-w-[120px]"></span>
                </div>
            </div>

            {{-- Product grid --}}
            <div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($products as $product)
                    @php $firstImage = $product->images->first(); @endphp
                    <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-lg transition-shadow duration-300">

                        {{-- Product image --}}
                        @if($firstImage)
                            <div class="aspect-square overflow-hidden bg-gray-50">
                                <img
                                    src="{{ Storage::url($firstImage->path) }}"
                                    alt="{{ $product->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                >
                            </div>
                        @else
                            <div class="aspect-square bg-gray-50 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        {{-- Card body --}}
                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900 text-base leading-snug">{{ $product->title }}</h3>
                                <span class="shrink-0 text-[11px] font-mono text-gray-400 bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded mt-0.5">
                                    {{ $product->sku }}
                                </span>
                            </div>

                            @php
                                $sizes = $product->customizations['sizes'] ?? [];
                                if (is_string($sizes)) $sizes = array_map('trim', explode(',', $sizes));
                                $colors = $product->customizations['colors'] ?? [];
                                if (is_string($colors)) $colors = array_map('trim', explode(',', $colors));
                            @endphp

                            <form method="POST" action="{{ route('cart.add', $product) }}" class="space-y-3 mt-auto pt-3">
                                @csrf

                                @if(!empty($sizes) || !empty($colors))
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        @if(!empty($sizes))
                                            <div>
                                                <label class="block text-gray-500 font-medium mb-1">Size</label>
                                                <select name="size" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs bg-gray-50 focus:bg-white focus:ring-1 focus:ring-gray-900 focus:outline-none">
                                                    @foreach($sizes as $sz)
                                                        <option value="{{ $sz }}">{{ $sz }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        @if(!empty($colors))
                                            <div>
                                                <label class="block text-gray-500 font-medium mb-1">Color</label>
                                                <select name="color" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs bg-gray-50 focus:bg-white focus:ring-1 focus:ring-gray-900 focus:outline-none">
                                                    @foreach($colors as $cl)
                                                        <option value="{{ $cl }}">{{ $cl }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-2xl font-bold text-gray-900">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="number"
                                        name="quantity"
                                        value="1"
                                        min="1"
                                        max="99"
                                        class="w-16 border border-gray-200 rounded-lg px-2 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-gray-900"
                                    >
                                    <button
                                        type="submit"
                                        class="flex-1 bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-gray-700 active:scale-[0.98] transition-all duration-150"
                                    >
                                        Add to Cart
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-4 text-center py-24 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        No products available right now.
                    </div>
                @endforelse
            </div>
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="mt-10">{{ $products->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>
