@php
    $user = Auth::user();
    $packageName = $user->package?->name ?? 'Aucun package';
    $balance = $user->wallet?->balance ?? 0;
    $isActive = (bool) $user->package_id;
    $statusLabel = $isActive ? 'Actif' : 'Inactif';
@endphp
<div class="subscriptions-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner subscriptions-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · {{ $statusLabel }}</p>
            <p class="shop-catalog-banner__title">{{ $subscriptions->count() }} package(s)</p>
            <p class="shop-catalog-banner__sub">{{ $packageName }} · ${{ number_format($balance, 2) }} disponible</p>
        </div>
        <div class="shop-catalog-banner__tools">
            <a href="{{ route('wallet.deposit') }}" class="shop-banner-icon-btn" aria-label="Déposer des fonds">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
            <a href="{{ route('cart.index') }}" class="shop-banner-icon-btn" aria-label="Panier">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </a>
        </div>
    </div>
</div>
