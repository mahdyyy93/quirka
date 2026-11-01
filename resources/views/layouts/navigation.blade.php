<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gallery-border transition-colors">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('shop.index') }}" class="flex items-center gap-3 focus:outline-none" aria-label="Quirka Home">
                        <img src="{{ asset('images/quirka-logo-horizontal.jpg') }}" alt="Quirka" class="h-9 sm:h-10 w-auto object-contain">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:items-center sm:gap-7">
                    <a href="{{ route('shop.index') }}"
                       class="text-sm font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('shop.index') ? 'border-gallery-accent text-gallery-charcoal font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-charcoal hover:border-gallery-border' }}">
                        {{ __('Gallery') }}
                    </a>

                    @auth
                        <a href="{{ route('my-orders.index') }}"
                           class="text-sm font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('my-orders.*') ? 'border-gallery-accent text-gallery-charcoal font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-charcoal hover:border-gallery-border' }}">
                            {{ __('My Orders') }}
                        </a>
                    @endauth

                    @can('products.view')
                        <a href="{{ route('dashboard') }}"
                           class="text-sm font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('dashboard') || request()->routeIs('products.*') ? 'border-gallery-accent text-gallery-charcoal font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-charcoal hover:border-gallery-border' }}">
                            {{ __('Curate Art') }}
                        </a>
                    @endcan

                    @can('orders.view')
                        <a href="{{ route('orders.index') }}"
                           class="text-sm font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('orders.*') ? 'border-gallery-accent text-gallery-charcoal font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-charcoal hover:border-gallery-border' }}">
                            {{ __('Orders') }}
                        </a>
                        <a href="{{ route('wallets.index') }}"
                           class="text-sm font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('wallets.*') ? 'border-gallery-accent text-gallery-charcoal font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-charcoal hover:border-gallery-border' }}">
                            {{ __('Payouts & Wallets') }}
                        </a>
                    @endcan

                    @can('pricing-limits.index')
                        <a href="{{ route('pricing-limits.index') }}"
                           class="text-sm font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('pricing-limits.*') ? 'border-gallery-accent text-gallery-charcoal font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-charcoal hover:border-gallery-border' }}">
                            {{ __('Pricing') }}
                        </a>
                    @endcan

                    @can('customers.view')
                        <a href="{{ route('customers.index') }}"
                           class="text-sm font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('customers.*') ? 'border-gallery-accent text-gallery-charcoal font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-charcoal hover:border-gallery-border' }}">
                            {{ __('Clients') }}
                        </a>
                    @endcan

                    @can('roles.view')
                        <a href="{{ route('roles.index') }}"
                           class="text-sm font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('roles.*') ? 'border-gallery-accent text-gallery-charcoal font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-charcoal hover:border-gallery-border' }}">
                            {{ __('Permissions') }}
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Right Side Action Cluster (Cart + Auth) -->
            <div class="hidden sm:flex sm:items-center sm:gap-4">
                {{-- Cart Button --}}
                @php $cartCount = collect(session()->get('cart', []))->sum('quantity'); @endphp
                <div
                    x-data="{ count: {{ $cartCount }} }"
                    x-on:cart-updated.window="if ($event.detail && typeof $event.detail.cartCount !== 'undefined') count = $event.detail.cartCount"
                    class="relative"
                >
                    <a href="{{ route('cart.index') }}" class="group relative inline-flex items-center gap-2 px-3.5 py-2 rounded-md bg-gallery-cream/70 hover:bg-gallery-cream text-gallery-charcoal text-xs font-medium border border-gallery-border hover:border-gallery-muted/40 transition-colors">
                        <svg class="w-4 h-4 text-gallery-charcoal group-hover:text-gallery-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Cart</span>
                        <template x-if="count > 0">
                            <span x-text="count" class="bg-gallery-accent text-white text-[11px] font-semibold rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        </template>
                    </a>
                    @if(session('success') || session('error'))
                        <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 3000)" x-show="visible" x-transition class="absolute top-full right-0 z-50 mt-2 w-64 rounded-md border px-3.5 py-2.5 text-xs shadow-lg {{ session('success') ? 'border-gallery-border bg-white text-gallery-charcoal' : 'border-red-200 bg-red-50 text-red-800' }}">
                            {{ session('success') ?? session('error') }}
                        </div>
                    @endif
                </div>

                @auth
                    {{-- User Profile Dropdown --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-gallery-border rounded-md text-xs font-medium text-gallery-charcoal bg-white hover:bg-gallery-cream/50 hover:border-gallery-muted/40 focus:outline-none transition-colors">
                                <span>{{ auth()->user()->name }}</span>
                                <svg class="ms-1.5 h-3.5 w-3.5 fill-current text-gallery-muted" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')" class="text-xs text-gallery-charcoal hover:bg-gallery-cream/60">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('artist.wallet')" class="text-xs text-gallery-charcoal hover:bg-gallery-cream/60">
                                {{ __('Artist Wallet') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-xs text-gallery-accent hover:bg-gallery-cream/60">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    {{-- Guest Auth Links --}}
                    <div class="flex items-center gap-3 text-xs font-medium">
                        <a href="{{ route('login') }}" class="text-gallery-muted hover:text-gallery-charcoal px-2.5 py-1.5 transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="bg-gallery-accent hover:bg-gallery-accent-hover text-white px-4 py-2 rounded-md font-medium transition-colors shadow-sm">
                            Join Quirka
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile Hamburger button -->
            <div class="flex items-center sm:hidden gap-2">
                <div
                    x-data="{ count: {{ $cartCount }} }"
                    x-on:cart-updated.window="if ($event.detail && typeof $event.detail.cartCount !== 'undefined') count = $event.detail.cartCount"
                    class="relative"
                >
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center p-2 text-gallery-charcoal" aria-label="Cart">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <template x-if="count > 0">
                            <span x-text="count" class="absolute top-0.5 right-0.5 bg-gallery-accent text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        </template>
                    </a>
                </div>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gallery-charcoal hover:bg-gallery-cream focus:outline-none transition-colors" aria-label="Toggle navigation">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-b border-gallery-border">
        <div class="pt-3 pb-4 px-4 space-y-1">
            <a href="{{ route('shop.index') }}" class="block py-2 text-sm font-medium {{ request()->routeIs('shop.index') ? 'text-gallery-accent font-semibold' : 'text-gallery-charcoal' }}">
                {{ __('Gallery') }}
            </a>

            @auth
                <a href="{{ route('my-orders.index') }}" class="block py-2 text-sm font-medium {{ request()->routeIs('my-orders.*') ? 'text-gallery-accent font-semibold' : 'text-gallery-charcoal' }}">
                    {{ __('My Orders') }}
                </a>
            @endauth

            @can('products.view')
                <a href="{{ route('dashboard') }}" class="block py-2 text-sm font-medium text-gallery-charcoal hover:text-gallery-accent">
                    {{ __('Curate Art') }}
                </a>
            @endcan

            @can('orders.view')
                <a href="{{ route('orders.index') }}" class="block py-2 text-sm font-medium text-gallery-charcoal hover:text-gallery-accent">
                    {{ __('Orders') }}
                </a>
                <a href="{{ route('wallets.index') }}" class="block py-2 text-sm font-medium text-gallery-charcoal hover:text-gallery-accent">
                    {{ __('Payouts & Wallets') }}
                </a>
            @endcan

            @can('pricing-limits.index')
                <a href="{{ route('pricing-limits.index') }}" class="block py-2 text-sm font-medium text-gallery-charcoal hover:text-gallery-accent">
                    {{ __('Pricing') }}
                </a>
            @endcan

            @can('customers.view')
                <a href="{{ route('customers.index') }}" class="block py-2 text-sm font-medium text-gallery-charcoal hover:text-gallery-accent">
                    {{ __('Clients') }}
                </a>
            @endcan

            @can('roles.view')
                <a href="{{ route('roles.index') }}" class="block py-2 text-sm font-medium text-gallery-charcoal hover:text-gallery-accent">
                    {{ __('Permissions') }}
                </a>
            @endcan
        </div>

        @auth
            <div class="pt-4 pb-4 border-t border-gallery-border px-4 space-y-2">
                <div class="text-sm font-medium text-gallery-charcoal">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gallery-muted">{{ auth()->user()->email }}</div>
                <div class="mt-3 space-y-1.5">
                    <a href="{{ route('profile.edit') }}" class="block text-xs font-medium text-gallery-charcoal hover:text-gallery-accent py-1">{{ __('Profile') }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block text-xs font-medium text-gallery-accent py-1">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-4 border-t border-gallery-border px-4 flex items-center gap-3">
                <a href="{{ route('login') }}" class="block flex-1 text-center text-xs font-medium text-gallery-charcoal border border-gallery-border py-2 rounded-md hover:bg-gallery-cream">Sign In</a>
                <a href="{{ route('register') }}" class="block flex-1 text-center text-xs font-medium text-white bg-gallery-accent py-2 rounded-md hover:bg-gallery-accent-hover">Join Quirka</a>
            </div>
        @endauth
    </div>
</nav>
