@props([
    /** @var 'sidebar'|'topbar'|'bottom-nav' */
    'variant' => 'topbar',
])

@php
    $cartCount = session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0;
    $cartLabel = $cartCount > 99 ? '99+' : (string) $cartCount;
@endphp

@if ($cartCount > 0)
    @if ($variant === 'sidebar')
        <span {{ $attributes->merge(['class' => 'absolute -top-2 -right-2 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full']) }}>
            {{ $cartLabel }}
        </span>
    @elseif ($variant === 'bottom-nav')
        <span {{ $attributes->merge(['class' => 'badge-count']) }}>
            {{ $cartLabel }}
        </span>
    @else
        <span {{ $attributes->merge(['class' => 'absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full', 'id' => 'cartCount']) }}>
            {{ $cartLabel }}
        </span>
    @endif
@endif
