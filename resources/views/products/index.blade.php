@extends('layouts.app')

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
    .product-card .overlay {
        position: absolute;
        inset: 0;
        background: rgba(90, 182, 56, 0.08);
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-card:hover .overlay {
        opacity: 1;
    }
    .product-card .overlay span {
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: var(--radius-md);
        font-size: 0.7rem;
        font-weight: 600;
        transform: scale(0.8);
        transition: transform 0.3s ease;
    }
    .product-card:hover .overlay span {
        transform: scale(1);
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
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
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
    
    /* ============================================================
       RESPONSIVE GRID
    ============================================================ */
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

    /* ============================================================
       PAGINATION - NEXT / PREVIOUS
    ============================================================ */
    .pagination-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }
    .pagination-container .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
        text-decoration: none;
        min-width: 100px;
    }
    .pagination-container .pagination-btn:hover:not(.disabled) {
        border-color: var(--primary-500);
        color: var(--primary-500);
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .pagination-container .pagination-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }
    .pagination-container .pagination-btn .icon {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
    }
    .pagination-container .pagination-info {
        font-size: 0.813rem;
        color: var(--text-secondary);
        padding: 0 0.5rem;
    }
    .pagination-container .page-numbers {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        flex-wrap: wrap;
    }
    .pagination-container .page-numbers .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
        background: transparent;
        color: var(--text-secondary);
        text-decoration: none;
    }
    .pagination-container .page-numbers .page-link:hover:not(.active) {
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    .pagination-container .page-numbers .page-link.active {
        border-color: var(--primary-500);
        background: var(--primary-500);
        color: white;
    }
    .pagination-container .page-numbers .page-link.disabled {
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
    }

    @media (max-width: 640px) {
        .pagination-container .pagination-btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            min-width: 70px;
        }
        .pagination-container .pagination-btn .icon {
            width: 1rem;
            height: 1rem;
        }
        .pagination-container .page-numbers .page-link {
            width: 2rem;
            height: 2rem;
            font-size: 0.75rem;
        }
        .pagination-container .pagination-info {
            font-size: 0.7rem;
        }
        .pagination-container {
            gap: 0.25rem;
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
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Produits</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Découvrez notre catalogue</p>
        </div>
        <div class="header-actions flex flex-wrap items-center gap-2 sm:gap-3">
            <div class="search-wrapper relative">
                <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" 
                       id="searchInput"
                       placeholder="Rechercher..."
                       class="input pl-7 sm:pl-9 search-input text-sm sm:text-base"
                       style="width: 200px;">
            </div>
            <a href="{{ route('cart.index') }}" class="btn btn-outline btn-sm relative">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Panier
                <span id="cartCount" class="absolute -top-1 -right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full hidden">
                    0
                </span>
            </a>
        </div>
    </div>

    <!-- Search Results -->
    <div id="searchResult" class="text-xs sm:text-sm text-[var(--text-secondary)] hidden animate-fadeInUp">
        Résultats: <span id="resultCount" class="font-semibold text-primary-500">0</span> produit(s)
    </div>

    <!-- Product Grid -->
    <div id="productsContainer">
        @if($products->count() > 0)
            <div class="product-grid">
                @foreach($products as $product)
                    <div class="product-card animate-fadeInUp delay-{{ min($loop->index % 6 + 1, 12) }}"
                         data-name="{{ strtolower($product->name) }}"
                         data-description="{{ strtolower($product->description ?? '') }}">
                        
                        <a href="{{ route('products.show', $product->slug) }}" class="block image-container">
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
                            <div class="overlay">
                                <span>Voir détails</span>
                            </div>
                            
                            @if($product->is_featured)
                                <span class="absolute top-1 sm:top-2 left-1 sm:left-2 badge badge-warning text-[8px] sm:text-[10px]">
                                    En vedette
                                </span>
                            @endif
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
                        </a>

                        <div class="product-content p-2 sm:p-3 flex flex-col flex-1">
                            <a href="{{ route('products.show', $product->slug) }}" class="block">
                                <h3 class="product-name font-semibold text-[var(--text-primary)] hover:text-primary-500 transition text-xs sm:text-sm truncate">
                                    {{ $product->name }}
                                </h3>
                            </a>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate-2 h-6 sm:h-8 flex-1">
                                {{ Str::limit($product->description ?? '', 40) }}
                            </p>
                            <div class="flex items-center justify-between mt-1 sm:mt-2 pt-1 sm:pt-2 border-t border-[var(--border-color)]">
                                <span class="product-price text-sm sm:text-lg font-bold text-primary-500">${{ number_format($product->price, 2) }}</span>
                                <span class="product-stock text-[8px] sm:text-[10px] {{ $product->stock > 10 ? 'text-green-500' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                    @if($product->stock > 10) En stock
                                    @elseif($product->stock > 0) {{ $product->stock }} restants
                                    @else Rupture
                                    @endif
                                </span>
                            </div>
                            <div class="mt-1 sm:mt-2 flex gap-1 sm:gap-2">
                                <a href="{{ route('products.show', $product->slug) }}" 
                                   class="flex-1 btn btn-outline btn-sm text-center text-[10px] sm:text-xs">
                                    Voir
                                </a>
                                @if($product->stock > 0)
                                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1" 
                                          onsubmit="addToCart(event, this, '{{ $product->name }}')">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm w-full">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <button class="flex-1 btn btn-danger btn-sm opacity-50 cursor-not-allowed text-[10px] sm:text-xs">
                                        Rupture
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- ✅ PAGINATION AVEC NEXT / PREVIOUS -->
            @if($products->hasPages())
                <div class="pagination-container" id="paginationContainer">
                    <!-- Previous Button -->
                    @if($products->onFirstPage())
                        <span class="pagination-btn disabled">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Précédent
                        </span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="pagination-btn">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Précédent
                        </a>
                    @endif

                    <!-- Page Numbers -->
                    <div class="page-numbers">
                        @php
                            $currentPage = $products->currentPage();
                            $lastPage = $products->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                            
                            if ($start > 1) {
                                echo '<a href="' . $products->url(1) . '" class="page-link">1</a>';
                                if ($start > 2) {
                                    echo '<span class="page-link disabled">...</span>';
                                }
                            }
                            
                            for ($i = $start; $i <= $end; $i++) {
                                if ($i == $currentPage) {
                                    echo '<span class="page-link active">' . $i . '</span>';
                                } else {
                                    echo '<a href="' . $products->url($i) . '" class="page-link">' . $i . '</a>';
                                }
                            }
                            
                            if ($end < $lastPage) {
                                if ($end < $lastPage - 1) {
                                    echo '<span class="page-link disabled">...</span>';
                                }
                                echo '<a href="' . $products->url($lastPage) . '" class="page-link">' . $lastPage . '</a>';
                            }
                        @endphp
                    </div>

                    <!-- Next Button -->
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="pagination-btn">
                            Suivant
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <span class="pagination-btn disabled">
                            Suivant
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    @endif
                </div>
            @endif
        @else
            <div class="card text-center py-8 sm:py-12 animate-fadeInUp p-4 sm:p-6">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
                <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun produit disponible</h3>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Revenez plus tard pour découvrir de nouveaux produits</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mettre à jour le compteur du panier
    updateCartCount();
    
    var searchInput = document.getElementById('searchInput');
    var productCards = document.querySelectorAll('.product-card');
    var searchResult = document.getElementById('searchResult');
    var resultCount = document.getElementById('resultCount');
    var paginationContainer = document.getElementById('paginationContainer');
    var timeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var query = this.value.trim().toLowerCase();
            
            clearTimeout(timeout);
            
            timeout = setTimeout(function() {
                var count = 0;
                
                productCards.forEach(function(card) {
                    var name = card.dataset.name || '';
                    var description = card.dataset.description || '';
                    
                    if (name.includes(query) || description.includes(query)) {
                        card.style.display = '';
                        count++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                if (query.length > 0) {
                    searchResult.classList.remove('hidden');
                    resultCount.textContent = count;
                    if (paginationContainer) paginationContainer.style.display = 'none';
                } else {
                    searchResult.classList.add('hidden');
                    if (paginationContainer) paginationContainer.style.display = '';
                }
            }, 300);
        });
    }
});

function updateCartCount() {
    fetch('{{ route("cart.count") }}')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var cartCount = document.getElementById('cartCount');
            if (cartCount) {
                if (data.count > 0) {
                    cartCount.textContent = data.count > 99 ? '99+' : data.count;
                    cartCount.classList.remove('hidden');
                } else {
                    cartCount.classList.add('hidden');
                }
            }
        })
        .catch(function(error) {
            console.log('Error updating cart count:', error);
        });
}

function addToCart(event, form, productName) {
    event.preventDefault();
    
    var submitBtn = form.querySelector('button[type="submit"]');
    var originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="animate-spin">...</span>';
    submitBtn.disabled = true;
    
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        if (data.success) {
            updateCartCount();
            
            submitBtn.innerHTML = 'Ajouté';
            setTimeout(function() {
                submitBtn.innerHTML = originalText;
            }, 1500);
            
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(function(error) {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        showToast('Erreur lors de l\'ajout au panier', 'error');
        console.log('Error:', error);
    });
}

function showToast(message, type) {
    type = type || 'success';
    document.querySelectorAll('.custom-toast').forEach(function(el) { el.remove(); });
    
    var toast = document.createElement('div');
    toast.className = 'custom-toast fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 px-4 sm:px-6 py-3 rounded-lg text-white font-medium shadow-lg z-50';
    toast.style.animation = 'slideUp 0.3s ease forwards';
    toast.style.fontSize = '0.875rem';
    
    if (type === 'success') {
        toast.style.background = '#22c55e';
    } else if (type === 'error') {
        toast.style.background = '#ef4444';
    } else if (type === 'warning') {
        toast.style.background = '#f59e0b';
    } else {
        toast.style.background = '#6366f1';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(function() { toast.remove(); }, 500);
    }, 3000);
}
</script>
@endpush
@endsection