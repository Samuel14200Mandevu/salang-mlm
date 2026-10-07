@extends('layouts.app')

@section('title', 'Panier')



@section('content')
<div class="cart-page space-y-4 sm:space-y-6">

    <div class="member-page-intro cart-page-intro-desktop animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Panier</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Vérifiez vos articles avant de passer la commande</p>
    </div>

    @include('cart.partials.catalog-head', ['cart' => $cart ?? [], 'walletBalance' => $walletBalance ?? 0])

    <div class="balance-card cart-balance-desktop animate-fadeInUp delay-1">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Solde de votre portefeuille</p>
                <p class="text-xl sm:text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                    ${{ number_format($walletBalance ?? 0, 2) }}
                </p>
            </div>
            <a href="{{ route('wallet.index') }}" class="btn btn-primary btn-sm">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Gérer mon portefeuille
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="cart-page-flash p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="cart-page-flash p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($cart))
        <div class="card cart-empty-card text-center py-8 sm:py-12 animate-fadeIn">
            <svg class="w-16 h-16 sm:w-24 sm:h-24 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Votre panier est vide</h3>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Découvrez nos produits et abonnements</p>
            <div class="flex flex-wrap justify-center gap-2 sm:gap-3 mt-3 sm:mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary text-sm sm:text-base">Voir les produits</a>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline text-sm sm:text-base">Voir les abonnements</a>
            </div>
        </div>
    @else
        <!-- Contenu du Panier -->
        <div class="cart-grid grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

            <div class="lg:col-span-2 animate-fadeInLeft cart-items-col">
                <div class="cart-items-panel">
                <div class="card">
                    <div class="divide-y divide-[var(--border-color)]">
                        @php $total = 0; @endphp
                        @foreach($cart as $key => $item)
                            @php 
                                $itemTotal = $item['price'] * $item['quantity']; 
                                $total += $itemTotal; 
                            @endphp
                            <div class="cart-item flex items-center gap-3 sm:gap-4">
                                <div class="item-info flex-1 min-w-0">
                                    <h4 class="font-medium text-[var(--text-primary)] text-sm sm:text-base truncate">
                                        {{ $item['name'] }}
                                    </h4>
                                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                                        {{ $item['type'] == 'package' ? 'Abonnement' : 'Produit' }}
                                        <span class="mx-1">•</span>
                                        Qté: {{ $item['quantity'] }}
                                        @if(isset($item['pv_value']) && $item['pv_value'] > 0)
                                            <span class="ml-2 text-green-500">{{ $item['pv_value'] }} PV</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="item-actions text-right flex items-center gap-3 sm:gap-4">
                                    <p class="font-bold text-primary-500 text-sm sm:text-base">
                                        ${{ number_format($itemTotal, 2) }}
                                    </p>
                                    <form action="{{ route('cart.remove', $key) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs sm:text-sm transition font-medium">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                </div>

                <div class="mt-3 sm:mt-4 flex flex-wrap gap-2 sm:gap-3 cart-actions-bar">
                    <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Continuer mes achats
                    </a>
                    <button type="button" onclick="openClearCartModal()" class="btn btn-danger btn-sm">
                        Vider le panier
                    </button>
                </div>
            </div>

            <div class="lg:col-span-1 animate-fadeInRight cart-summary-col">
                <div class="card sticky-top cart-summary-panel">
                    <h3 class="font-bold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Résumé de la commande</h3>
                    
                    @php
                        $shipping = 0;
                        $grandTotal = $total + $shipping;
                        $balance = $walletBalance ?? 0;
                        $canAfford = $balance >= $grandTotal;
                    @endphp

                    <div class="space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--text-secondary)]">Sous-total</span>
                            <span class="font-medium">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--text-secondary)]">Livraison</span>
                            <span class="font-medium text-green-500">Gratuite</span>
                        </div>
                        <div class="border-t border-[var(--border-color)] pt-3 mt-3">
                            <div class="flex justify-between text-base sm:text-lg font-bold">
                                <span>Total</span>
                                <span class="text-primary-500">
                                    ${{ number_format($grandTotal, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Vérification du solde -->
                    <div class="mt-3">
                        @if($canAfford)
                            <form action="{{ route('cart.checkout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-full text-sm sm:text-base py-2 sm:py-2.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Passer la commande
                                </button>
                            </form>
                        @else
                            <button class="btn btn-primary w-full text-sm sm:text-base py-2 sm:py-2.5 cursor-not-allowed opacity-50" disabled>
                                Solde insuffisant
                            </button>
                            <p class="insufficient-balance">
                                Il vous manque ${{ number_format($grandTotal - $balance, 2) }} pour finaliser cette commande
                            </p>
                            <a href="{{ route('wallet.index') }}" class="btn btn-outline w-full mt-2 text-sm sm:text-base py-2 sm:py-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Alimenter mon portefeuille
                            </a>
                        @endif
                    </div>
                    
                    <!-- PV Total -->
                    @php
                        $totalPV = array_sum(array_map(function($item) {
                            return ($item['pv_value'] ?? 0) * ($item['quantity'] ?? 1);
                        }, $cart));
                    @endphp
                    @if($totalPV > 0)
                        <div class="mt-3 pt-3 border-t border-[var(--border-color)] text-center">
                            <p class="text-xs text-[var(--text-secondary)]">
                                Vous gagnerez <span class="font-bold text-green-500">{{ $totalPV }} PV</span> avec cette commande
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- ============================================================ -->
<!-- MODAL DE CONFIRMATION POUR VIDER LE PANIER -->
<!-- ============================================================ -->
<div id="clearCartModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="modal-title">Vider le panier ?</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-warning">vider votre panier</strong> ?
            <br>
            <span class="text-xs text-[var(--text-tertiary)]">Cette action est irréversible.</span>
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeClearCartModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <form id="clearCartForm" action="{{ route('cart.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" id="confirmClearBtn">
                    Vider le panier
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
// MODAL VIDER LE PANIER
// ============================================================
function openClearCartModal() {
    document.getElementById('clearCartModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeClearCartModal() {
    document.getElementById('clearCartModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// FERMER LES MODALS EN CLIQUANT À L'EXTÉRIEUR
// ============================================================
document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// ============================================================
// FERMER LES MODALS AVEC LA TOUCHE ESCAPE
// ============================================================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

// ============================================================
// DÉSACTIVER LE BOUTON APRÈS SOUMISSION
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('clearCartForm');
    if (form) {
        form.addEventListener('submit', function() {
            var btn = document.getElementById('confirmClearBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Suppression...';
            }
        });
    }
});
</script>
@endpush
@endsection