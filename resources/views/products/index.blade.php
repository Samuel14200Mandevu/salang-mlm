@extends('layouts.app')

@section('title', 'Produits')

@section('content')
<div class="member-shop member-shop--catalog space-y-3 sm:space-y-4">
    @include('products.partials.shop-catalog-toolbar')

    <div id="searchResult" class="member-shop-results hidden">
        Résultats : <span id="resultCount" class="font-semibold text-primary-500">0</span> produit(s)
    </div>

    <div id="productsContainer"
         class="member-shop-animate member-shop-animate--2"
         data-catalog-url="{{ route('products.index') }}">
        @include('products.partials.shop-catalog-results')
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

function bumpCartBadge() {
    var btn = document.querySelector('.member-shop-cart-btn');
    if (!btn) return;
    btn.classList.remove('is-bump');
    void btn.offsetWidth;
    btn.classList.add('is-bump');
    setTimeout(function() { btn.classList.remove('is-bump'); }, 500);
}

var lastCartCount = null;

function updateCartCount() {
    fetch('{{ route("cart.count") }}')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var cartCount = document.getElementById('cartCount');
            var count = data.count || 0;
            if (cartCount) {
                if (count > 0) {
                    cartCount.textContent = count > 99 ? '99+' : count;
                    cartCount.classList.remove('hidden');
                    if (lastCartCount !== null && count > lastCartCount) {
                        bumpCartBadge();
                    }
                } else {
                    cartCount.classList.add('hidden');
                }
            }
            lastCartCount = count;
        })
        .catch(function(error) {
            console.log('Error updating cart count:', error);
        });
}

function addToCart(event, form, productName) {
    event.preventDefault();

    var submitBtn = form.querySelector('button[type="submit"]');
    if (!submitBtn) return;
    var originalText = submitBtn.innerHTML;
    submitBtn.classList.add('is-loading');
    submitBtn.innerHTML = '<span class="animate-spin inline-block">…</span>';
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
        submitBtn.classList.remove('is-loading');
        submitBtn.disabled = false;

        if (data.success) {
            updateCartCount();
            submitBtn.classList.add('is-success');
            var glyph = submitBtn.querySelector('.shop-action-btn__glyph');
            var label = submitBtn.querySelector('.shop-action-btn__label');
            if (glyph && label) {
                glyph.innerHTML = '<svg class="shop-action-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
                label.textContent = 'Ajouté';
            } else {
                submitBtn.innerHTML = '✓';
            }
            setTimeout(function() {
                submitBtn.classList.remove('is-success');
                submitBtn.innerHTML = originalText;
            }, 1200);
            showToast(data.message, 'success');
        } else {
            submitBtn.innerHTML = originalText;
            showToast(data.message, 'error');
        }
    })
    .catch(function(error) {
        submitBtn.classList.remove('is-loading');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        showToast('Erreur lors de l\'ajout au panier', 'error');
        console.log('Error:', error);
    });
}

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
</script>
@endpush
@endsection
