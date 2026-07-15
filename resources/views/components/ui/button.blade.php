@props(['variant' => 'primary', 'type' => 'submit', 'size' => 'md'])

@php
    $variantClass = match($variant) {
        'secondary' => 'btn-secondary',
        'outline' => 'btn-outline',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
        default => 'btn-primary',
    };
    $sizeClass = match($size) {
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => '',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => trim("btn $variantClass $sizeClass")]) }}>
    {{ $slot }}
</button>
