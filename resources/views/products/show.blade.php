@extends('layouts.app')

@push('styles')
<style>
    .product-gallery img {
        transition: transform 0.5s ease;
    }
    .product-gallery:hover img {
        transform: scale(1.02);
    }
    .quantity-btn {
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-md);
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        user-select: none;
    }
    .quantity-btn:hover {
        background: var(--bg-hover);
        border-color: var(--primary-500);
    }
    .quantity-btn:active { transform: scale(0.95); }
    
    @media (max-width: 640px) {
        .quantity-btn { width: 1.75rem; height: 1.75rem; font-size: 0.875rem; }
        .product-card { padding: 0.75rem; }
        .text-3xl { font-size: 1.5rem; }
        .text-8xl { font-size: 4rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Fil d'Ariane -->
    <nav class="text-xs sm:text-sm text-[var(--text-secondary)] animate-fadeInUp">
        <a href="{{ route('products.index') }}" class="hover:text-primary-500 transition">Produits</a>
        <span class="mx-1 sm:mx-2">/</span>
        <span class="text-[var(--text-primary)] font-medium">{{ $product->name }}</span>
    </nav>

    <!-- Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Image -->
        <div class="card product-gallery animate-fadeInLeft p-3 sm:p-4 md:p-6">
            <div class="flex items-center justify-center min-h-[200px] sm:min-h-[300px] md:min-h-[400px] relative">
                @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                    <img src="{{ asset('storage/products/' . $product->image) }}" 
                         alt="{{ $product->name }}"
                         class="max-h-[200px] sm:max-h-[300px] md:max-h-[400px] w-auto object-contain">
                @else
                    <svg class="w-24 h-24 sm:w-32 sm:h-32 md:w-48 md:h-48 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                @endif
                
                @if($product->is_featured)
                    <span class="absolute top-2 sm:top-4 right-2 sm:right-4 badge badge-warning text-[10px] sm:text-xs">
                        Vedette
                    </span>
                @endif
                @if($product->stock == 0)
                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-xl">
                        <span class="badge badge-danger text-sm sm:text-xl py-2 sm:py-4 px-4 sm:px-8 transform -rotate-12">
                            RUPTURE DE STOCK
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Infos -->
        <div class="card animate-fadeInRight p-3 sm:p-4 md:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">{{ $product->category ?? 'General' }}</p>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)] mt-0.5 sm:mt-1">{{ $product->name }}</h1>
                </div>
                @if($product->is_featured)
                    <span class="text-xl sm:text-2xl">⭐</span>
                @endif
            </div>

            <div class="mt-3 sm:mt-4 flex flex-wrap items-end gap-2 sm:gap-4">
                <span class="text-2xl sm:text-3xl md:text-4xl font-bold text-primary-500">${{ number_format($product->price, 2) }}</span>
                @if($product->cost)
                    <span class="text-base sm:text-lg text-[var(--text-secondary)] line-through">${{ number_format($product->cost, 2) }}</span>
                @endif
                <span class="text-xs sm:text-sm ml-auto {{ $product->stock > 10 ? 'text-green-500' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                    @if($product->stock > 10) En stock
                    @elseif($product->stock > 0) {{ $product->stock }} restant(s)
                    @else Rupture
                    @endif
                </span>
            </div>

            <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-[var(--border-color)]">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-1 sm:mb-2">Description</h3>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] leading-relaxed">
                    {{ $product->description ?? 'Aucune description disponible pour ce produit.' }}
                </p>
            </div>

            @if($product->sku)
                <div class="mt-3 sm:mt-4 text-xs sm:text-sm text-[var(--text-secondary)]">
                    <span class="font-medium">SKU:</span> {{ $product->sku }}
                </div>
            @endif

            <!-- Boutons -->
            <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row gap-2 sm:gap-3">
                @if($product->stock > 0)
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <div class="flex items-center gap-1">
                                <button type="button" class="quantity-btn" onclick="decrementQty()">-</button>
                                <input type="number" id="qty" name="quantity" value="1" min="1" max="{{ $product->stock }}" 
                                       class="input w-12 sm:w-16 text-center text-sm sm:text-base">
                                <button type="button" class="quantity-btn" onclick="incrementQty()">+</button>
                            </div>
                            <button type="submit" class="btn btn-primary flex-1 text-sm sm:text-base py-2 sm:py-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Ajouter au panier
                            </button>
                        </div>
                    </form>
                @else
                    <button class="btn btn-danger w-full opacity-50 cursor-not-allowed text-sm sm:text-base py-2 sm:py-2.5">
                        Rupture de stock
                    </button>
                @endif
            </div>

            <!-- Partage -->
            <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-[var(--border-color)]">
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mb-1 sm:mb-2">Partager ce produit :</p>
                <div class="flex gap-1.5 sm:gap-2">
                    <button onclick="copyLink()" class="btn btn-outline btn-sm btn-icon" title="Copier le lien">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits similaires -->
    @if($relatedProducts->count() > 0)
        <div class="animate-fadeInUp delay-4">
            <h2 class="text-base sm:text-xl font-bold text-[var(--text-primary)] mb-3 sm:mb-4">Produits similaires</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
                @foreach($relatedProducts as $related)
                    <div class="product-card card p-2 sm:p-3 text-center hover:shadow-hover transition">
                        <a href="{{ route('products.show', $related->slug) }}">
                            <div class="aspect-square bg-[var(--bg-secondary)] rounded-lg overflow-hidden flex items-center justify-center">
                                @if($related->image && file_exists(storage_path('app/public/products/' . $related->image)))
                                    <img src="{{ asset('storage/products/' . $related->image) }}" 
                                         alt="{{ $related->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <svg class="w-8 h-8 sm:w-12 sm:h-12 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="font-medium text-[var(--text-primary)] mt-1 sm:mt-2 text-xs sm:text-sm truncate">{{ $related->name }}</p>
                            <p class="text-xs sm:text-sm font-bold text-primary-500">${{ number_format($related->price, 2) }}</p>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function incrementQty() {
    var input = document.getElementById('qty');
    var max = parseInt(input.max);
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQty() {
    var input = document.getElementById('qty');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function copyLink() {
    var link = window.location.href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(function() {
            showToast('Lien copie !');
        });
    } else {
        var input = document.createElement('input');
        input.value = link;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showToast('Lien copie !');
    }
}

function showToast(message) {
    var toast = document.createElement('div');
    toast.className = 'fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 px-4 sm:px-6 py-3 rounded-lg bg-green-500 text-white font-medium shadow-lg z-50 transform transition-all duration-500';
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