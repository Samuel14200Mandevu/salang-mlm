{{-- resources/views/cashier/checkout.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .checkout-page {
        max-width: 72rem;
        margin: 0 auto;
    }
    .checkout-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }
    .checkout-steps span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-secondary);
    }
    .checkout-steps span strong {
        color: var(--primary, #1E5DAD);
        font-variant-numeric: tabular-nums;
    }
    .checkout-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid var(--border-color);
    }
    .checkout-section-head h3 {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .checkout-section-head p {
        margin: 0.15rem 0 0;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .checkout-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: color-mix(in srgb, var(--bg-secondary) 55%, var(--bg-card));
    }
    .checkout-item + .checkout-item {
        margin-top: 0.65rem;
    }
    .checkout-item .item-image {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
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
        font-weight: 650;
        color: var(--text-primary);
        font-size: 0.875rem;
        margin: 0;
        letter-spacing: -0.01em;
    }
    .checkout-item .item-info .item-meta {
        font-size: 0.72rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
    }
    .checkout-item .item-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        gap: 0.45rem;
        flex-shrink: 0;
        min-width: 5.75rem;
    }
    .checkout-item .item-price {
        font-weight: 750;
        color: var(--primary, #1E5DAD);
        font-size: 0.9375rem;
        text-align: right;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.02em;
        line-height: 1.1;
    }
    .checkout-item .portion-toggle {
        display: inline-grid;
        grid-template-columns: 1fr 1fr;
        gap: 2px;
        padding: 2px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .checkout-item .portion-toggle label {
        margin: 0;
        cursor: pointer;
        position: relative;
    }
    .checkout-item .portion-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        inset: 0;
    }
    .checkout-item .portion-toggle span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.65rem;
        padding: 0.28rem 0.45rem;
        border-radius: 6px;
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: var(--text-secondary);
        background: transparent;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        user-select: none;
        white-space: nowrap;
    }
    .checkout-item .portion-toggle label:hover span {
        color: var(--text-primary);
        background: color-mix(in srgb, var(--bg-secondary) 80%, transparent);
    }
    .checkout-item .portion-toggle input:checked + span {
        background: var(--primary, #1E5DAD);
        color: #fff;
        box-shadow: 0 1px 2px rgba(30, 93, 173, 0.28);
    }
    .checkout-item .portion-toggle input:focus-visible + span {
        outline: 2px solid color-mix(in srgb, var(--primary, #1E5DAD) 45%, transparent);
        outline-offset: 1px;
    }
    
    .checkout-summary {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.15rem 1.2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .checkout-summary__title {
        margin: 0 0 0.85rem;
        font-size: 0.8125rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-tertiary);
    }
    .checkout-summary .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.4rem 0;
        font-size: 0.8125rem;
        color: var(--text-secondary);
    }
    .checkout-summary .summary-row.total {
        font-size: 1.05rem;
        font-weight: 750;
        color: var(--text-primary);
        border-top: 1px solid var(--border-color);
        padding-top: 0.85rem;
        margin-top: 0.55rem;
    }
    .checkout-summary .summary-row.total .amount {
        color: var(--primary, #1E5DAD);
        font-variant-numeric: tabular-nums;
    }
    .checkout-summary__note {
        margin-top: 0.85rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
        font-size: 0.7rem;
        line-height: 1.45;
        color: var(--text-tertiary);
        text-align: left;
    }
    .checkout-summary__actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    .form-group label {
        display: block;
        font-size: 0.688rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 0.35rem;
    }
    .form-group .input {
        width: 100%;
        padding: 0.55rem 0.75rem;
        font-size: 0.813rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        outline: none;
    }
    .form-group .input:focus {
        border-color: var(--primary, #1E5DAD);
        background: var(--bg-card);
        box-shadow: 0 0 0 3px rgba(30, 93, 173, 0.12);
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
    .customer-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid var(--border-color);
    }
    .customer-card-head__left {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
        flex: 0 1 auto;
    }
    .customer-card-head h3 {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .customer-card-head p {
        margin: 0.15rem 0 0;
        font-size: 0.75rem;
        color: var(--text-secondary);
        line-height: 1.35;
    }
    .customer-search-tools {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        flex: 1 1 auto;
        min-width: 0;
        max-width: min(100%, 22rem);
    }
    .customer-search-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 999px;
        border: 1px solid var(--border-color);
        background: var(--bg-secondary);
        color: var(--text-secondary);
        cursor: pointer;
        flex-shrink: 0;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .customer-search-toggle:hover {
        border-color: var(--primary-500, var(--primary));
        color: var(--primary-500, var(--primary));
        background: color-mix(in srgb, var(--primary-500, var(--primary)) 8%, var(--bg-card));
    }
    .customer-search-toggle.is-active {
        border-color: var(--primary-500, var(--primary));
        color: var(--primary-500, var(--primary));
        background: color-mix(in srgb, var(--primary-500, var(--primary)) 12%, var(--bg-card));
    }
    .customer-search-panel {
        display: none;
        flex: 1 1 auto;
        min-width: 0;
    }
    .customer-search-panel.is-open {
        display: block;
    }
    .customer-search-wrapper {
        position: relative;
    }
    .customer-search-wrapper .search-input {
        padding-right: 2.5rem;
        width: 100%;
        font-size: 0.8125rem;
    }
    .customer-search-panel .form-label-sr {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
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
        border-radius: 8px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 50;
        display: none;
        margin-top: 0.25rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
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
        border-radius: 8px;
        background: var(--primary, #1E5DAD);
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
        background: color-mix(in srgb, #1C7E4A 6%, var(--bg-card));
        border: 1px solid color-mix(in srgb, #1C7E4A 22%, var(--border-color));
        border-radius: 8px;
        padding: 0.75rem 0.9rem;
        display: none;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.15rem;
    }
    .customer-selected.visible {
        display: flex;
    }
    .customer-selected .customer-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 8px;
        background: var(--primary, #1E5DAD);
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
        background: rgba(30, 93, 173, 0.12);
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
        border-radius: 8px;
        padding: 1.15rem 1.2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .checkout-fields-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.85rem;
    }
    @media (min-width: 768px) {
        .checkout-fields-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .checkout-fields-grid .md\:col-span-2 {
            grid-column: 1 / -1;
        }
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.55rem 1.15rem;
        border-radius: 8px;
        font-weight: 650;
        font-size: 0.8125rem;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .btn-sm {
        padding: 0.4rem 0.85rem;
        font-size: 0.75rem;
    }
    .btn-primary {
        background: var(--primary, #1E5DAD);
        color: #fff;
        border-color: var(--primary, #1E5DAD);
    }
    .btn-primary:hover {
        background: var(--primary-hover, #184f94);
        border-color: var(--primary-hover, #184f94);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border-color: var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary, #1E5DAD);
        color: var(--primary, #1E5DAD);
        background: color-mix(in srgb, var(--primary, #1E5DAD) 6%, transparent);
    }
    .btn-success {
        background: #1C7E4A;
        color: #fff;
        border-color: #1C7E4A;
    }
    .btn-success:hover {
        background: #16653c;
        border-color: #16653c;
    }
    .btn-success:disabled {
        opacity: 0.5;
        cursor: not-allowed;
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
        color: #e8940f;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.35s ease forwards; }
    .delay-1 { animation-delay: 0.04s; }
    .delay-2 { animation-delay: 0.08s; }
    
    @media (max-width: 640px) {
        .customer-card-head {
            flex-wrap: nowrap;
            gap: 0.5rem;
        }
        .customer-card-head__left p {
            display: none;
        }
        .customer-search-tools {
            max-width: none;
            flex: 1 1 auto;
        }
        .checkout-item {
            align-items: flex-start;
        }
        .checkout-item .item-image {
            width: 48px;
            height: 48px;
        }
        .checkout-item .item-info h4 {
            font-size: 0.813rem;
        }
        .checkout-item .item-side {
            min-width: 5.25rem;
            gap: 0.35rem;
            padding-top: 0.1rem;
        }
        .checkout-item .portion-toggle span {
            min-width: 2.35rem;
            padding: 0.25rem 0.35rem;
            font-size: 0.58rem;
        }
        .checkout-item .item-price {
            font-size: 0.875rem;
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
<div class="checkout-page space-y-4 sm:space-y-5">
    
    <div class="animate-fadeInUp">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-[0.08em] text-[var(--text-tertiary)] mb-1">Caisse POS</p>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[var(--text-primary)]">
                    Passer la commande
                </h1>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-1 max-w-xl">
                    Vérifiez le panier, renseignez le client, puis validez le paiement en espèces.
                </p>
            </div>
            @if(isset($cartItems) && $cartItems->isNotEmpty())
                <a href="{{ route('cashier.pos') }}" class="btn btn-outline btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Boutique
                </a>
            @endif
        </div>
        @if(isset($cartItems) && $cartItems->isNotEmpty())
            <div class="checkout-steps">
                <span><strong>{{ $cartItems->count() }}</strong> article{{ $cartItems->count() > 1 ? 's' : '' }}</span>
                <span>Total <strong>${{ number_format($total ?? 0, 2) }}</strong></span>
                @if(($totalPv ?? 0) > 0)
                    <span><strong>{{ number_format($totalPv, 0) }}</strong> PV</span>
                @endif
                <span>Espèces</span>
            </div>
        @endif
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

    @if(!empty($consultationCheckout) && !empty($consultationCheckout['consultation_id']))
        <div class="p-3 sm:p-4 bg-primary-500/10 border border-primary-500/25 rounded-lg text-sm sm:text-base animate-fadeIn">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-[var(--text-primary)]">
                        Vente liée à la consultation #{{ $consultationCheckout['consultation_id'] }}
                    </p>
                    <p class="text-[var(--text-secondary)] mt-0.5">
                        @if($linkedConsultation)
                            Patient : {{ $linkedConsultation->nom_complet }}
                            @if(!empty($consultationCheckout['pv_member_code']))
                                · PV → Code ID {{ $consultationCheckout['pv_member_code'] }}
                                @if(!empty($consultationCheckout['pv_member_name']))
                                    ({{ $consultationCheckout['pv_member_name'] }})
                                @endif
                            @endif
                            —
                            <a href="{{ route('cashier.consultations.show', $linkedConsultation) }}" class="text-primary-500 hover:underline">Retour à la fiche</a>
                        @else
                            Seuls les produits sélectionnés sur la fiche seront enregistrés comme payés.
                        @endif
                    </p>
                </div>
                <form id="cancelConsultationCheckoutForm"
                      action="{{ route('cashier.consultations.cancel-checkout', $consultationCheckout['consultation_id']) }}"
                      method="POST"
                      class="flex-shrink-0">
                    @csrf
                    <button type="button"
                            id="cancelConsultationCheckoutBtn"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-secondary)] hover:text-[var(--ui-stat-danger,#b32a2a)] hover:border-[var(--ui-stat-danger,#b32a2a)] transition"
                            title="Annuler l'encaissement"
                            aria-label="Annuler l'encaissement pour cette fiche">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </form>
            </div>
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
                    <div class="checkout-section-head">
                        <div>
                            <h3>Panier</h3>
                            <p>{{ isset($cartItems) ? $cartItems->count() : 0 }} article{{ (isset($cartItems) && $cartItems->count() > 1) ? 's' : '' }} à encaisser</p>
                        </div>
                        <a href="{{ route('cashier.pos') }}" class="btn btn-outline btn-sm">
                            Modifier
                        </a>
                    </div>
                    
                    <div id="checkoutItems">
                        @if(isset($cartItems) && $cartItems->isNotEmpty())
                            @foreach($cartItems as $index => $item)
                                @php
                                    $itemType = $item['type'] ?? null;
                                    $itemBadge = ($itemType === 'package')
                                        ? 'Package'
                                        : ($item['source_label'] ?? $item['sourceLabel'] ?? 'POS');
                                    $hasHalf = ! empty($item['has_half']);
                                    $portion = ($item['portion'] ?? 'full') === 'half' ? 'half' : 'full';
                                    $lineQty = (int) ($item['quantity'] ?? 1);
                                    $lineTotal = ((float) ($item['price'] ?? 0)) * $lineQty;
                                @endphp
                                <div class="checkout-item"
                                     data-cart-index="{{ $index }}"
                                     data-has-half="{{ $hasHalf ? '1' : '0' }}"
                                     data-full-price="{{ $item['full_price'] ?? $item['price'] ?? 0 }}"
                                     data-half-price="{{ $item['half_price'] ?? '' }}"
                                     data-full-pv="{{ $item['full_pv'] ?? $item['pv_value'] ?? 0 }}"
                                     data-half-pv="{{ $item['half_pv'] ?? '' }}"
                                     data-qty="{{ $lineQty }}">
                                    <div class="item-image">
                                        @if(isset($item['image']) && $item['image'])
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                                        @else
                                            <svg class="w-full h-full p-2 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="item-info">
                                        <h4 class="item-name">{{ $item['name'] }}</h4>
                                        <div class="item-meta">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold badge-pos">
                                                {{ $itemBadge }}
                                            </span>
                                            <span class="pv-badge item-pv @if(!(($item['pv_value'] ?? 0) > 0)) hidden @endif">{{ (int) ($item['pv_value'] ?? 0) }} PV</span>
                                            <span class="text-[var(--text-secondary)]">× {{ $lineQty }}</span>
                                        </div>
                                    </div>
                                    <div class="item-side">
                                        @if($itemType !== 'package' && $hasHalf)
                                            <div class="portion-toggle" role="group" aria-label="Portion {{ $item['name'] }}">
                                                <label>
                                                    <input type="radio"
                                                           class="checkout-portion"
                                                           name="portion_{{ $index }}"
                                                           value="full"
                                                           @checked($portion === 'full')>
                                                    <span>Flacon</span>
                                                </label>
                                                <label>
                                                    <input type="radio"
                                                           class="checkout-portion"
                                                           name="portion_{{ $index }}"
                                                           value="half"
                                                           @checked($portion === 'half')>
                                                    <span>1/2</span>
                                                </label>
                                            </div>
                                        @endif
                                        <div class="item-price">
                                            $<span class="item-line-total">{{ number_format($lineTotal, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Informations du client -->
                <div class="card animate-fadeInUp delay-2">
                    <div class="customer-card-head">
                        <div class="customer-card-head__left">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-primary-500/10 flex items-center justify-center text-primary-500 flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-[18px] sm:h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3>Client</h3>
                                <p>Existant (loupe) ou nouveau — nom & téléphone requis</p>
                            </div>
                        </div>
                        <div class="customer-search-tools">
                            <div id="customerSearchPanel" class="customer-search-panel" aria-hidden="true">
                                <label for="customer_search" class="form-label-sr">Rechercher un client existant</label>
                                <div class="customer-search-wrapper">
                                    <input type="text"
                                           id="customer_search"
                                           class="input search-input"
                                           placeholder="Nom, téléphone ou email…"
                                           autocomplete="off">
                                    <button type="button" id="clearCustomerSearch" class="clear-search" aria-label="Effacer la recherche">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <div id="customerResults" class="customer-results"></div>
                                </div>
                            </div>
                            <button type="button"
                                    id="customerSearchToggle"
                                    class="customer-search-toggle"
                                    aria-controls="customerSearchPanel"
                                    aria-expanded="false"
                                    aria-label="Rechercher un client existant"
                                    title="Rechercher un client">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('cashier.checkout.order') }}" method="POST" id="checkoutOrderForm">
                        @csrf

                        <div class="checkout-fields-grid">

                            <!-- Client sélectionné -->
                            <div class="form-group md:col-span-2">
                                <div id="customerSelected" class="customer-selected">
                                    <div id="selectedAvatar" class="customer-avatar">—</div>
                                    <div class="customer-info">
                                        <div id="selectedName" class="name">Client sélectionné</div>
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
                            <div id="manualCustomerFields" class="md:col-span-2 checkout-fields-grid" style="{{ old('customer_id') ? 'display:none;' : '' }}">
                                <div class="form-group">
                                    <label for="name">Nom complet <span class="text-red-500">*</span></label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           class="input @error('name') input-error @enderror" 
                                           placeholder="Nom du client"
                                           value="{{ old('name', data_get($consultationCheckout, 'patient_name', '')) }}">
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

                            @php
                                $lockedPvCode = !empty($consultationCheckout['pv_member_code'])
                                    ? $consultationCheckout['pv_member_code']
                                    : null;
                            @endphp
                            <div class="form-group md:col-span-2">
                                <label for="sponsor_code">
                                    @if($lockedPvCode)
                                        Code ID (membre PV) <span class="text-red-500">*</span>
                                    @else
                                        Code parrain <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           id="sponsor_code" 
                                           name="sponsor_code" 
                                           class="input flex-1 @error('sponsor_code') input-error @enderror" 
                                           placeholder="{{ $lockedPvCode ? 'Code membre de la consultation' : 'Saisir le code de parrain' }}"
                                           value="{{ old('sponsor_code', $lockedPvCode) }}"
                                           @if($lockedPvCode) readonly @endif
                                           required>
                                    @unless($lockedPvCode)
                                    <button type="button" onclick="checkSponsor()" class="btn btn-outline btn-sm flex-shrink-0">
                                        Vérifier
                                    </button>
                                    @endunless
                                </div>
                                <div id="sponsorInfo" class="sponsor-info">
                                    @if($lockedPvCode && !empty($consultationCheckout['pv_member_name']))
                                        <span class="text-sm text-[var(--ui-stat-success)]">
                                            Membre : <strong>{{ $consultationCheckout['pv_member_name'] }}</strong>
                                            — PV crédités dans ses PV disponibles pour l’allocation
                                        </span>
                                    @endif
                                </div>
                                @error('sponsor_code')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group md:col-span-2">
                                <label for="commission_amount">Commission CASH POS <span class="normal-case tracking-normal font-normal text-[var(--text-tertiary)]">(optionnel · 4$–15$)</span></label>
                                <div class="flex items-center gap-3">
                                    <input type="number" 
                                           id="commission_amount" 
                                           name="commission_amount" 
                                           class="input @error('commission_amount') input-error @enderror w-full md:w-40" 
                                           placeholder="0 ou 4–15"
                                           min="0"
                                           max="15"
                                           step="0.5"
                                           value="{{ old('commission_amount', 0) }}"
                                           title="0 = aucune commission ; sinon entre 4$ et 15$">
                                    <span class="text-xs text-[var(--text-secondary)]">USD</span>
                                </div>
                                @if($lockedPvCode)
                                    <p class="text-[10px] text-[var(--text-tertiary)] mt-1.5">
                                        Payée en espèces au membre Code ID
                                        @if(!empty($consultationCheckout['pv_member_name']))
                                            (<strong class="text-[var(--text-secondary)]">{{ $consultationCheckout['pv_member_name'] }}</strong>)
                                        @endif
                                        — enregistrée sur la vente consultation
                                    </p>
                                @else
                                    <p class="text-[10px] text-[var(--text-tertiary)] mt-1.5">Payée en espèces au parrain</p>
                                @endif
                                @error('commission_amount')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Résumé -->
            <div class="lg:col-span-1">
                <div class="checkout-summary animate-fadeInUp delay-2 sticky top-20">
                    <h3 class="checkout-summary__title">Résumé</h3>
                    
                    <div class="summary-row">
                        <span>Sous-total</span>
                        <span>$<span id="checkoutSubtotal">{{ number_format($total ?? 0, 2) }}</span></span>
                    </div>
                    
                    <div class="summary-row {{ (($totalPv ?? 0) > 0) ? '' : 'hidden' }}" id="checkoutPvRow" @if(!(($totalPv ?? 0) > 0)) style="display:none" @endif>
                        <span>PV total</span>
                        <span class="text-primary-500 font-semibold"><span id="checkoutTotalPv">{{ number_format($totalPv ?? 0, 0) }}</span> PV</span>
                    </div>

                    <div class="summary-row">
                        <span>Articles</span>
                        <span id="checkoutItemCount">{{ isset($cartItems) ? $cartItems->count() : 0 }}</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total à payer</span>
                        <span class="amount">$<span id="checkoutGrandTotal">{{ number_format($total ?? 0, 2) }}</span></span>
                    </div>

                    <div class="checkout-summary__actions">
                        <button type="submit"
                                form="checkoutOrderForm"
                                class="btn btn-success btn-block"
                                id="submitOrderBtn">
                            Valider la commande
                        </button>
                        <a href="{{ route('cashier.pos') }}" class="btn btn-outline btn-block">
                            Retour à la boutique
                        </a>
                    </div>
                    
                    <div class="checkout-summary__note">
                        Paiement en espèces.
                        @if(!empty($consultationCheckout['consultation_id']))
                            Commission CASH POS versée au membre Code ID de la consultation.
                        @else
                            Commission CASH POS versée directement au parrain.
                        @endif
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
const customerSearchToggle = document.getElementById('customerSearchToggle');
const customerSearchPanel = document.getElementById('customerSearchPanel');
let searchTimeout = null;

function setCustomerSearchOpen(open) {
    if (!customerSearchPanel || !customerSearchToggle) return;
    customerSearchPanel.classList.toggle('is-open', open);
    customerSearchPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
    customerSearchToggle.classList.toggle('is-active', open);
    customerSearchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open && customerSearch) {
        setTimeout(() => customerSearch.focus(), 50);
    }
    if (!open && customerResults) {
        customerResults.classList.remove('visible');
        customerResults.innerHTML = '';
    }
}

if (customerSearchToggle && customerSearchPanel) {
    customerSearchToggle.addEventListener('click', function () {
        setCustomerSearchOpen(!customerSearchPanel.classList.contains('is-open'));
    });
}

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
    setCustomerSearchOpen(false);

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
    manualFields.style.display = '';
    setCustomerSearchOpen(true);
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

    fetch(`{{ route('cashier.check-sponsor') }}?code=${encodeURIComponent(code)}`, {
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
//  PORTION COMPLET / 1/2
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const portionUrl = @json(route('cashier.cart.portion'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('#checkoutOrderForm input[name="_token"]')?.value
        || '';

    function money(n) {
        return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function applyTotals(totals) {
        if (!totals) return;
        const sub = document.getElementById('checkoutSubtotal');
        const grand = document.getElementById('checkoutGrandTotal');
        const pv = document.getElementById('checkoutTotalPv');
        const count = document.getElementById('checkoutItemCount');
        const pvRow = document.getElementById('checkoutPvRow');
        if (sub) sub.textContent = money(totals.total);
        if (grand) grand.textContent = money(totals.total);
        if (pv) pv.textContent = Number(totals.total_pv || 0).toLocaleString('en-US');
        if (count) count.textContent = String(totals.count || 0);
        if (pvRow) {
            const showPv = totals.total_pv > 0;
            pvRow.classList.toggle('hidden', !showPv);
            pvRow.style.display = showPv ? '' : 'none';
        }
    }

    function applyItemUi(row, item) {
        if (!row || !item) return;
        const nameEl = row.querySelector('.item-name');
        const totalEl = row.querySelector('.item-line-total');
        const pvEl = row.querySelector('.item-pv');
        const qty = Number(row.dataset.qty || item.quantity || 1);
        if (nameEl) nameEl.textContent = item.name || nameEl.textContent;
        if (totalEl) totalEl.textContent = money((Number(item.price) || 0) * qty);
        if (pvEl) {
            const pv = Number(item.pv_value || 0);
            pvEl.textContent = pv + ' PV';
            pvEl.classList.toggle('hidden', !(pv > 0));
        }
    }

    document.querySelectorAll('.checkout-portion').forEach(function (input) {
        input.addEventListener('change', function () {
            if (!this.checked) return;
            const row = this.closest('.checkout-item');
            if (!row) return;
            const index = Number(row.dataset.cartIndex);
            const portion = this.value;

            fetch(portionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ index: index, portion: portion }),
            })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (result) {
                    if (!result.ok || !result.data?.success) {
                        throw new Error(result.data?.message || 'Impossible de mettre à jour la portion');
                    }
                    applyItemUi(row, result.data.item);
                    applyTotals(result.data.totals);
                })
                .catch(function (err) {
                    if (typeof window.showToast === 'function') {
                        window.showToast(err.message || 'Erreur portion', 'error');
                    } else {
                        alert(err.message || 'Erreur portion');
                    }
                });
        });
    });

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
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
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
                submitBtn.textContent = 'Traitement…';
                submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
            
            return true;
        });
    }

});
</script>
@endpush
@endsection