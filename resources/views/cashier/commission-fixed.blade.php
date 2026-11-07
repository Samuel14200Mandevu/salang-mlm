@extends('cashier.layouts.app')

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .commission-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .commission-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem;
        text-align: center;
    }
    .commission-stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
    }
    .commission-stat-card .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .commission-stat-card .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 6px);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    .icon-green { background: rgba(34, 197, 94, 0.10); color: #16a34a; }
    .icon-blue { background: rgba(59, 130, 246, 0.10); color: #2563eb; }
    .icon-purple { background: rgba(139, 92, 246, 0.10); color: #e8940f; }
    .icon-orange { background: rgba(245, 158, 11, 0.10); color: #d97706; }

    .commission-table {
        width: 100%;
        font-size: 0.813rem;
        border-collapse: collapse;
    }
    .commission-table th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
    }
    .commission-table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }
    .commission-table tr:hover td {
        background: var(--bg-secondary);
    }
    .commission-table .badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .badge-cash {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.15);
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: var(--primary-hover, #134178);
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-pdf {
        background: #b32a2a;
        color: #FFFFFF;
    }
    .btn-pdf:hover {
        background: #8f2121;
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .filter-section select,
    .filter-section input {
        padding: 0.3rem 0.6rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 100px;
    }
    .filter-section select:focus,
    .filter-section input:focus {
        border-color: var(--primary);
        outline: none;
    }

    .search-wrapper {
        position: relative;
        flex: 2;
        min-width: 150px;
    }
    .search-wrapper .search-icon {
        position: absolute;
        left: 0.6rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-tertiary);
        pointer-events: none;
    }
    .search-wrapper .search-input {
        width: 100%;
        padding: 0.3rem 0.6rem 0.3rem 2rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 0.813rem;
    }
    .search-wrapper .search-input:focus {
        border-color: var(--primary);
        outline: none;
    }
    .search-wrapper .clear-search {
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-tertiary);
        cursor: pointer;
        display: none;
        background: none;
        border: none;
        padding: 0.15rem;
    }
    .search-wrapper .clear-search.visible {
        display: block;
    }

    .filter-section .btn-filter {
        padding: 0.3rem 1.25rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius-md, 6px);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .filter-section .btn-filter:hover {
        background: var(--primary-hover, #134178);
    }
    .filter-section .btn-reset {
        padding: 0.3rem 1.25rem;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: background 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .filter-section .btn-reset:hover {
        background: var(--bg-secondary);
    }

    .type-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
    }
    .type-badge-cash_pos { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .type-badge-direct { background: rgba(30, 93, 173, 0.12); color: #1e5dad; }
    .type-badge-indirect { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .type-badge-leadership { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .type-badge-sponsor { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .type-badge-default { background: var(--bg-secondary); color: var(--text-secondary); }

    .from-user-info {
        font-size: 0.65rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 0.15rem;
    }
    .from-user-info svg {
        width: 0.7rem;
        height: 0.7rem;
    }

    .commission-row.hidden {
        display: none;
    }

    /* ✅ Style pour le loader d'impression */
    .print-loader-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .print-loader-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .print-loader-box {
        background: var(--bg-card);
        color: var(--text-primary);
        border-radius: 12px;
        padding: 2rem 3rem;
        text-align: center;
        max-width: 400px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .print-loader-box .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #b32a2a;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .print-loader-box h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
    }
    .print-loader-box p {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0;
    }

    @media (max-width: 640px) {
        .commission-stats { grid-template-columns: 1fr 1fr; }
        .commission-table { font-size: 0.7rem; }
        .commission-table th, .commission-table td { padding: 0.3rem 0.4rem; }
        .filter-section { flex-direction: column; }
        .filter-section select, .filter-section input, .filter-section button { width: 100%; min-width: unset; }
        .search-wrapper { width: 100%; }
        .card { padding: 0.875rem; }
        .header-actions { flex-wrap: wrap; width: 100%; }
        .header-actions .btn { flex: 1; justify-content: center; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .print-loader-box { padding: 1.5rem; margin: 1rem; }
    }
</style>
@endpush

@section('title', 'Toutes les commissions')

@section('content')
@php
    $searchOpen = filled(request('search'));
    $filterActive = filled(request('type'))
        || filled(request('status'))
        || filled(request('source'))
        || filled(request('date_from'))
        || filled(request('date_to'));
    $commissionSubtitle = ($commissions->total() ?? 0).' commission(s)'
        .(request('search') ? ' · Résultats pour "'.e(request('search')).'"' : '');
    $filterQueryBase = array_filter([
        'search' => request('search'),
    ], fn ($v) => filled($v));
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE : PDF + Commandes + Nouvelle vente dans le div --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Toutes les commissions</h1>
                <p id="listSubtitleMobile" class="page-subtitle">{!! $commissionSubtitle !!}</p>
            </div>
            <div class="admin-title-banner__tools admin-title-banner__tools--commissions-index">
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
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open"
                            class="admin-banner-icon-btn{{ $filterActive ? ' is-active' : '' }}"
                            :aria-expanded="open" aria-label="Filtrer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition @click.outside="open = false" class="users-filter-menu users-filter-panel">
                        <form method="GET" action="{{ route('cashier.commissions') }}" class="space-y-2">
                            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                            <div>
                                <label for="type_m">Type</label>
                                <select name="type" id="type_m" class="form-control">
                                    <option value="">Tous les types</option>
                                    @foreach($types ?? [] as $type)
                                        @if(in_array($type, ['cash_pos', 'direct', 'indirect', 'leadership', 'sponsor']))
                                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type == 'cash_pos' ? 'CASH POS' : ucfirst($type) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="status_m">Statut</label>
                                <select name="status" id="status_m" class="form-control">
                                    <option value="">Tous les statuts</option>
                                    @foreach($statuses ?? [] as $key => $label)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="source_m">Source</label>
                                <select name="source" id="source_m" class="form-control">
                                    <option value="">Toutes les sources</option>
                                    <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS</option>
                                    <option value="mlm" {{ request('source') == 'mlm' ? 'selected' : '' }}>MLM</option>
                                </select>
                            </div>
                            <div><label for="date_from_m">Du</label><input type="date" name="date_from" id="date_from_m" value="{{ request('date_from') }}" class="form-control"></div>
                            <div><label for="date_to_m">Au</label><input type="date" name="date_to" id="date_to_m" value="{{ request('date_to') }}" class="form-control"></div>
                            <div class="flex gap-2 pt-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-1">Appliquer</button>
                                <a href="{{ route('cashier.commissions', $filterQueryBase) }}" class="btn btn-outline btn-sm">Effacer</a>
                            </div>
                        </form>
                    </div>
                </div>
                <button type="button" onclick="printPdfDirect()" class="btn btn-success btn-sm btn-round-icon" title="Imprimer PDF" aria-label="Imprimer PDF">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </button>
                <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm btn-round-icon" title="Commandes" aria-label="Commandes">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </a>
                <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm btn-round-icon" title="Nouvelle vente" aria-label="Nouvelle vente">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            </div>
        </div>
        <div id="adminSearchPanel"
             class="admin-search-panel{{ $searchOpen ? ' is-open' : '' }}"
             data-admin-search-panel="1"
             aria-hidden="{{ $searchOpen ? 'false' : 'true' }}">
            <div class="search-wrapper admin-shop-search">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Rechercher une commission" autocomplete="off" value="{{ request('search') }}">
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Toutes les commissions
            </h1>
            <p id="listSubtitle" class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">{!! $commissionSubtitle !!}</p>
        </div>
        <div class="flex gap-2 flex-wrap header-actions">
            <div class="header-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="searchInputDesktop" class="search-input"
                           placeholder="Rechercher une commission" autocomplete="off" value="{{ request('search') }}">
                </div>
            </div>
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button" @click="open = !open"
                        class="btn btn-outline btn-sm {{ $filterActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="open" aria-haspopup="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filtrer</span>
                </button>
                <div x-show="open" x-cloak x-transition @click.outside="open = false" class="users-filter-menu users-filter-panel">
                    <form method="GET" action="{{ route('cashier.commissions') }}" class="space-y-2">
                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                        <div>
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control">
                                <option value="">Tous les types</option>
                                @foreach($types ?? [] as $type)
                                    @if(in_array($type, ['cash_pos', 'direct', 'indirect', 'leadership', 'sponsor']))
                                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type == 'cash_pos' ? 'CASH POS' : ucfirst($type) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status">Statut</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Tous les statuts</option>
                                @foreach($statuses ?? [] as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="source">Source</label>
                            <select name="source" id="source" class="form-control">
                                <option value="">Toutes les sources</option>
                                <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS</option>
                                <option value="mlm" {{ request('source') == 'mlm' ? 'selected' : '' }}>MLM</option>
                            </select>
                        </div>
                        <div><label for="date_from">Du</label><input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control"></div>
                        <div><label for="date_to">Au</label><input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control"></div>
                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-1">Appliquer</button>
                            <a href="{{ route('cashier.commissions', $filterQueryBase) }}" class="btn btn-outline btn-sm">Effacer</a>
                        </div>
                    </form>
                </div>
            </div>
            <button type="button" onclick="printPdfDirect()" class="btn btn-pdf btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Imprimer PDF
            </button>
            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle vente
            </a>
        </div>
    </div>

    {{-- STATISTIQUES --}}
    <div class="commission-stats">
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <div class="label">Total</div>
        </div>

        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['pos_total'] ?? 0, 2) }}</div>
            <div class="label">POS CASH</div>
        </div>

        <div class="commission-stat-card">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['mlm_total'] ?? 0, 2) }}</div>
            <div class="label">MLM</div>
        </div>

        <div class="commission-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number">{{ $stats['total_members'] ?? 0 }}</div>
            <div class="label">Membres</div>
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
                        <th>Source</th>
                        <th>Type</th>
                        <th>%</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th class="hidden sm:table-cell">Période</th>
                        <th class="hidden md:table-cell">Date</th>
                        <th class="hidden lg:table-cell">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($commissions ?? [] as $commission)
                        @php
                            $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];
                            if (in_array($commission->type, $excludedTypes)) { continue; }

                            $typeClass = 'type-badge-' . $commission->type;
                            if (!in_array($commission->type, ['cash_pos', 'direct', 'indirect', 'leadership', 'sponsor'])) {
                                $typeClass = 'type-badge-default';
                            }
                            $typeLabel = $commission->type_label ?? ucfirst(str_replace('_', ' ', $commission->type));
                            if ($commission->type == 'cash_pos') { $typeLabel = 'CASH POS'; }
                        @endphp
                        <tr class="commission-row"
                            data-search="{{ strtolower($commission->user?->name ?? '') }} {{ strtolower($commission->user?->sponsor_id ?? '') }} {{ strtolower($commission->type ?? '') }} {{ $commission->id }}"
                            data-type="{{ $commission->type }}"
                            data-status="{{ $commission->status }}"
                            data-source="{{ $commission->source }}"
                            data-user="{{ $commission->user_id }}"
                            data-date="{{ $commission->created_at->format('Y-m-d') }}">
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $commission->id }}</td>
                            <td>
                                <div class="font-medium text-sm">{{ $commission->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">Code: {{ $commission->user?->sponsor_id ?? 'N/A' }}</div>
                                @if($commission->fromUser)
                                    <div class="from-user-info">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        De: {{ $commission->fromUser->name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $commission->source == 'pos' ? 'badge-source-pos' : 'badge-source-mlm' }}">
                                    {{ $commission->source == 'pos' ? 'POS' : 'MLM' }}
                                </span>
                            </td>
                            <td>
                                <span class="type-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                            </td>
                            <td class="commission-percentage">{{ $commission->percentage ?? 0 }}%</td>
                            <td>
                                <span class="font-bold {{ $commission->amount > 0 ? 'text-[var(--ui-stat-success)]' : 'text-[var(--ui-stat-danger)]' }} text-sm">
                                    {{ $commission->amount > 0 ? '+' : '' }}${{ number_format($commission->amount, 2) }}
                                </span>
                                @if($commission->source == 'pos')
                                    <span class="badge-cash ml-1">CASH</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending' => ['label' => 'En attente', 'class' => 'badge-warning'],
                                        'approved' => ['label' => 'Approuvée', 'class' => 'badge-info'],
                                        'paid' => ['label' => 'Payée', 'class' => 'badge-success'],
                                        'rejected' => ['label' => 'Rejetée', 'class' => 'badge-danger'],
                                        'cancelled' => ['label' => 'Annulée', 'class' => 'badge-secondary'],
                                    ];
                                    $status = $statusMap[$commission->status] ?? ['label' => ucfirst($commission->status), 'class' => 'badge-secondary'];
                                @endphp
                                <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-tertiary)]">
                                {{ $commission->period ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell text-xs text-[var(--text-tertiary)]">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden lg:table-cell">
                                <a href="{{ route('cashier.commissions.show', $commission->id) }}" class="btn btn-primary btn-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Aucune commission trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="mt-4">
            @if(isset($commissions) && $commissions->hasPages())
                <x-salang-pagination :paginator="$commissions" />
            @endif
        </div>
    </div>
    
</div>

{{-- ✅ LOADER D'IMPRESSION (sans nouvelle fenêtre) --}}
<div id="printLoader" class="print-loader-overlay">
    <div class="print-loader-box">
        <div class="spinner"></div>
        <h3>Préparation du PDF...</h3>
        <p>Veuillez patienter pendant la génération du rapport</p>
    </div>
</div>

@push('scripts')
@include('cashier.partials.live-list-search', [
    'inputs' => ['searchInput', 'searchInputDesktop'],
    'replaceIds' => ['tableBody', 'paginationContainer', 'listSubtitle', 'listSubtitleMobile'],
])
<script>
// ============================================================
// IMPRIMER PDF DIRECTEMENT SANS NOUVELLE FENÊTRE
// ============================================================

function printPdfDirect() {
    // Afficher le loader
    const loader = document.getElementById('printLoader');
    loader.classList.add('active');
    
    // Récupérer les filtres
    const params = new URLSearchParams(window.location.search);
    params.delete('page');
    const dateFrom = params.get('date_from');
    const dateTo = params.get('date_to');
    const search = document.getElementById('searchInput')?.value;

    if (search) {
        params.set('search', search);
    } else {
        params.delete('search');
    }

    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const year = now.getFullYear();
    if (!dateFrom && !dateTo) params.set('period', year + '-' + month);

    const pdfUrl = '{{ route('cashier.commissions.export-pdf') }}?' + params.toString();

    // ✅ Télécharger le PDF en arrière-plan
    const xhr = new XMLHttpRequest();
    xhr.open('GET', pdfUrl, true);
    xhr.responseType = 'blob';
    
    xhr.onload = function() {
        // Cacher le loader
        loader.classList.remove('active');
        
        if (this.status === 200) {
            // Créer un objet URL pour le blob
            const blob = new Blob([this.response], { type: 'application/pdf' });
            const url = window.URL.createObjectURL(blob);
            
            // ✅ Imprimer directement sans ouvrir de fenêtre
            const iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.top = '-9999px';
            iframe.style.left = '-9999px';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = 'none';
            iframe.style.visibility = 'hidden';
            document.body.appendChild(iframe);
            
            iframe.onload = function() {
                try {
                    iframe.contentWindow.print();
                    // Nettoyer après l'impression
                    setTimeout(function() {
                        document.body.removeChild(iframe);
                        window.URL.revokeObjectURL(url);
                    }, 5000);
                } catch(e) {
                    console.error('Erreur impression:', e);
                    // Fallback: téléchargement
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'rapport_commissions_' + year + '-' + month + '.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(function() {
                        window.URL.revokeObjectURL(url);
                    }, 1000);
                }
            };
            
            iframe.src = url;
            
        } else {
            alert('Erreur lors du chargement du PDF. Veuillez réessayer.');
        }
    };
    
    xhr.onerror = function() {
        loader.classList.remove('active');
        alert('Erreur de connexion. Veuillez réessayer.');
    };
    
    xhr.send();
}

// Fonction pour fermer le loader (si besoin)
function closePrintLoader() {
    document.getElementById('printLoader').classList.remove('active');
}
</script>
@endpush
@endsection