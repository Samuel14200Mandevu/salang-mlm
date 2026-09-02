{{-- resources/views/cashier/pos.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    /* ============================================================
       PRODUCT CARD – Sobres, sans ombres excessives
       ============================================================ */
    .product-card {
        transition: background 0.2s ease, border-color 0.2s ease;
        cursor: pointer;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .product-card:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
    }
    .product-card .image-container {
        position: relative;
        overflow: hidden;
        background: var(--bg-secondary);
        cursor: pointer;
        aspect-ratio: 1 / 1;
        flex-shrink: 0;
    }
    .product-card .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* ============================================================
       BADGES PV
       ============================================================ */
    .pv-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.1rem 0.6rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        min-width: 40px;
    }
    .pv-badge-sm {
        font-size: 0.55rem;
        padding: 0.075rem 0.5rem;
        min-width: 32px;
    }
    .pv-15 { background: rgba(16, 185, 129, 0.12); color: #10b981; }
    .pv-20 { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .pv-25 { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .pv-30 { background: rgba(236, 72, 153, 0.12); color: #ec4899; }
    .pv-35 { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .pv-40 { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .pv-45 { background: rgba(168, 85, 247, 0.12); color: #8b5cf6; }
    .pv-50 { background: rgba(236, 72, 153, 0.12); color: #ec4899; }
    .pv-55 { background: rgba(20, 184, 166, 0.12); color: #14b8a6; }
    .pv-75 { background: rgba(234, 88, 12, 0.12); color: #ea580c; }
    .pv-100 { background: rgba(220, 38, 38, 0.12); color: #dc2626; }
    .pv-default { background: rgba(107, 114, 128, 0.12); color: #6b7280; }

    .bv-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 600;
        background: rgba(139, 92, 246, 0.10);
        color: #8b5cf6;
    }

    /* ============================================================
       BADGES
       ============================================================ */
    .badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
    }
    .badge-danger {
        background: rgba(179, 42, 42, 0.12);
        color: #b32a2a;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }
    .badge-mlm {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
    }
    .badge-pos {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }

    /* ============================================================
       INPUTS
       ============================================================ */
    .input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        color: var(--text-primary);
        transition: border-color 0.2s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary);
    }

    /* ============================================================
       BOUTONS AMÉLIORÉS
       ============================================================ */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: var(--primary-hover, #091E3B);
    }
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-danger {
        background: #b32a2a;
        color: #FFFFFF;
    }
    .btn-danger:hover {
        background: #8f2121;
    }

    .btn-success {
        background: #22c55e;
        color: #FFFFFF;
    }
    .btn-success:hover {
        background: #16a34a;
    }

    .btn-gold {
        background: #d97706;
        color: #FFFFFF;
    }
    .btn-gold:hover {
        background: #b45309;
    }

    .btn-sm {
        padding: 0.3rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.15rem 0.5rem;
        font-size: 0.65rem;
    }

    .opacity-50 { opacity: 0.5; }
    .cursor-not-allowed { cursor: not-allowed; }

    /* ============================================================
       CARD
       ============================================================ */
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem;
    }

    .truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ============================================================
       PRODUCT GRID
       ============================================================ */
    .product-grid {
        display: grid;
        gap: 1rem;
    }
    @media (min-width: 1280px) {
        .product-grid { grid-template-columns: repeat(5, 1fr); gap: 1.25rem; }
    }
    @media (min-width: 1024px) and (max-width: 1279px) {
        .product-grid { grid-template-columns: repeat(4, 1fr); gap: 1.25rem; }
    }
    @media (min-width: 768px) and (max-width: 1023px) {
        .product-grid { grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    }
    @media (min-width: 480px) and (max-width: 767px) {
        .product-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    }
    @media (max-width: 479px) {
        .product-grid { grid-template-columns: 1fr; gap: 0.75rem; }
        .product-card { flex-direction: row; height: auto; }
        .product-card .image-container { aspect-ratio: 1/1; width: 40%; flex-shrink: 0; }
        .product-card .product-content { flex: 1; padding: 0.75rem; }
        .product-card .product-name { font-size: 0.8rem; }
        .product-card .product-price { font-size: 0.9rem; }
    }

    /* ============================================================
       TABS
       ============================================================ */
    .tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
        flex-wrap: wrap;
    }
    .tab-btn {
        padding: 0.4rem 1.25rem;
        border: none;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.813rem;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
        background: transparent;
        color: var(--text-secondary);
    }
    .tab-btn:hover {
        color: var(--text-primary);
        background: var(--bg-secondary);
    }
    .tab-btn.active {
        background: var(--primary);
        color: #FFFFFF;
    }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* ============================================================
       SCANNER
       ============================================================ */
    .scanner-container {
        position: relative;
        border: 1.5px dashed var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 0.35rem 1rem;
        text-align: center;
        transition: border-color 0.2s ease;
        cursor: pointer;
        background: var(--bg-secondary);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .scanner-container:hover {
        border-color: var(--primary);
        background: var(--bg-primary);
    }
    .scanner-container .scanner-text {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .scanner-container .scanner-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    .scanner-container.scanning {
        border-color: var(--primary);
        background: rgba(15, 43, 79, 0.04);
    }

    .qr-result {
        margin-top: 0.5rem;
        padding: 0.5rem;
        border-radius: var(--radius-md, 6px);
        font-size: 0.75rem;
        display: none;
    }
    .qr-result.show { display: block; }
    .qr-result.success {
        background: rgba(34, 197, 94, 0.08);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.15);
    }
    .qr-result.error {
        background: rgba(179, 42, 42, 0.08);
        color: #b32a2a;
        border: 1px solid rgba(179, 42, 42, 0.15);
    }

    /* ============================================================
       TOAST
       ============================================================ */
    .toast-add {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #22c55e;
        color: white;
        padding: 10px 20px;
        border-radius: var(--radius-md, 8px);
        font-weight: 600;
        z-index: 9999;
        animation: slideUp 0.3s ease forwards;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.875rem;
    }
    .toast-add.error {
        background: #b32a2a;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes slideDown {
        from { opacity: 1; transform: translateY(0) scale(1); }
        to { opacity: 0; transform: translateY(20px) scale(0.95); }
    }

    .click-hint {
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 0.55rem;
        font-weight: 500;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        white-space: nowrap;
    }
    .image-container:hover .click-hint { opacity: 1; }

    .add-to-cart-btn {
        width: 100%;
        padding: 0.3rem 0.5rem;
        font-size: 0.7rem;
    }

    @media (max-width: 640px) {
        .add-to-cart-btn {
            font-size: 0.6rem;
            padding: 0.2rem 0.375rem;
        }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .tab-btn { padding: 0.3rem 0.75rem; font-size: 0.75rem; }
        .scanner-container { padding: 0.25rem 0.75rem; }
        .scanner-container .scanner-text { font-size: 0.65rem; }
    }

    @media (max-width: 480px) {
        .product-card .product-price { font-size: 0.85rem; }
        .tabs { gap: 0.25rem; }
        .tab-btn { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .toast-add { font-size: 0.75rem; padding: 8px 14px; bottom: 70px; }
    }
</style>
@endpush

@section('title', 'Point de Vente')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-[var(--primary)] mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
                Point de Vente
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Vendez des produits ou activez des packages MLM
            </p>
        </div>
        <div class="flex gap-2 flex-wrap items-center">
            {{-- SCANNER QR / CODE-BARRES --}}
            <div class="scanner-container" id="scannerContainer">
                <span class="scanner-text">Scanner QR / Code-barres</span>
                <input type="text" id="qrScanner" class="scanner-input" autofocus>
            </div>
            <div id="qrResult" class="qr-result"></div>

            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
        </div>
    </div>

    {{-- TABS --}}
    <div class="tabs">
        <button class="tab-btn active" data-tab="products" onclick="switchTab('products')">
            Produits
        </button>
        <button class="tab-btn" data-tab="packages" onclick="switchTab('packages')">
            Packages MLM
        </button>
        <div style="margin-left: auto;">
            <input type="text"
                   id="searchInput"
                   placeholder="Rechercher un produit ou package..."
                   class="input text-sm" style="min-width: 180px;">
        </div>
    </div>

    <div id="searchResult" class="text-xs sm:text-sm text-[var(--text-secondary)] hidden">
        Résultats: <span id="resultCount" class="font-semibold text-[var(--primary)]">0</span> article(s)
    </div>

    <div id="productsContainer">
        @if((isset($products) && $products->count() > 0) || (isset($packages) && $packages->count() > 0))
            {{-- TAB PRODUITS --}}
            <div id="tab-products" class="tab-content active">
                @if(isset($products) && $products->count() > 0)
                    <div class="product-grid">
                        @foreach($products as $product)
                            @php
                                $pvClass = $product->pv_value ? 'pv-' . $product->pv_value : 'pv-default';
                            @endphp
                            <div class="product-card"
                                 data-name="{{ strtolower($product->name) }}"
                                 data-description="{{ strtolower($product->description ?? '') }}"
                                 data-product-id="{{ $product->id }}"
                                 data-type="product">

                                <div class="image-container" onclick="addToCart({{ $product->id }}, 'product')">
                                    @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                                        <img src="{{ asset('storage/products/' . $product->image) }}"
                                             alt="{{ $product->name }}"
                                             loading="lazy"
                                             onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}'">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-4xl sm:text-5xl text-[var(--text-tertiary)]">
                                            <svg class="w-12 h-12 sm:w-16 sm:h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                            </svg>
                                        </div>
                                    @endif

                                    <span class="absolute top-1 sm:top-2 left-1 sm:left-2 badge badge-pos text-[8px] sm:text-[10px]">
                                        POS
                                    </span>

                                    @if($product->stock < 5 && $product->stock > 0)
                                        <span class="absolute top-1 sm:top-2 right-1 sm:right-2 badge badge-warning text-[8px] sm:text-[10px]">
                                            Stock faible
                                        </span>
                                    @endif
                                    @if($product->stock == 0)
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                            <span class="badge badge-danger text-xs sm:text-sm py-1 sm:py-2 px-2 sm:px-4 transform -rotate-12">
                                                Rupture de stock
                                            </span>
                                        </div>
                                    @endif

                                    <div class="click-hint">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Cliquez pour ajouter
                                    </div>
                                </div>

                                <div class="product-content p-2 sm:p-3 flex flex-col flex-1">
                                    <h3 class="product-name font-semibold text-[var(--text-primary)] text-xs sm:text-sm truncate">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate-2 h-6 sm:h-8 flex-1">
                                        {{ Str::limit($product->description ?? '', 40) }}
                                    </p>

                                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                        @if($product->pv_value)
                                            <span class="pv-badge pv-badge-sm {{ $pvClass }}">
                                                {{ $product->pv_value }} PV
                                            </span>
                                        @endif
                                        @if($product->bv_value)
                                            <span class="bv-badge text-[0.5rem]">
                                                {{ $product->bv_value }} BV
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between mt-1 sm:mt-2 pt-1 sm:pt-2 border-t border-[var(--border-color)]">
                                        <span class="product-price text-sm sm:text-lg font-bold text-[var(--primary)]">${{ number_format($product->price, 2) }}</span>
                                        <span class="product-stock text-[8px] sm:text-[10px] {{ $product->stock > 10 ? 'text-[#16a34a]' : ($product->stock > 0 ? 'text-[#d97706]' : 'text-[#b32a2a]') }}">
                                            @if($product->stock > 10) En stock
                                            @elseif($product->stock > 0) {{ $product->stock }} restants
                                            @else Rupture
                                            @endif
                                        </span>
                                    </div>

                                    <div class="mt-1 sm:mt-2">
                                        @if($product->stock > 0)
                                            <button onclick="addToCart({{ $product->id }}, 'product')"
                                                    class="btn btn-primary btn-sm w-full text-[10px] sm:text-xs add-to-cart-btn">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Ajouter au panier
                                            </button>
                                        @else
                                            <button class="btn btn-danger btn-sm w-full opacity-50 cursor-not-allowed text-[10px] sm:text-xs" disabled>
                                                Rupture de stock
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card text-center py-8 sm:py-12">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                        <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun produit disponible</h3>
                        <p class="text-sm sm:text-base text-[var(--text-tertiary)] mt-1 sm:mt-2">Aucun produit n'est disponible pour le moment</p>
                    </div>
                @endif
            </div>

            {{-- TAB PACKAGES --}}
            <div id="tab-packages" class="tab-content">
                @if(isset($packages) && $packages->count() > 0)
                    <div class="product-grid">
                        @foreach($packages as $package)
                            @php
                                $pvClass = $package->pv_value ? 'pv-' . $package->pv_value : 'pv-default';
                            @endphp
                            <div class="product-card"
                                 data-name="{{ strtolower($package->name) }}"
                                 data-description="{{ strtolower($package->description ?? '') }}"
                                 data-product-id="{{ $package->id }}"
                                 data-type="package">

                                <div class="image-container" onclick="addToCart({{ $package->id }}, 'package')" style="background: rgba(59,130,246,0.05);">
                                    <div class="w-full h-full flex items-center justify-center text-4xl sm:text-5xl text-[var(--primary)]">
                                        <svg class="w-12 h-12 sm:w-16 sm:h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                        </svg>
                                    </div>

                                    <span class="absolute top-1 sm:top-2 left-1 sm:left-2 badge badge-mlm text-[8px] sm:text-[10px]">
                                        MLM
                                    </span>

                                    @if($package->is_popular ?? false)
                                        <span class="absolute top-1 sm:top-2 right-1 sm:right-2 badge badge-success text-[8px] sm:text-[10px]">
                                            Populaire
                                        </span>
                                    @endif

                                    <div class="click-hint">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Cliquez pour ajouter
                                    </div>
                                </div>

                                <div class="product-content p-2 sm:p-3 flex flex-col flex-1">
                                    <h3 class="product-name font-semibold text-[var(--text-primary)] text-xs sm:text-sm truncate">
                                        {{ $package->name }}
                                    </h3>
                                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate-2 h-6 sm:h-8 flex-1">
                                        {{ Str::limit($package->description ?? 'Package MLM', 40) }}
                                    </p>

                                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                        @if($package->pv_value)
                                            <span class="pv-badge pv-badge-sm {{ $pvClass }}">
                                                {{ $package->pv_value }} PV
                                            </span>
                                        @endif
                                        @if($package->bv_value)
                                            <span class="bv-badge text-[0.5rem]">
                                                {{ $package->bv_value }} BV
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between mt-1 sm:mt-2 pt-1 sm:pt-2 border-t border-[var(--border-color)]">
                                        <span class="product-price text-sm sm:text-lg font-bold text-[var(--primary)]">${{ number_format($package->price, 2) }}</span>
                                        <span class="text-[8px] sm:text-[10px] text-[#2563eb]">
                                            {{ $package->commission_rate ?? 30 }}% commission
                                        </span>
                                    </div>

                                    <div class="mt-1 sm:mt-2">
                                        <button onclick="addToCart({{ $package->id }}, 'package')"
                                                class="btn btn-gold btn-sm w-full text-[10px] sm:text-xs add-to-cart-btn">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Ajouter au panier
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card text-center py-8 sm:py-12">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                        <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun package disponible</h3>
                        <p class="text-sm sm:text-base text-[var(--text-tertiary)] mt-1 sm:mt-2">Aucun package MLM n'est disponible pour le moment</p>
                    </div>
                @endif
            </div>
        @else
            <div class="card text-center py-8 sm:py-12">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
                <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun article disponible</h3>
                <p class="text-sm sm:text-base text-[var(--text-tertiary)] mt-1 sm:mt-2">Aucun produit ou package n'est disponible pour le moment</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// ================================================================
//  TOAST NOTIFICATION
// ================================================================
function showToast(message, isError) {
    const existing = document.querySelector('.toast-add');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'toast-add' + (isError ? ' error' : '');
    toast.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="${isError ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7'}"/>
        </svg>
        ${message}
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideDown 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

// ================================================================
//  AJOUTER AU PANIER
// ================================================================
function addToCart(productId, type) {
    const card = document.querySelector(`.product-card[data-product-id="${productId}"][data-type="${type}"]`);
    if (!card) {
        showToast('Produit non trouvé', true);
        return;
    }

    const name = card.querySelector('.product-name')?.textContent?.trim() || 'Produit';
    const priceElement = card.querySelector('.product-price');
    const price = parseFloat(priceElement?.textContent?.replace('$', '').replace(',', '')) || 0;
    const pvElement = card.querySelector('.pv-badge');
    const pv = pvElement ? parseInt(pvElement.textContent) || 0 : 0;

    if (type === 'product') {
        const stockElement = card.querySelector('.product-stock');
        const stockText = stockElement?.textContent?.trim() || '';
        if (stockText.includes('Rupture') || stockText.includes('0 restants')) {
            showToast('Ce produit est en rupture de stock', true);
            return;
        }
    }

    if (typeof window.addToCartGlobal === 'function') {
        window.addToCartGlobal(productId, type, name, price, pv);
        showToast(' ' + name + ' ajouté au panier !');
    } else {
        try {
            let cart = JSON.parse(localStorage.getItem('pos_cart') || '[]');
            const existing = cart.find(item => item.id === productId && item.type === type);
            if (existing) {
                existing.quantity = (existing.quantity || 1) + 1;
            } else {
                cart.push({
                    id: productId,
                    type: type,
                    name: name,
                    price: price,
                    pv: pv,
                    quantity: 1
                });
            }
            localStorage.setItem('pos_cart', JSON.stringify(cart));
            showToast(' ' + name + ' ajouté au panier !');

            if (typeof window.renderCart === 'function') window.renderCart();
            if (typeof window.updateCartCount === 'function') window.updateCartCount();
        } catch (e) {
            showToast('Erreur lors de l\'ajout au panier', true);
        }
    }
}

// ================================================================
//  SWITCH TAB
// ================================================================
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

    const target = document.getElementById('tab-' + tab);
    if (target) target.classList.add('active');

    const btn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    if (btn) btn.classList.add('active');

    document.getElementById('searchInput').value = '';
    document.querySelectorAll('.product-card').forEach(card => card.style.display = '');
    document.getElementById('searchResult').classList.add('hidden');
}

// ================================================================
//  RECHERCHE
// ================================================================
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const productCards = document.querySelectorAll('.product-card');
    const searchResult = document.getElementById('searchResult');
    const resultCount = document.getElementById('resultCount');
    let timeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();

            clearTimeout(timeout);

            timeout = setTimeout(function() {
                let count = 0;
                const activeTab = document.querySelector('.tab-content.active');
                const activeTabId = activeTab ? activeTab.id : 'tab-products';

                productCards.forEach(function(card) {
                    const name = card.dataset.name || '';
                    const description = card.dataset.description || '';

                    let isInActiveTab = false;
                    if (activeTabId === 'tab-products' && card.dataset.type === 'product') {
                        isInActiveTab = true;
                    } else if (activeTabId === 'tab-packages' && card.dataset.type === 'package') {
                        isInActiveTab = true;
                    }

                    if (isInActiveTab && (name.includes(query) || description.includes(query))) {
                        card.style.display = '';
                        count++;
                    } else if (!isInActiveTab) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (query.length > 0) {
                    searchResult.classList.remove('hidden');
                    resultCount.textContent = count;
                } else {
                    searchResult.classList.add('hidden');
                }
            }, 300);
        });
    }
});

// ================================================================
//  NETTOYER LE PANIER
// ================================================================
window.clearCart = function() {
    if (typeof localStorage !== 'undefined') localStorage.removeItem('pos_cart');
    window.cart = [];
    if (typeof window.renderCart === 'function') window.renderCart();
    if (typeof window.updateCartCount === 'function') window.updateCartCount();
    showToast(' Panier vidé');
};

// ================================================================
//  SCANNER QR / CODE-BARRES
// ================================================================
document.addEventListener('DOMContentLoaded', function() {
    const scannerInput = document.getElementById('qrScanner');
    const scannerContainer = document.getElementById('scannerContainer');

    if (scannerInput) {
        scannerInput.addEventListener('focus', function() {
            scannerContainer.classList.add('scanning');
        });

        scannerInput.addEventListener('blur', function() {
            scannerContainer.classList.remove('scanning');
        });

        scannerInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = this.value.trim();
                if (code) processScannedCode(code);
                this.value = '';
            }
        });

        setTimeout(() => scannerInput.focus(), 500);
    }
});

function processScannedCode(code) {
    const qrResult = document.getElementById('qrResult');
    const codeValue = code.trim();
    const isNumeric = /^\d+$/.test(codeValue);

    if (isNumeric) {
        findProductById(parseInt(codeValue));
    } else {
        findProductBySku(codeValue);
    }
}

function findProductById(id) {
    const qrResult = document.getElementById('qrResult');

    qrResult.className = 'qr-result show';
    qrResult.innerHTML = '🔍 Recherche du produit...';

    fetch(`/cashier/product/find/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            qrResult.className = 'qr-result show success';
            qrResult.innerHTML = ' ' + data.product.name + ' ajouté au panier!';
            addToCart(data.product.id, 'product');

            const container = document.getElementById('scannerContainer');
            container.style.borderColor = '#22c55e';
            setTimeout(() => container.style.borderColor = '', 2000);
        } else {
            qrResult.className = 'qr-result show error';
            qrResult.innerHTML = ' ' + (data.message || 'Produit non trouvé (ID: ' + id + ')');

            const container = document.getElementById('scannerContainer');
            container.style.borderColor = '#b32a2a';
            setTimeout(() => container.style.borderColor = '', 2000);
        }
    })
    .catch(error => {
        qrResult.className = 'qr-result show error';
        qrResult.innerHTML = ' Erreur: ' + error.message;
    });

    setTimeout(() => qrResult.className = 'qr-result', 5000);
}

function findProductBySku(sku) {
    const qrResult = document.getElementById('qrResult');

    qrResult.className = 'qr-result show';
    qrResult.innerHTML = '🔍 Recherche du produit (SKU: ' + sku + ')...';

    fetch(`/cashier/product/find-by-sku/${encodeURIComponent(sku)}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            qrResult.className = 'qr-result show success';
            qrResult.innerHTML = ' ' + data.product.name + ' ajouté au panier!';
            addToCart(data.product.id, 'product');

            const container = document.getElementById('scannerContainer');
            container.style.borderColor = '#22c55e';
            setTimeout(() => container.style.borderColor = '', 2000);
        } else {
            qrResult.className = 'qr-result show error';
            qrResult.innerHTML = ' ' + (data.message || 'Produit non trouvé (SKU: ' + sku + ')');

            const container = document.getElementById('scannerContainer');
            container.style.borderColor = '#b32a2a';
            setTimeout(() => container.style.borderColor = '', 2000);
        }
    })
    .catch(error => {
        qrResult.className = 'qr-result show error';
        qrResult.innerHTML = ' Erreur: ' + error.message;
    });

    setTimeout(() => qrResult.className = 'qr-result', 5000);
}
</script>
@endpush
@endsection