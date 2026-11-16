@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
<style>
    /* Même langage visuel que admin/users (Créer un compte) */
    .guichet-form-layout {
        display: grid;
        gap: 1rem;
    }
    @media (min-width: 1024px) {
        .guichet-form-layout {
            grid-template-columns: minmax(0, 1fr) 20rem;
            align-items: start;
            gap: 1.25rem;
        }
    }
    @media (min-width: 1280px) {
        .guichet-form-layout {
            grid-template-columns: minmax(0, 1fr) 22rem;
        }
    }
    .guichet-form-section {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.85rem;
        padding: 1rem 1.1rem 1.15rem;
    }
    .guichet-form-section__head {
        margin-bottom: 0.9rem;
        padding-bottom: 0.7rem;
        border-bottom: 1px solid var(--border-light, var(--border-color));
    }
    .guichet-form-section__head h3 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .guichet-form-section__head p {
        margin: 0.2rem 0 0;
        font-size: 0.72rem;
        color: var(--text-tertiary);
    }
    .guichet-form-section .form-group { margin: 0; }
    .guichet-form-section .form-group + .form-group { margin-top: 0.85rem; }
    .guichet-form-section .form-group label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 0.35rem;
    }
    .guichet-form-section .help-text {
        display: block;
        font-size: 0.68rem;
        color: var(--text-tertiary);
        margin-top: 0.3rem;
    }
    .guichet-form-section .form-control {
        width: 100%;
        padding: 0.55rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 0.55rem;
        background: var(--bg-input, var(--bg-primary));
        color: var(--text-primary);
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.15s ease;
    }
    .guichet-form-section .form-control:focus { border-color: var(--primary); }
    .guichet-form-section .form-control:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
    .guichet-search-wrap { position: relative; }
    .guichet-search-wrap .search-ico {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.05rem;
        height: 1.05rem;
        color: var(--text-tertiary);
        pointer-events: none;
        z-index: 1;
    }
    /* Spécificité > .guichet-form-section .form-control (padding shorthand) */
    .guichet-form-section .guichet-search-wrap > .form-control {
        padding: 0.65rem 2.4rem 0.65rem 2.95rem !important;
        min-height: 2.6rem;
    }
    .guichet-search-wrap.is-loading .search-ico { opacity: 0; }
    .guichet-search-spinner {
        position: absolute;
        left: 0.95rem;
        top: 50%;
        width: 0.95rem;
        height: 0.95rem;
        margin-top: -0.475rem;
        z-index: 1;
        border: 2px solid color-mix(in srgb, var(--primary) 25%, transparent);
        border-top-color: var(--primary);
        border-radius: 999px;
        animation: guichetSpin 0.7s linear infinite;
    }
    @keyframes guichetSpin { to { transform: rotate(360deg); } }
    .guichet-search-clear {
        position: absolute;
        right: 0.4rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.6rem;
        height: 1.6rem;
        border: none;
        border-radius: 999px;
        background: transparent;
        color: var(--text-tertiary);
        cursor: pointer;
        display: grid;
        place-items: center;
    }
    .guichet-search-clear:hover {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }
    .guichet-hint {
        margin-top: 0.3rem;
        font-size: 0.72rem;
        color: var(--text-tertiary);
        min-height: 1rem;
    }
    .guichet-hint.is-error { color: #B91C1C; }
    .guichet-hint.is-ok { color: #1C7E4A; }
    .guichet-member-results {
        margin-top: 0.55rem;
        max-height: 12.5rem;
        overflow-y: auto;
        border: 1px solid var(--border-color);
        border-radius: 0.55rem;
        background: var(--bg-secondary);
    }
    .guichet-member-result {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        text-align: left;
        width: 100%;
        padding: 0.65rem 0.75rem;
        border: none;
        border-bottom: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-primary);
        cursor: pointer;
    }
    .guichet-member-result:last-child { border-bottom: none; }
    .guichet-member-result:hover:not(:disabled),
    .guichet-member-result.is-focus {
        background: color-mix(in srgb, var(--primary) 8%, transparent);
    }
    .guichet-member-result:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .guichet-member-result__body { min-width: 0; flex: 1; }
    .guichet-member-result .name {
        display: block;
        font-weight: 700;
        font-size: 0.84rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .guichet-member-result .meta {
        margin-top: 0.1rem;
        font-size: 0.7rem;
        color: var(--text-secondary);
    }
    .guichet-member-result .bal {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        font-size: 0.84rem;
        color: #1C7E4A;
        white-space: nowrap;
    }
    .guichet-selected-box {
        margin-top: 0.7rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.55rem;
        padding: 0.7rem 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .guichet-selected-box__main { min-width: 0; flex: 1; }
    .guichet-selected-box__name {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
    }
    .guichet-selected-box__meta {
        margin-top: 0.2rem;
        font-size: 0.72rem;
        color: var(--text-secondary);
        font-variant-numeric: tabular-nums;
    }
    .guichet-selected-box__meta strong { color: #1C7E4A; font-weight: 700; }
    .guichet-amount-input { position: relative; }
    .guichet-amount-input .currency {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-secondary);
        pointer-events: none;
        z-index: 1;
    }
    /* Même logique que la recherche : ne pas laisser le padding global coller le $ */
    .guichet-form-section .guichet-amount-input > .form-control {
        padding: 0.7rem 0.9rem 0.7rem 2.35rem !important;
        min-height: 2.85rem;
        font-size: 1.15rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .guichet-quick {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.55rem;
    }
    .guichet-quick button {
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-secondary);
        border-radius: 0.45rem;
        padding: 0.32rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
    }
    .guichet-quick button:hover:not(:disabled),
    .guichet-quick button.is-active {
        border-color: var(--primary);
        color: var(--primary);
        background: color-mix(in srgb, var(--primary) 8%, transparent);
    }
    .guichet-quick button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .guichet-summary {
        margin-top: 0.55rem;
        padding: 0.55rem 0.7rem;
        border-radius: 0.5rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        font-size: 0.75rem;
        color: var(--text-secondary);
        line-height: 1.4;
    }
    .guichet-summary strong {
        color: var(--text-primary);
        font-variant-numeric: tabular-nums;
    }
    .guichet-summary.is-warn {
        background: rgba(217, 119, 6, 0.08);
        border-color: rgba(217, 119, 6, 0.25);
        color: #92400E;
        font-weight: 600;
    }
    .guichet-info-box {
        border-radius: 0.65rem;
        padding: 0.75rem 0.85rem;
        font-size: 0.8rem;
        background: color-mix(in srgb, var(--primary) 8%, transparent);
        border: 1px solid color-mix(in srgb, var(--primary) 18%, transparent);
        color: var(--text-secondary);
    }
    .guichet-recap {
        display: grid;
        gap: 0.55rem;
        font-size: 0.78rem;
    }
    .guichet-recap__row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        color: var(--text-secondary);
    }
    .guichet-recap__row strong {
        color: var(--text-primary);
        font-variant-numeric: tabular-nums;
        text-align: right;
    }
    .guichet-recap__row.is-muted strong { color: var(--text-tertiary); font-weight: 600; }
    .guichet-steps {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 0.45rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .guichet-steps li {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .guichet-steps__dot {
        width: 1.15rem;
        height: 1.15rem;
        border-radius: 999px;
        border: 1.5px solid var(--border-color);
        display: grid;
        place-items: center;
        flex-shrink: 0;
        font-size: 0.62rem;
        font-weight: 700;
        color: var(--text-tertiary);
        background: var(--bg-card);
    }
    .guichet-steps li.is-done { color: #1C7E4A; font-weight: 600; }
    .guichet-steps li.is-done .guichet-steps__dot {
        border-color: #1C7E4A;
        background: rgba(28, 126, 74, 0.1);
        color: #1C7E4A;
    }
    .guichet-steps li.is-active { color: var(--primary); font-weight: 600; }
    .guichet-steps li.is-active .guichet-steps__dot {
        border-color: var(--primary);
        background: color-mix(in srgb, var(--primary) 12%, transparent);
        color: var(--primary);
    }
    .guichet-form-actions {
        display: grid;
        gap: 0.5rem;
    }
    .guichet-form-actions .btn {
        min-height: 2.65rem;
        font-weight: 700;
    }
    .guichet-form-actions .btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .guichet-form-side { position: relative; }
    @media (min-width: 1024px) {
        .guichet-form-side {
            position: sticky;
            top: 1rem;
        }
    }
    .guichet-side-member {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .guichet-side-avatar {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 999px;
        display: grid;
        place-items: center;
        flex-shrink: 0;
        background: var(--primary);
        color: #fff;
        font-size: 0.95rem;
        font-weight: 700;
    }
    .guichet-empty-hint {
        margin: 0;
        padding: 0.85rem 0.9rem;
        border-radius: 0.55rem;
        border: 1px dashed var(--border-color);
        background: var(--bg-secondary);
        color: var(--text-tertiary);
        font-size: 0.78rem;
        line-height: 1.45;
        text-align: center;
    }
</style>
@endpush

@section('title', 'Retraits guichet')

@section('content')
@php
    $currentStatus = $status ?? request('status', 'all');
    $filterActive = in_array($currentStatus, ['pending', 'completed'], true);
    $filterLabels = [
        'pending' => 'En attente',
        'completed' => 'Terminés',
        'all' => 'Tous',
    ];
    $filterButtonLabel = $filterLabels[$currentStatus] ?? 'Filtrer';
    $filterQueryBase = array_filter([
        'search' => request('search'),
    ], fn ($v) => filled($v));
    $searchOpen = filled(request('search'));
    $subtitleHtml = ($stats['pending'] ?? 0).' en attente · $'.number_format($stats['pending_amount'] ?? 0, 2)
        .($filterActive ? ' · '.e($filterButtonLabel) : '')
        .(request('search')
            ? ' <span class="text-xs opacity-90">· Résultats pour "'.e(request('search')).'"</span>'
            : '');
@endphp
<div class="space-y-4 sm:space-y-6"
     x-data="guichetCreateForm(@json($errors->any() || session('error')))">

    {{-- BANDEAU MOBILE : loupe + Filtrer (comme Membres) --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Retraits guichet</h1>
                <p class="page-subtitle">{!! $subtitleHtml !!}</p>
            </div>
            <div class="admin-title-banner__tools">
                <button type="button"
                        class="admin-banner-icon-btn{{ $searchOpen ? ' is-active' : '' }}"
                        data-admin-search-toggle="1"
                        aria-controls="adminSearchPanel"
                        aria-expanded="{{ $searchOpen ? 'true' : 'false' }}"
                        aria-label="Rechercher">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <div class="relative" x-data="{ filterOpen: false }" @keydown.escape.window="filterOpen = false">
                    <button type="button"
                            @click="filterOpen = !filterOpen"
                            class="admin-banner-icon-btn{{ $filterActive ? ' is-active' : '' }}"
                            :aria-expanded="filterOpen"
                            aria-label="Filtrer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </button>
                    <div x-show="filterOpen"
                         x-cloak
                         x-transition
                         @click.outside="filterOpen = false"
                         class="users-filter-menu">
                        <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'all'])) }}"
                           class="filter-link {{ $currentStatus === 'all' ? 'is-active' : '' }}">
                            <span>Tous</span>
                            <span class="filter-count">{{ $stats['all'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'pending'])) }}"
                           class="filter-link {{ $currentStatus === 'pending' ? 'is-active' : '' }}">
                            <span>En attente</span>
                            <span class="filter-count">{{ $stats['pending'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'completed'])) }}"
                           class="filter-link {{ $currentStatus === 'completed' ? 'is-active' : '' }}">
                            <span>Terminés</span>
                            <span class="filter-count">{{ $stats['completed'] ?? 0 }}</span>
                        </a>
                    </div>
                </div>
                <button type="button"
                        class="btn btn-primary btn-sm btn-round-icon"
                        :class="{ 'is-active': open }"
                        @click="toggleOpen()"
                        :aria-expanded="open"
                        aria-controls="guichetCreateBody"
                        title="Nouveau retrait au comptoir"
                        aria-label="Nouveau retrait au comptoir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true" x-show="!open">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true" x-show="open" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <a href="{{ route('cashier.guichet.export-daily-pdf', ['date' => now()->toDateString()]) }}"
                   class="btn btn-sm btn-round-icon btn-pdf-icon"
                   title="Rapport journalier PDF"
                   aria-label="Rapport journalier PDF">
                    <x-pdf-icon size="lg" />
                </a>
            </div>
        </div>
        <div id="adminSearchPanel"
             class="admin-search-panel{{ $searchOpen ? ' is-open' : '' }}"
             data-admin-search-panel="1"
             aria-hidden="{{ $searchOpen ? 'false' : 'true' }}">
            <form method="GET" action="{{ route('cashier.guichet.index') }}" class="search-wrapper admin-shop-search">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" class="search-input"
                       placeholder="Rechercher un membre"
                       autocomplete="off"
                       value="{{ request('search') }}">
            </form>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Retraits guichet</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">{!! $subtitleHtml !!}</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('cashier.guichet.index') }}" class="header-search">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="search-input"
                           placeholder="Rechercher un membre"
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </form>
            <div class="relative" x-data="{ filterOpen: false }" @keydown.escape.window="filterOpen = false">
                <button type="button"
                        @click="filterOpen = !filterOpen"
                        class="btn btn-outline btn-sm btn-round-icon {{ $filterActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="filterOpen"
                        aria-haspopup="true"
                        title="{{ $filterButtonLabel }}"
                        aria-label="{{ $filterButtonLabel }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
                <div x-show="filterOpen"
                     x-cloak
                     x-transition
                     @click.outside="filterOpen = false"
                     class="users-filter-menu">
                    <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'all'])) }}"
                       class="filter-link {{ $currentStatus === 'all' ? 'is-active' : '' }}">
                        <span>Tous</span>
                        <span class="filter-count">{{ $stats['all'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'pending'])) }}"
                       class="filter-link {{ $currentStatus === 'pending' ? 'is-active' : '' }}">
                        <span>En attente</span>
                        <span class="filter-count">{{ $stats['pending'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'completed'])) }}"
                       class="filter-link {{ $currentStatus === 'completed' ? 'is-active' : '' }}">
                        <span>Terminés</span>
                        <span class="filter-count">{{ $stats['completed'] ?? 0 }}</span>
                    </a>
                </div>
            </div>
            <button type="button"
                    class="btn btn-primary btn-sm btn-round-icon"
                    @click="toggleOpen()"
                    :aria-expanded="open"
                    aria-controls="guichetCreateBody"
                    :title="open ? 'Fermer' : 'Nouveau retrait'"
                    :aria-label="open ? 'Fermer' : 'Nouveau retrait'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true" x-show="!open">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true" x-show="open" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <a href="{{ route('cashier.guichet.export-daily-pdf', ['date' => now()->toDateString()]) }}"
               class="btn btn-sm btn-round-icon btn-pdf-icon"
               title="Rapport journalier PDF"
               aria-label="Rapport journalier PDF">
                <x-pdf-icon size="lg" />
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORMULAIRE CAISSIER — même structure que admin Créer un compte --}}
    <div id="guichetCreateBody"
         x-show="open"
         x-transition.opacity.duration.150ms
         x-cloak>
        <form method="POST"
              action="{{ route('cashier.guichet.store') }}"
              id="guichetCreateForm"
              class="guichet-form-layout"
              @submit="return canSubmit()">
            @csrf
            <input type="hidden" name="user_id" :value="selected ? selected.id : ''">

            <div class="guichet-form-main space-y-4">
                <section class="guichet-form-section">
                    <div class="guichet-form-section__head">
                        <h3>1 · Membre</h3>
                        <p>Compte présent au comptoir (sans smartphone).</p>
                    </div>
                    <div class="form-group">
                        <label for="guichetMemberSearch">Rechercher</label>
                        <div class="guichet-search-wrap" :class="{ 'is-loading': searching }">
                            <svg class="search-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="guichet-search-spinner" x-show="searching" x-cloak></span>
                            <input type="text"
                                   id="guichetMemberSearch"
                                   class="form-control"
                                   placeholder="Code membre, téléphone ou nom"
                                   autocomplete="off"
                                   x-model="query"
                                   :disabled="!!selected"
                                   @input.debounce.300ms="search()"
                                   @keydown.enter.prevent="onSearchEnter()"
                                   @keydown.arrow-down.prevent="focusNext()"
                                   @keydown.arrow-up.prevent="focusPrev()"
                                   @keydown.escape.prevent="clearSearch()">
                            <button type="button"
                                    class="guichet-search-clear"
                                    x-show="query.length > 0 || selected"
                                    @click="clearSearch()"
                                    title="Effacer"
                                    aria-label="Effacer la recherche">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <p class="guichet-hint"
                           :class="{ 'is-error': hintError, 'is-ok': !hintError && selected }"
                           x-text="statusMessage"></p>
                    </div>

                    <div class="guichet-member-results"
                         x-show="results.length > 0 && !selected"
                         x-transition>
                        <template x-for="(m, idx) in results" :key="m.id">
                            <button type="button"
                                    class="guichet-member-result"
                                    :class="{ 'is-focus': focusIndex === idx }"
                                    @click="selectMember(m)"
                                    :disabled="!m.eligible">
                                <div class="guichet-member-result__body">
                                    <span class="name" x-text="m.label"></span>
                                    <div class="meta">
                                        <span x-text="m.phone || 'Sans téléphone'"></span>
                                        <span x-show="Number(m.pending_balance) > 0"
                                              x-text="' · En attente $' + Number(m.pending_balance).toFixed(2)"></span>
                                        <span x-show="m.message" x-text="' — ' + m.message"></span>
                                    </div>
                                </div>
                                <span class="bal" x-text="'$' + Number(m.balance).toFixed(2)"></span>
                            </button>
                        </template>
                    </div>

                    <div class="guichet-selected-box" x-show="selected" x-cloak>
                        <div class="guichet-side-avatar" x-text="selected ? initials(selected.name || selected.label) : '?'"></div>
                        <div class="guichet-selected-box__main">
                            <div class="guichet-selected-box__name" x-text="selected ? selected.name : ''"></div>
                            <div class="guichet-selected-box__meta">
                                <span x-text="selected ? (selected.sponsor_id || 'Sans code') : ''"></span>
                                <span x-show="selected && selected.phone" x-text="selected ? ' · ' + selected.phone : ''"></span>
                                <span> · Disponible </span>
                                <strong x-text="selected ? '$' + Number(selected.balance).toFixed(2) : ''"></strong>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" @click="clearSelection()">Changer</button>
                    </div>
                </section>

                <section class="guichet-form-section">
                    <div class="guichet-form-section__head">
                        <h3>2 · Montant</h3>
                        <p>Somme à remettre en espèces au membre.</p>
                    </div>

                    <template x-if="!selected">
                        <p class="guichet-empty-hint">Sélectionnez d’abord un membre pour saisir le montant.</p>
                    </template>

                    <div x-show="selected" x-cloak>
                        <div class="form-group">
                            <label for="guichetAmount">Montant à remettre <span class="text-[var(--danger)]">*</span></label>
                            <div class="guichet-amount-input">
                                <span class="currency">$</span>
                                <input type="number"
                                       id="guichetAmount"
                                       name="amount"
                                       class="form-control"
                                       step="0.01"
                                       min="2"
                                       max="10000"
                                       :required="!!selected"
                                       :disabled="!selected"
                                       x-model="amount"
                                       x-ref="amountInput"
                                       placeholder=""
                                       inputmode="decimal">
                            </div>
                            <span class="help-text">Min $2 · plafond jour $5 000 · frais $0</span>
                        </div>

                        <div class="guichet-quick">
                            <template x-for="chip in quickAmounts" :key="chip">
                                <button type="button"
                                        :class="{ 'is-active': Number(amount) === chip }"
                                        @click="setAmount(chip)"
                                        :disabled="!selected || chip > Number(selected.balance)"
                                        x-text="'$' + chip"></button>
                            </template>
                            <button type="button"
                                    :class="{ 'is-active': selected && Number(amount) === Number(Number(selected.balance).toFixed(2)) }"
                                    @click="setAmount(selected.balance)"
                                    :disabled="!selected || Number(selected.balance) < 2">
                                Tout
                            </button>
                        </div>

                        <div class="guichet-summary"
                             x-show="Number(amount) > 0"
                             :class="{ 'is-warn': amountOverBalance }"
                             x-cloak>
                            <template x-if="!amountOverBalance">
                                <span>
                                    Remise <strong x-text="'$' + Number(amount || 0).toFixed(2)"></strong>
                                    · reste sur le compte <strong x-text="'$' + remainingBalance.toFixed(2)"></strong>
                                </span>
                            </template>
                            <template x-if="amountOverBalance">
                                <span>Montant supérieur au solde (<strong x-text="selected ? '$' + Number(selected.balance).toFixed(2) : ''"></strong>).</span>
                            </template>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="guichet-form-side space-y-4">
                <section class="guichet-form-section">
                    <div class="guichet-form-section__head">
                        <h3>Progression</h3>
                        <p>3 étapes au comptoir.</p>
                    </div>
                    <ul class="guichet-steps">
                        <li :class="selected ? 'is-done' : 'is-active'">
                            <span class="guichet-steps__dot" x-text="selected ? '✓' : '1'"></span>
                            <span>Identifier le membre</span>
                        </li>
                        <li :class="canCreate ? 'is-done' : (selected ? 'is-active' : '')">
                            <span class="guichet-steps__dot" x-text="canCreate ? '✓' : '2'"></span>
                            <span>Saisir le montant</span>
                        </li>
                        <li :class="canCreate ? 'is-active' : ''">
                            <span class="guichet-steps__dot">3</span>
                            <span>Créer puis remettre + reçu</span>
                        </li>
                    </ul>
                </section>

                <section class="guichet-form-section">
                    <div class="guichet-form-section__head">
                        <h3>Récapitulatif</h3>
                        <p>Contrôle avant création.</p>
                    </div>
                    <div class="guichet-recap">
                        <div class="guichet-recap__row" :class="{ 'is-muted': !selected }">
                            <span>Membre</span>
                            <strong x-text="selected ? selected.name : '—'"></strong>
                        </div>
                        <div class="guichet-recap__row" :class="{ 'is-muted': !selected }">
                            <span>Code</span>
                            <strong class="font-mono" x-text="selected ? (selected.sponsor_id || '—') : '—'"></strong>
                        </div>
                        <div class="guichet-recap__row" :class="{ 'is-muted': !selected }">
                            <span>Disponible</span>
                            <strong x-text="selected ? ('$' + Number(selected.balance).toFixed(2)) : '—'"></strong>
                        </div>
                        <div class="guichet-recap__row" :class="{ 'is-muted': !(selected && Number(amount) > 0) }">
                            <span>À remettre</span>
                            <strong x-text="selected && Number(amount) > 0 ? ('$' + Number(amount).toFixed(2)) : '—'"></strong>
                        </div>
                    </div>
                </section>

                <div class="guichet-form-actions">
                    <button type="submit"
                            class="btn btn-primary w-full"
                            :disabled="!canCreate">
                        Créer le retrait
                    </button>
                    <button type="button"
                            class="btn btn-outline w-full"
                            @click="open = false">
                        Fermer
                    </button>
                </div>
            </aside>
        </form>
    </div>

    {{-- STATS + LISTE : masqués pendant la saisie du formulaire --}}
    <div x-show="!open" x-cloak class="space-y-4 sm:space-y-6">
    <div class="commission-stats">
        <div class="commission-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">{{ $stats['pending'] }}</div>
            <div class="label">En attente</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($stats['pending_amount'], 2) }}</div>
            <div class="label">Montant dû</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">{{ $stats['completed_today'] }}</div>
            <div class="label">Remis aujourd’hui</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($stats['paid_today_amount'] ?? 0, 2) }}</div>
            <div class="label">Montant payé</div>
        </div>
    </div>

    {{-- TABLEAU --}}
    <div class="card">
        <div class="table-wrap">
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Membre</th>
                        <th class="col-amount">Total</th>
                        <th class="col-amount">Retiré</th>
                        <th class="col-amount">Reste</th>
                        <th>Statut</th>
                        <th class="hidden sm:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $w)
                        @php
                            $memberLabel = $w->user
                                ? trim(($w->user->sponsor_id ? $w->user->sponsor_id.' : ' : '').$w->user->name)
                                : '—';
                            $detailUrl = route('cashier.guichet.show', $w->id);
                            $retire = (float) $w->amount;
                            $reste = (float) ($w->wallet?->balance ?? 0);
                            // Pending / completed : le débit est déjà sorti du solde → total = reste + retiré
                            $total = in_array($w->status, ['pending', 'completed'], true)
                                ? $reste + $retire
                                : $reste;
                        @endphp
                        <tr class="commission-row"
                            role="link"
                            tabindex="0"
                            data-href="{{ $detailUrl }}"
                            onclick="window.location.href=this.dataset.href"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href=this.dataset.href}">
                            <td class="font-mono text-xs text-[var(--text-secondary)]">{{ $w->id }}</td>
                            <td>
                                <span class="commission-member">{{ $memberLabel }}</span>
                            </td>
                            <td class="col-amount text-[var(--text-primary)]">
                                ${{ number_format($total, 2) }}
                            </td>
                            <td class="col-amount text-[var(--ui-stat-success)]">
                                ${{ number_format($retire, 2) }}
                            </td>
                            <td class="col-amount text-[var(--text-secondary)]">
                                ${{ number_format($reste, 2) }}
                            </td>
                            <td>
                                @if($w->status === 'pending')
                                    <span class="badge badge-warning">En attente</span>
                                @elseif($w->status === 'completed')
                                    <span class="badge badge-success">Remis</span>
                                @else
                                    <span class="badge badge-secondary">{{ $w->status }}</span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-tertiary)]">
                                {{ $w->created_at?->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <p class="text-base font-medium text-[var(--text-primary)]">
                                    @if($currentStatus === 'pending')
                                        Aucun retrait en attente
                                    @elseif($currentStatus === 'completed')
                                        Aucun retrait remis
                                    @else
                                        Aucun retrait guichet
                                    @endif
                                </p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">
                                    @if(request('search'))
                                        Aucun résultat pour cette recherche.
                                    @elseif($currentStatus === 'pending' && ($stats['completed'] ?? 0) > 0)
                                        Les retraits remis sont dans « Terminés » ou « Tous ».
                                    @elseif(($stats['all'] ?? 0) === 0)
                                        Les retraits créés au comptoir apparaîtront ici.
                                    @else
                                        Essayez un autre filtre.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="mt-4">
                <x-salang-pagination :paginator="$withdrawals" />
            </div>
        @endif
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function guichetCreateForm(startOpen = false) {
    return {
        open: !!startOpen,
        query: @json(old('q', '')),
        amount: @json(old('amount', '')),
        results: [],
        selected: null,
        searching: false,
        focusIndex: -1,
        hintError: false,
        statusMessage: 'Code membre, téléphone ou nom (min. 2 caractères).',
        findUrl: @json(route('cashier.guichet.find-member')),
        quickAmounts: [20, 50, 100, 200, 500],
        toggleOpen() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => {
                    const card = document.getElementById('guichetCreateBody');
                    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    const el = document.getElementById('guichetMemberSearch');
                    if (el) el.focus();
                });
            }
        },
        initials(name) {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '?';
            if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        },
        get canCreate() {
            if (!this.selected) return false;
            const n = Number(this.amount);
            return Number.isFinite(n) && n >= 2 && n <= Number(this.selected.balance);
        },
        get amountOverBalance() {
            if (!this.selected) return false;
            const n = Number(this.amount);
            return Number.isFinite(n) && n > 0 && n > Number(this.selected.balance);
        },
        get remainingBalance() {
            if (!this.selected) return 0;
            const n = Number(this.amount) || 0;
            return Math.max(0, Number(this.selected.balance) - n);
        },
        focusNext() {
            if (!this.results.length || this.selected) return;
            this.focusIndex = Math.min(this.results.length - 1, this.focusIndex + 1);
        },
        focusPrev() {
            if (!this.results.length || this.selected) return;
            this.focusIndex = Math.max(0, this.focusIndex - 1);
        },
        onSearchEnter() {
            if (this.selected) return;
            if (this.focusIndex >= 0 && this.results[this.focusIndex]) {
                this.selectMember(this.results[this.focusIndex]);
                return;
            }
            if (this.results.length === 1) {
                this.selectMember(this.results[0]);
                return;
            }
            this.search();
        },
        async search() {
            const q = (this.query || '').trim();
            this.hintError = false;
            this.results = [];
            this.focusIndex = -1;
            if (this.selected) return;
            if (q.length < 2) {
                this.statusMessage = 'Saisissez au moins 2 caractères.';
                return;
            }
            this.searching = true;
            this.statusMessage = 'Recherche…';
            try {
                const res = await fetch(this.findUrl + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (!data.ok) {
                    this.hintError = true;
                    this.statusMessage = data.message || 'Aucun résultat.';
                    return;
                }
                this.results = data.members || [];
                const exact = this.results.find((m) =>
                    String(m.sponsor_id || '').toLowerCase() === q.toLowerCase() && m.eligible
                );
                if (exact && this.results.filter((m) => m.eligible).length === 1) {
                    this.selectMember(exact);
                    return;
                }
                this.focusIndex = this.results.findIndex((m) => m.eligible);
                this.statusMessage = this.results.length === 1
                    ? '1 membre trouvé — Entrée pour sélectionner.'
                    : this.results.length + ' membre(s) — choisissez-en un.';
            } catch (e) {
                this.hintError = true;
                this.statusMessage = 'Erreur de recherche. Réessayez.';
            } finally {
                this.searching = false;
            }
        },
        selectMember(m) {
            if (!m || !m.eligible) {
                this.hintError = true;
                this.statusMessage = (m && m.message) || 'Ce membre ne peut pas retirer.';
                return;
            }
            this.selected = m;
            this.results = [];
            this.focusIndex = -1;
            this.hintError = false;
            this.statusMessage = 'Membre sélectionné — saisissez le montant.';
            this.$nextTick(() => {
                if (this.$refs.amountInput) {
                    this.$refs.amountInput.focus();
                    this.$refs.amountInput.select();
                }
            });
        },
        setAmount(value) {
            const n = Math.floor(Number(value) * 100) / 100;
            if (!Number.isFinite(n) || n < 2) return;
            this.amount = n.toFixed(2);
        },
        clearSelection() {
            this.selected = null;
            this.amount = '';
            this.hintError = false;
            this.statusMessage = 'Recherchez un autre membre.';
            this.$nextTick(() => {
                const el = document.getElementById('guichetMemberSearch');
                if (el) {
                    el.disabled = false;
                    el.focus();
                    el.select();
                }
            });
            if ((this.query || '').trim().length >= 2) this.search();
        },
        clearSearch() {
            this.query = '';
            this.results = [];
            this.selected = null;
            this.amount = '';
            this.focusIndex = -1;
            this.hintError = false;
            this.statusMessage = 'Code membre, téléphone ou nom (min. 2 caractères).';
            this.$nextTick(() => {
                const el = document.getElementById('guichetMemberSearch');
                if (el) {
                    el.disabled = false;
                    el.focus();
                }
            });
        },
        canSubmit() {
            if (!this.selected) {
                this.hintError = true;
                this.statusMessage = 'Sélectionnez un membre d’abord.';
                return false;
            }
            const n = Number(this.amount);
            if (!Number.isFinite(n) || n < 2) {
                this.hintError = true;
                this.statusMessage = 'Montant minimum : $2.';
                return false;
            }
            if (n > Number(this.selected.balance)) {
                this.hintError = true;
                this.statusMessage = 'Montant supérieur au solde disponible.';
                return false;
            }
            return true;
        }
    };
}
</script>
@endpush
