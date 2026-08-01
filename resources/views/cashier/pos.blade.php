{{-- resources/views/cashier/pos.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .product-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary-500);
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
        transition: transform 0.5s ease;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-card:hover .image-container img {
        transform: scale(1.05);
    }
    .product-card .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }
    .badge-mlm {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    .badge-pos {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }
    .pv-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 600;
        background: rgba(59,130,246,0.12);
        color: #3b82f6;
    }
    
    .input {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
        transform: translateY(-2px);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
    }
    .btn-success {
        background: var(--gradient-success);
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.3);
    }
    .btn-gold {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
    }
    .opacity-50 {
        opacity: 0.5;
    }
    .cursor-not-allowed {
        cursor: not-allowed;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
    }
    
    .truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-grid {
        display: grid;
        gap: 1rem;
    }
    
    @media (min-width: 1280px) {
        .product-grid {
            grid-template-columns: repeat(5, 1fr);
            gap: 1.25rem;
        }
    }
    @media (min-width: 1024px) and (max-width: 1279px) {
        .product-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
        }
    }
    @media (min-width: 768px) and (max-width: 1023px) {
        .product-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
    }
    @media (min-width: 480px) and (max-width: 767px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
    }
    @media (max-width: 479px) {
        .product-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        .product-card {
            flex-direction: row;
            height: auto;
        }
        .product-card .image-container {
            aspect-ratio: 1 / 1;
            width: 40%;
            flex-shrink: 0;
        }
        .product-card .product-content {
            flex: 1;
            padding: 0.75rem;
        }
        .product-card .product-name {
            font-size: 0.8rem;
        }
        .product-card .product-price {
            font-size: 0.9rem;
        }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    .hidden { display: none; }
    
    .custom-toast {
        animation: slideUp 0.3s ease forwards;
        max-width: 400px;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .add-to-cart-btn {
        width: 100%;
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
    }
    
    @media (max-width: 640px) {
        .add-to-cart-btn {
            font-size: 0.6rem;
            padding: 0.25rem 0.375rem;
        }
    }

    .tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    .tab-btn {
        padding: 0.5rem 1.5rem;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: transparent;
        color: var(--text-secondary);
    }
    .tab-btn:hover {
        color: var(--text-primary);
        background: var(--bg-secondary);
    }
    .tab-btn.active {
        color: white;
        background: var(--gradient-primary);
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
</style>
@endpush

@section('title', 'Point de Vente')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
                Point de Vente
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Vendez des produits ou activez des packages MLM
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
        </div>
    </div>

    <div class="tabs animate-fadeInUp delay-1">
        <button class="tab-btn active" data-tab="products" onclick="switchTab('products')">
            Produits
        </button>
        <button class="tab-btn" data-tab="packages" onclick="switchTab('packages')">
            Packages MLM
        </button>
        <div class="animate-fadeInUp delay-1">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                
            </span>
            <input type="text" 
                   id="searchInput"
                   placeholder="Rechercher un produit ou package..."
                   class="input pl-10 search-input text-sm sm:text-base">
        </div>
    </div>

    </div>

    
    <div id="searchResult" class="text-xs sm:text-sm text-[var(--text-secondary)] hidden animate-fadeInUp">
        Résultats: <span id="resultCount" class="font-semibold text-primary-500">0</span> article(s)
    </div>

    <div id="productsContainer">
        @if((isset($products) && $products->count() > 0) || (isset($packages) && $packages->count() > 0))
            <div id="tab-products" class="tab-content active">
                @if(isset($products) && $products->count() > 0)
                    <div class="product-grid">
                        @foreach($products as $product)
                            <div class="product-card animate-fadeInUp delay-{{ min($loop->index % 6 + 1, 12) }}"
                                 data-name="{{ strtolower($product->name) }}"
                                 data-description="{{ strtolower($product->description ?? '') }}"
                                 data-product-id="{{ $product->id }}"
                                 data-type="product">
                                
                                <div class="image-container">
                                    @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                                        <img src="{{ asset('storage/products/' . $product->image) }}" 
                                             alt="{{ $product->name }}"
                                             loading="lazy"
                                             onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}'">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-4xl sm:text-5xl text-[var(--text-tertiary)]">
                                            <svg class="w-12 h-12 sm:w-16 sm:h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
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
                                </div>

                                <div class="product-content p-2 sm:p-3 flex flex-col flex-1">
                                    <h3 class="product-name font-semibold text-[var(--text-primary)] text-xs sm:text-sm truncate">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate-2 h-6 sm:h-8 flex-1">
                                        {{ Str::limit($product->description ?? '', 40) }}
                                    </p>
                                    
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($product->pv_value)
                                            <span class="pv-badge">{{ $product->pv_value }} PV</span>
                                        @endif
                                        @if($product->bv_value)
                                            <span class="pv-badge" style="background:rgba(139,92,246,0.12);color:#8b5cf6;">{{ $product->bv_value }} BV</span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-1 sm:mt-2 pt-1 sm:pt-2 border-t border-[var(--border-color)]">
                                        <span class="product-price text-sm sm:text-lg font-bold text-primary-500">${{ number_format($product->price, 2) }}</span>
                                        <span class="product-stock text-[8px] sm:text-[10px] {{ $product->stock > 10 ? 'text-green-500' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                            @if($product->stock > 10) En stock
                                            @elseif($product->stock > 0) {{ $product->stock }} restants
                                            @else Rupture
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <div class="mt-1 sm:mt-2">
                                        @if($product->stock > 0)
                                            <button onclick="window.addToCart({{ $product->id }}, 'product')" 
                                                    class="btn btn-primary btn-sm w-full text-[10px] sm:text-xs add-to-cart-btn">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                        <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun produit disponible</h3>
                        <p class="text-sm sm:text-base text-[var(--text-tertiary)] mt-1 sm:mt-2">Aucun produit n'est disponible pour le moment</p>
                    </div>
                @endif
            </div>

            <div id="tab-packages" class="tab-content">
                @if(isset($packages) && $packages->count() > 0)
                    <div class="product-grid">
                        @foreach($packages as $package)
                            <div class="product-card animate-fadeInUp delay-{{ min($loop->index % 6 + 1, 12) }}"
                                 data-name="{{ strtolower($package->name) }}"
                                 data-description="{{ strtolower($package->description ?? '') }}"
                                 data-product-id="{{ $package->id }}"
                                 data-type="package">
                                
                                <div class="image-container" style="background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(139,92,246,0.1));">
                                    <div class="w-full h-full flex items-center justify-center text-4xl sm:text-5xl text-primary-500">
                                        <svg class="w-12 h-12 sm:w-16 sm:h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
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
                                </div>

                                <div class="product-content p-2 sm:p-3 flex flex-col flex-1">
                                    <h3 class="product-name font-semibold text-[var(--text-primary)] text-xs sm:text-sm truncate">
                                        {{ $package->name }}
                                    </h3>
                                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate-2 h-6 sm:h-8 flex-1">
                                        {{ Str::limit($package->description ?? 'Package MLM', 40) }}
                                    </p>
                                    
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($package->pv_value)
                                            <span class="pv-badge">{{ $package->pv_value }} PV</span>
                                        @endif
                                        @if($package->bv_value)
                                            <span class="pv-badge" style="background:rgba(139,92,246,0.12);color:#8b5cf6;">{{ $package->bv_value }} BV</span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-1 sm:mt-2 pt-1 sm:pt-2 border-t border-[var(--border-color)]">
                                        <span class="product-price text-sm sm:text-lg font-bold text-primary-500">${{ number_format($package->price, 2) }}</span>
                                        <span class="text-[8px] sm:text-[10px] text-blue-500">
                                            {{ $package->commission_rate ?? 30 }}% commission
                                        </span>
                                    </div>
                                    
                                    <div class="mt-1 sm:mt-2">
                                        <button onclick="window.addToCart({{ $package->id }}, 'package')" 
                                                class="btn btn-gold btn-sm w-full text-[10px] sm:text-xs add-to-cart-btn">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                        <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun package disponible</h3>
                        <p class="text-sm sm:text-base text-[var(--text-tertiary)] mt-1 sm:mt-2">Aucun package MLM n'est disponible pour le moment</p>
                    </div>
                @endif
            </div>
        @else
            <div class="card text-center py-8 sm:py-12 animate-fadeInUp">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
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
//  SWITCH TAB
// ================================================================
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tab).classList.add('active');
    document.querySelector(`.tab-btn[data-tab="${tab}"]`).classList.add('active');
    
    document.getElementById('searchInput').value = '';
    document.querySelectorAll('.product-card').forEach(card => card.style.display = '');
    document.getElementById('searchResult').classList.add('hidden');
}

// ================================================================
//  RECHERCHE
// ================================================================
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var productCards = document.querySelectorAll('.product-card');
    var searchResult = document.getElementById('searchResult');
    var resultCount = document.getElementById('resultCount');
    var timeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var query = this.value.trim().toLowerCase();
            
            clearTimeout(timeout);
            
            timeout = setTimeout(function() {
                var count = 0;
                var activeTab = document.querySelector('.tab-content.active');
                var activeTabId = activeTab ? activeTab.id : 'tab-products';
                
                productCards.forEach(function(card) {
                    var name = card.dataset.name || '';
                    var description = card.dataset.description || '';
                    
                    var isInActiveTab = false;
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
    
    // Initialiser le panier (s'assurer qu'il est chargé)
    if (typeof window.loadCart === 'function') {
        window.loadCart();
    }
});
</script>
@endpush
@endsection