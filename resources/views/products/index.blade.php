@extends('layouts.app')

@push('styles')
<style>
    .product-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
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
    }
    .product-card .image-container img {
        transition: transform 0.5s ease;
    }
    .product-card:hover .image-container img {
        transform: scale(1.05);
    }
    .product-card .overlay {
        position: absolute;
        inset: 0;
        background: rgba(99,102,241,0.1);
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-card:hover .overlay { opacity: 1; }
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
    .product-card:hover .overlay span { transform: scale(1); }
    
    @media (max-width: 640px) {
        .product-card .overlay span { font-size: 0.6rem; padding: 0.3rem 0.6rem; }
        .product-card .text-lg { font-size: 1rem; }
        .product-card .btn-sm { font-size: 0.65rem; padding: 0.25rem 0.5rem; }
        .product-card .btn-sm svg { width: 0.875rem; height: 0.875rem; }
        .text-6xl { font-size: 3rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Produits</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Decouvrez notre catalogue</p>
        </div>
        <div class="relative">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" 
                   id="searchInput"
                   placeholder="Rechercher..."
                   class="input pl-7 sm:pl-9 w-36 sm:w-48 md:w-64 text-sm sm:text-base">
        </div>
    </div>

    <!-- Resultats -->
    <div id="searchResult" class="text-xs sm:text-sm text-[var(--text-secondary)] hidden animate-fadeIn">
        Resultats : <span id="resultCount" class="font-semibold text-primary-500">0</span> produit(s)
    </div>

    <!-- Grille -->
    <div id="productsContainer">
        @if($products->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3 md:gap-4">
                @foreach($products as $product)
                    <div class="product-card card p-0 overflow-hidden animate-fadeInUp delay-{{ $loop->index % 6 + 1 }}"
                         data-name="{{ strtolower($product->name) }}"
                         data-description="{{ strtolower($product->description ?? '') }}">
                        
                        <a href="{{ route('products.show', $product->slug) }}" class="block">
                            <div class="image-container aspect-square">
                                @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                                    <img src="{{ asset('storage/products/' . $product->image) }}" 
                                         alt="{{ $product->name }}"
                                         loading="lazy"
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}'">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-4xl sm:text-5xl text-[var(--text-tertiary)]">
                                        <svg class="w-12 h-12 sm:w-16 sm:h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="overlay">
                                    <span>Voir details</span>
                                </div>
                                
                                @if($product->is_featured)
                                    <span class="absolute top-1 sm:top-2 left-1 sm:left-2 badge badge-warning text-[8px] sm:text-[10px]">
                                        Vedette
                                    </span>
                                @endif
                                @if($product->stock < 5 && $product->stock > 0)
                                    <span class="absolute top-1 sm:top-2 right-1 sm:right-2 badge badge-warning text-[8px] sm:text-[10px]">
                                        Stock limite
                                    </span>
                                @endif
                                @if($product->stock == 0)
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                        <span class="badge badge-danger text-xs sm:text-sm py-1 sm:py-2 px-2 sm:px-4 transform -rotate-12">
                                            Rupture
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="p-2 sm:p-3">
                            <a href="{{ route('products.show', $product->slug) }}" class="block">
                                <h3 class="font-semibold text-[var(--text-primary)] hover:text-primary-500 transition text-xs sm:text-sm truncate">
                                    {{ $product->name }}
                                </h3>
                            </a>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate-2 h-6 sm:h-8">
                                {{ Str::limit($product->description ?? '', 40) }}
                            </p>
                            <div class="flex items-center justify-between mt-1 sm:mt-2 pt-1 sm:pt-2 border-t border-[var(--border-color)]">
                                <span class="text-sm sm:text-lg font-bold text-primary-500">${{ number_format($product->price, 2) }}</span>
                                <span class="text-[8px] sm:text-[10px] {{ $product->stock > 10 ? 'text-green-500' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                    @if($product->stock > 10) En stock
                                    @elseif($product->stock > 0) {{ $product->stock }} restant(s)
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

            @if($products->hasPages())
                <div class="mt-4 sm:mt-6" id="paginationContainer">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="card text-center py-8 sm:py-12 animate-fadeInUp p-4 sm:p-6">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
                <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun produit disponible</h3>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Revenez plus tard pour decouvrir nos nouveautes</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var productCards = document.querySelectorAll('.product-card');
    var searchResult = document.getElementById('searchResult');
    var resultCount = document.getElementById('resultCount');
    var paginationContainer = document.getElementById('paginationContainer');
    var timeout;

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
});

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
            var cartCount = document.getElementById('cartCount');
            if (data.count > 0) {
                if (cartCount) {
                    cartCount.textContent = data.count > 99 ? '99+' : data.count;
                    cartCount.classList.remove('hidden');
                } else {
                    var cartIcon = document.querySelector('a[href="{{ route("cart.index") }}"]');
                    if (cartIcon) {
                        var badge = document.createElement('span');
                        badge.id = 'cartCount';
                        badge.className = 'absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full';
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        cartIcon.appendChild(badge);
                    }
                }
            }
            
            submitBtn.innerHTML = 'Ajoute';
            setTimeout(function() {
                submitBtn.innerHTML = originalText;
            }, 1500);
            
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    ['catch'](function(error) {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        showToast('Erreur lors de l\'ajout au panier', 'error');
        console.log('Erreur:', error);
    });
}

function showToast(message, type) {
    type = type || 'success';
    document.querySelectorAll('.custom-toast').forEach(function(el) { el.remove(); });
    
    var toast = document.createElement('div');
    toast.className = 'custom-toast fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 px-4 sm:px-6 py-3 rounded-lg text-white font-medium shadow-lg z-50 transform transition-all duration-500 ' + (type === 'success' ? 'bg-green-500' : 'bg-red-500');
    toast.style.animation = 'fadeInUp 0.3s ease forwards';
    toast.style.fontSize = '0.875rem';
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