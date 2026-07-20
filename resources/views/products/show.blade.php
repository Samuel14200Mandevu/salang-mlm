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
    .quantity-btn:active {
        transform: scale(0.95);
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
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
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(59,130,246,0.12);
        color: #3b82f6;
    }
    .bv-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(139,92,246,0.12);
        color: #8b5cf6;
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
    .btn-icon {
        width: 2.5rem;
        height: 2.5rem;
        padding: 0;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
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
    .input-sm {
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
    }
    
    .opacity-50 {
        opacity: 0.5;
    }
    .cursor-not-allowed {
        cursor: not-allowed;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .animate-fadeInLeft { animation: fadeInLeft 0.6s ease forwards; }
    .animate-fadeInRight { animation: fadeInRight 0.6s ease forwards; }
    .delay-4 { animation-delay: 0.20s; }
    
    .custom-toast {
        animation: slideUp 0.3s ease forwards;
    }
    
    .product-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        padding: 0.75rem;
        transition: all 0.3s ease;
        text-align: center;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary-500);
    }
    .product-card .aspect-square {
        aspect-ratio: 1 / 1;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-card .aspect-square img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    @media (max-width: 640px) {
        .quantity-btn {
            width: 1.75rem;
            height: 1.75rem;
            font-size: 0.875rem;
        }
        .card {
            padding: 0.875rem;
        }
        .btn {
            font-size: 0.75rem;
            padding: 0.375rem 0.875rem;
        }
        .input {
            font-size: 0.813rem;
            padding: 0.5rem 0.75rem;
        }
        .text-3xl {
            font-size: 1.5rem;
        }
        .pv-badge, .bv-badge {
            font-size: 0.6rem;
            padding: 0.15rem 0.4rem;
        }
    }
    
    @media (max-width: 480px) {
        .card {
            padding: 0.75rem;
        }
        .product-details-grid {
            grid-template-columns: 1fr !important;
        }
        .related-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Breadcrumb -->
    <nav class="text-xs sm:text-sm text-[var(--text-secondary)] animate-fadeInUp">
        <a href="{{ route('products.index') }}" class="hover:text-primary-500 transition">Produits</a>
        <span class="mx-1 sm:mx-2">/</span>
        <span class="text-[var(--text-primary)] font-medium">{{ $product->name }}</span>
    </nav>

    <!-- Product Details -->
    <div class="product-details-grid grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        
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
        <div class="card animate-fadeInRight p-3 sm:p-4 md:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">{{ $product->category ?? 'Général' }}</p>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)] mt-0.5 sm:mt-1">{{ $product->name }}</h1>
                </div>
            </div>

            <!-- ✅ PV ET BV -->
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

            <!-- ✅ INFORMATIONS SUPPLÉMENTAIRES AVEC PV -->
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

            <!-- Actions -->
            <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row gap-2 sm:gap-3">
                @if($product->stock > 0)
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <div class="flex items-center gap-1">
                                <button type="button" class="quantity-btn" onclick="decrementQty()">-</button>
                                <input type="number" id="qty" name="quantity" value="1" min="1" max="{{ $product->stock }}" 
                                       class="input input-sm w-12 sm:w-16 text-center text-sm sm:text-base">
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

            <!-- Share -->
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

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="animate-fadeInUp delay-4">
            <h2 class="text-base sm:text-xl font-bold text-[var(--text-primary)] mb-3 sm:mb-4">Produits similaires</h2>
            <div class="related-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
                @foreach($relatedProducts as $related)
                    <div class="product-card">
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
            var qty = parseInt(qtyInput.value);
            var max = parseInt(qtyInput.max);
            if (qty > max) {
                e.preventDefault();
                showToast('Quantité maximum : ' + max, 'warning');
            }
        });
    }
});

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