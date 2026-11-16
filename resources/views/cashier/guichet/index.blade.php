@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
<style>
    .guichet-create {
        padding: 0;
        overflow: hidden;
    }
    .guichet-create__head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1.1rem;
        border-bottom: 1px solid transparent;
        background: color-mix(in srgb, var(--primary) 5%, var(--bg-card));
    }
    .guichet-create.is-open .guichet-create__head {
        align-items: flex-start;
        padding: 1rem 1.1rem 0.85rem;
        border-bottom-color: var(--border-color);
    }
    .guichet-create__toggle {
        flex-shrink: 0;
        margin-left: auto;
        align-self: center;
    }
    .guichet-create__icon {
        flex-shrink: 0;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.55rem;
        display: grid;
        place-items: center;
        background: color-mix(in srgb, var(--primary) 14%, transparent);
        color: var(--primary);
    }
    .guichet-create__title {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.25;
    }
    .guichet-create__sub {
        margin-top: 0.15rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
        line-height: 1.35;
    }
    .guichet-create__steps {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.55rem;
    }
    .guichet-step {
        font-size: 0.68rem;
        font-weight: 650;
        letter-spacing: 0.02em;
        padding: 0.18rem 0.5rem;
        border-radius: 999px;
        background: var(--bg-secondary);
        color: var(--text-tertiary);
        border: 1px solid transparent;
    }
    .guichet-step.is-active {
        background: color-mix(in srgb, var(--primary) 12%, transparent);
        color: var(--primary);
        border-color: color-mix(in srgb, var(--primary) 25%, transparent);
    }
    .guichet-step.is-done {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
    }
    .guichet-create__body {
        padding: 1rem 1.1rem 1.15rem;
        display: grid;
        gap: 0.9rem;
    }
    @media (min-width: 900px) {
        .guichet-create__body {
            grid-template-columns: minmax(0, 1.35fr) minmax(240px, 0.85fr);
            align-items: start;
            gap: 1.15rem;
        }
    }
    .guichet-create .form-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-tertiary);
        margin-bottom: 0.35rem;
    }
    .guichet-create .form-control {
        width: 100%;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        padding: 0.55rem 0.75rem;
        font-size: 0.875rem;
        color: var(--text-primary);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .guichet-create .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(30, 93, 173, 0.12);
    }
    .guichet-create .form-control:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
    .guichet-search-wrap {
        position: relative;
    }
    .guichet-search-wrap .search-ico {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1rem;
        height: 1rem;
        color: var(--text-tertiary);
        pointer-events: none;
    }
    .guichet-search-wrap .form-control {
        padding-left: 2.25rem;
        padding-right: 2.25rem;
    }
    .guichet-search-clear {
        position: absolute;
        right: 0.45rem;
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
        margin-top: 0.35rem;
        font-size: 0.72rem;
        color: var(--text-tertiary);
        min-height: 1rem;
    }
    .guichet-hint.is-error { color: #b32a2a; }
    .guichet-member-results {
        margin-top: 0.55rem;
        max-height: 13.5rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        padding-right: 0.15rem;
    }
    .guichet-member-result {
        text-align: left;
        width: 100%;
        padding: 0.65rem 0.75rem;
        border-radius: var(--radius-md, 8px);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .guichet-member-result:hover:not(:disabled),
    .guichet-member-result.is-active {
        border-color: var(--primary);
        background: color-mix(in srgb, var(--primary) 8%, var(--bg-card));
    }
    .guichet-member-result:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
    .guichet-member-result .row-top {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: baseline;
    }
    .guichet-member-result .name {
        font-weight: 650;
        font-size: 0.875rem;
    }
    .guichet-member-result .bal {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        font-size: 0.8125rem;
        color: var(--ui-stat-success, #16a34a);
        white-space: nowrap;
    }
    .guichet-member-result .meta {
        margin-top: 0.15rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .guichet-selected {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.65rem;
        padding: 0.7rem 0.8rem;
        border-radius: var(--radius-md, 8px);
        border: 1px solid color-mix(in srgb, var(--primary) 35%, var(--border-color));
        background: color-mix(in srgb, var(--primary) 6%, var(--bg-card));
    }
    .guichet-selected__main {
        min-width: 0;
    }
    .guichet-selected__name {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .guichet-selected__pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.35rem;
    }
    .guichet-pill {
        font-size: 0.7rem;
        font-weight: 650;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: var(--bg-secondary);
        color: var(--text-secondary);
        font-variant-numeric: tabular-nums;
    }
    .guichet-pill.is-ok {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }
    .guichet-amount-panel {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 0.85rem;
        background: var(--bg-primary);
    }
    .guichet-amount-panel.is-locked {
        opacity: 0.72;
    }
    .guichet-amount-input {
        position: relative;
    }
    .guichet-amount-input .currency {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 700;
        color: var(--text-secondary);
        pointer-events: none;
    }
    .guichet-amount-input .form-control {
        padding-left: 1.65rem;
        font-size: 1.05rem;
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
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 650;
        cursor: pointer;
    }
    .guichet-quick button:hover:not(:disabled) {
        border-color: var(--primary);
        color: var(--primary);
    }
    .guichet-quick button:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .guichet-create__actions {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        margin-top: 0.85rem;
    }
    .guichet-create__actions .btn {
        width: 100%;
        min-height: 2.55rem;
    }
    .guichet-create__actions .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .guichet-create__foot {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        text-align: center;
        line-height: 1.35;
    }
</style>
@endpush

@section('title', 'Retraits guichet')

@section('content')
@php
    $currentStatus = $status ?? request('status', 'pending');
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
                <a href="{{ route('cashier.offline-bonus.pay') }}"
                   class="btn btn-outline btn-sm btn-round-icon"
                   title="Paiement des bonus"
                   aria-label="Paiement des bonus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </a>
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
            <a href="{{ route('cashier.offline-bonus.pay') }}"
               class="btn btn-outline btn-sm btn-round-icon"
               title="Paiement des bonus"
               aria-label="Paiement des bonus">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </a>
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

    {{-- FORMULAIRE CAISSIER (sans smartphone) — ouvert via le bouton barre d’outils --}}
    <div class="card guichet-create is-open"
         id="guichetCreateBody"
         x-show="open"
         x-cloak>
        <div class="guichet-create__head">
            <div class="guichet-create__icon" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="guichet-create__title">Nouveau retrait au comptoir</div>
                <p class="guichet-create__sub">Membre sans smartphone — recherchez le compte, saisissez le montant, puis confirmez la remise espèces.</p>
                <div class="guichet-create__steps" aria-hidden="true">
                    <span class="guichet-step" :class="stepClass(1)">1 · Membre</span>
                    <span class="guichet-step" :class="stepClass(2)">2 · Montant</span>
                    <span class="guichet-step" :class="stepClass(3)">3 · Remise</span>
                </div>
            </div>
        </div>

        <div class="guichet-create__body">
            <div>
                <label class="form-label" for="guichetMemberSearch">Membre</label>
                <div class="guichet-search-wrap">
                    <svg class="search-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="guichetMemberSearch"
                           class="form-control"
                           placeholder="Code membre, téléphone ou nom"
                           autocomplete="off"
                           x-model="query"
                           @input.debounce.350ms="search()"
                           @keydown.enter.prevent="search()"
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
                <p class="guichet-hint" :class="{ 'is-error': hintError }" x-text="statusMessage"></p>

                <div class="guichet-member-results"
                     x-show="results.length > 0 && !selected"
                     x-transition>
                    <template x-for="m in results" :key="m.id">
                        <button type="button"
                                class="guichet-member-result"
                                :class="{ 'is-active': selected && selected.id === m.id }"
                                @click="selectMember(m)"
                                :disabled="!m.eligible">
                            <div class="row-top">
                                <span class="name" x-text="m.label"></span>
                                <span class="bal" x-text="'$' + Number(m.balance).toFixed(2)"></span>
                            </div>
                            <div class="meta">
                                <span x-text="m.phone || 'Sans téléphone'"></span>
                                <span x-show="m.message" x-text="' — ' + m.message"></span>
                            </div>
                        </button>
                    </template>
                </div>

                <div class="guichet-selected" x-show="selected" x-transition>
                    <div class="guichet-selected__main">
                        <div class="guichet-selected__name" x-text="selected ? selected.label : ''"></div>
                        <div class="guichet-selected__pills">
                            <span class="guichet-pill is-ok"
                                  x-text="selected ? 'Disponible $' + Number(selected.balance).toFixed(2) : ''"></span>
                            <span class="guichet-pill"
                                  x-text="selected ? 'En attente $' + Number(selected.pending_balance).toFixed(2) : ''"></span>
                            <span class="guichet-pill" x-show="selected && selected.phone" x-text="selected ? selected.phone : ''"></span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" @click="clearSelection()">Changer</button>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('cashier.guichet.store') }}"
                  class="guichet-amount-panel"
                  :class="{ 'is-locked': !selected }"
                  @submit="return canSubmit()">
                @csrf
                <input type="hidden" name="user_id" :value="selected ? selected.id : ''">

                <label class="form-label" for="guichetAmount">Montant à remettre</label>
                <div class="guichet-amount-input">
                    <span class="currency">$</span>
                    <input type="number"
                           id="guichetAmount"
                           name="amount"
                           class="form-control"
                           step="0.01"
                           min="2"
                           max="10000"
                           required
                           :disabled="!selected"
                           x-model="amount"
                           x-ref="amountInput"
                           placeholder="0.00">
                </div>

                <div class="guichet-quick" x-show="selected">
                    <template x-for="chip in quickAmounts" :key="chip">
                        <button type="button"
                                @click="setAmount(chip)"
                                :disabled="!selected || chip > Number(selected.balance)"
                                x-text="'$' + chip"></button>
                    </template>
                    <button type="button"
                            @click="setAmount(selected.balance)"
                            :disabled="!selected || Number(selected.balance) < 2"
                            x-show="selected">
                        Tout
                    </button>
                </div>

                <div class="guichet-create__actions">
                    <button type="submit"
                            class="btn btn-primary"
                            :disabled="!canCreate">
                        Créer le retrait
                    </button>
                    <p class="guichet-create__foot">Min $2 · plafond jour $5 000 · frais guichet $0<br>Ensuite : confirmer la remise + imprimer le reçu</p>
                </div>
            </form>
        </div>
    </div>

    {{-- STATS --}}
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
    </div>

    {{-- TABLEAU --}}
    <div class="card">
        <div class="table-wrap">
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Membre</th>
                        <th class="col-amount">Montant</th>
                        <th>Statut</th>
                        <th class="hidden sm:table-cell">Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $w)
                        @php
                            $memberLabel = $w->user
                                ? trim(($w->user->sponsor_id ? $w->user->sponsor_id.' : ' : '').$w->user->name)
                                : '—';
                            $detailUrl = route('cashier.guichet.show', $w->id);
                        @endphp
                        <tr class="commission-row"
                            role="link"
                            tabindex="0"
                            data-href="{{ $detailUrl }}"
                            onclick="window.location.href=this.dataset.href"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href=this.dataset.href}">
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $w->id }}</td>
                            <td>
                                <span class="commission-member">{{ $memberLabel }}</span>
                            </td>
                            <td class="col-amount text-[var(--ui-stat-success)]">
                                ${{ number_format($w->amount, 2) }}
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
                            <td>
                                <a href="{{ $detailUrl }}" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">
                                    {{ $w->status === 'pending' ? 'Traiter' : 'Reçu' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucun retrait guichet</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">
                                    @if(request('search'))
                                        Aucun résultat pour cette recherche.
                                    @else
                                        Les demandes membres apparaîtront ici.
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
        hintError: false,
        statusMessage: 'Saisissez le code membre, le téléphone ou le nom.',
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
        get canCreate() {
            if (!this.selected) return false;
            const n = Number(this.amount);
            return Number.isFinite(n) && n >= 2 && n <= Number(this.selected.balance);
        },
        stepClass(step) {
            if (step === 1) {
                return this.selected ? 'is-done' : 'is-active';
            }
            if (step === 2) {
                if (!this.selected) return '';
                return this.canCreate ? 'is-done' : 'is-active';
            }
            return this.canCreate ? 'is-active' : '';
        },
        async search() {
            const q = (this.query || '').trim();
            this.hintError = false;
            this.results = [];
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
                this.statusMessage = this.results.length + ' membre(s) trouvé(s) — choisissez-en un.';
            } catch (e) {
                this.hintError = true;
                this.statusMessage = 'Erreur de recherche. Réessayez.';
            } finally {
                this.searching = false;
            }
        },
        selectMember(m) {
            if (!m.eligible) {
                this.hintError = true;
                this.statusMessage = m.message || 'Ce membre ne peut pas retirer.';
                return;
            }
            this.selected = m;
            this.results = [];
            this.hintError = false;
            this.statusMessage = 'Membre prêt — indiquez le montant à remettre.';
            this.$nextTick(() => {
                if (this.$refs.amountInput) this.$refs.amountInput.focus();
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
                if (el) el.focus();
            });
            if ((this.query || '').trim().length >= 2) this.search();
        },
        clearSearch() {
            this.query = '';
            this.results = [];
            this.selected = null;
            this.amount = '';
            this.hintError = false;
            this.statusMessage = 'Saisissez le code membre, le téléphone ou le nom.';
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
