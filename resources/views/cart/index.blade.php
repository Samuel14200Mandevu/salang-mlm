@extends('layouts.app')

@section('title', 'Panier')

@php
    $cartItems = $cart ?? [];
    $cartCount = count($cartItems);
    $cartUnits = (int) array_sum(array_map(fn ($item) => (int) ($item['quantity'] ?? 1), $cartItems));
    $pageTotal = 0;
    $pagePv = 0;
    foreach ($cartItems as $item) {
        $qty = (int) ($item['quantity'] ?? 1);
        $pageTotal += ((float) ($item['price'] ?? 0)) * $qty;
        $pagePv += ((float) ($item['pv_value'] ?? 0)) * $qty;
    }
    $balance = (float) ($walletBalance ?? 0);
    $canAfford = $cartCount > 0 && $balance >= $pageTotal;
    $missing = max(0, $pageTotal - $balance);
@endphp

@section('content')
<div class="cart-page space-y-4 sm:space-y-6">

    <div class="cart-page-header-desktop member-list-header animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon panier</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                @if($cartCount > 0)
                    {{ $cartUnits }} article{{ $cartUnits > 1 ? 's' : '' }} · ${{ number_format($pageTotal, 2) }}
                @else
                    Vérifiez vos articles avant de commander
                @endif
            </p>
        </div>
        <div class="member-header-actions cart-header-actions">
            <a href="{{ route('wallet.index') }}" class="cart-header-btn cart-header-btn--primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>Portefeuille</span>
            </a>
        </div>
    </div>

    @include('cart.partials.catalog-head', ['cart' => $cartItems, 'walletBalance' => $balance])

    <div class="cart-wallet-strip cart-balance-desktop animate-fadeInUp delay-1">
        <div class="cart-wallet-strip__main">
            <p class="cart-wallet-strip__label">Solde portefeuille</p>
            <p class="cart-wallet-strip__value">${{ number_format($balance, 2) }}</p>
        </div>
        <div class="cart-wallet-strip__meta">
            @if($cartCount > 0)
                <span class="cart-wallet-pill {{ $canAfford ? 'is-ok' : 'is-warn' }}">
                    {{ $canAfford ? 'Solde suffisant' : 'Manque $' . number_format($missing, 2) }}
                </span>
            @endif
            <a href="{{ route('wallet.deposit') }}" class="btn btn-primary btn-sm">Alimenter</a>
        </div>
    </div>

    @if(session('success'))
        <div class="cart-page-flash cart-flash cart-flash--ok animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="cart-page-flash cart-flash cart-flash--err animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if($cartCount === 0)
        <div class="cart-empty-card animate-fadeIn">
            <div class="cart-empty-card__icon" aria-hidden="true">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="cart-empty-card__title">Votre panier est vide</h3>
            <p class="cart-empty-card__sub">Ajoutez des produits ou un package pour continuer.</p>
            <div class="cart-empty-card__actions">
                <a href="{{ route('products.index') }}" class="btn btn-primary">Voir les produits</a>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline">Voir les abonnements</a>
            </div>
        </div>
    @else
        <div class="cart-grid grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="lg:col-span-2 animate-fadeInLeft cart-items-col">
                <div class="cart-items-panel">
                    <div class="cart-items-panel__head">
                        <div class="cart-items-panel__head-left">
                            <h2 class="cart-items-panel__title">Articles</h2>
                            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $cartUnits }}</span>
                        </div>
                        <button type="button"
                                onclick="openClearCartModal()"
                                class="cart-items-panel__clear">
                            Vider
                        </button>
                    </div>

                    <div class="cart-items-list">
                        @foreach($cartItems as $key => $item)
                            @php
                                $qty = max(1, (int) ($item['quantity'] ?? 1));
                                $unit = (float) ($item['price'] ?? 0);
                                $lineTotal = $unit * $qty;
                                $isPackage = ($item['type'] ?? '') === 'package';
                                $pv = (float) ($item['pv_value'] ?? 0);
                                $image = $item['image'] ?? null;
                                $imageUrl = ($image && !$isPackage)
                                    ? asset('storage/products/' . $image)
                                    : null;
                            @endphp
                            <article class="cart-line">
                                <div class="cart-line__media" aria-hidden="true">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="" class="cart-line__img" loading="lazy">
                                    @else
                                        <span class="cart-line__fallback {{ $isPackage ? 'is-package' : 'is-product' }}">
                                            @if($isPackage)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                <div class="cart-line__body">
                                    <div class="cart-line__top">
                                        <div class="min-w-0">
                                            <h3 class="cart-line__name">{{ $item['name'] }}</h3>
                                            <p class="cart-line__meta">
                                                <span class="cart-line__type">{{ $isPackage ? 'Abonnement' : 'Produit' }}</span>
                                                <span class="cart-line__dot" aria-hidden="true">·</span>
                                                <span>${{ number_format($unit, 2) }} / u.</span>
                                                @if($pv > 0)
                                                    <span class="cart-line__dot" aria-hidden="true">·</span>
                                                    <span class="cart-line__pv">{{ number_format($pv * $qty, 0) }} PV</span>
                                                @endif
                                            </p>
                                        </div>
                                        <p class="cart-line__total">${{ number_format($lineTotal, 2) }}</p>
                                    </div>

                                    <div class="cart-line__actions">
                                        @if($isPackage)
                                            <span class="cart-qty-readonly">Qté {{ $qty }}</span>
                                        @else
                                            <form action="{{ route('cart.update') }}" method="POST" class="cart-qty">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="id" value="{{ $key }}">
                                                <button type="submit"
                                                        name="quantity"
                                                        value="{{ max(1, $qty - 1) }}"
                                                        class="cart-qty__btn"
                                                        @disabled($qty <= 1)
                                                        aria-label="Diminuer la quantité">−</button>
                                                <span class="cart-qty__value" aria-live="polite">{{ $qty }}</span>
                                                <button type="submit"
                                                        name="quantity"
                                                        value="{{ $qty + 1 }}"
                                                        class="cart-qty__btn"
                                                        aria-label="Augmenter la quantité">+</button>
                                            </form>
                                        @endif

                                        <form action="{{ route('cart.remove', $key) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="cart-line__remove">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 flex flex-wrap gap-2 sm:gap-3 cart-actions-bar">
                    <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Continuer mes achats
                    </a>
                    <button type="button" onclick="openClearCartModal()" class="btn btn-danger btn-sm md:hidden">
                        Vider le panier
                    </button>
                </div>
            </div>

            <div class="lg:col-span-1 animate-fadeInRight cart-summary-col">
                <aside class="cart-summary-panel sticky-top">
                    <h3 class="cart-summary-panel__title">Résumé</h3>

                    <dl class="cart-summary-rows">
                        <div class="cart-summary-row">
                            <dt>Sous-total</dt>
                            <dd>${{ number_format($pageTotal, 2) }}</dd>
                        </div>
                        <div class="cart-summary-row">
                            <dt>Livraison</dt>
                            <dd class="is-free">Gratuite</dd>
                        </div>
                        @if($pagePv > 0)
                            <div class="cart-summary-row">
                                <dt>PV gagnés</dt>
                                <dd class="is-pv">{{ number_format($pagePv, 0) }} PV</dd>
                            </div>
                        @endif
                        <div class="cart-summary-row is-total">
                            <dt>Total</dt>
                            <dd>${{ number_format($pageTotal, 2) }}</dd>
                        </div>
                    </dl>

                    <div class="cart-summary-balance {{ $canAfford ? 'is-ok' : 'is-warn' }}">
                        <div>
                            <p class="cart-summary-balance__label">Portefeuille</p>
                            <p class="cart-summary-balance__value">${{ number_format($balance, 2) }}</p>
                        </div>
                        <p class="cart-summary-balance__hint">
                            @if($canAfford)
                                Prêt à commander
                            @else
                                Manque ${{ number_format($missing, 2) }}
                            @endif
                        </p>
                    </div>

                    <div class="cart-summary-cta">
                        @if($canAfford)
                            <form action="{{ route('cart.checkout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-full">
                                    Passer la commande
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-primary w-full opacity-50 cursor-not-allowed" disabled>
                                Solde insuffisant
                            </button>
                            <a href="{{ route('wallet.deposit') }}" class="btn btn-outline w-full mt-2">
                                Alimenter mon portefeuille
                            </a>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    @endif
</div>

<div id="clearCartModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="modal-title">Vider le panier ?</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-warning">vider votre panier</strong> ?
            <br>
            <span class="text-xs text-[var(--text-tertiary)]">Cette action est irréversible.</span>
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeClearCartModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <form id="clearCartForm" action="{{ route('cart.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" id="confirmClearBtn">
                    Vider le panier
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openClearCartModal() {
    document.getElementById('clearCartModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeClearCartModal() {
    document.getElementById('clearCartModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(function (modal) {
    modal.addEventListener('click', function (e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('clearCartForm');
    if (form) {
        form.addEventListener('submit', function () {
            var btn = document.getElementById('confirmClearBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Suppression...';
            }
        });
    }
});
</script>
@endpush
@endsection
