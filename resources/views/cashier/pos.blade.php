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

    /* Styles du panier */
    .cart-sidebar {
        position: fixed;
        right: 0;
        top: 0;
        height: 100vh;
        width: 380px;
        background: var(--bg-card);
        border-left: 1px solid var(--border-color);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 999;
        display: flex;
        flex-direction: column;
        box-shadow: -4px 0 24px rgba(0,0,0,0.1);
    }
    .cart-sidebar.open {
        transform: translateX(0);
    }
    .cart-sidebar .cart-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cart-sidebar .cart-body {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 1.5rem;
    }
    .cart-sidebar .cart-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-color);
        background: var(--bg-secondary);
    }
    .cart-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .cart-item .item-image {
        width: 50px;
        height: 50px;
        border-radius: var(--radius-md);
        overflow: hidden;
        flex-shrink: 0;
        background: var(--bg-secondary);
    }
    .cart-item .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .cart-item .item-info {
        flex: 1;
    }
    .cart-item .item-info h4 {
        font-size: 0.813rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .cart-item .item-info .item-price {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .cart-item .item-quantity {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .cart-item .item-quantity button {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-primary);
        cursor: pointer;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cart-item .item-quantity button:hover {
        background: var(--primary-500);
        color: white;
        border-color: var(--primary-500);
    }
    .cart-item .item-quantity span {
        min-width: 20px;
        text-align: center;
        font-weight: 600;
    }
    .cart-item .remove-item {
        color: #ef4444;
        cursor: pointer;
        background: none;
        border: none;
        font-size: 1rem;
    }
    .cart-item .remove-item:hover {
        color: #dc2626;
    }
    
    .cart-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 998;
        display: none;
    }
    .cart-overlay.active {
        display: block;
    }

    .cart-total {
        display: flex;
        justify-content: space-between;
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .cart-total .total-price {
        color: var(--primary-500);
    }

    .cart-badge {
        position: relative;
    }
    .cart-badge .badge-count {
        position: absolute;
        top: -6px;
        right: -6px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
</style>
@endpush

@section('title', 'Point de Vente')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
                Point de Vente
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Ajoutez un ou plusieurs produits au panier
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="toggleCart()" class="btn btn-outline btn-sm cart-badge">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Panier
                <span id="cartCount" class="badge-count hidden">0</span>
            </button>
            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="animate-fadeInUp delay-1">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" 
                   id="searchInput"
                   placeholder="Rechercher un produit..."
                   class="input pl-10 search-input text-sm sm:text-base">
        </div>
    </div>

    <!-- Search Results -->
    <div id="searchResult" class="text-xs sm:text-sm text-[var(--text-secondary)] hidden animate-fadeInUp">
        Resultats: <span id="resultCount" class="font-semibold text-primary-500">0</span> produit(s)
    </div>

    <!-- Product Grid -->
    <div id="productsContainer">
        @if(isset($products) && $products->count() > 0)
            <div class="product-grid">
                @foreach($products as $product)
                    <div class="product-card animate-fadeInUp delay-{{ min($loop->index % 6 + 1, 12) }}"
                         data-name="{{ strtolower($product->name) }}"
                         data-description="{{ strtolower($product->description ?? '') }}"
                         data-product-id="{{ $product->id }}">
                        
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
                            
                            <!-- PV -->
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
                                    <button onclick="addToCart({{ $product->id }})" 
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
            <div class="card text-center py-8 sm:py-12 animate-fadeInUp">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
                <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun produit disponible</h3>
                <p class="text-sm sm:text-base text-[var(--text-tertiary)] mt-1 sm:mt-2">Aucun produit n'est disponible pour le moment</p>
            </div>
        @endif
    </div>
</div>

<!-- Overlay -->
<div id="cartOverlay" class="cart-overlay" onclick="toggleCart()"></div>

<!-- Cart Sidebar -->
<div id="cartSidebar" class="cart-sidebar">
    <div class="cart-header">
        <h3 class="font-bold text-[var(--text-primary)]">🛒 Panier</h3>
        <button onclick="toggleCart()" class="text-[var(--text-tertiary)] hover:text-[var(--text-primary)]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div id="cartBody" class="cart-body">
        <div id="cartEmpty" class="text-center py-8 text-[var(--text-tertiary)]">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p>Votre panier est vide</p>
            <p class="text-xs mt-1">Ajoutez des produits en cliquant sur "Ajouter au panier"</p>
        </div>
        <div id="cartItems" class="hidden">
            <!-- Les items seront ajoutés ici par JavaScript -->
        </div>
    </div>
    <div id="cartFooter" class="cart-footer hidden">
        <div class="cart-total">
            <span>Total</span>
            <span id="cartTotal" class="total-price">$0.00</span>
        </div>
        <button onclick="checkout()" class="btn btn-success btn-block mt-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Passer la commande
        </button>
        <button onclick="clearCart()" class="btn btn-outline btn-sm w-full mt-2">
            Vider le panier
        </button>
    </div>
</div>

@push('scripts')
<script>
// ================================================================
//  PANIER (GESTION MULTI-PRODUITS)
// ================================================================
let cart = [];
let cartItemsContainer = document.getElementById('cartItems');
let cartEmpty = document.getElementById('cartEmpty');
let cartFooter = document.getElementById('cartFooter');
let cartTotal = document.getElementById('cartTotal');
let cartCount = document.getElementById('cartCount');

// Récupérer le panier depuis le localStorage
function loadCart() {
    try {
        const saved = localStorage.getItem('pos_cart');
        if (saved) {
            cart = JSON.parse(saved);
            renderCart();
        }
    } catch (e) {
        cart = [];
    }
}

// Sauvegarder le panier dans localStorage
function saveCart() {
    localStorage.setItem('pos_cart', JSON.stringify(cart));
    updateCartCount();
}

// Ajouter un produit au panier
function addToCart(productId) {
    const existing = cart.find(item => item.id === productId);
    if (existing) {
        existing.quantity += 1;
        saveCart();
        renderCart();
        showToast('Quantité augmentée', 'success');
        return;
    }
    
    const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
    if (!card) {
        showToast('Erreur: produit non trouvé', 'error');
        return;
    }
    
    const name = card.querySelector('.product-name')?.textContent || 'Produit';
    const priceText = card.querySelector('.product-price')?.textContent || '$0.00';
    const price = parseFloat(priceText.replace('$', '').replace(',', ''));
    const image = card.querySelector('.image-container img')?.getAttribute('src') || null;
    
    cart.push({
        id: productId,
        name: name,
        price: price,
        image: image,
        quantity: 1
    });
    
    saveCart();
    renderCart();
    showToast('Produit ajouté au panier', 'success');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    renderCart();
}

function updateQuantity(productId, delta) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            saveCart();
            renderCart();
        }
    }
}

function clearCart() {
    if (cart.length === 0) return;
    if (confirm('Vider le panier ?')) {
        cart = [];
        saveCart();
        renderCart();
        showToast('Panier vidé', 'info');
    }
}

function renderCart() {
    cartItemsContainer.innerHTML = '';
    
    if (cart.length === 0) {
        cartEmpty.classList.remove('hidden');
        cartItemsContainer.classList.add('hidden');
        cartFooter.classList.add('hidden');
        updateCartCount();
        return;
    }
    
    cartEmpty.classList.add('hidden');
    cartItemsContainer.classList.remove('hidden');
    cartFooter.classList.remove('hidden');
    
    let total = 0;
    cart.forEach(item => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div class="item-image">
                ${item.image ? `<img src="${item.image}" alt="${item.name}">` : `
                    <svg class="w-full h-full p-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                `}
            </div>
            <div class="item-info">
                <h4>${item.name}</h4>
                <div class="item-price">$${item.price.toFixed(2)} x ${item.quantity}</div>
            </div>
            <div class="item-quantity">
                <button onclick="updateQuantity(${item.id}, -1)">-</button>
                <span>${item.quantity}</span>
                <button onclick="updateQuantity(${item.id}, 1)">+</button>
            </div>
            <button class="remove-item" onclick="removeFromCart(${item.id})">×</button>
        `;
        cartItemsContainer.appendChild(div);
    });
    
    cartTotal.textContent = `$${total.toFixed(2)}`;
    updateCartCount();
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    if (count > 0) {
        cartCount.textContent = count;
        cartCount.classList.remove('hidden');
    } else {
        cartCount.classList.add('hidden');
    }
}

function toggleCart() {
    document.getElementById('cartSidebar').classList.toggle('open');
    document.getElementById('cartOverlay').classList.toggle('active');
}

function checkout() {
    if (cart.length === 0) {
        showToast('Le panier est vide', 'error');
        return;
    }
    const products = cart.map(item => item.id);
    window.location.href = `/cashier/checkout?products=${products.join(',')}`;
}

function showToast(message, type = 'success') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    const toast = document.createElement('div');
    toast.className = `custom-toast fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${colors[type] || colors.success} text-white text-sm max-w-xs`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ================================================================
//  RECHERCHE DE PRODUITS
// ================================================================
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    
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
                } else {
                    searchResult.classList.add('hidden');
                }
            }, 300);
        });
    }
});
</script>
@endpush
@endsection