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
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-md);
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
        font-size: 1.25rem;
        font-weight: 700;
        cursor: pointer;
        user-select: none;
    }
    .quantity-btn:hover {
        background: var(--bg-hover);
        border-color: var(--primary-500);
    }
    .quantity-btn:active { transform: scale(0.95); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Fil d'Ariane -->
    <nav class="text-sm text-[var(--text-secondary)] animate-fadeInUp">
        <a href="{{ route('products.index') }}" class="hover:text-primary-500 transition">🛍️ Produits</a>
        <span class="mx-2">/</span>
        <span class="text-[var(--text-primary)] font-medium">{{ $product->name }}</span>
    </nav>

    <!-- Détails -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Image -->
        <div class="card product-gallery animate-fadeInLeft">
            <div class="flex items-center justify-center min-h-[300px] md:min-h-[400px] relative">
                @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                    <img src="{{ asset('storage/products/' . $product->image) }}" 
                         alt="{{ $product->name }}"
                         class="max-h-[400px] w-auto object-contain">
                @else
                    <div class="text-8xl text-[var(--text-tertiary)]">📦</div>
                @endif
                
                @if($product->is_featured)
                    <span class="absolute top-4 right-4 badge badge-warning text-sm">
                        ⭐ Vedette
                    </span>
                @endif
                @if($product->stock == 0)
                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-xl">
                        <span class="badge badge-danger text-xl py-4 px-8 transform -rotate-12">
                            RUPTURE DE STOCK
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Infos -->
        <div class="card animate-fadeInRight">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-[var(--text-secondary)]">{{ $product->category ?? 'Général' }}</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)] mt-1">{{ $product->name }}</h1>
                </div>
                @if($product->is_featured)
                    <span class="text-2xl">⭐</span>
                @endif
            </div>

            <div class="mt-4 flex items-end gap-4">
                <span class="text-3xl md:text-4xl font-bold text-primary-500">${{ number_format($product->price, 2) }}</span>
                @if($product->cost)
                    <span class="text-lg text-[var(--text-secondary)] line-through">${{ number_format($product->cost, 2) }}</span>
                @endif
                <span class="text-sm ml-auto {{ $product->stock > 10 ? 'text-green-500' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                    @if($product->stock > 10) ✅ En stock
                    @elseif($product->stock > 0) ⚠️ {{ $product->stock }} restant(s)
                    @else ❌ Rupture
                    @endif
                </span>
            </div>

            <div class="mt-6 pt-6 border-t border-[var(--border-color)]">
                <h3 class="font-semibold text-[var(--text-primary)] mb-2">📝 Description</h3>
                <p class="text-[var(--text-secondary)] leading-relaxed">
                    {{ $product->description ?? 'Aucune description disponible pour ce produit.' }}
                </p>
            </div>

            @if($product->sku)
                <div class="mt-4 text-sm text-[var(--text-secondary)]">
                    <span class="font-medium">SKU:</span> {{ $product->sku }}
                </div>
            @endif

            <!-- Boutons -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                @if($product->stock > 0)
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                <button type="button" class="quantity-btn" onclick="decrementQty()">−</button>
                                <input type="number" id="qty" name="quantity" value="1" min="1" max="{{ $product->stock }}" 
                                       class="input w-16 text-center">
                                <button type="button" class="quantity-btn" onclick="incrementQty()">+</button>
                            </div>
                            <button type="submit" class="btn btn-primary flex-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Ajouter au panier
                            </button>
                        </div>
                    </form>
                @else
                    <button class="btn btn-danger w-full opacity-50 cursor-not-allowed">
                        ❌ Rupture de stock
                    </button>
                @endif
            </div>

            <!-- Partage -->
            <div class="mt-6 pt-6 border-t border-[var(--border-color)]">
                <p class="text-sm text-[var(--text-secondary)] mb-2">Partager ce produit :</p>
                <div class="flex gap-2">
                    <a href="#" class="btn btn-outline btn-sm btn-icon" title="Copier le lien">📋</a>
                    <a href="#" class="btn btn-outline btn-sm btn-icon" title="Twitter">🐦</a>
                    <a href="#" class="btn btn-outline btn-sm btn-icon" title="Facebook">📘</a>
                    <a href="#" class="btn btn-outline btn-sm btn-icon" title="WhatsApp">💬</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits similaires -->
    @if($relatedProducts->count() > 0)
        <div class="animate-fadeInUp delay-4">
            <h2 class="text-xl font-bold text-[var(--text-primary)] mb-4">🔥 Produits similaires</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($relatedProducts as $related)
                    <div class="product-card card p-3 text-center hover:shadow-hover transition">
                        <a href="{{ route('products.show', $related->slug) }}">
                            <div class="aspect-square bg-[var(--bg-secondary)] rounded-lg overflow-hidden flex items-center justify-center">
                                @if($related->image && file_exists(storage_path('app/public/products/' . $related->image)))
                                    <img src="{{ asset('storage/products/' . $related->image) }}" 
                                         alt="{{ $related->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="text-3xl">📦</span>
                                @endif
                            </div>
                            <p class="font-medium text-[var(--text-primary)] mt-2 text-sm truncate">{{ $related->name }}</p>
                            <p class="text-sm font-bold text-primary-500">${{ number_format($related->price, 2) }}</p>
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
    const input = document.getElementById('qty');
    const max = parseInt(input.max);
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQty() {
    const input = document.getElementById('qty');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}
</script>
@endpush
@endsection