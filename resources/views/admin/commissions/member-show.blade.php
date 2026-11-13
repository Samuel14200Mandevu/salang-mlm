@extends('admin.layouts.app')

@section('title', 'Commissions — '.$member->name)

@push('styles')
<style>
.table-wrap { overflow-x: auto; }
.table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.table thead th {
    padding: 0.5rem 0.55rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td,
.table tfoot td {
    padding: 0.55rem 0.55rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
}
/* Largeurs : Source limitée, chiffres aérés */
.table th.col-type,
.table td.col-type { width: 9%; }
.table th.col-source,
.table td.col-source { width: 18%; }
.table th.col-grade,
.table td.col-grade { width: 6%; }
.table th.col-gen,
.table td.col-gen { width: 6%; }
.table th.col-pct,
.table td.col-pct { width: 8%; }
.table th.col-pv,
.table td.col-pv { width: 8%; }
.table th.col-amount-end,
.table td.col-amount-end { width: 12%; }
.table th.col-status,
.table td.col-status { width: 15%; }
.table th.col-period,
.table td.col-period { width: 9%; }

.table thead th.col-source,
.table tbody td.col-source {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
.table tbody td.col-source .font-medium,
.table tbody td.col-source .font-mono {
    display: block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.table thead th.col-amount,
.table tbody td.col-amount,
.table tfoot td.col-amount {
    text-align: right !important;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    padding-left: 0.65rem;
    padding-right: 0.65rem;
}
.table thead th.col-amount-end,
.table tbody td.col-amount-end,
.table tfoot td.col-amount-end {
    padding-right: 1.75rem;
}
.table thead th.col-status,
.table tbody td.col-status {
    padding-left: 1.75rem;
    white-space: nowrap;
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.65rem;
    border-radius: 6px;
    font-size: 0.6875rem;
    font-weight: 600;
    border: 1px solid transparent;
    white-space: nowrap;
}
.badge-success { background: #E6F4EC; color: #1F7B4D; border-color: #B8DFCC; }
.badge-warning { background: #FEF1E6; color: #A65A0E; border-color: #FADCB8; }
.badge-info { background: #E8EDF5; color: var(--primary-navy, #0A2A6C); border-color: #C8D4E3; }
.badge-danger { background: #FDE8E8; color: #B32A2A; border-color: #F5C8C8; }

/* Couleurs Type */
.type-badge-direct {
    background: #E8EDF5;
    color: var(--primary-navy, #0A2A6C);
    border-color: #C8D4E3;
}
.type-badge-indirect {
    background: #E8F4FC;
    color: #065F9C;
    border-color: #B8D8EC;
}
.type-badge-leadership {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}
.type-badge-cash_pos {
    background: #E6F4EC;
    color: #1F7B4D;
    border-color: #B8DFCC;
}
.amount-positive {
    color: #1F7B4D;
    font-weight: 700;
}

.admin-page-header.header-with-search {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}
.header-left {
    flex: 1 1 auto;
    min-width: 0;
}
.header-right {
    flex: 0 0 auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
    margin-left: auto;
}
</style>
@endpush

@php
    $typeLabels = [
        'direct' => 'Direct',
        'indirect' => 'Indirect',
        'leadership' => 'Leadership',
        'cash_pos' => 'Cash POS',
    ];
    $statusLabels = [
        'paid' => 'En portefeuille',
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'cancelled' => 'Annulé',
    ];
    $statusClasses = [
        'paid' => 'badge-success',
        'pending' => 'badge-warning',
        'approved' => 'badge-info',
        'cancelled' => 'badge-danger',
    ];
    $backUrl = route('admin.commissions', $backQuery ?? []);
    $pdfUrl = route('admin.commissions.member.pdf', array_filter(
        array_merge(
            ['user' => $member->id],
            request()->only(['type', 'status', 'period', 'band'])
        ),
        fn ($v) => $v !== null && $v !== ''
    ));
@endphp

@section('content')
<div class="space-y-4 sm:space-y-6">

    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1>{{ $member->name }}</h1>
                <p>{{ $member->sponsor_id ?: '—' }} · {{ $summary['line_count'] }} lignes</p>
            </div>
            <div class="admin-title-banner__tools">
                <a href="{{ $pdfUrl }}" class="btn btn-pdf-icon btn-sm btn-round-icon" title="PDF détail" aria-label="PDF détail">
                    <x-pdf-icon size="lg" />
                </a>
                <a href="{{ $backUrl }}" class="btn btn-outline btn-sm btn-round-icon" title="Retour" aria-label="Retour">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="admin-page-header page-header header-with-search hidden md:flex animate-fadeInUp">
        <div class="header-left admin-mobile-page-head">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">{{ $member->name }}</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $member->sponsor_id ?: '—' }}
                · Parrain {{ $member->parrain?->sponsor_id ?? '—' }}
                @if($member->parrain?->name)
                    ({{ $member->parrain->name }})
                @endif
                · Grade {{ $summary['grade'] }}
                · PV {{ $summary['pv'] === null ? '—' : number_format($summary['pv'], 0) }}{{ !empty($summary['pv_period']) ? ' ('.$summary['pv_period'].')' : '' }}
            </p>
        </div>
        <div class="header-right admin-page-header-actions">
            <a href="{{ $pdfUrl }}" class="btn btn-outline btn-sm" title="PDF détail">
                <x-pdf-icon class="w-4 h-4" />
                PDF
            </a>
            <a href="{{ $backUrl }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 salang-flash-success border rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 sm:p-4 salang-flash-error border rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="admin-kpi-rail stats-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Direct</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">{{ number_format($summary['direct'], 2) }} $</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Indirect</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">{{ number_format($summary['indirect'], 2) }} $</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Leadership</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">{{ number_format($summary['leadership'], 2) }} $</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Cash POS</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">{{ number_format($summary['cash_pos'], 2) }} $</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-warning)]">{{ number_format($summary['pending_amount'], 2) }} $</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-success)]">{{ number_format($summary['total'], 2) }} $</p>
        </div>
    </div>

    {{-- Mobile lines --}}
    <div class="admin-user-list md:hidden">
        @foreach($commissions as $commission)
            <div class="admin-entity-card">
                <div class="admin-entity-card__top">
                    <div class="min-w-0">
                        <div class="admin-entity-card__name">
                            <span class="badge type-badge-{{ $commission->type }}">
                                {{ $typeLabels[$commission->type] ?? ucfirst($commission->type) }}
                            </span>
                        </div>
                        <div class="admin-entity-card__sub">
                            @if($commission->type === 'direct')
                                {{ number_format((float) $commission->percentage, 2) }}%
                            @elseif($commission->from_user_id && $commission->from_user_id !== $commission->user_id)
                                {{ $commission->fromUser?->name ?? '—' }}
                            @else
                                —
                            @endif
                            @if($commission->period) · {{ $commission->period }} @endif
                        </div>
                    </div>
                    <span class="amount-positive text-sm font-bold shrink-0">{{ number_format($commission->amount, 2) }} $</span>
                </div>
                <div class="admin-entity-card__meta">
                    <span class="badge {{ $statusClasses[$commission->status] ?? 'badge-warning' }}">
                        {{ $statusLabels[$commission->status] ?? ucfirst($commission->status) }}
                    </span>
                    @php
                        $mobileGrade = $commission->type === 'direct'
                            ? ($member->rank_level ?? $summary['grade'] ?? '—')
                            : ($commission->fromUser?->rank_level ?? '—');
                    @endphp
                    <span class="text-xs text-[var(--text-secondary)]">G{{ $mobileGrade }}</span>
                    @if($commission->generation)
                        <span class="text-xs text-[var(--text-secondary)]">Gén. {{ $commission->generation }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Desktop detail table --}}
    <div class="card p-3 sm:p-4 hidden md:block">
        <div class="flex items-center justify-between gap-2 mb-3">
            <h2 class="text-base font-bold text-[var(--text-primary)]">
                Détail des commissions
                <span class="text-sm font-medium text-[var(--text-tertiary)]">({{ $summary['line_count'] }})</span>
            </h2>
        </div>
        <div class="table-wrap">
            <table class="table table-striped admin-table-desktop-only text-sm">
                <thead>
                    <tr>
                        <th class="col-type">Type</th>
                        <th class="col-source">Source</th>
                        <th class="col-amount col-grade">Grade</th>
                        <th class="col-amount col-gen">Gén.</th>
                        <th class="col-amount col-pct">%</th>
                        <th class="col-amount col-pv">PV</th>
                        <th class="col-amount col-amount-end">Montant</th>
                        <th class="col-status">Statut</th>
                        <th class="col-period">Période</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $commission)
                        @php
                            $lineGrade = $commission->type === 'direct'
                                ? ($member->rank_level ?? $summary['grade'] ?? '—')
                                : ($commission->fromUser?->rank_level ?? '—');
                        @endphp
                        <tr>
                            <td class="col-type">
                                <span class="badge type-badge-{{ $commission->type }}">
                                    {{ $typeLabels[$commission->type] ?? ucfirst(str_replace('_', ' ', $commission->type)) }}
                                </span>
                            </td>
                            <td class="col-source" title="{{ $commission->type === 'direct' ? 'PV personnel' : ($commission->fromUser?->name ?? '') }}">
                                @if($commission->type === 'direct')
                                    <span class="font-semibold tabular-nums">{{ number_format((float) $commission->percentage, 2) }}%</span>
                                    <div class="text-xs text-[var(--text-secondary)]">PV personnel</div>
                                @elseif($commission->from_user_id && $commission->from_user_id !== $commission->user_id)
                                    <div class="font-medium">{{ $commission->fromUser?->name ?? '—' }}</div>
                                    <div class="text-xs font-mono text-[var(--text-secondary)]">{{ $commission->fromUser?->sponsor_id ?? '' }}</div>
                                @else
                                    <span class="text-[var(--text-tertiary)]">—</span>
                                @endif
                            </td>
                            <td class="col-amount col-grade">{{ $lineGrade }}</td>
                            <td class="col-amount col-gen">{{ $commission->generation ?? 0 }}</td>
                            <td class="col-amount col-pct">{{ number_format((float) $commission->percentage, 2) }}%</td>
                            <td class="col-amount col-pv">{{ number_format((float) ($commission->pv_used ?? 0), 1) }}</td>
                            <td class="col-amount col-amount-end font-semibold amount-positive">{{ number_format((float) $commission->amount, 2) }} $</td>
                            <td class="col-status">
                                <span class="badge {{ $statusClasses[$commission->status] ?? 'badge-warning' }}">
                                    {{ $statusLabels[$commission->status] ?? ucfirst($commission->status) }}
                                </span>
                            </td>
                            <td class="col-period text-[var(--text-secondary)]">{{ $commission->period ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td colspan="6" style="text-align:right;">Total</td>
                        <td class="col-amount col-amount-end amount-positive">{{ number_format($summary['total'], 2) }} $</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
