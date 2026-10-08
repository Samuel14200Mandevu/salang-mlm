{{-- resources/views/admin/pv/commission-history.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    box-sizing: border-box;
}

body {
    background: #F4F5F7;
    color: #1A1D23;
}

/* HEADER */
.page-header {
    padding: 1.5rem 0 1rem 0;
    border-bottom: 1px solid #DDE0E3;
}

.page-header .top-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.page-title {
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: #1A1D23;
    margin: 0;
}

.page-subtitle {
    font-size: 0.875rem;
    color: #5A626A;
    margin-top: 0.125rem;
}

.header-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* CARTES */
.card {
    background: #F8F9FA;
    border: 1px solid #DDE0E3;
    border-radius: 6px;
    padding: 1.25rem;
}

.card-title {
    font-size: 0.813rem;
    font-weight: 600;
    color: #5A626A;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* BOUTONS */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.813rem;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    transition: background 0.2s ease;
}

.btn-primary {
    background: #1A1D23;
    color: #FFFFFF;
    border-color: #1A1D23;
}

.btn-primary:hover {
    background: #2D333B;
    border-color: #2D333B;
}

.btn-warning {
    background: #B54708;
    color: #FFFFFF;
    border-color: #B54708;
}

.btn-warning:hover {
    background: #93370A;
    border-color: #93370A;
}

.btn-outline {
    background: transparent;
    color: #1A1D23;
    border-color: #DDE0E3;
}

.btn-outline:hover {
    background: #EEF0F2;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
}

.btn .icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* BADGES AVEC COULEURS */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.688rem;
    font-weight: 500;
    border: 1px solid transparent;
}

.badge-direct {
    background: rgba(79, 70, 229, 0.12);
    color: #4F46E5;
}

.badge-indirect {
    background: rgba(37, 99, 235, 0.12);
    color: #2563EB;
}

.badge-leadership {
    background: rgba(166, 90, 14, 0.12);
    color: #A65A0E;
}

.badge-cash {
    background: rgba(22, 163, 74, 0.12);
    color: #16a34a;
}

/* FORMULAIRES */
.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #DDE0E3;
    border-radius: 6px;
    background: #F8F9FA;
    color: #1A1D23;
    font-size: 0.875rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    border-color: #1A1D23;
    outline: none;
}

/* TABLEAU */
.table-wrap {
    overflow-x: auto;
    width: 100%;
    max-width: 100%;
}

@media (max-width: 767px) {
    .table-wrap {
        overflow-x: visible;
    }
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.table th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #5A626A;
    border-bottom: 2px solid #DDE0E3;
    background: #F8F9FA;
}

.table td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #E8EBEE;
    color: #1A1D23;
}

.table tr:hover td {
    background: #EEF0F2;
}

.table .text-right {
    text-align: right;
}

.table .text-center {
    text-align: center;
}

/* GROUPES DE BONUS AVEC COULEURS */
.bonus-group-header {
    border-bottom: 2px solid #DDE0E3 !important;
}

.bonus-group-header td {
    font-weight: 600;
    font-size: 0.813rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.75rem 0.75rem !important;
}

/* Couleurs des en-têtes de groupe */
.bonus-group-direct .bonus-group-header td {
    background: rgba(79, 70, 229, 0.08) !important;
    color: #4F46E5;
}

.bonus-group-indirect .bonus-group-header td {
    background: rgba(37, 99, 235, 0.08) !important;
    color: #2563EB;
}

.bonus-group-leadership .bonus-group-header td {
    background: rgba(166, 90, 14, 0.08) !important;
    color: #A65A0E;
}

.bonus-group-cash_pos .bonus-group-header td {
    background: rgba(22, 163, 74, 0.08) !important;
    color: #16a34a;
}

.bonus-group-total {
    font-weight: 600;
}

.bonus-group-total td {
    padding: 0.5rem 0.75rem !important;
    border-top: 2px solid #DDE0E3;
}

/* Couleurs des sous-totaux */
.bonus-group-direct .bonus-group-total td {
    background: rgba(79, 70, 229, 0.05) !important;
}

.bonus-group-indirect .bonus-group-total td {
    background: rgba(37, 99, 235, 0.05) !important;
}

.bonus-group-leadership .bonus-group-total td {
    background: rgba(166, 90, 14, 0.05) !important;
}

.bonus-group-cash_pos .bonus-group-total td {
    background: rgba(22, 163, 74, 0.05) !important;
}

/* SOMMAIRE AVEC COULEURS */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.summary-item {
    background: #F8F9FA;
    border: 1px solid #DDE0E3;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    text-align: center;
}

.summary-item .amount {
    font-size: 1.25rem;
    font-weight: 700;
}

.summary-item .label {
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #8E959C;
    margin-top: 0.125rem;
}

/* Couleurs des montants dans le sommaire */
.direct-color { color: #4F46E5; }
.indirect-color { color: #2563EB; }
.leadership-color { color: #A65A0E; }
.cash-color { color: #16a34a; }

.summary-item.total {
    background: #1A1D23;
    color: #FFFFFF;
    border-color: #1A1D23;
}

.summary-item.total .label {
    color: rgba(255, 255, 255, 0.7);
}

.total-color { color: #FFFFFF; }

/* ALERTES */
.alert {
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    border: 1px solid transparent;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: #ECFDF3;
    border-color: #A6F4C5;
    color: #067647;
}

.alert-error {
    background: #FEF3F2;
    border-color: #FECDCA;
    color: #B42318;
}

.alert-warning {
    background: #FFFAEB;
    border-color: #FEDF89;
    color: #B54708;
}

/* MODAL DE CONFIRMATION */
.confirm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
}

.confirm-overlay.active { display: flex; }

.confirm-box {
    background: #F8F9FA;
    border-radius: 10px;
    padding: 1.5rem;
    max-width: 440px;
    width: 95%;
    border: 1px solid #DDE0E3;
}

.confirm-box h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1A1D23;
    text-align: center;
    margin-bottom: 0.5rem;
}

.confirm-box p {
    color: #5A626A;
    font-size: 0.938rem;
    text-align: center;
    margin-bottom: 0.5rem;
}

.confirm-box .warning {
    margin: 1rem 0 1.5rem 0;
    padding: 0.625rem 0.875rem;
    background: #FEF3F2;
    border-radius: 6px;
    border-left: 3px solid #B91C1C;
    font-size: 0.813rem;
    color: #B91C1C;
}

.confirm-box .actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}

.confirm-box .actions .btn { min-width: 100px; }

/* UTILITAIRES */
.flex { display: flex; }
.flex-wrap { flex-wrap: wrap; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.gap-4 { gap: 1rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.mb-4 { margin-bottom: 1rem; }
.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.text-muted { color: #8E959C; }
.text-secondary { color: #5A626A; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }

/* FOOTER */
.footer-links {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #DDE0E3;
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    font-size: 0.813rem;
}

.footer-links a {
    color: #5A626A;
    text-decoration: none;
}

.footer-links a:hover {
    color: #1A1D23;
    text-decoration: underline;
}

/* RESPONSIVE */
@media (max-width: 640px) {
    .summary-grid { grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
    .page-header .top-row { flex-direction: column; align-items: stretch; }
    .header-actions { justify-content: stretch; }
    .header-actions .btn { flex: 1; }
    .card { padding: 1rem; }
    .confirm-box .actions { flex-direction: column; }
    .confirm-box .actions .btn { width: 100%; }
    .footer-links { flex-wrap: wrap; gap: 1rem; }
}

.commission-period-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.commission-period-card-head .card-title {
    margin-bottom: 0;
    flex: 1;
    min-width: 0;
}

.commission-period-card-tools {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.4rem;
    flex-shrink: 0;
}

.commission-card-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    padding: 0;
    border-radius: 999px;
    border: 1px solid var(--border-color, #DDE0E3);
    background: var(--bg-card, #F8F9FA);
    color: var(--text-primary, #1A1D23);
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
}

.commission-card-icon-btn:hover {
    background: var(--bg-hover, #EEF0F2);
}

.commission-card-icon-btn.is-active {
    border-color: var(--color-primary-500, #2563eb);
    color: var(--color-primary-600, #1d4ed8);
    background: color-mix(in srgb, var(--color-primary-500, #2563eb) 8%, var(--bg-card, #fff));
}

.commission-filter-panel {
    display: none;
    margin-bottom: 1rem;
}

.commission-filter-panel.is-open {
    display: block !important;
    animation: commission-filter-fade 0.22s ease forwards;
}

@keyframes commission-filter-fade {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 767px) {
    .admin-app .admin-period-panel {
        display: none;
        width: 100%;
        box-sizing: border-box;
        margin: 0 0 1rem;
        padding: 0.5rem;
        border-radius: 12px;
        border: 1px solid var(--border-light, #DDE0E3);
        background: var(--bg-card, #fff);
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
        max-height: min(14rem, 45vh);
        overflow: auto;
    }

    .admin-app .admin-period-panel.is-open {
        display: block;
    }

    .admin-app .admin-period-panel__title {
        margin: 0 0 0.35rem;
        padding: 0.15rem 0.45rem;
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-tertiary, #5A626A);
    }

    .admin-app .admin-period-panel__list {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .admin-app .admin-period-panel__option {
        display: block;
        padding: 0.45rem 0.55rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-primary, #1A1D23);
        text-decoration: none;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .admin-app .admin-period-panel__option:hover {
        background: var(--bg-secondary, #EEF0F2);
    }

    .admin-app .admin-period-panel__option.is-active {
        background: color-mix(in srgb, var(--color-primary-500, #2563eb) 12%, var(--bg-card, #fff));
        color: var(--color-primary-700, #1d4ed8);
    }

    .admin-app .commission-card-icon-btn.is-open {
        border-color: var(--color-primary-500, #2563eb);
        color: var(--color-primary-600, #1d4ed8);
    }

    .admin-app .main-content table.commission-history-table.admin-mobile-table tbody td.commission-history-action {
        display: none !important;
    }

    .admin-app .main-content table.commission-history-table.admin-mobile-table tbody td.commission-history-desc {
        display: block !important;
        text-align: left;
        padding-top: 0.55rem !important;
        margin-top: 0.2rem;
        border-top: 1px dashed var(--border-light, #DDE0E3);
        line-height: 1.45;
    }

    .admin-app .main-content table.commission-history-table.admin-mobile-table tbody td.commission-history-desc::before {
        display: block;
        width: 100%;
        margin-bottom: 0.35rem;
    }

    .admin-app .main-content table.commission-history-table.admin-mobile-table tbody tr:not([class*='bonus-group']) td:not(.commission-history-desc):not(.commission-history-action) {
        text-align: right;
        word-break: break-word;
    }

    .admin-app .main-content .card:has(.commission-history-table) {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .admin-app .commission-history-table .badge {
        background: transparent !important;
        border-color: transparent !important;
        padding: 0 !important;
        border-radius: 0 !important;
        font-weight: 600;
    }

    .admin-app .commission-history-table .badge-direct { color: #4F46E5; }
    .admin-app .commission-history-table .badge-indirect { color: #2563EB; }
    .admin-app .commission-history-table .badge-leadership { color: #A65A0E; }
    .admin-app .commission-history-table .badge-cash_pos,
    .admin-app .commission-history-table .badge-cash { color: #16a34a; }
}

@media (min-width: 768px) {
    .admin-app .commission-period-panel-mobile,
    .admin-app .commission-history-mobile-filters {
        display: none !important;
    }

    .admin-app .commission-history-desktop-head {
        display: block;
    }

    .admin-app .commission-history-desktop-filters {
        display: flex !important;
    }

    .admin-app .commission-history-table .badge {
        padding: 0.125rem 0.5rem !important;
        border-radius: 9999px !important;
    }

    .admin-app .commission-history-table .badge-direct {
        background: rgba(79, 70, 229, 0.12) !important;
        border-color: transparent !important;
    }

    .admin-app .commission-history-table .badge-indirect {
        background: rgba(37, 99, 235, 0.12) !important;
        border-color: transparent !important;
    }

    .admin-app .commission-history-table .badge-leadership {
        background: rgba(166, 90, 14, 0.12) !important;
        border-color: transparent !important;
    }

    .admin-app .commission-history-table .badge-cash_pos {
        background: rgba(22, 163, 74, 0.12) !important;
        border-color: transparent !important;
    }

    .admin-app .main-content .card:has(.commission-history-table) {
        padding: 1.25rem !important;
    }
}

.commission-history-desktop-head {
    display: none;
}
</style>
@endpush

@push('admin_mobile_greeting')
    @include('admin.layouts.partials.mobile-greeting', [
        'title' => 'Commissions historiques',
        'subtitle' => 'Recalcul à partir des PV historiques',
    ])
@endpush

@section('content')
<div class="commission-history-page">

<div class="page-header commission-history-desktop-head">
    <div class="top-row">
        <div>
            <h1 class="page-title">Commissions historiques</h1>
            <p class="page-subtitle">Recalcul à partir des PV historiques</p>
        </div>
        <div class="header-actions">
            @include('admin.pv.partials.commission-history-header-actions', [
                'selectedPeriod' => $selectedPeriod,
                'userId' => $userId,
            ])
        </div>
    </div>
</div>

<div class="admin-page-header page-header animate-fadeInUp md:hidden">
    <div class="admin-mobile-page-head">
        <h1 class="page-title">Commissions historiques</h1>
        <p class="page-subtitle">Recalcul à partir des PV historiques</p>
    </div>
    <div class="admin-page-header-actions header-actions flex flex-wrap gap-2">
        @include('admin.pv.partials.commission-history-header-actions', [
            'selectedPeriod' => $selectedPeriod,
            'userId' => $userId,
        ])
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

<div class="summary-grid">
    <div class="summary-item"><div class="amount direct-color">${{ number_format($totals['direct'] ?? 0, 2) }}</div><div class="label">Direct</div></div>
    <div class="summary-item"><div class="amount indirect-color">${{ number_format($totals['indirect'] ?? 0, 2) }}</div><div class="label">Indirect</div></div>
    <div class="summary-item"><div class="amount leadership-color">${{ number_format($totals['leadership'] ?? 0, 2) }}</div><div class="label">Leadership</div></div>
    <div class="summary-item"><div class="amount cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</div><div class="label">Cash POS</div></div>
    <div class="summary-item total"><div class="amount total-color">${{ number_format($totals['total'] ?? 0, 2) }}</div><div class="label">Total</div></div>
</div>

<div class="card">
    @php
        $hasPeriodFilter = filled($selectedPeriod);
        $hasMemberFilter = filled($prefillMemberQ ?? '');
        $commissionFilterQuery = array_filter([
            'member_q' => request('member_q') ?: ($prefillMemberQ ?? null),
            'user_id' => request('user_id'),
        ], fn ($value) => filled($value));
    @endphp
    <form method="GET" id="commissionHistoryFilters">
        <div class="commission-history-desktop-filters hidden md:flex flex-wrap items-center justify-between mb-4">
            <div class="card-title mb-0">Commissions par période</div>
            <div class="flex gap-2 flex-wrap items-center">
                <select name="period" class="form-control" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
                    <option value="">Toutes les périodes</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->period }}" {{ $selectedPeriod == $p->period ? 'selected' : '' }}>
                            {{ $p->period }}
                        </option>
                    @endforeach
                </select>
                <select name="user_id" class="form-control" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
                    <option value="">Tous les utilisateurs</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (string) $userId === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="commission-history-mobile-filters md:hidden">
        <div class="commission-period-filter-wrap" data-admin-commission-period-picker>
        <div class="commission-period-card-head">
            <div class="card-title">Commissions par période</div>
            <div class="commission-period-card-tools">
                <button type="button"
                        class="commission-card-icon-btn {{ $hasPeriodFilter ? 'has-filter is-active' : '' }}"
                        id="commissionPeriodFilterToggle"
                        data-admin-period-toggle
                        aria-expanded="false"
                        aria-controls="commissionPeriodFilterPanel"
                        aria-label="Filtrer par période"
                        title="Période">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </button>
                <button type="button"
                        class="commission-card-icon-btn {{ $hasMemberFilter ? 'has-filter is-active' : '' }}"
                        id="commissionMemberSearchToggle"
                        aria-expanded="{{ $hasMemberFilter ? 'true' : 'false' }}"
                        aria-controls="commissionMemberSearchPanel"
                        aria-label="Rechercher par code ou nom"
                        title="Rechercher">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="commissionPeriodFilterPanel"
             class="admin-period-panel commission-period-panel-mobile"
             data-admin-period-panel
             role="dialog"
             aria-label="Filtrer par période"
             aria-hidden="true">
            <p class="admin-period-panel__title">Période</p>
            <div class="admin-period-panel__list">
                <a href="{{ route('admin.pv.commission-history.index', $commissionFilterQuery) }}"
                   class="admin-period-panel__option {{ !$hasPeriodFilter ? 'is-active' : '' }}">
                    Toutes les périodes
                </a>
                @foreach($periods as $p)
                    @php
                        $periodValue = $p->period;
                        $periodLabel = $periodValue;
                        if (preg_match('/^(\d{4})-(\d{1,2})/', (string) $periodValue, $periodParts)) {
                            $periodLabel = str_pad($periodParts[2], 2, '0', STR_PAD_LEFT) . '/' . $periodParts[1];
                        }
                    @endphp
                    <a href="{{ route('admin.pv.commission-history.index', array_merge($commissionFilterQuery, ['period' => $periodValue])) }}"
                       class="admin-period-panel__option {{ $selectedPeriod == $periodValue ? 'is-active' : '' }}">
                        {{ $periodLabel }}
                    </a>
                @endforeach
            </div>
        </div>
        </div>

        <div id="commissionMemberSearchPanel"
             class="commission-filter-panel admin-search-panel {{ $hasMemberFilter ? 'is-open' : '' }}"
             aria-hidden="{{ $hasMemberFilter ? 'false' : 'true' }}">
            <label class="form-label text-xs font-semibold uppercase tracking-wide text-secondary" for="commissionMemberSearchInput">Membre (code ou nom)</label>
            <div class="search-wrapper admin-shop-search">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="commissionMemberSearchInput"
                       name="member_q"
                       class="form-control search-input w-full pl-9 commission-filter-mobile-only"
                       placeholder="Code sponsor ou nom"
                       value="{{ $prefillMemberQ ?? '' }}"
                       autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-full mt-2">Appliquer</button>
        </div>
        </div>
    </form>

    @if($commissions->count() > 0)
    <div class="table-wrap">
        <table class="table commission-history-table">
            <thead>
                <tr>
                    <th>Période</th>
                    <th>Type</th>
                    <th>Bénéficiaire</th>
                    <th>De</th>
                    <th class="text-right">Montant</th>
                    <th class="text-center">Taux</th>
                    <th>PV utilisé</th>
                    <th>Description</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $bonusOrder = ['direct', 'indirect', 'leadership', 'cash_pos'];
                    $bonusLabels = [
                        'direct' => 'Direct Bonus',
                        'indirect' => 'Indirect Bonus',
                        'leadership' => 'Leadership Bonus',
                        'cash_pos' => 'Cash POS Bonus'
                    ];
                    
                    $groupedCommissions = [];
                    foreach ($bonusOrder as $type) {
                        $groupedCommissions[$type] = $commissions->where('type', $type);
                    }
                @endphp
                
                @foreach($bonusOrder as $type)
                    @php
                        $typeCommissions = $groupedCommissions[$type];
                        $typeTotal = $typeCommissions->sum('amount');
                    @endphp
                    
                    @if($typeCommissions->count() > 0)
                        <tr class="bonus-group-{{ $type }}">
                            <td colspan="9" class="bonus-group-header">
                                {{ $bonusLabels[$type] ?? ucfirst($type) }}
                                <span style="float: right; font-weight: 700;">
                                    Total: ${{ number_format($typeTotal, 2) }}
                                </span>
                            </td>
                        </tr>
                        
                        @foreach($typeCommissions as $commission)
                        <tr>
                            <td>{{ $commission->period }}</td>
                            <td>
                                <span class="badge badge-{{ $commission->type }}">
                                    {{ ucfirst($commission->type) }}
                                </span>
                            </td>
                            <td>{{ $commission->user?->name ?? 'N/A' }}</td>
                            <td>{{ $commission->fromUser?->name ?? 'N/A' }}</td>
                            <td class="text-right font-medium">${{ number_format($commission->amount, 2) }}</td>
                            <td class="text-center">{{ $commission->percentage }}%</td>
                            <td class="text-center">{{ number_format($commission->pv_used, 1) }}</td>
                            <td class="text-sm text-secondary commission-history-desc" data-label="Description">{{ $commission->description }}</td>
                            <td class="text-center commission-history-action">
                                <a href="{{ route('admin.pv.commission-history.details', $commission->period) }}" class="btn btn-primary btn-sm">
                                    <svg class="icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Détails
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        
                        <tr class="bonus-group-{{ $type }}">
                            <td colspan="4" class="bonus-group-total" style="text-align: right; font-weight: 600;">
                                Sous-total {{ $bonusLabels[$type] ?? ucfirst($type) }}
                            </td>
                            <td class="text-right group-total-amount" style="font-weight: 700; 
                                @if($type == 'direct') color: #4F46E5;
                                @elseif($type == 'indirect') color: #2563EB;
                                @elseif($type == 'leadership') color: #A65A0E;
                                @elseif($type == 'cash_pos') color: #16a34a;
                                @endif
                            ">
                                ${{ number_format($typeTotal, 2) }}
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background: #1A1D23; color: #FFFFFF;">
                    <td colspan="4" class="text-right">Total Général</td>
                    <td class="text-right" style="color: #FFFFFF;">${{ number_format($totals['total'], 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="text-center text-muted py-4">
        Aucune commission historique. Cliquez sur "Recalculer" pour générer.
    </div>
    @endif
</div>

<div id="recalculateModal" class="confirm-overlay">
    <div class="confirm-box">
        <h3>Recalculer les commissions</h3>
        <p>Recalcul à partir des PV historiques.</p>
        <form method="POST" action="{{ route('admin.pv.commission-history.recalculate') }}">
            @csrf
            <div style="margin: 1rem 0;">
                <label style="display:block; font-size:0.813rem; font-weight:500; margin-bottom:0.25rem;">Période</label>
                <select name="period" class="form-control">
                    <option value="">Toutes les périodes</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->period }}">{{ $p->period }}</option>
                    @endforeach
                </select>
                <span style="font-size:0.75rem; color: #8E959C; display:block; margin-top:0.25rem;">
                    Laissez vide pour toutes les périodes
                </span>
            </div>
            <div style="margin: 1rem 0;">
                <label style="display:block; font-size:0.813rem; font-weight:500; margin-bottom:0.25rem;">Utilisateur</label>
                <select name="user_id" class="form-control">
                    <option value="">Tous les utilisateurs</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
                <span style="font-size:0.75rem; color: #8E959C; display:block; margin-top:0.25rem;">
                    Laissez vide pour tous les utilisateurs
                </span>
            </div>
            <div class="warning">Les commissions existantes seront supprimées puis recréées.</div>
            <div class="actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('recalculateModal')">Annuler</button>
                <button type="submit" class="btn btn-warning">
                    <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Recalculer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRecalculateModal() {
    document.getElementById('recalculateModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('recalculateModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal('recalculateModal');
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.confirm-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

(function initCommissionPeriodFilters() {
    var mq = window.matchMedia('(max-width: 767px)');

    function syncCommissionFilterFields() {
        var mobile = mq.matches;
        document.querySelectorAll('.commission-history-desktop-filters select').forEach(function (el) {
            el.disabled = mobile;
        });
        document.querySelectorAll('.commission-history-mobile-filters input, .commission-history-mobile-filters select').forEach(function (el) {
            el.disabled = !mobile;
        });
    }

    function setSearchPanelOpen(panel, toggle, open) {
        if (!panel || !toggle) {
            return;
        }
        panel.classList.toggle('is-open', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.classList.toggle('is-active', open || toggle.classList.contains('has-filter'));
        if (open) {
            var focusEl = panel.querySelector('input');
            if (focusEl) {
                window.setTimeout(function () { focusEl.focus(); }, 120);
            }
        }
    }

    function setPeriodPanelOpen(panel, toggle, open) {
        if (!panel || !toggle) {
            return;
        }
        panel.classList.toggle('is-open', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.classList.toggle('is-open', open);
    }

    function closePeriodPanel() {
        var periodToggle = document.getElementById('commissionPeriodFilterToggle');
        var periodPanel = document.getElementById('commissionPeriodFilterPanel');
        setPeriodPanelOpen(periodPanel, periodToggle, false);
    }

    function bindCommissionFilterToggles() {
        var periodPicker = document.querySelector('[data-admin-commission-period-picker]');
        var periodToggle = document.getElementById('commissionPeriodFilterToggle');
        var periodPanel = document.getElementById('commissionPeriodFilterPanel');
        var searchToggle = document.getElementById('commissionMemberSearchToggle');
        var searchPanel = document.getElementById('commissionMemberSearchPanel');

        if (periodToggle && periodPanel) {
            periodToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                var open = !periodPanel.classList.contains('is-open');
                if (open && searchPanel) {
                    setSearchPanelOpen(searchPanel, searchToggle, false);
                }
                setPeriodPanelOpen(periodPanel, periodToggle, open);
            });

            periodPanel.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    closePeriodPanel();
                });
            });
        }

        if (periodPicker) {
            periodPicker.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        if (searchToggle && searchPanel) {
            searchPanel.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            searchToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                var open = !searchPanel.classList.contains('is-open');
                if (open) {
                    closePeriodPanel();
                }
                setSearchPanelOpen(searchPanel, searchToggle, open);
            });
        }

        document.addEventListener('click', function () {
            closePeriodPanel();
            if (searchPanel && searchToggle) {
                setSearchPanelOpen(searchPanel, searchToggle, false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePeriodPanel();
                if (searchPanel && searchToggle) {
                    setSearchPanelOpen(searchPanel, searchToggle, false);
                }
            }
        });
    }

    syncCommissionFilterFields();

    if (mq.matches) {
        bindCommissionFilterToggles();
    }

    mq.addEventListener('change', function () {
        syncCommissionFilterFields();
    });
})();
</script>

</div>{{-- .commission-history-page --}}

@endsection