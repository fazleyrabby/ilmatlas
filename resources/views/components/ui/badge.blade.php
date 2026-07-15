@props(['type' => 'neutral'])

@php
    $class = match($type) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'info' => 'badge-info',
        'primary' => 'badge-primary',
        default => 'badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge $class"]) }}>
    {{ $slot }}
</span>
