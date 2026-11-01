<footer class="bg-white border-t border-gallery-border mt-20 sm:mt-28 py-16 text-gallery-charcoal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-gallery-border">
            {{-- Brand Column --}}
            <div class="md:col-span-5 space-y-4">
                <span class="text-xs uppercase tracking-wider text-gallery-muted font-semibold">Contemporary Art Editions</span>
                <h3 class="font-serif text-3xl font-normal tracking-tight text-gallery-charcoal">Quirka</h3>
                <p class="text-sm text-gallery-muted leading-relaxed max-w-sm">
                    Connecting collectors with curated, on-demand museum-grade prints by independent artists. Handcrafted solid wood framing and archival pigment printing.
                </p>
                <div class="pt-1">
                    <span class="inline-block px-3 py-1 bg-gallery-cream text-gallery-charcoal text-xs font-medium rounded-full border border-gallery-border">
                        Archival Fine Art Standards
                    </span>
                </div>
            </div>

            {{-- Navigation Columns --}}
            <div class="md:col-span-3 space-y-3">
                <h4 class="text-xs uppercase tracking-wider font-semibold text-gallery-charcoal">Collection</h4>
                <ul class="space-y-2.5 text-sm text-gallery-muted">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-gallery-charcoal transition-colors">The Art Collection</a></li>
                    @auth
                        <li><a href="{{ route('my-orders.index') }}" class="hover:text-gallery-charcoal transition-colors">My Orders</a></li>
                    @endauth
                    <li><a href="{{ route('cart.index') }}" class="hover:text-gallery-charcoal transition-colors">Shopping Bag</a></li>
                </ul>
            </div>

            <div class="md:col-span-4 space-y-3">
                <h4 class="text-xs uppercase tracking-wider font-semibold text-gallery-charcoal">Collectors' Care</h4>
                <ul class="space-y-2.5 text-sm text-gallery-muted">
                    <li><a href="{{ route('legal.shipping') }}" class="hover:text-gallery-charcoal transition-colors">Shipping &amp; Packaging</a></li>
                    <li><a href="{{ route('legal.returns') }}" class="hover:text-gallery-charcoal transition-colors">Returns &amp; Replacement</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="hover:text-gallery-charcoal transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-gallery-charcoal transition-colors">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gallery-muted">
            <div>
                &copy; {{ date('Y') }} Quirka. Works copyrighted by their respective artists.
            </div>
            <div class="text-xs font-medium text-gallery-muted">
                Cairo &middot; On-Demand Fine Art
            </div>
        </div>
    </div>
</footer>
