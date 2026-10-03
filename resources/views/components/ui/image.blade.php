@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'lazy' => true,
    'priority' => false,
])

@php
    $path = parse_url($src, PHP_URL_PATH) ?? $src;
    $relative = ltrim($path, '/');
    $webpRelative = preg_replace('/\.(png|jpe?g)$/i', '.webp', $relative);
    $webpUrl = ($webpRelative !== $relative && file_exists(public_path($webpRelative)))
        ? asset($webpRelative)
        : null;

    $loading = $priority ? 'eager' : ($lazy ? 'lazy' : 'eager');
    $fetchpriority = $priority ? 'high' : null;
    $imgClass = $attributes->get('class', '');
@endphp

@if ($webpUrl)
    <picture>
        <source srcset="{{ $webpUrl }}" type="image/webp">
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            @if ($width) width="{{ $width }}" @endif
            @if ($height) height="{{ $height }}" @endif
            loading="{{ $loading }}"
            @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
            {{ $attributes->except('class')->merge(['class' => $imgClass]) }}
        >
    </picture>
@else
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        @if ($width) width="{{ $width }}" @endif
        @if ($height) height="{{ $height }}" @endif
        loading="{{ $loading }}"
        @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        {{ $attributes->merge(['class' => $imgClass]) }}
    >
@endif
