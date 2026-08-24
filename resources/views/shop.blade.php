<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shop</h2>
            <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-2 bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.4 5.6a1 1 0 001 1.4H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                Cart
                @php $cartCount = collect(session()->get('cart', []))->sum('quantity'); @endphp
                @if($cartCount > 0)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($products as $product)
                @php $firstImage = $product->images->first(); @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition-shadow duration-200">

                    {{-- Product image --}}
                    @if($firstImage)
                        <div class="aspect-square overflow-hidden bg-gray-50">
                            <img
                                src="{{ Storage::url($firstImage->path) }}"
                                alt="{{ $product->title }}"
                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                            >
                        </div>
                    @else
                        <div class="aspect-square bg-gray-100 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Card body --}}
                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="font-semibold text-gray-900 text-base leading-snug">{{ $product->title }}</h3>
                            <span class="text-xs text-gray-400 font-mono bg-gray-50 border border-gray-100 px-2 py-0.5 rounded shrink-0">{{ $product->sku }}</span>
                        </div>

                        @if($product->customizations)
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach($product->customizations as $key => $value)
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full capitalize">{{ $key }}: {{ $value }}</span>
                                @endforeach
                            </div>
                        @endif

                        <p class="text-2xl font-bold text-gray-900 mt-auto mb-4">${{ number_format($product->price, 2) }}</p>

                        <form method="POST" action="{{ route('cart.add', $product) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="number" name="quantity" value="1" min="1" max="99"
                                    class="w-16 border border-gray-200 rounded-lg px-2 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-gray-900">
                                <button type="submit"
                                    class="flex-1 bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                    Add to Cart
                                </button>
                            </form>
                    </div>
                </div>
                @empty
                    <div class="col-span-4 text-center py-20 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        No products available right now.
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="mt-8">{{ $products->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>
