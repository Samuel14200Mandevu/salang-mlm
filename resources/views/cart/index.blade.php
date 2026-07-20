@extends('layouts.app')

@push('styles')
<style>
    .cart-item {
        transition: all 0.3s ease;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem 0;
    }
    .cart-item:first-child {
        padding-top: 0;
    }
    .cart-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .cart-item:hover {
        background: var(--bg-hover);
        margin: 0 -0.5rem;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        border-radius: var(--radius-sm);
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
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
    }
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    .btn-cancel {
        background: var(--bg-secondary);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }
    .btn-cancel:hover {
        background: var(--bg-hover);
        transform: translateY(-2px);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .sticky-top {
        position: sticky;
        top: 1.5rem;
    }
    
    .insufficient-balance {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.5rem;
        text-align: center;
    }
    
    .balance-card {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(245, 158, 11, 0.02));
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .balance-card:hover {
        border-color: rgba(245, 158, 11, 0.4);
        transform: translateX(4px);
    }

    /* ===== MODAL DE CONFIRMATION ===== */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        box-shadow: var(--shadow-xl);
        transform: scale(0.9);
        transition: transform 0.3s ease;
        border: 1px solid var(--border-color);
    }
    .modal-overlay.active .modal-box {
        transform: scale(1);
    }
    .modal-icon {
        width: 4rem;
        height: 4rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .modal-icon-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    .modal-icon-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    .modal-title {
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .modal-text {
        text-align: center;
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    .modal-text strong {
        color: var(--text-primary);
    }
    .modal-text .text-danger {
        color: #ef4444;
    }
    .modal-text .text-warning {
        color: #f59e0b;
    }
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .modal-actions .btn {
        min-width: 100px;
        justify-content: center;
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
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .animate-fadeInLeft { animation: fadeInLeft 0.6s ease forwards; }
    .animate-fadeInRight { animation: fadeInRight 0.6s ease forwards; }
    .animate-fadeIn { animation: fadeIn 0.4s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    
    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .cart-item { padding: 0.75rem 0; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .btn-sm { font-size: 0.65rem; padding: 0.25rem 0.5rem; }
        .cart-grid {
            grid-template-columns: 1fr !important;
        }
        .cart-item .item-actions {
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }
        .balance-card { padding: 0.75rem; }
        .modal-box { padding: 1.5rem; }
        .modal-actions { flex-direction: column; }
        .modal-actions .btn { width: 100%; }
        .modal-title { font-size: 1rem; }
        .modal-text { font-size: 0.813rem; }
    }
    
    @media (max-width: 480px) {
        .cart-item {
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }
        .cart-item .item-info {
            text-align: center;
        }
        .cart-item .item-actions {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Panier</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Vérifiez vos articles avant de passer la commande</p>
    </div>

    <!-- Balance Info -->
    <div class="balance-card animate-fadeInUp delay-1">
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
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($cart))
        <!-- Panier Vide -->
        <div class="card text-center py-8 sm:py-12 animate-fadeIn">
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
            
            <!-- Articles -->
            <div class="lg:col-span-2 animate-fadeInLeft">
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
                
                <!-- Actions -->
                <div class="mt-3 sm:mt-4 flex flex-wrap gap-2 sm:gap-3">
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

            <!-- Résumé -->
            <div class="lg:col-span-1 animate-fadeInRight">
                <div class="card sticky-top">
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