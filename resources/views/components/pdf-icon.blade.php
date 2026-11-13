@props([
    'size' => 'md',
    'alt' => 'PDF',
])
@php
    $sizeClass = match ($size) {
        'sm' => 'pdf-pic-icon--sm',
        'lg' => 'pdf-pic-icon--lg',
        default => 'pdf-pic-icon--md',
    };
@endphp
<img
    src="{{ asset('images') }}/{{ rawurlencode('pdf 2.webp') }}"
    alt="{{ $alt }}"
    {{ $attributes->class(['pdf-pic-icon', $sizeClass]) }}
    width="28"
    height="28"
    loading="lazy"
    decoding="async"
>
