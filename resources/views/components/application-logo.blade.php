@props(['horizontal' => false])

@if($horizontal)
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center overflow-hidden']) }}>
        <img src="{{ asset('images/quirka-logo-horizontal.jpg') }}" alt="Quirka" class="h-full w-auto object-contain">
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center overflow-hidden rounded-lg']) }}>
        <img src="{{ asset('images/quirka-logo.jpg') }}" alt="Quirka Logo" class="h-full w-full object-cover">
    </div>
@endif
