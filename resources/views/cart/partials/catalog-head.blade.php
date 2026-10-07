@php
    $items = $cart ?? [];
    $cartCount = count($items);
    $cartTotal = 0;
    foreach ($items as $item) {
        $cartTotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
    }
    $balance = $walletBalance ?? 0;
@endphp
<div class="cart-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner cart-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · Panier</p>
            @if($cartCount > 0)
                <p class="shop-catalog-banner__title">{{ $cartCount }} article(s)</p>
                <p class="shop-catalog-banner__sub">${{ number_format($cartTotal, 2) }} · ${{ number_format($balance, 2) }} en portefeuille</p>
            @else
                <p class="shop-catalog-banner__title">Panier vide</p>
                <p class="shop-catalog-banner__sub">${{ number_format($balance, 2) }} disponible · Boutique &amp; packages</p>
            @endif
        </div>
        <div class="shop-catalog-banner__tools">
            <a href="{{ route('products.index') }}" class="shop-banner-icon-btn" aria-label="Boutique">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </a>
            <a href="{{ route('wallet.deposit') }}" class="shop-banner-icon-btn" aria-label="Déposer des fonds">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>
    </div>
</div>
