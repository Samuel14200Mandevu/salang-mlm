@extends('cashier.layouts.app')

@push('styles')
<style>
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
    .icon-purple { background: rgba(139, 92, 246, 0.10); color: #8b5cf6; }
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
        background: var(--primary-hover, #091E3B);
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
        background: var(--primary-hover, #091E3B);
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
    .type-badge-direct { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
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

    .pdf-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        padding: 1rem;
    }
    .pdf-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .pdf-modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-md, 8px);
        max-width: 1000px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--border-color);
        transform: scale(0.95);
        transition: transform 0.25s ease;
    }
    .pdf-modal-overlay.active .pdf-modal-box {
        transform: scale(1);
    }
    .pdf-modal-header {
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        background: var(--bg-secondary);
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .pdf-modal-header h2 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .pdf-modal-header .close-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-md, 6px);
        font-size: 1.25rem;
        transition: background 0.2s ease;
    }
    .pdf-modal-header .close-btn:hover {
        background: var(--bg-hover);
    }
    .pdf-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem;
        background: #ffffff;
        border-radius: 0 0 var(--radius-md) var(--radius-md);
    }
    .pdf-modal-footer {
        padding: 0.75rem 1.25rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-shrink: 0;
        background: var(--bg-secondary);
        border-radius: 0 0 var(--radius-md) var(--radius-md);
    }
    .pdf-modal-footer .btn {
        min-width: 100px;
        justify-content: center;
    }

    .pdf-report {
        font-family: 'Courier New', monospace;
        font-size: 11px;
        color: #1a1a1a;
        background: #ffffff;
    }
    .pdf-report .report-header {
        text-align: center;
        border-bottom: 2px solid var(--primary);
        padding-bottom: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .pdf-report .report-header h1 {
        font-size: 18px;
        font-weight: 900;
        color: var(--primary);
        letter-spacing: 1px;
        margin: 0;
    }
    .pdf-report .report-header .period {
        font-size: 12px;
        font-weight: 700;
        color: var(--primary);
        margin-top: 4px;
    }
    .pdf-report .report-header .date {
        font-size: 9px;
        color: #888;
        margin-top: 2px;
    }
    .pdf-report table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
    }
    .pdf-report table th {
        background: #f0f4f8;
        color: var(--primary);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 7px;
        padding: 4px;
        border: 1px solid #ddd;
        text-align: center;
    }
    .pdf-report table td {
        padding: 4px;
        border: 1px solid #ddd;
        text-align: center;
        vertical-align: middle;
    }
    .pdf-report table tr:nth-child(even) {
        background: #f9fafb;
    }
    .pdf-report .total-row td {
        font-weight: 700;
        background: #e8f0fe !important;
        border-top: 2px solid var(--primary);
    }
    .pdf-report .amount-positive {
        color: #16a34a;
        font-weight: 700;
    }
    .pdf-report .amount-negative {
        color: #b32a2a;
        font-weight: 700;
    }
    .pdf-report .report-footer {
        margin-top: 1rem;
        text-align: center;
        font-size: 8px;
        color: #999;
        border-top: 1px solid #ddd;
        padding-top: 0.5rem;
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
        .pdf-modal-box { max-height: 95vh; max-width: 100%; }
        .pdf-modal-body { padding: 0.75rem; }
        .pdf-modal-footer { flex-direction: column; }
        .pdf-modal-footer .btn { width: 100%; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
    }
</style>
@endpush

@section('title', 'Toutes les commissions')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Toutes les commissions
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Commissions POS (CASH) et MLM
            </p>
        </div>
        <div class="flex gap-2 flex-wrap header-actions">
            <button onclick="openPdfModal()" class="btn btn-pdf btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Aperçu PDF
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

    {{-- FILTRES --}}
    <div class="card">
        <form method="GET" action="{{ route('cashier.commissions') }}" class="filter-section" id="filterForm">
            <div class="search-wrapper">
                <span class="search-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" id="searchInput" class="search-input" placeholder="Rechercher..." autocomplete="off">
                <button type="button" id="clearSearch" class="clear-search">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <select name="type" id="typeFilter">
                <option value="">Tous les types</option>
                @foreach($types ?? [] as $type)
                    @if(in_array($type, ['cash_pos', 'direct', 'indirect', 'leadership', 'sponsor']))
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ $type == 'cash_pos' ? 'CASH POS' : ucfirst($type) }}
                        </option>
                    @endif
                @endforeach
            </select>

            <select name="status" id="statusFilter">
                <option value="">Tous les statuts</option>
                @foreach($statuses ?? [] as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="source" id="sourceFilter">
                <option value="">Toutes les sources</option>
                <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS</option>
                <option value="mlm" {{ request('source') == 'mlm' ? 'selected' : '' }}>MLM</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Date début" id="dateFrom">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Date fin" id="dateTo">

            <button type="submit" class="btn-filter">Filtrer</button>
            <a href="{{ route('cashier.commissions') }}" class="btn-reset">↺ Réinitialiser</a>
        </form>
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
                <tbody id="commissionsTable">
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
                                <span class="font-bold {{ $commission->amount > 0 ? 'text-[#16a34a]' : 'text-[#b32a2a]' }} text-sm">
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

        @if(isset($commissions) && $commissions->hasPages())
            <div class="mt-4">{{ $commissions->links() }}</div>
        @endif
    </div>

    {{-- LÉGENDE --}}
    <div class="card" style="background: rgba(34, 197, 94, 0.03); border-color: rgba(34, 197, 94, 0.10);">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-[#16a34a] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h4 class="font-semibold text-[var(--text-primary)] text-sm">Légende des commissions</h4>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mt-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #16a34a;"></span>
                        <span class="text-[var(--text-secondary)]">POS CASH</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #6366f1;"></span>
                        <span class="text-[var(--text-secondary)]">Direct Bonus</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #2563eb;"></span>
                        <span class="text-[var(--text-secondary)]">Indirect Bonus</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #d97706;"></span>
                        <span class="text-[var(--text-secondary)]">Leadership Bonus</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: #16a34a;"></span>
                        <span class="text-[var(--text-secondary)]">Sponsor Bonus</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PDF --}}
<div id="pdfModal" class="pdf-modal-overlay">
    <div class="pdf-modal-box">
        <div class="pdf-modal-header">
            <h2>
                <svg class="w-5 h-5 text-[#b32a2a]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Aperçu du rapport des commissions
            </h2>
            <button onclick="closePdfModal()" class="close-btn">✕</button>
        </div>
        <div id="pdfModalBody" class="pdf-modal-body">
            <div class="pdf-report" id="pdfReportContent">
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <p class="mt-2 text-gray-500">Génération du rapport...</p>
                </div>
            </div>
        </div>
        <div class="pdf-modal-footer">
            <button onclick="closePdfModal()" class="btn btn-outline btn-sm">Fermer</button>
            <button onclick="downloadPdf()" class="btn btn-pdf btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Télécharger PDF
            </button>
            <button onclick="printPdf()" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');
    const rows = document.querySelectorAll('#commissionsTable .commission-row');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const sourceFilter = document.getElementById('sourceFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    let searchTimeout = null;

    function filterRows() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const type = typeFilter.value;
        const status = statusFilter.value;
        const source = sourceFilter.value;
        const dateFromVal = dateFrom.value;
        const dateToVal = dateTo.value;

        let visibleCount = 0;

        rows.forEach(function(row) {
            const searchData = row.dataset.search || '';
            const rowType = row.dataset.type || '';
            const rowStatus = row.dataset.status || '';
            const rowSource = row.dataset.source || '';
            const rowDate = row.dataset.date || '';

            let show = true;

            if (searchTerm && !searchData.includes(searchTerm)) show = false;
            if (type && rowType !== type) show = false;
            if (status && rowStatus !== status) show = false;
            if (source && rowSource !== source) show = false;
            if (dateFromVal && rowDate < dateFromVal) show = false;
            if (dateToVal && rowDate > dateToVal) show = false;

            if (show) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        clearSearchBtn.classList.toggle('visible', searchTerm.length > 0);

        const noResultMsg = document.querySelector('#noResultsMsg');
        if (visibleCount === 0 && rows.length > 0) {
            if (!noResultMsg) {
                const msg = document.createElement('tr');
                msg.id = 'noResultsMsg';
                msg.innerHTML = `
                    <td colspan="10" class="text-center py-8 text-[var(--text-secondary)]">
                        <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-base font-medium text-[var(--text-primary)]">Aucun résultat</p>
                        <p class="text-sm text-[var(--text-tertiary)] mt-1">Aucune commission ne correspond à votre recherche</p>
                    </td>
                `;
                document.querySelector('#commissionsTable').appendChild(msg);
            }
        } else {
            if (noResultMsg) noResultMsg.remove();
        }
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterRows, 200);
    });

    [typeFilter, statusFilter, sourceFilter, dateFrom, dateTo].forEach(function(el) {
        if (el) el.addEventListener('change', filterRows);
    });

    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchBtn.classList.remove('visible');
        filterRows();
        searchInput.focus();
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            clearSearchBtn.classList.remove('visible');
            filterRows();
            searchInput.blur();
        }
    });

    filterRows();
});

// ============================================================
// MODAL PDF
// ============================================================
let currentPdfUrl = '';

function openPdfModal() {
    const modal = document.getElementById('pdfModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    generateReportContent();
}

function closePdfModal() {
    document.getElementById('pdfModal').classList.remove('active');
    document.body.style.overflow = '';
}

function generateReportContent() {
    const params = new URLSearchParams();
    const type = document.getElementById('typeFilter')?.value;
    const status = document.getElementById('statusFilter')?.value;
    const source = document.getElementById('sourceFilter')?.value;
    const dateFrom = document.getElementById('dateFrom')?.value;
    const dateTo = document.getElementById('dateTo')?.value;
    const search = document.getElementById('searchInput')?.value;

    if (type) params.append('type', type);
    if (status) params.append('status', status);
    if (source) params.append('source', source);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (search) params.append('search', search);

    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const year = now.getFullYear();
    if (!dateFrom && !dateTo) params.append('period', year + '-' + month);

    currentPdfUrl = '{{ route('cashier.commissions.export-pdf') }}?' + params.toString();

    fetch(currentPdfUrl, {
        headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('pdfReportContent').innerHTML = html;
    })
    .catch(error => {
        document.getElementById('pdfReportContent').innerHTML = `
            <div class="text-center py-8 text-[#b32a2a]">
                <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Erreur lors du chargement du rapport</p>
                <p class="text-sm text-gray-400">${error.message}</p>
            </div>
        `;
    });
}

function downloadPdf() {
    if (currentPdfUrl) {
        const url = new URL(currentPdfUrl);
        url.searchParams.set('download', 'true');
        window.open(url.toString(), '_blank');
    }
}

function printPdf() {
    if (currentPdfUrl) {
        const win = window.open(currentPdfUrl, '_blank');
        win.onload = function() { win.print(); };
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePdfModal();
});

document.getElementById('pdfModal').addEventListener('click', function(e) {
    if (e.target === this) closePdfModal();
});
</script>
@endpush
@endsection