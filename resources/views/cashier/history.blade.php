{{-- resources/views/cashier/history.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .history-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .history-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem;
        text-align: center;
    }
    .history-stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .history-stat-card .number.green { color: #22c55e; }
    .history-stat-card .number.blue { color: #2563eb; }
    .history-stat-card .number.purple { color: #e8940f; }
    .history-stat-card .number.orange { color: #d97706; }
    .history-stat-card .number.red { color: #b32a2a; }
    .history-stat-card .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .history-stat-card .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 6px);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    .icon-green { background: rgba(34, 197, 94, 0.10); color: #22c55e; }
    .icon-blue { background: rgba(59, 130, 246, 0.10); color: #2563eb; }
    .icon-purple { background: rgba(139, 92, 246, 0.10); color: #e8940f; }
    .icon-orange { background: rgba(245, 158, 11, 0.10); color: #d97706; }
    .icon-red { background: rgba(179, 42, 42, 0.10); color: #b32a2a; }

    .history-table {
        width: 100%;
        font-size: 0.813rem;
        border-collapse: collapse;
    }
    .history-table th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
        white-space: nowrap;
    }
    .history-table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }
    .history-table tr:hover td {
        background: var(--bg-secondary);
    }
    .history-table tbody tr.history-row {
        cursor: pointer;
    }
    .history-table tbody tr.history-row:focus-visible {
        outline: 2px solid color-mix(in srgb, var(--primary, #1E5DAD) 45%, transparent);
        outline-offset: -2px;
    }
    .history-table .text-right { text-align: right; }
    .history-table .text-center { text-align: center; }
    .history-table .col-amount {
        text-align: left;
        font-weight: 650;
        color: var(--text-primary);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        vertical-align: bottom;
    }
    .history-member {
        font-size: 0.8125rem;
        font-weight: 650;
        color: var(--text-primary);
        letter-spacing: -0.01em;
    }

    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
        align-items: center;
    }
    .filter-section input {
        padding: 0.3rem 0.6rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 120px;
    }
    .filter-section input:focus {
        border-color: var(--primary);
        outline: none;
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

    .badge-paid {
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

    .badge-commission {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.15);
    }

    .badge-sponsor {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(31, 123, 77, 0.12);
        color: #1F7B4D;
        border: 1px solid rgba(31, 123, 77, 0.15);
    }

    .badge-membership {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(139, 92, 246, 0.12);
        color: #7c3aed;
        border: 1px solid rgba(139, 92, 246, 0.15);
    }

    .total-pv {
        font-weight: 600;
        color: #16a34a;
    }

    .commission-amount {
        font-weight: 600;
        color: #d97706;
    }

    .sponsor-amount {
        font-weight: 600;
        color: #1F7B4D;
    }

    .rest-amount.zero { color: #16a34a; }
    .rest-amount.positive { color: #b32a2a; }

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

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
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

    @media (max-width: 768px) {
        .history-stats { grid-template-columns: 1fr 1fr; }
        .history-table { font-size: 0.7rem; }
        .history-table th, .history-table td { padding: 0.3rem 0.4rem; }
        .filter-section { flex-direction: column; }
        .filter-section input, .filter-section button { width: 100%; min-width: unset; }
        .card { padding: 0.875rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
    }
</style>
@endpush

@section('title', 'Historique des ventes POS')

@section('content')
@php
    $searchOpen = filled(request('search'));
    $hasDateFilter = filled(request('date_from')) || filled(request('date_to'));
    $filterActive = $hasDateFilter;
    $historySubtitle = ($orders->total() ?? 0).' vente(s)'
        .($hasDateFilter ? ' · Période filtrée' : '')
        .(request('search') ? ' · Résultats pour "'.e(request('search')).'"' : '');
    $filterQueryBase = array_filter([
        'search' => request('search'),
    ], fn ($v) => filled($v));
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Historique des ventes POS</h1>
                <p id="listSubtitleMobile" class="page-subtitle">{!! $historySubtitle !!}</p>
            </div>
            <div class="admin-title-banner__tools admin-title-banner__tools--history-index">
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
                    <button type="button"
                            @click="open = !open"
                            class="admin-banner-icon-btn{{ $filterActive ? ' is-active' : '' }}"
                            :aria-expanded="open"
                            aria-label="Filtrer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition @click.outside="open = false" class="users-filter-menu users-filter-panel">
                        <form method="GET" action="{{ route('cashier.history') }}" class="space-y-2">
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
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
                                <a href="{{ route('cashier.history', $filterQueryBase) }}" class="btn btn-outline btn-sm">Effacer</a>
                            </div>
                        </form>
                    </div>
                </div>
                <a href="{{ route('cashier.history.export-pdf', request()->query()) }}" class="btn btn-sm btn-round-icon btn-pdf-icon" title="Rapport PDF" aria-label="Rapport PDF">
                    <x-pdf-icon size="lg" />
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
                <input type="text"
                       id="searchInput"
                       class="search-input"
                       placeholder="Rechercher (nom, téléphone, commande)"
                       autocomplete="off"
                       value="{{ request('search') }}">
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Historique des ventes POS
            </h1>
            <p id="listSubtitle" class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {!! $historySubtitle !!}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <div class="header-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInputDesktop"
                           class="search-input"
                           placeholder="Rechercher (nom, téléphone, commande)"
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button"
                        @click="open = !open"
                        class="btn btn-outline btn-sm {{ $filterActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="open"
                        aria-haspopup="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filtrer</span>
                </button>
                <div x-show="open" x-cloak x-transition @click.outside="open = false" class="users-filter-menu users-filter-panel">
                    <form method="GET" action="{{ route('cashier.history') }}" class="space-y-2">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
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
                            <a href="{{ route('cashier.history', $filterQueryBase) }}" class="btn btn-outline btn-sm">Effacer</a>
                        </div>
                    </form>
                </div>
            </div>
            <a href="{{ route('cashier.history.export-pdf', request()->query()) }}" class="btn btn-outline btn-sm">
                <x-pdf-icon />
                Rapport PDF
            </a>
        </div>
    </div>

    {{-- STATISTIQUES --}}
    <div class="history-stats">
        <div class="history-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="number blue">{{ $stats['total_orders'] ?? 0 }}</div>
            <div class="label">Total commandes</div>
        </div>

        <div class="history-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number green">${{ number_format($stats['total_sales'] ?? 0, 2) }}</div>
            <div class="label">Total encaissé</div>
        </div>

        <div class="history-stat-card">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number purple">{{ number_format($stats['total_pv'] ?? 0) }}</div>
            <div class="label">Total PV distribués</div>
        </div>

        <div class="history-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number orange">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <div class="label">Total commissions CASH</div>
        </div>
    </div>

    {{-- TABLEAU --}}
    <div class="card">
        <div class="table-wrap">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N° commande</th>
                        <th>Client</th>
                        <th>Membre</th>
                        <th class="col-amount">Prix</th>
                        <th class="text-right">Commission</th>
                        <th class="text-right">PV</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($orders ?? [] as $order)
                        @php
                            $totalProducts = $order->subtotal;

                            $client = $order->user;
                            $metaSponsorId = data_get($order->metadata, 'sponsor_id');
                            $sponsor = $metaSponsorId
                                ? ($sponsorsById[$metaSponsorId] ?? null)
                                : ($client?->parrain ?? ($client && $client->user_type === 'member' ? $client : null));

                            $sponsorAmount = (float) $order->commissions->where('type', 'sponsor')->sum('amount');
                            $cashAmount = (float) $order->commissions->where('type', 'cash_pos')->sum('amount');
                            $totalCommission = $sponsorAmount + $cashAmount;

                            $totalPV = $order->items->sum(function($item) {
                                return ($item->pv_value ?? 0) * $item->quantity;
                            });

                            $isMembership = ($order->source ?? '') === 'membership' || isset($order->metadata['is_membership']);

                            $teamPV = 0;
                            if ($isMembership && $metaSponsorId && $sponsor) {
                                $teamPV = $sponsor->team_pv ?? 0;
                            }

                            $detailUrl = route('cashier.orders.show', ['id' => $order->id, 'from' => 'history']);
                        @endphp
                        <tr class="history-row"
                            role="link"
                            tabindex="0"
                            data-href="{{ $detailUrl }}"
                            onclick="window.location.href=this.dataset.href"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href=this.dataset.href}">
                            <td class="whitespace-nowrap text-xs text-[var(--text-tertiary)]">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="font-mono text-xs text-[var(--primary)]">
                                #{{ $order->order_number }}
                            </td>
                            <td>
                                <span class="history-member">{{ $client?->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($sponsor)
                                    <span class="history-member">
                                        @if(!empty($sponsor->sponsor_id)){{ $sponsor->sponsor_id }} : @endif{{ $sponsor->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">—</span>
                                @endif
                            </td>
                            <td class="col-amount">
                                ${{ number_format($totalProducts, 2) }}
                            </td>
                            <td class="text-right">
                                @if($totalCommission > 0)
                                    <span class="commission-amount">${{ number_format($totalCommission, 2) }}</span>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div>
                                    <span class="total-pv">{{ number_format($totalPV) }} PV</span>
                                </div>
                                @if($isMembership && $teamPV > 0)
                                    <div class="text-xs text-[var(--text-secondary)]">
                                        Sponsor: {{ number_format($teamPV) }} PV
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucune vente POS</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">L'historique des ventes apparaîtra ici</p>
                                <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Faire une vente
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="mt-4">
            @if(isset($orders) && $orders->hasPages())
                <x-salang-pagination :paginator="$orders" />
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('cashier.partials.live-list-search', [
    'inputs' => ['searchInput', 'searchInputDesktop'],
    'replaceIds' => ['tableBody', 'paginationContainer', 'listSubtitle', 'listSubtitleMobile'],
])
@endpush