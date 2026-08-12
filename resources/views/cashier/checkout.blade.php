{{-- resources/views/cashier/checkout.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .checkout-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .checkout-item:last-child {
        border-bottom: none;
    }
    .checkout-item .item-image {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-md);
        overflow: hidden;
        flex-shrink: 0;
        background: var(--bg-secondary);
    }
    .checkout-item .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .checkout-item .item-info {
        flex: 1;
        min-width: 0;
    }
    .checkout-item .item-info h4 {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
        margin: 0;
    }
    .checkout-item .item-info .item-meta {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 0.125rem;
    }
    .checkout-item .item-price {
        font-weight: 700;
        color: var(--primary-500);
        font-size: 0.875rem;
        text-align: right;
        flex-shrink: 0;
    }
    
    .checkout-summary {
        background: var(--bg-secondary);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    .checkout-summary .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }
    .checkout-summary .summary-row.total {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary);
        border-top: 2px solid var(--border-color);
        padding-top: 0.75rem;
        margin-top: 0.5rem;
    }
    .checkout-summary .summary-row.total .amount {
        color: var(--primary-500);
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        font-size: 0.813rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.375rem;
    }
    .form-group .input {
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
    .form-group .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    .form-group .input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .form-group .input-error {
        border-color: #ef4444;
    }
    .form-group .input-error:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
    }
    .form-group .error-text {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    
    /* STYLES POUR LA RECHERCHE DE CLIENT */
    .customer-search-wrapper {
        position: relative;
    }
    .customer-search-wrapper .search-input {
        padding-right: 2.5rem;
    }
    .customer-search-wrapper .clear-search {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-tertiary);
        cursor: pointer;
        display: none;
        padding: 0.25rem;
        border-radius: 50%;
    }
    .customer-search-wrapper .clear-search:hover {
        color: var(--text-primary);
        background: var(--bg-hover);
    }
    .customer-search-wrapper .clear-search.visible {
        display: block;
    }
    
    .customer-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        max-height: 250px;
        overflow-y: auto;
        z-index: 50;
        display: none;
        margin-top: 0.25rem;
        box-shadow: var(--shadow-lg);
    }
    .customer-results.visible {
        display: block;
    }
    .customer-results .result-item {
        padding: 0.625rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .customer-results .result-item:last-child {
        border-bottom: none;
    }
    .customer-results .result-item:hover {
        background: var(--bg-hover);
    }
    .customer-results .result-item .customer-avatar {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: var(--gradient-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
        flex-shrink: 0;
    }
    .customer-results .result-item .customer-info {
        flex: 1;
        min-width: 0;
    }
    .customer-results .result-item .customer-info .name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    .customer-results .result-item .customer-info .details {
        font-size: 0.7rem;
        color: var(--text-secondary);
    }
    .customer-results .result-item .customer-info .details .sponsor-code {
        color: var(--primary-500);
        font-weight: 600;
    }
    .customer-results .result-item .select-badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
        flex-shrink: 0;
    }
    .customer-selected {
        background: rgba(34, 197, 94, 0.05);
        border: 1px solid rgba(34, 197, 94, 0.2);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        display: none;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }
    .customer-selected.visible {
        display: flex;
    }
    .customer-selected .customer-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: var(--gradient-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        flex-shrink: 0;
    }
    .customer-selected .customer-info .name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    .customer-selected .customer-info .details {
        font-size: 0.7rem;
        color: var(--text-secondary);
    }
    .customer-selected .change-btn {
        margin-left: auto;
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
    }
    .customer-selected .change-btn:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }
    
    .sponsor-info {
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.15);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        display: none;
        margin-top: 0.5rem;
    }
    .sponsor-info.visible {
        display: block;
    }
    .sponsor-info.valid {
        background: rgba(34, 197, 94, 0.08);
        border-color: rgba(34, 197, 94, 0.2);
    }
    .sponsor-info.invalid {
        background: rgba(239, 68, 68, 0.08);
        border-color: rgba(239, 68, 68, 0.2);
    }
    .sponsor-info .sponsor-name {
        font-weight: 600;
        color: var(--text-primary);
    }
    .sponsor-info .sponsor-detail {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .sponsor-info .sponsor-rank {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 600;
        background: rgba(90, 182, 56, 0.12);
        color: var(--primary-500);
    }
    
    .commission-info {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.15);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
    }
    .commission-info .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
    }
    .commission-info .value {
        font-size: 0.875rem;
        font-weight: 700;
        color: #d97706;
    }
    .commission-info .hint {
        font-size: 0.65rem;
        color: var(--text-tertiary);
        margin-top: 0.25rem;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
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
        transform: translateY(-2px);
    }
    .btn-success {
        background: var(--gradient-success);
        color: white;
        box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.4);
    }
    .btn-success:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }
    .btn-block {
        width: 100%;
        justify-content: center;
    }
    
    .badge-pos {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-mlm {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
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
    .pv-badge-bv {
        background: rgba(139,92,246,0.12);
        color: #8b5cf6;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    
    @media (max-width: 640px) {
        .checkout-item .item-image {
            width: 48px;
            height: 48px;
        }
        .checkout-item .item-info h4 {
            font-size: 0.813rem;
        }
        .checkout-summary {
            padding: 1rem;
        }
        .card {
            padding: 0.875rem;
        }
        .customer-selected {
            flex-wrap: wrap;
        }
        .customer-selected .change-btn {
            margin-left: 0;
            width: 100%;
            text-align: center;
        }
        .customer-results .result-item {
            padding: 0.5rem 0.75rem;
        }
    }
</style>
@endpush

@section('title', 'Passer la commande')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
            <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            Passer la commande
        </h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Vérifiez votre panier et complétez les informations du client
        </p>
    </div>

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($cartItems) && $cartItems->isEmpty())
        <!-- Panier vide -->
        <div class="card text-center py-8 sm:py-12 animate-fadeInUp">
            <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">Votre panier est vide</h3>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1">
                Ajoutez des produits avant de passer commande.
            </p>
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary mt-4 inline-flex">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Retour à la boutique
            </a>
        </div>
    @else
        <!-- Panier avec articles -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            
            <!-- Formulaire -->
            <div class="lg:col-span-2 space-y-3 sm:space-y-4">
                
                <!-- Liste des articles -->
                <div class="card animate-fadeInUp delay-1">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">
                            Votre panier
                            <span class="text-sm font-normal text-[var(--text-secondary)]">
                                ({{ isset($cartItems) ? $cartItems->count() : 0 }} article(s))
                            </span>
                        </h3>
                        <a href="{{ route('cashier.pos') }}" class="text-sm text-primary-500 hover:text-primary-600 transition">
                            <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Modifier
                        </a>
                    </div>
                    
                    <div id="checkoutItems">
                        @if(isset($cartItems) && $cartItems->isNotEmpty())
                            @foreach($cartItems as $item)
                                <div class="checkout-item">
                                    <div class="item-image">
                                        @if(isset($item['image']) && $item['image'])
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                                        @else
                                            <svg class="w-full h-full p-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="item-info">
                                        <h4>{{ $item['name'] }}</h4>
                                        <div class="item-meta">
                                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ isset($item['source']) && $item['source'] === 'pos' ? 'badge-pos' : 'badge-mlm' }}">
                                                {{ $item['source_label'] ?? ($item['source'] ?? 'POS') }}
                                            </span>
                                            @if(isset($item['pv_value']) && $item['pv_value'] > 0)
                                                <span class="pv-badge ml-1">{{ $item['pv_value'] }} PV</span>
                                            @endif
                                            @if(isset($item['bv_value']) && $item['bv_value'] > 0)
                                                <span class="pv-badge pv-badge-bv ml-1">{{ $item['bv_value'] }} BV</span>
                                            @endif
                                            <span class="ml-2 text-[var(--text-secondary)]">Qté: {{ $item['quantity'] ?? 1 }}</span>
                                        </div>
                                    </div>
                                    <div class="item-price">
                                        ${{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Informations du client -->
                <div class="card animate-fadeInUp delay-2">
                    <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-primary-500/10 flex items-center justify-center text-primary-500 flex-shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Client</h3>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Informations du client pour la commande</p>
                        </div>
                    </div>

                    <form action="{{ route('cashier.checkout.order') }}" method="POST" id="checkoutOrderForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            
                            <!-- RECHERCHE DE CLIENT -->
                            <div class="form-group md:col-span-2">
                                <label for="customer_search">Rechercher un client existant</label>
                                <div class="customer-search-wrapper">
                                    <input type="text" 
                                           id="customer_search" 
                                           class="input search-input" 
                                           placeholder="Rechercher par nom, téléphone ou email..."
                                           autocomplete="off">
                                    <button type="button" id="clearCustomerSearch" class="clear-search" aria-label="Effacer la recherche">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <div id="customerResults" class="customer-results"></div>
                                </div>
                                
                                <!-- Client sélectionné -->
                                <div id="customerSelected" class="customer-selected">
                                    <div id="selectedAvatar" class="customer-avatar">JD</div>
                                    <div class="customer-info">
                                        <div id="selectedName" class="name">Jean Dupont</div>
                                        <div id="selectedDetails" class="details">
                                            <span id="selectedPhone">+225 07 00 00 00 00</span>
                                            <span class="sponsor-code" id="selectedSponsorCode">SALXXXXXX</span>
                                        </div>
                                    </div>
                                    <button type="button" id="changeCustomerBtn" class="change-btn">Changer</button>
                                </div>
                            </div>

                            <!-- Champs cachés pour les données du client sélectionné -->
                            <input type="hidden" id="selectedCustomerId" name="customer_id" value="{{ old('customer_id') }}">
                            <input type="hidden" id="selectedCustomerName" name="customer_name" value="{{ old('customer_name') }}">
                            <input type="hidden" id="selectedCustomerPhone" name="customer_phone" value="{{ old('customer_phone') }}">
                            <input type="hidden" id="selectedCustomerEmail" name="customer_email" value="{{ old('customer_email') }}">
                            <input type="hidden" id="selectedCustomerAddress" name="customer_address" value="{{ old('customer_address') }}">
                            <input type="hidden" id="selectedCustomerCity" name="customer_city" value="{{ old('customer_city') }}">
                            <input type="hidden" id="selectedCustomerCountry" name="customer_country" value="{{ old('customer_country') }}">
                            <input type="hidden" id="selectedCustomerParrainId" name="customer_parrain_id" value="{{ old('customer_parrain_id') }}">

                            <!-- Informations manuelles (si nouveau client) -->
                            <div id="manualCustomerFields" style="{{ old('customer_id') ? 'display:none;' : '' }}">
                                <div class="form-group">
                                    <label for="name">Nom complet <span class="text-red-500">*</span></label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           class="input @error('name') input-error @enderror" 
                                           placeholder="Nom du client"
                                           value="{{ old('name') }}">
                                    @error('name')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">Téléphone <span class="text-red-500">*</span></label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           class="input @error('phone') input-error @enderror" 
                                           placeholder="Numéro de téléphone"
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="input @error('email') input-error @enderror" 
                                           placeholder="Email du client"
                                           value="{{ old('email') }}">
                                    @error('email')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="address">Adresse</label>
                                    <input type="text" 
                                           id="address" 
                                           name="address" 
                                           class="input @error('address') input-error @enderror" 
                                           placeholder="Adresse du client"
                                           value="{{ old('address') }}">
                                    @error('address')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="city">Ville</label>
                                    <input type="text" 
                                           id="city" 
                                           name="city" 
                                           class="input @error('city') input-error @enderror" 
                                           placeholder="Ville"
                                           value="{{ old('city') }}">
                                    @error('city')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="country">Pays</label>
                                    <input type="text" 
                                           id="country" 
                                           name="country" 
                                           class="input @error('country') input-error @enderror" 
                                           placeholder="Pays"
                                           value="{{ old('country') }}">
                                    @error('country')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group md:col-span-2">
                                <label for="sponsor_code">Code parrain <span class="text-red-500">*</span></label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           id="sponsor_code" 
                                           name="sponsor_code" 
                                           class="input flex-1 @error('sponsor_code') input-error @enderror" 
                                           placeholder="Ex: SALXXXXXX"
                                           value="{{ old('sponsor_code') }}"
                                           required>
                                    <button type="button" onclick="checkSponsor()" class="btn btn-outline btn-sm flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        Vérifier
                                    </button>
                                </div>
                                <div id="sponsorInfo" class="sponsor-info"></div>
                                @error('sponsor_code')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group md:col-span-2">
                                <label for="commission_amount">Commission CASH POS <span class="text-[10px] text-[var(--text-secondary)]">(Optionnel: 5$ - 15$)</span></label>
                                <div class="flex items-center gap-3">
                                    <input type="number" 
                                           id="commission_amount" 
                                           name="commission_amount" 
                                           class="input @error('commission_amount') input-error @enderror w-full md:w-48" 
                                           placeholder="0"
                                           min="0"
                                           max="15"
                                           step="0.5"
                                           value="{{ old('commission_amount', 0) }}">
                                    <span class="text-sm text-[var(--text-secondary)]">USD</span>
                                </div>
                                <p class="text-[10px] text-[var(--text-secondary)] mt-1">Commission payée en espèces au parrain (entre 5$ et 15$)</p>
                                @error('commission_amount')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                            <a href="{{ route('cashier.pos') }}" class="btn btn-outline w-full sm:w-auto text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Retour
                            </a>
                            <button type="submit" class="btn btn-success w-full sm:w-auto text-sm" id="submitOrderBtn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Valider la commande
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Résumé -->
            <div class="lg:col-span-1">
                <div class="checkout-summary animate-fadeInUp delay-2 sticky top-20">
                    <h3 class="font-bold text-[var(--text-primary)] text-base sm:text-lg mb-3">Résumé</h3>
                    
                    <div class="summary-row">
                        <span>Sous-total</span>
                        <span>${{ number_format($total ?? 0, 2) }}</span>
                    </div>
                    
                    @if(isset($totalPv) && $totalPv > 0)
                        <div class="summary-row">
                            <span>PV total</span>
                            <span class="text-primary-500 font-semibold">{{ number_format($totalPv, 0) }} PV</span>
                        </div>
                    @endif
                    
                    @if(isset($totalBv) && $totalBv > 0)
                        <div class="summary-row">
                            <span>BV total</span>
                            <span class="text-purple-500 font-semibold">{{ number_format($totalBv, 0) }} BV</span>
                        </div>
                    @endif
                    
                    <div class="summary-row">
                        <span>Articles</span>
                        <span>{{ isset($cartItems) ? $cartItems->count() : 0 }}</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total à payer</span>
                        <span class="amount">${{ number_format($total ?? 0, 2) }}</span>
                    </div>
                    
                    <div class="mt-3 text-xs text-[var(--text-secondary)] text-center border-t border-[var(--border-color)] pt-3">
                        <p>Paiement en espèces</p>
                        <p class="mt-0.5">Commission CASH POS payée directement au parrain</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
// ============================================================
//  RECHERCHE DE CLIENT
// ============================================================
const customerSearch = document.getElementById('customer_search');
const customerResults = document.getElementById('customerResults');
const clearSearchBtn = document.getElementById('clearCustomerSearch');
const customerSelected = document.getElementById('customerSelected');
const selectedAvatar = document.getElementById('selectedAvatar');
const selectedName = document.getElementById('selectedName');
const selectedPhone = document.getElementById('selectedPhone');
const selectedSponsorCode = document.getElementById('selectedSponsorCode');
const changeCustomerBtn = document.getElementById('changeCustomerBtn');
const manualFields = document.getElementById('manualCustomerFields');
let searchTimeout = null;

// Champs cachés du client sélectionné
const selectedId = document.getElementById('selectedCustomerId');
const selectedNameHidden = document.getElementById('selectedCustomerName');
const selectedPhoneHidden = document.getElementById('selectedCustomerPhone');
const selectedEmailHidden = document.getElementById('selectedCustomerEmail');
const selectedAddressHidden = document.getElementById('selectedCustomerAddress');
const selectedCityHidden = document.getElementById('selectedCustomerCity');
const selectedCountryHidden = document.getElementById('selectedCustomerCountry');
const selectedParrainIdHidden = document.getElementById('selectedCustomerParrainId');

// Champs manuels
const nameInput = document.getElementById('name');
const phoneInput = document.getElementById('phone');
const emailInput = document.getElementById('email');
const addressInput = document.getElementById('address');
const cityInput = document.getElementById('city');
const countryInput = document.getElementById('country');

// Recherche de client
customerSearch.addEventListener('input', function() {
    const query = this.value.trim();
    
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        customerResults.classList.remove('visible');
        customerResults.innerHTML = '';
        clearSearchBtn.classList.remove('visible');
        return;
    }
    
    clearSearchBtn.classList.add('visible');
    
    searchTimeout = setTimeout(function() {
        fetch(`{{ route('cashier.customers.search') }}?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            customerResults.innerHTML = '';
            
            if (data.length === 0) {
                customerResults.innerHTML = `
                    <div class="result-item" style="cursor:default; justify-content:center; color:var(--text-tertiary);">
                        Aucun client trouvé
                    </div>
                `;
                customerResults.classList.add('visible');
                return;
            }
            
            data.forEach(function(customer) {
                const div = document.createElement('div');
                div.className = 'result-item';
                div.innerHTML = `
                    <div class="customer-avatar">${customer.name.charAt(0).toUpperCase()}</div>
                    <div class="customer-info">
                        <div class="name">${customer.name}</div>
                        <div class="details">
                            ${customer.phone ? customer.phone + ' • ' : ''}
                            ${customer.email || 'N/A'}
                            ${customer.sponsor_id ? `<span class="sponsor-code"> • Code: ${customer.sponsor_id}</span>` : ''}
                            ${customer.parrain ? `<span class="sponsor-code"> • Parrain: ${customer.parrain.name}</span>` : ''}
                        </div>
                    </div>
                    <span class="select-badge">Sélectionner</span>
                `;
                div.addEventListener('click', function() {
                    selectCustomer(customer);
                });
                customerResults.appendChild(div);
            });
            
            customerResults.classList.add('visible');
        })
        .catch(function() {
            customerResults.innerHTML = `
                <div class="result-item" style="cursor:default; justify-content:center; color:var(--text-tertiary);">
                    Erreur lors de la recherche
                </div>
            `;
            customerResults.classList.add('visible');
        });
    }, 300);
});

// Effacer la recherche
clearSearchBtn.addEventListener('click', function() {
    customerSearch.value = '';
    customerResults.classList.remove('visible');
    customerResults.innerHTML = '';
    clearSearchBtn.classList.remove('visible');
    customerSearch.focus();
});

// Sélectionner un client
function selectCustomer(customer) {
    // Remplir les champs cachés
    selectedId.value = customer.id;
    selectedNameHidden.value = customer.name;
    selectedPhoneHidden.value = customer.phone || '';
    selectedEmailHidden.value = customer.email || '';
    selectedAddressHidden.value = customer.address || '';
    selectedCityHidden.value = customer.city || '';
    selectedCountryHidden.value = customer.country || '';
    selectedParrainIdHidden.value = customer.parrain_id || '';
    
    // Afficher le client sélectionné
    selectedAvatar.textContent = customer.name.charAt(0).toUpperCase();
    selectedName.textContent = customer.name;
    selectedPhone.textContent = customer.phone || 'Téléphone non renseigné';
    selectedSponsorCode.textContent = customer.sponsor_id ? 'Code: ' + customer.sponsor_id : '';
    
    customerSelected.classList.add('visible');
    manualFields.style.display = 'none';
    customerResults.classList.remove('visible');
    customerResults.innerHTML = '';
    customerSearch.value = '';
    clearSearchBtn.classList.remove('visible');
    
    // Nettoyer les champs manuels
    nameInput.value = '';
    phoneInput.value = '';
    emailInput.value = '';
    addressInput.value = '';
    cityInput.value = '';
    countryInput.value = '';
    
    // Afficher un toast de confirmation
    if (typeof window.showToast === 'function') {
        window.showToast('Client sélectionné : ' + customer.name, 'success');
    }
}

// Changer de client
changeCustomerBtn.addEventListener('click', function() {
    customerSelected.classList.remove('visible');
    manualFields.style.display = 'block';
    selectedId.value = '';
    selectedNameHidden.value = '';
    selectedPhoneHidden.value = '';
    selectedEmailHidden.value = '';
    selectedAddressHidden.value = '';
    selectedCityHidden.value = '';
    selectedCountryHidden.value = '';
    selectedParrainIdHidden.value = '';
    customerSearch.focus();
});

// Fermer les résultats en cliquant ailleurs
document.addEventListener('click', function(e) {
    if (!e.target.closest('.customer-search-wrapper') && !e.target.closest('#customerSelected')) {
        customerResults.classList.remove('visible');
    }
});

// ============================================================
//  VÉRIFICATION DU CODE PARRAIN
// ============================================================
function checkSponsor() {
    const code = document.getElementById('sponsor_code').value.trim();
    const info = document.getElementById('sponsorInfo');
    
    if (!code) {
        info.className = 'sponsor-info';
        info.innerHTML = '';
        return;
    }

    info.className = 'sponsor-info visible';
    info.innerHTML = '<span class="text-[var(--text-secondary)]">Vérification...</span>';

    fetch(`{{ route('cashier.sponsor.check') }}?code=${encodeURIComponent(code)}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Erreur serveur: ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        info.classList.add('visible');
        if (data.valid) {
            const rankName = data.sponsor.rank || 'Membre';
            
            info.className = 'sponsor-info visible valid';
            info.innerHTML = `
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <div class="sponsor-name">${data.sponsor.name}</div>
                        <div class="sponsor-detail">
                            Email: ${data.sponsor.email || 'N/A'} | Téléphone: ${data.sponsor.phone || 'N/A'}
                            <span class="sponsor-rank ml-2">${rankName}</span>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('sponsor_code').classList.remove('input-error');
        } else {
            info.className = 'sponsor-info visible invalid';
            info.innerHTML = `
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <div>
                        <span class="text-red-500">${data.message || 'Code parrain invalide'}</span>
                    </div>
                </div>
            `;
            document.getElementById('sponsor_code').classList.add('input-error');
        }
    })
    .catch(function(error) {
        console.error('Erreur:', error);
        info.className = 'sponsor-info visible invalid';
        info.innerHTML = `
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <div>
                    <span class="text-red-500">Erreur lors de la vérification</span>
                </div>
            </div>
        `;
    });
}

// ============================================================
//  VALIDATION DU FORMULAIRE - CORRIGÉE
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    // Vérification automatique du code parrain
    const sponsorInput = document.getElementById('sponsor_code');
    if (sponsorInput) {
        let timeout = null;
        sponsorInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                checkSponsor();
            }, 500);
        });
        
        sponsorInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                checkSponsor();
            }
        });
    }

    // ✅ Validation du formulaire - CORRIGÉE
    const form = document.getElementById('checkoutOrderForm');
    const submitBtn = document.getElementById('submitOrderBtn');

    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = '';
            
            // ✅ Cas 1: Client sélectionné via la recherche
            const isCustomerSelected = selectedId.value !== '';
            
            // ✅ Cas 2: Nouveau client - champs manuels
            const name = document.getElementById('name')?.value.trim();
            const phone = document.getElementById('phone')?.value.trim();
            
            // ✅ Vérifier le code parrain
            const sponsorCode = document.getElementById('sponsor_code')?.value.trim();
            
            // ✅ Si client sélectionné, pas besoin de vérifier name et phone
            if (!isCustomerSelected) {
                // Client non sélectionné → vérifier les champs manuels
                if (!name) {
                    isValid = false;
                    errorMessage = 'Veuillez entrer le nom du client';
                    document.getElementById('name')?.focus();
                } else if (!phone) {
                    isValid = false;
                    errorMessage = 'Veuillez entrer le téléphone du client';
                    document.getElementById('phone')?.focus();
                }
            }
            
            // ✅ Vérifier le code parrain (toujours requis)
            if (isValid && !sponsorCode) {
                isValid = false;
                errorMessage = 'Veuillez entrer un code parrain';
                document.getElementById('sponsor_code')?.focus();
            }
            
            // ✅ Si une erreur, empêcher la soumission
            if (!isValid) {
                e.preventDefault();
                if (typeof window.showToast === 'function') {
                    window.showToast('❌ ' + errorMessage, 'error');
                } else {
                    alert(errorMessage);
                }
                return false;
            }
            
            // ✅ Désactiver le bouton pour éviter les doubles soumissions
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Traitement...
                `;
                submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
            
            return true;
        });
    }

    // VIDER LE PANIER SI COMMANDE VALIDÉE
    @if(session('clear_cart'))
        if (typeof localStorage !== 'undefined') {
            localStorage.removeItem('pos_cart');
            console.log('Panier localstorage vidé');
        }
        
        if (typeof window.loadCart === 'function') {
            window.loadCart();
        }
        
        if (typeof window.showToast === 'function') {
            window.showToast('Commande validée avec succès !', 'success');
        }
    @endif
});
</script>
@endpush
@endsection