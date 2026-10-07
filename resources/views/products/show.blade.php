{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')

@section('title', $product->name)



@section('content')
<div class="member-shop member-shop--detail space-y-4 sm:space-y-6">

    <nav class="text-xs sm:text-sm text-[var(--text-secondary)] member-shop-animate member-shop-animate--1">
        <a href="{{ route('products.index') }}" class="hover:text-primary-500 transition">Produits</a>
        <span class="mx-1 sm:mx-2">/</span>
        <span class="text-[var(--text-primary)] font-medium">{{ $product->name }}</span>
    </nav>

    <!-- Product Details -->
    <div class="product-details-grid grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        
        <!-- Image -->
        <div class="card product-gallery shop-gallery p-3 sm:p-4 md:p-6">
            <div class="flex items-center justify-center min-h-[200px] sm:min-h-[300px] md:min-h-[400px] relative">
                @auth
                    <button type="button"
                            onclick="toggleWishlist({{ $product->id }})"
                            class="shop-gallery-fav {{ $isInWishlist ? 'is-active' : '' }}"
                            id="wishlistBtnFloat"
                            aria-label="Favoris">
                        <svg class="shop-gallery-fav__icon" fill="{{ $isInWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                @endauth
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
                        En vedette
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

        <!-- Information -->
        <div class="card member-shop-detail-info p-3 sm:p-4 md:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">{{ $product->category ?? 'Général' }}</p>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)] mt-0.5 sm:mt-1">{{ $product->name }}</h1>
                </div>
            </div>

            <!-- PV ET BV -->
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if($product->pv_value)
                    <span class="pv-badge">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        {{ $product->pv_value }} PV
                    </span>
                @endif
                @if($product->bv_value)
                    <span class="bv-badge">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $product->bv_value }} BV
                    </span>
                @endif
            </div>

            <div class="mt-3 sm:mt-4 flex flex-wrap items-end gap-2 sm:gap-4">
                <span class="text-2xl sm:text-3xl md:text-4xl font-bold text-primary-500">${{ number_format($product->price, 2) }}</span>
                @if($product->cost)
                    <span class="text-base sm:text-lg text-[var(--text-secondary)] line-through">${{ number_format($product->cost, 2) }}</span>
                @endif
                <span class="text-xs sm:text-sm ml-auto {{ $product->stock > 10 ? 'text-green-500' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                    @if($product->stock > 10) En stock
                    @elseif($product->stock > 0) {{ $product->stock }} restants
                    @else Rupture de stock
                    @endif
                </span>
            </div>

            <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-[var(--border-color)]">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-1 sm:mb-2">Description</h3>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] leading-relaxed">
                    {{ $product->description ?? 'Aucune description disponible pour ce produit.' }}
                </p>
            </div>

            <!-- Informations supplémentaires -->
            @if($product->sku || $product->pv_value || $product->bv_value)
                <div class="mt-3 sm:mt-4 grid grid-cols-2 gap-2 text-xs sm:text-sm text-[var(--text-secondary)]">
                    @if($product->sku)
                        <div>
                            <span class="font-medium">SKU:</span> {{ $product->sku }}
                        </div>
                    @endif
                    @if($product->pv_value)
                        <div>
                            <span class="font-medium">PV:</span> {{ $product->pv_value }}
                        </div>
                    @endif
                    @if($product->bv_value)
                        <div>
                            <span class="font-medium">BV:</span> {{ $product->bv_value }}
                        </div>
                    @endif
                </div>
            @endif

            @if($product->stock > 0)
                <div class="shop-buy-panel mt-4 sm:mt-6">
                    <p class="shop-buy-panel__label">Quantité</p>
                    <form action="{{ route('cart.add') }}" method="POST" id="productAddForm" class="shop-buy-panel__form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="shop-qty-row">
                            <div class="shop-qty-stepper">
                                <button type="button" class="shop-qty-btn" onclick="decrementQty()" aria-label="Diminuer">−</button>
                                <input type="number" id="qty" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                                       class="shop-qty-input" aria-label="Quantité">
                                <button type="button" class="shop-qty-btn" onclick="incrementQty()" aria-label="Augmenter">+</button>
                            </div>
                            <span class="shop-qty-hint">{{ $product->stock }} disponible(s)</span>
                        </div>
                        <div class="shop-cta-row">
                            <button type="submit" class="shop-cta shop-cta--primary btn-shop-add" id="productAddBtn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Ajouter au panier
                            </button>
                            <a href="{{ route('cart.index') }}" class="shop-cta shop-cta--secondary">
                                Voir le panier
                            </a>
                        </div>
                    </form>
                </div>
            @else
                <button type="button" class="shop-cta shop-cta--disabled w-full mt-4 sm:mt-6" disabled>
                    Rupture de stock
                </button>
            @endif

            <div class="shop-social-bar mt-4 sm:mt-5" role="group" aria-label="Interactions">
                @auth
                    <button type="button"
                            onclick="toggleWishlist({{ $product->id }})"
                            class="shop-social-btn shop-social-btn--like {{ $isInWishlist ? 'is-active' : '' }}"
                            id="wishlistBtn">
                        <svg class="shop-social-btn__icon" fill="{{ $isInWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span id="wishlistText">{{ $isInWishlist ? 'Aimé' : 'J\'aime' }}</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="shop-social-btn shop-social-btn--like">
                        <svg class="shop-social-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span>J'aime</span>
                    </a>
                @endauth
                <button type="button"
                        class="shop-social-btn shop-social-btn--share"
                        data-shop-share-url="{{ url()->current() }}"
                        data-shop-share-title="{{ $product->name }}">
                    <svg class="shop-social-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    <span>Partager</span>
                </button>
                <button type="button" class="shop-social-btn" onclick="copyLink()">
                    <svg class="shop-social-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span>Copier</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="member-shop-related member-shop-animate member-shop-animate--3">
            <h2 class="text-base sm:text-xl font-bold text-[var(--text-primary)] mb-3 sm:mb-4">Produits similaires</h2>
            <div class="related-grid product-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
                @foreach($relatedProducts as $related)
                    <div class="product-card member-shop-card">
                        <a href="{{ route('products.show', $related->slug) }}" class="block">
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
                            @if($related->pv_value)
                                <span class="pv-badge text-[8px] sm:text-[10px]">{{ $related->pv_value }} PV</span>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@if($product->stock > 0)
    <div class="shop-sticky-bar md:hidden" aria-hidden="false">
        <div class="shop-sticky-bar__meta">
            <span class="shop-sticky-bar__price">${{ number_format($product->price, 2) }}</span>
            <span class="shop-sticky-bar__stock text-green-600">En stock</span>
        </div>
        <button type="submit" form="productAddForm" class="shop-sticky-bar__cta">
            Ajouter au panier
        </button>
    </div>
@endif

@push('scripts')
<script>
// ============================================================
//  QUANTITY CONTROLS
// ============================================================
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

document.addEventListener('DOMContentLoaded', function() {
    var qtyInput = document.getElementById('qty');
    var form = qtyInput ? qtyInput.closest('form') : null;
    
    if (qtyInput) {
        qtyInput.addEventListener('change', function() {
            var val = parseInt(this.value);
            var min = parseInt(this.min);
            var max = parseInt(this.max);
            if (isNaN(val) || val < min) this.value = min;
            if (val > max) this.value = max;
        });
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            var qty = parseInt(qtyInput.value, 10);
            var max = parseInt(qtyInput.max, 10);
            if (qty > max) {
                e.preventDefault();
                showToast('Quantité maximum : ' + max, 'warning');
                return;
            }
            e.preventDefault();
            submitProductAddForm(form);
        });
    }
});

function submitProductAddForm(form) {
    var btn = document.getElementById('productAddBtn');
    var sticky = document.querySelector('.shop-sticky-bar__cta');
    var original = btn ? btn.innerHTML : '';
    if (btn) {
        btn.classList.add('is-loading');
        btn.disabled = true;
    }
    if (sticky) {
        sticky.disabled = true;
        sticky.textContent = '…';
    }

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (btn) {
            btn.classList.remove('is-loading');
            btn.disabled = false;
            btn.innerHTML = original;
        }
        if (sticky) {
            sticky.disabled = false;
            sticky.textContent = 'Ajouter au panier';
        }
        if (data.success) {
            if (btn) {
                btn.classList.add('is-success');
                setTimeout(function() { btn.classList.remove('is-success'); }, 600);
            }
            showToast(data.message || 'Ajouté au panier', 'success');
        } else {
            showToast(data.message || 'Erreur', 'error');
        }
    })
    .catch(function() {
        if (btn) {
            btn.classList.remove('is-loading');
            btn.disabled = false;
            btn.innerHTML = original;
        }
        if (sticky) {
            sticky.disabled = false;
            sticky.textContent = 'Ajouter au panier';
        }
        showToast('Erreur lors de l\'ajout au panier', 'error');
    });
}

// ============================================================
//  COPY LINK
// ============================================================
function copyLink() {
    var link = window.location.href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(function() {
            showToast('Lien copié !');
        });
    } else {
        var input = document.createElement('input');
        input.value = link;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showToast('Lien copié !');
    }
}

// ============================================================
//  TOAST NOTIFICATION
// ============================================================
window.showToast = function showToast(message, type) {
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
        toast.style.background = '#5ab638';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(function() { toast.remove(); }, 500);
    }, 3000);
}

// ============================================================
//  WISHLIST TOGGLE
// ============================================================
function toggleWishlist(productId) {
    const btn = document.getElementById('wishlistBtn');
    const floatBtn = document.getElementById('wishlistBtnFloat');
    const text = document.getElementById('wishlistText');
    const icons = [];
    if (btn) {
        icons.push(btn.querySelector('.shop-social-btn__icon'));
    }
    if (floatBtn) {
        icons.push(floatBtn.querySelector('.shop-gallery-fav__icon'));
    }
    icons.forEach(function(icon) {
        if (icon) {
            icon.classList.add('shop-like-pop');
            setTimeout(function() { icon.classList.remove('shop-like-pop'); }, 450);
        }
    });

    fetch(`/wishlist/toggle/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var active = !!data.added;
            if (btn) {
                btn.classList.toggle('is-active', active);
            }
            if (floatBtn) {
                floatBtn.classList.toggle('is-active', active);
            }
            icons.forEach(function(icon) {
                if (icon) {
                    icon.setAttribute('fill', active ? 'currentColor' : 'none');
                }
            });
            if (text) {
                text.textContent = active ? 'Aimé' : 'J\'aime';
            }
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(' Une erreur est survenue', 'error');
    });
}
</script>
@endpush
@endsection