@props([
    'src',
    'alt' => '',
    'class' => '',
    'width' => null,
    'height' => null,
    'loading' => null,
    'fetchpriority' => null,
    'sizes' => null,
    'srcset' => null,
    'lazy' => true,
    'priority' => false,
])

@php
    $path = parse_url($src, PHP_URL_PATH) ?? $src;
    $cleanSrc = ltrim($path, '/');
    $extension = pathinfo($cleanSrc, PATHINFO_EXTENSION);
    $webpSrc = preg_replace('/\.(png|jpe?g)$/i', '.webp', $cleanSrc);
    $webpExists = file_exists(public_path($webpSrc));

    if ($priority) {
        $loading = $loading ?? 'eager';
        $fetchpriority = $fetchpriority ?? 'high';
    } else {
        $loading = $loading ?? ($lazy ? 'lazy' : 'eager');
    }

    $attrs = [
        'alt' => $alt,
        'class' => $attributes->get('class', $class),
        'loading' => $loading,
    ];

    if ($width) {
        $attrs['width'] = $width;
    }
    if ($height) {
        $attrs['height'] = $height;
    }
    if ($fetchpriority) {
        $attrs['fetchpriority'] = $fetchpriority;
    }
    if ($sizes) {
        $attrs['sizes'] = $sizes;
    }
    if ($srcset) {
        $attrs['srcset'] = $srcset;
    }

    $imgSrc = str_starts_with($src, 'http') || str_starts_with($src, '//') ? $src : asset($cleanSrc);
@endphp

@if ($webpExists && in_array(strtolower($extension), ['png', 'jpg', 'jpeg'], true))
    <picture>
        <source srcset="{{ asset($webpSrc) }}" type="image/webp">
        <img src="{{ $imgSrc }}" {{ $attributes->except('class')->merge($attrs) }}>
    </picture>
@else
    <img src="{{ $imgSrc }}" {{ $attributes->merge($attrs) }}>
@endif
