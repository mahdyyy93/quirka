<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200/80 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('shop.index') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-900" />
                        <span class="font-extrabold text-xl tracking-tight text-gray-900">Quirka</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:flex">
                    @if(auth()->user()?->role === 'admin')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Products') }}
                        </x-nav-link>
                        <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                            {{ __('Suppliers') }}
                        </x-nav-link>
                        <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                            {{ __('Orders') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                            {{ __('Customers') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.index')">
                            {{ __('Shop') }}
                        </x-nav-link>
                        @auth
                            <x-nav-link :href="route('my-orders.index')" :active="request()->routeIs('my-orders.*')">
                                {{ __('My Orders') }}
                            </x-nav-link>
                        @endauth
                    @endif
                </div>
            </div>

            <!-- Right Side Action Cluster (Cart + Auth) -->
            <div class="hidden sm:flex sm:items-center sm:gap-4">
                {{-- Cart Button (Visible for all users) --}}
                @php $cartCount = collect(session()->get('cart', []))->sum('quantity'); @endphp
                <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.4 5.6a1 1 0 001 1.4H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    <span>Cart</span>
                    @if($cartCount > 0)
                        <span class="bg-red-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] px-1.5 flex items-center justify-center">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                @auth
                    {{-- User Profile Dropdown --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3.5 py-2 border border-gray-200 text-sm leading-4 font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 focus:outline-none transition duration-150">
                                <span>{{ auth()->user()->name }}</span>
                                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-500" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    {{-- Guest Auth Links --}}
                    <div class="flex items-center gap-3 text-sm font-semibold text-gray-600">
                        <a href="{{ route('login') }}" class="hover:text-gray-900 px-3 py-2 rounded-lg transition-colors">Log In</a>
                        <a href="{{ route('register') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-900 px-3.5 py-2 rounded-xl transition-colors">Sign Up</a>
                    </div>
                @endauth
            </div>

            <!-- Mobile Hamburger button -->
            <div class="-me-2 flex items-center sm:hidden gap-2">
                <a href="{{ route('cart.index') }}" class="relative inline-flex items-center p-2 text-gray-700 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.4 5.6a1 1 0 001 1.4H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none transition duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-b border-gray-200">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()?->role === 'admin')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Products') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                    {{ __('Suppliers') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                    {{ __('Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                    {{ __('Customers') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.index')">
                    {{ __('Shop') }}
                </x-responsive-nav-link>
                @auth
                    <x-responsive-nav-link :href="route('my-orders.index')" :active="request()->routeIs('my-orders.*')">
                        {{ __('My Orders') }}
                    </x-responsive-nav-link>
                @endauth
            @endif
        </div>

        @auth
            <div class="pt-4 pb-3 border-t border-gray-200 px-4 space-y-2">
                <div class="font-semibold text-gray-800">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-3 border-t border-gray-200 px-4 space-y-2">
                <a href="{{ route('login') }}" class="block text-gray-700 font-medium py-1">Log In</a>
                <a href="{{ route('register') }}" class="block text-indigo-600 font-semibold py-1">Sign Up</a>
            </div>
        @endauth
    </div>
</nav>
