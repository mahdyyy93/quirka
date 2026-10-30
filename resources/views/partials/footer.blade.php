<footer class="bg-gallery-cream border-t-2 border-gallery-burgundy mt-24 py-16 text-gallery-charcoal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-12 pb-12 border-b border-gallery-burgundy/20">
            {{-- Brand Column --}}
            <div class="md:col-span-5 space-y-4">
                <span class="text-xs uppercase tracking-[0.25em] text-gallery-burgundy font-bold">Contemporary Art Marketplace</span>
                <h3 class="font-serif text-3xl font-normal tracking-tight text-gallery-charcoal">Quirka</h3>
                <p class="text-sm text-gallery-muted leading-relaxed max-w-sm">
                    Connecting discerning collectors with curated, on-demand museum-grade prints by independent artists. Produced with archival inks and premium wooden framing.
                </p>
                <div class="pt-2">
                    <span class="inline-block px-3 py-1 bg-gallery-burgundy/10 text-gallery-burgundy text-[11px] uppercase tracking-wider font-semibold border border-gallery-burgundy/30">
                        Archival Guarantee
                    </span>
                </div>
            </div>

            {{-- Navigation Columns --}}
            <div class="md:col-span-3 space-y-3">
                <h4 class="text-xs uppercase tracking-[0.2em] font-bold text-gallery-burgundy">Curation</h4>
                <ul class="space-y-2 text-sm text-gallery-muted">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-gallery-burgundy transition-colors">The Art Collection</a></li>
                    @auth
                        <li><a href="{{ route('my-orders.index') }}" class="hover:text-gallery-burgundy transition-colors">Order Archive</a></li>
                    @endauth
                    <li><a href="{{ route('cart.index') }}" class="hover:text-gallery-burgundy transition-colors">Acquisition Bag</a></li>
                </ul>
            </div>

            <div class="md:col-span-4 space-y-3">
                <h4 class="text-xs uppercase tracking-[0.2em] font-bold text-gallery-burgundy">Collectors' Service</h4>
                <ul class="space-y-2 text-sm text-gallery-muted">
                    <li><a href="{{ route('legal.shipping') }}" class="hover:text-gallery-burgundy transition-colors">Delivery & Archival Care</a></li>
                    <li><a href="{{ route('legal.returns') }}" class="hover:text-gallery-burgundy transition-colors">Returns & Guarantees</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="hover:text-gallery-burgundy transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-gallery-burgundy transition-colors">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gallery-muted">
            <div>
                &copy; {{ date('Y') }} Quirka. All works protected by copyright of their respective artists.
            </div>
            <div class="text-xs tracking-wider uppercase text-gallery-burgundy font-semibold">
                Cairo &middot; On-Demand Fine Art
            </div>
        </div>
    </div>
</footer>
