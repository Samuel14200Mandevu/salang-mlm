@extends('cashier.layouts.app')

@section('title', 'Rapports journaliers')

@push('styles')
<style>
    /* ============================================================
       STAT CARDS – Identiques au dashboard
       ============================================================ */
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem 1.25rem;
        transition: background 0.2s ease;
    }

    .stat-card .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-today { background: rgba(30, 93, 173, 0.10); color: var(--primary); }
    .stat-icon-month { background: rgba(34, 197, 94, 0.10); color: #22c55e; }
    .stat-icon-pending { background: rgba(245, 158, 11, 0.10); color: #f59e0b; }
    .stat-icon-approved { background: rgba(34, 197, 94, 0.10); color: #16a34a; }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
    }

    /* ============================================================
       BOUTONS
       ============================================================ */
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

    .btn-danger {
        background: #b32a2a;
        color: #FFFFFF;
    }
    .btn-danger:hover {
        background: #8f2121;
    }

    .btn-danger-outline {
        background: transparent;
        color: #b32a2a;
        border: 1.5px solid var(--border-color);
    }
    .btn-danger-outline:hover {
        background: rgba(179, 42, 42, 0.06);
        border-color: #b32a2a;
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.15rem 0.5rem;
        font-size: 0.65rem;
    }

    /* ============================================================
       TABLE
       ============================================================ */
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.813rem;
    }

    .table thead th {
        text-align: left;
        padding: 0.6rem 0.75rem;
        font-weight: 600;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody td {
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover td {
        background: var(--bg-secondary);
    }

    /* ============================================================
       BADGES
       ============================================================ */
    .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .badge-success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-info { background: rgba(30, 93, 173, 0.10); color: var(--primary); }
    .badge-secondary { background: rgba(107, 114, 128, 0.12); color: #6b7280; }

    /* ============================================================
       FORMULAIRES
       ============================================================ */
    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md, 6px);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.813rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(30, 93, 173, 0.1);
    }

    /* ============================================================
       ALERTE
       ============================================================ */
    .alert-info {
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.20);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #2563eb;
        font-size: 0.875rem;
    }

    /* ============================================================
       MODAL DE SUPPRESSION
       ============================================================ */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .modal-overlay.active {
        opacity: 1;
    }
    .modal-dialog {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        max-width: 440px;
        width: 100%;
        padding: 1.5rem 1.5rem 1.25rem;
        text-align: center;
        transform: translateY(8px);
        transition: transform 0.15s ease;
    }
    .modal-overlay.active .modal-dialog {
        transform: translateY(0);
    }
    .modal-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 0.75rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(179, 42, 42, 0.10);
        color: #b32a2a;
    }
    .modal-icon svg { width: 1.5rem; height: 1.5rem; }
    .modal-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .modal-message {
        font-size: 0.875rem;
        color: var(--text-secondary);
        line-height: 1.5;
        margin-bottom: 1.25rem;
    }
    .modal-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    .modal-actions .btn { min-width: 100px; }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 640px) {
        .stat-card { padding: 0.75rem 1rem; }
        .stat-value { font-size: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.4rem 0.5rem; font-size: 0.7rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .btn-xs { padding: 0.1rem 0.375rem; font-size: 0.6rem; }
    }

    @media (max-width: 480px) {
        .stat-value { font-size: 1rem; }
        .stat-card { padding: 0.5rem 0.75rem; }
        .stat-card .stat-icon { width: 2rem; height: 2rem; }
        .stat-card .stat-icon svg { width: 1.25rem; height: 1.25rem; }
    }

    /* Liste — même panel que Historique ventes POS / Dépenses */
    .reports-history-panel {
        display: flex;
        flex-direction: column;
        min-height: 14rem;
        max-height: min(42vh, 22rem);
    }
    .reports-history-panel .table-wrap {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
    }
    .reports-history-panel .table-wrap tbody td[colspan] {
        height: 8rem;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
@php
    $searchOpen = filled(request('search'));
    $filterActive = filled(request('status'))
        || filled(request('date_from'))
        || filled(request('date_to'));
    $statusLabels = [
        'draft' => 'Brouillon',
        'submitted' => 'Soumis',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
    ];
    $filterButtonLabel = $statusLabels[request('status')] ?? 'Filtrer';
    $filterQueryBase = array_filter([
        'search' => request('search'),
    ], fn ($v) => filled($v));
    $reportsSubtitle = 'Rapports de caisse à soumettre en fin de journée'
        .(request('search') ? ' · « '.e(request('search')).' »' : '')
        .(isset($statusLabels[request('status')]) ? ' · '.$statusLabels[request('status')] : '');
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Rapports journaliers</h1>
                <p class="page-subtitle">{!! $reportsSubtitle !!}</p>
            </div>
            <div class="admin-title-banner__tools admin-title-banner__tools--reports-index">
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
                        <form method="GET" action="{{ route('cashier.reports.index') }}" class="space-y-2">
                            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                            <div>
                                <label for="status_m">Statut</label>
                                <select id="status_m" name="status" class="form-control">
                                    <option value="">Tous les statuts</option>
                                    @foreach($statusLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="date_from_m">Du</label>
                                <input type="date" id="date_from_m" name="date_from" value="{{ request('date_from') }}" class="form-control">
                            </div>
                            <div>
                                <label for="date_to_m">Au</label>
                                <input type="date" id="date_to_m" name="date_to" value="{{ request('date_to') }}" class="form-control">
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-1">Appliquer</button>
                                <a href="{{ route('cashier.reports.index', $filterQueryBase) }}" class="btn btn-outline btn-sm">Effacer</a>
                            </div>
                        </form>
                    </div>
                </div>
                <a href="{{ route('cashier.reports.create') }}"
                   class="btn btn-primary btn-sm btn-round-icon"
                   title="Nouveau rapport"
                   aria-label="Nouveau rapport">
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
            <form method="GET" action="{{ route('cashier.reports.index') }}" class="search-wrapper admin-shop-search">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                @if(request('date_from'))<input type="hidden" name="date_from" value="{{ request('date_from') }}">@endif
                @if(request('date_to'))<input type="hidden" name="date_to" value="{{ request('date_to') }}">@endif
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search" name="search" class="search-input"
                       placeholder="N° rapport…" autocomplete="off" value="{{ request('search') }}"
                       aria-label="Rechercher un rapport">
            </form>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP : titre | recherche + Filtrer + Nouveau --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Rapports journaliers
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">{!! $reportsSubtitle !!}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end">
            <form method="GET" action="{{ route('cashier.reports.index') }}" class="header-search">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                @if(request('date_from'))<input type="hidden" name="date_from" value="{{ request('date_from') }}">@endif
                @if(request('date_to'))<input type="hidden" name="date_to" value="{{ request('date_to') }}">@endif
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search" name="search" class="search-input"
                           placeholder="Rechercher un rapport" autocomplete="off"
                           value="{{ request('search') }}"
                           aria-label="Rechercher un rapport">
                </div>
            </form>
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button" @click="open = !open"
                        class="btn btn-outline btn-sm {{ $filterActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="open" aria-haspopup="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>{{ $filterButtonLabel }}</span>
                </button>
                <div x-show="open" x-cloak x-transition @click.outside="open = false" class="users-filter-menu users-filter-panel">
                    <form method="GET" action="{{ route('cashier.reports.index') }}" class="space-y-2">
                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                        <div>
                            <label for="status">Statut</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">Tous les statuts</option>
                                @foreach($statusLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_from">Du</label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="form-control">
                        </div>
                        <div>
                            <label for="date_to">Au</label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="form-control">
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-1">Appliquer</button>
                            <a href="{{ route('cashier.reports.index', $filterQueryBase) }}" class="btn btn-outline btn-sm">Effacer</a>
                        </div>
                    </form>
                </div>
            </div>
            <a href="{{ route('cashier.reports.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau rapport
            </a>
        </div>
    </div>

    {{-- STATISTIQUES --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Aujourd'hui</p>
                    <p class="stat-value text-[var(--primary)]">{{ $stats['today'] }}</p>
                </div>
                <div class="stat-icon stat-icon-today">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Ce mois</p>
                    <p class="stat-value text-[var(--ui-stat-success)]">{{ $stats['month'] }}</p>
                </div>
                <div class="stat-icon stat-icon-month">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">En attente</p>
                    <p class="stat-value text-[var(--ui-stat-warning)]">{{ $stats['pending'] }}</p>
                </div>
                <div class="stat-icon stat-icon-pending">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Approuvés</p>
                    <p class="stat-value text-[var(--ui-stat-success)]">{{ $stats['approved'] }}</p>
                </div>
                <div class="stat-icon stat-icon-approved">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- LISTE (div comme Historique ventes POS / Dépenses) --}}
    <div class="card p-3 sm:p-4 reports-history-panel">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 shrink-0">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Historique des rapports</h3>
        </div>

        <div class="table-wrap">
            <table class="table" id="reportsTable">
                <thead>
                    <tr>
                        <th>N° Rapport</th>
                        <th>Date</th>
                        <th class="hidden sm:table-cell">Caissier</th>
                        <th>Ventes</th>
                        <th>Dépenses</th>
                        <th>Solde net</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($reports as $report)
                        <tr class="cursor-pointer"
                            role="link"
                            tabindex="0"
                            data-href="{{ route('cashier.reports.show', $report->id) }}"
                            onclick="window.location.href=this.dataset.href"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href=this.dataset.href}">
                            <td class="font-mono text-[var(--primary)] text-xs">
                                {{ $report->report_number }}
                            </td>
                            <td class="text-xs">{{ $report->report_date->format('d/m/Y') }}</td>
                            <td class="hidden sm:table-cell text-xs">
                                {{ $report->user->name ?? 'N/A' }}
                            </td>
                            <td class="text-xs font-semibold text-[var(--text-primary)]">
                                ${{ number_format($report->total_sales, 2) }}
                            </td>
                            <td class="text-xs font-semibold text-[var(--ui-stat-danger)]">
                                ${{ number_format($report->total_expenses, 2) }}
                            </td>
                            <td class="font-semibold text-xs text-[var(--primary)]">
                                ${{ number_format($report->net_balance, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $report->status_badge_class }}">
                                    {{ $report->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                @if($searchOpen || $filterActive)
                                    <p class="text-base font-medium text-[var(--text-primary)]">Aucun rapport pour ces critères</p>
                                    <p class="text-sm text-[var(--text-tertiary)] mt-1">Modifiez la recherche ou les filtres</p>
                                    <a href="{{ route('cashier.reports.index') }}" class="btn btn-outline btn-sm mt-3">Effacer les filtres</a>
                                @else
                                    <p class="text-base font-medium text-[var(--text-primary)]">Aucun rapport enregistré</p>
                                    <p class="text-sm text-[var(--text-tertiary)] mt-1">L'historique des rapports de caisse apparaîtra ici</p>
                                    <a href="{{ route('cashier.reports.create') }}" class="btn btn-primary btn-sm mt-3">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Nouveau rapport
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="mt-3 shrink-0">
            @if($reports->hasPages())
                <x-salang-pagination :paginator="$reports" />
            @endif
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL DE CONFIRMATION DE SUPPRESSION
     ============================================================ --}}
<div id="deleteModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>

        <h3 class="modal-title">Confirmer la suppression</h3>

        <p class="modal-message">
            Êtes-vous sûr de vouloir supprimer le rapport
            <strong id="deleteReportNumber">—</strong> ?
            Cette action est <strong>irréversible</strong>.
        </p>

        <div class="modal-actions">
            <button type="button"
                    onclick="closeDeleteModal()"
                    class="btn btn-outline">
                Annuler
            </button>

            <form method="POST" id="deleteForm" action="" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer définitivement
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDeleteModal(id, reportNumber) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const label = document.getElementById('deleteReportNumber');

        // Construire l'URL de suppression
        form.action = `{{ url('/cashier/reports') }}/${id}`;
        label.textContent = reportNumber;

        modal.style.display = 'flex';
        requestAnimationFrame(() => modal.classList.add('active'));
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 150);
    }

    // Fermer en cliquant sur l'overlay
    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Fermer avec la touche Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>
@endpush