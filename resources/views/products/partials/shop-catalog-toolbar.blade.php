@php
    $activeCategory = request('category');
    $totalOnPage = $products->count();
    $hasSearchQuery = filled(request('search'));
@endphp
<div class="shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Boutique Salang</p>
            <p class="shop-catalog-banner__title"><span id="shopCatalogTotal">{{ $products->total() }}</span> article(s)</p>
            <p class="shop-catalog-banner__sub">Catalogue MLM · PV &amp; commandes</p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn {{ $hasSearchQuery ? 'is-active' : '' }}"
                    id="shopSearchToggle"
                    aria-expanded="{{ $hasSearchQuery ? 'true' : 'false' }}"
                    aria-controls="shopSearchPanel"
                    aria-label="Rechercher">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <a href="{{ route('cart.index') }}" class="shop-banner-icon-btn member-shop-cart-btn" aria-label="Panier">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span id="cartCount" class="shop-banner-icon-btn__badge hidden">0</span>
            </a>
        </div>
    </div>

    <div class="shop-catalog-search-panel {{ $hasSearchQuery ? 'is-open' : '' }}"
         id="shopSearchPanel"
         aria-hidden="{{ $hasSearchQuery ? 'false' : 'true' }}">
        <div class="member-shop-search search-wrapper">
            <span class="member-shop-search__icon">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text"
                   id="searchInput"
                   placeholder="Rechercher un produit…"
                   class="input search-input text-sm"
                   autocomplete="off"
                   value="{{ request('search') }}">
        </div>
    </div>

    @if($products->count() > 0)
        <div class="shop-filter-bar" role="tablist" aria-label="Filtrer le catalogue">
            <a href="{{ route('products.index', request()->except('category', 'page')) }}"
               class="shop-chip {{ !$activeCategory ? 'is-active' : '' }}">
                Tous
            </a>
            <button type="button" class="shop-chip" data-shop-quick="featured">Vedette</button>
            <button type="button" class="shop-chip" data-shop-quick="instock">Disponibles</button>
            @foreach($categories as $category)
                <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category])) }}"
                   class="shop-chip {{ $activeCategory === $category ? 'is-active' : '' }}">
                    {{ $category }}
                </a>
            @endforeach
        </div>

        <div class="shop-catalog-meta">
            <span id="shopVisibleCount">{{ $totalOnPage }}</span> affiché(s)
            @if($products->total() > $totalOnPage)
                · {{ $products->total() }} au catalogue
            @endif
            @if($activeCategory)
                · <span class="shop-catalog-meta__active">{{ $activeCategory }}</span>
            @endif
        </div>
    @endif
</div>
