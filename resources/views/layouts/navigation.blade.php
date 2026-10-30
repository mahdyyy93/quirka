<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-gallery-cream/95 backdrop-blur-md border-b border-gallery-border transition-colors">
    <!-- Top Gallery Accent Bar -->
    <div class="h-1 w-full bg-gallery-burgundy"></div>

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('shop.index') }}" class="group flex items-center gap-3">
                        <img src="{{ asset('images/quirka-logo-horizontal.jpg') }}" alt="Quirka" class="h-12 w-auto object-contain rounded-sm border border-gallery-burgundy/30 group-hover:border-gallery-burgundy transition-colors">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:flex items-center">
                    <a href="{{ route('shop.index') }}"
                       class="text-xs uppercase tracking-[0.2em] font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('shop.index') ? 'border-gallery-burgundy text-gallery-burgundy font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-burgundy hover:border-gallery-burgundy/40' }}">
                        {{ __('Gallery') }}
                    </a>

                    @auth
                        <a href="{{ route('my-orders.index') }}"
                           class="text-xs uppercase tracking-[0.2em] font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('my-orders.*') ? 'border-gallery-burgundy text-gallery-burgundy font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-burgundy hover:border-gallery-burgundy/40' }}">
                            {{ __('My Orders') }}
                        </a>
                    @endauth

                    @can('products.view')
                        <a href="{{ route('dashboard') }}"
                           class="text-xs uppercase tracking-[0.2em] font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('dashboard') || request()->routeIs('products.*') ? 'border-gallery-burgundy text-gallery-burgundy font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-burgundy hover:border-gallery-burgundy/40' }}">
                            {{ __('Curate Art') }}
                        </a>
                    @endcan

                    @can('orders.view')
                        <a href="{{ route('orders.index') }}"
                           class="text-xs uppercase tracking-[0.2em] font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('orders.*') ? 'border-gallery-burgundy text-gallery-burgundy font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-burgundy hover:border-gallery-burgundy/40' }}">
                            {{ __('Orders') }}
                        </a>
                    @endcan

                    @can('customers.view')
                        <a href="{{ route('customers.index') }}"
                           class="text-xs uppercase tracking-[0.2em] font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('customers.*') ? 'border-gallery-burgundy text-gallery-burgundy font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-burgundy hover:border-gallery-burgundy/40' }}">
                            {{ __('Clients') }}
                        </a>
                    @endcan

                    @can('roles.view')
                        <a href="{{ route('roles.index') }}"
                           class="text-xs uppercase tracking-[0.2em] font-medium transition-colors pb-1 border-b-2 {{ request()->routeIs('roles.*') ? 'border-gallery-burgundy text-gallery-burgundy font-semibold' : 'border-transparent text-gallery-muted hover:text-gallery-burgundy hover:border-gallery-burgundy/40' }}">
                            {{ __('Permissions') }}
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Right Side Action Cluster (Cart + Auth) -->
            <div class="hidden sm:flex sm:items-center sm:gap-5">
                {{-- Cart Button (Visible for all users) --}}
                @php $cartCount = collect(session()->get('cart', []))->sum('quantity'); @endphp
                <div
                    x-data="{ count: {{ $cartCount }} }"
                    x-on:cart-updated.window="if ($event.detail && typeof $event.detail.cartCount !== 'undefined') count = $event.detail.cartCount"
                    class="relative"
                >
                    <a href="{{ route('cart.index') }}" class="group relative inline-flex items-center gap-2.5 bg-gallery-burgundy hover:bg-gallery-burgundy-dark text-gallery-cream text-xs uppercase tracking-[0.15em] font-medium px-4 py-2.5 rounded-none border border-gallery-burgundy hover:border-gallery-burgundy-dark transition-all duration-200 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-gallery-ivory" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Bag</span>
                        <template x-if="count > 0">
                            <span x-text="count" class="bg-white text-gallery-burgundy text-[10px] font-bold rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center transition-colors">
                                {{ $cartCount }}
                            </span>
                        </template>
                    </a>
                    @if(session('success') || session('error'))
                        <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 3000)" x-show="visible" x-transition class="absolute top-full left-1/2 z-50 mt-2 w-64 -translate-x-1/2 border px-3.5 py-2.5 text-xs shadow-lg {{ session('success') ? 'border-gallery-burgundy/30 bg-gallery-cream text-gallery-burgundy' : 'border-red-200 bg-red-50 text-red-800' }}">
                            {{ session('success') ?? session('error') }}
                        </div>
                    @endif
                </div>

                @auth
                    {{-- User Profile Dropdown --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3.5 py-2 border border-gallery-burgundy/30 text-xs uppercase tracking-[0.15em] font-medium text-gallery-charcoal bg-gallery-cream hover:bg-white hover:border-gallery-burgundy focus:outline-none transition duration-150">
                                <span>{{ auth()->user()->name }}</span>
                                <svg class="ms-1.5 h-3.5 w-3.5 fill-current text-gallery-burgundy" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')" class="text-xs uppercase tracking-[0.1em] text-gallery-charcoal hover:text-gallery-burgundy">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-xs uppercase tracking-[0.1em] text-gallery-burgundy hover:bg-gallery-burgundy/10">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    {{-- Guest Auth Links --}}
                    <div class="flex items-center gap-3 text-xs uppercase tracking-[0.15em] font-medium text-gallery-muted">
                        <a href="{{ route('login') }}" class="hover:text-gallery-burgundy px-2 py-1.5 transition-colors">Sign In</a>
                        <span class="text-gallery-burgundy/40">/</span>
                        <a href="{{ route('register') }}" class="text-gallery-burgundy font-semibold hover:text-gallery-burgundy-dark px-2 py-1.5 transition-colors border border-gallery-burgundy/40 hover:border-gallery-burgundy bg-gallery-burgundy/5">Join</a>
                    </div>
                @endauth
            </div>

            <!-- Mobile Hamburger button -->
            <div class="-me-2 flex items-center sm:hidden gap-3">
                <div
                    x-data="{ count: {{ $cartCount }} }"
                    x-on:cart-updated.window="if ($event.detail && typeof $event.detail.cartCount !== 'undefined') count = $event.detail.cartCount"
                    class="relative"
                >
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center p-2 text-gallery-burgundy">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <template x-if="count > 0">
                            <span x-text="count" class="absolute top-0 right-0 bg-gallery-burgundy text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        </template>
                    </a>
                </div>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 border border-gallery-burgundy/40 text-gallery-burgundy hover:bg-white focus:outline-none transition duration-150">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-gallery-cream border-b border-gallery-border">
        <div class="pt-3 pb-4 px-4 space-y-2">
            <a href="{{ route('shop.index') }}" class="block py-2 text-xs uppercase tracking-[0.2em] font-medium text-gallery-burgundy">
                {{ __('Gallery') }}
            </a>

            @auth
                <a href="{{ route('my-orders.index') }}" class="block py-2 text-xs uppercase tracking-[0.2em] font-medium text-gallery-charcoal hover:text-gallery-burgundy">
                    {{ __('My Orders') }}
                </a>
            @endauth

            @can('products.view')
                <a href="{{ route('dashboard') }}" class="block py-2 text-xs uppercase tracking-[0.2em] font-medium text-gallery-charcoal hover:text-gallery-burgundy">
                    {{ __('Curate Art') }}
                </a>
            @endcan

            @can('orders.view')
                <a href="{{ route('orders.index') }}" class="block py-2 text-xs uppercase tracking-[0.2em] font-medium text-gallery-charcoal hover:text-gallery-burgundy">
                    {{ __('Orders') }}
                </a>
            @endcan

            @can('customers.view')
                <a href="{{ route('customers.index') }}" class="block py-2 text-xs uppercase tracking-[0.2em] font-medium text-gallery-charcoal hover:text-gallery-burgundy">
                    {{ __('Clients') }}
                </a>
            @endcan

            @can('roles.view')
                <a href="{{ route('roles.index') }}" class="block py-2 text-xs uppercase tracking-[0.2em] font-medium text-gallery-charcoal hover:text-gallery-burgundy">
                    {{ __('Permissions') }}
                </a>
            @endcan
        </div>

        @auth
            <div class="pt-4 pb-4 border-t border-gallery-border px-4 space-y-2">
                <div class="font-serif italic text-base text-gallery-burgundy">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gallery-muted">{{ auth()->user()->email }}</div>
                <div class="mt-3 space-y-2">
                    <a href="{{ route('profile.edit') }}" class="block text-xs uppercase tracking-[0.15em] text-gallery-charcoal hover:text-gallery-burgundy py-1">{{ __('Profile') }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block text-xs uppercase tracking-[0.15em] text-gallery-burgundy py-1">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-4 border-t border-gallery-border px-4 space-y-2">
                <a href="{{ route('login') }}" class="block text-xs uppercase tracking-[0.15em] text-gallery-charcoal hover:text-gallery-burgundy py-1.5">Sign In</a>
                <a href="{{ route('register') }}" class="block text-xs uppercase tracking-[0.15em] text-gallery-burgundy font-semibold py-1.5">Join Quirka</a>
            </div>
        @endauth
    </div>
</nav>
