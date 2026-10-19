<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <!-- Account Type Selection -->
        <div class="mb-5" x-data="{ accountType: '{{ old('account_type', 'customer') }}' }">
            <x-input-label :value="__('I am joining as:')" class="mb-2 text-xs uppercase tracking-wider font-semibold text-gray-700" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Customer Option -->
                <label
                    @click="accountType = 'customer'"
                    :class="accountType === 'customer' ? 'border-gray-900 bg-gray-50/80 ring-2 ring-gray-900' : 'border-gray-200 hover:border-gray-300'"
                    class="relative flex flex-col p-3.5 border rounded-xl cursor-pointer transition-all bg-white shadow-2xs"
                >
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🛍️</span>
                            <span class="font-bold text-sm text-gray-900">Customer</span>
                        </div>
                        <input
                            type="radio"
                            name="account_type"
                            value="customer"
                            x-model="accountType"
                            class="text-gray-900 focus:ring-gray-900"
                        >
                    </div>
                    <span class="text-xs text-gray-500 leading-normal">
                        Browse, discover unique artworks, and place orders.
                    </span>
                </label>

                <!-- Artist Option -->
                <label
                    @click="accountType = 'artist'"
                    :class="accountType === 'artist' ? 'border-gray-900 bg-gray-50/80 ring-2 ring-gray-900' : 'border-gray-200 hover:border-gray-300'"
                    class="relative flex flex-col p-3.5 border rounded-xl cursor-pointer transition-all bg-white shadow-2xs"
                >
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🎨</span>
                            <span class="font-bold text-sm text-gray-900">Artist</span>
                        </div>
                        <input
                            type="radio"
                            name="account_type"
                            value="artist"
                            x-model="accountType"
                            class="text-gray-900 focus:ring-gray-900"
                        >
                    </div>
                    <span class="text-xs text-gray-500 leading-normal">
                        Upload artwork and manage your products.
                    </span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
