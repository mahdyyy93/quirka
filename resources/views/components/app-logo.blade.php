@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Quirka')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-11 items-center justify-center rounded-xl overflow-hidden border border-zinc-200 bg-white shadow-xs">
            <img src="{{ asset('images/quirka-logo.jpg') }}" alt="Quirka" class="size-full object-cover">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'Quirka')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-11 items-center justify-center rounded-xl overflow-hidden border border-zinc-200 bg-white shadow-xs">
            <img src="{{ asset('images/quirka-logo.jpg') }}" alt="Quirka" class="size-full object-cover">
        </x-slot>
    </flux:brand>
@endif
