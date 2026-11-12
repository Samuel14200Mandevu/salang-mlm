@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
<style>
    .offline-calc-card .card-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
    }
    .offline-calc-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        align-items: end;
    }
    .offline-calc-grid .form-group {
        margin-bottom: 0;
    }
    .offline-calc-grid .form-label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.813rem;
        font-weight: 500;
        color: var(--text-primary);
    }
    .offline-calc-grid .form-label .required {
        color: var(--danger, #b32a2a);
        margin-left: 0.125rem;
    }
    .offline-calc-grid .form-control {
        width: 100%;
        min-height: 2.5rem;
        border-radius: var(--radius-md, 8px);
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    .offline-calc-grid .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 14%, transparent);
    }
    .offline-calc-status {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }
    .offline-calc-status .form-label {
        visibility: hidden;
    }
    .offline-calc-status-box {
        min-height: 2.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        padding: 0.45rem 0.75rem;
        border-radius: var(--radius-md, 8px);
        border: 1px solid var(--border-color);
        background: var(--bg-secondary);
        font-size: 0.8125rem;
        color: var(--text-secondary);
    }
    .offline-calc-status-box strong {
        color: var(--text-primary);
        font-variant-numeric: tabular-nums;
    }
    .offline-calc-help {
        display: block;
        margin-top: 0.75rem;
        font-size: 0.75rem;
        line-height: 1.45;
        color: var(--text-muted, var(--text-tertiary));
    }
    .offline-calc-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    .offline-calc-footer .btn {
        min-height: 2.5rem;
    }
    .offline-calc-empty {
        text-align: center;
        padding: 1.5rem 0.5rem;
        color: var(--text-secondary);
    }
    .offline-calc-empty .title {
        font-size: 1rem;
        font-weight: 650;
        color: var(--text-primary);
    }
    .offline-calc-empty .hint {
        margin-top: 0.35rem;
        font-size: 0.8125rem;
        color: var(--text-tertiary);
    }
    @media (max-width: 900px) {
        .offline-calc-grid {
            grid-template-columns: 1fr;
        }
        .offline-calc-status .form-label {
            display: none;
        }
        .offline-calc-footer .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('title', 'Calculer bonus hors système')

@section('content')
@php
    $monthNames = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];
    $formatPeriod = function (?string $period) use ($monthNames): string {
        if (! $period || ! preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
            return (string) $period;
        }

        return ($monthNames[(int) $m[2]] ?? $period).' '.$m[1];
    };
    $periodLabel = $formatPeriod($selectedPeriod);
    $subtitle = $selectedPeriod
        ? ($periodLabel.($summary ? ' · '.number_format($summary['pv_rows']).' ligne(s) PV' : ''))
        : 'Importez d’abord les PV Excel';
    $periodStatusLabel = $periodRecord?->status_label;
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Calculer les bonus</h1>
                <p class="page-subtitle">{{ $subtitle }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <a href="{{ route('cashier.commissions') }}"
                   class="btn btn-outline btn-sm btn-round-icon" title="Commissions" aria-label="Commissions">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Calculer les bonus</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">{{ $subtitle }}</p>
        </div>
        <div class="flex gap-2 flex-wrap header-actions">
            <a href="{{ route('cashier.commissions') }}" class="btn btn-outline btn-sm">
                Retour commissions
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    {{-- STATS --}}
    @if($summary)
    <div class="commission-stats">
        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="number">{{ number_format($summary['pv_rows']) }}</div>
            <div class="label">Lignes PV</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div class="number">{{ number_format($summary['pv_total'], 1) }}</div>
            <div class="label">Total PV</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($summary['commission_total'], 2) }}</div>
            <div class="label">Bonus calculés</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($summary['pending_total'], 2) }}</div>
            <div class="label">À payer</div>
        </div>
    </div>
    @endif

    {{-- FORMULAIRE (style Ajouter des PV manuellement) --}}
    <div class="card offline-calc-card">
        <div class="card-title">Calculer les bonus du mois</div>

        @if($periodsWithPv->isEmpty())
            <div class="offline-calc-empty">
                <p class="title">Aucun PV historique</p>
                <p class="hint">Importez d’abord le fichier Excel (admin → Import PV).</p>
            </div>
        @else
            <div class="offline-calc-grid">
                <form method="GET" action="{{ route('cashier.offline-bonus.calculate') }}" class="form-group">
                    <label class="form-label" for="offlineCalcPeriod">Période <span class="required">*</span></label>
                    <input type="month"
                           id="offlineCalcPeriod"
                           name="period"
                           class="form-control"
                           value="{{ $selectedPeriod ?: date('Y-m') }}"
                           onchange="this.form.submit()"
                           required>
                </form>

                <div class="form-group">
                    <label class="form-label">Membres</label>
                    <div class="form-control" style="display:flex;align-items:center;background:var(--bg-secondary);">
                        <strong>{{ number_format($summary['members'] ?? 0) }}</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Lignes PV</label>
                    <div class="form-control" style="display:flex;align-items:center;background:var(--bg-secondary);">
                        <strong>{{ number_format($summary['pv_rows'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>

            @if($periodStatusLabel)
                <span class="offline-calc-help">
                    Statut de la période : <strong>{{ $periodStatusLabel }}</strong>
                </span>
            @endif

            @if($selectedPeriod && $summary && ($summary['pv_rows'] ?? 0) > 0)
                <span class="offline-calc-help">
                    Calcule Direct, Indirect et Leadership pour <strong>{{ $periodLabel }}</strong>
                    à partir des PV importés. Les résultats apparaissent dans commissions et historique.
                </span>

                <div class="offline-calc-footer">
                    <form method="POST" action="{{ route('cashier.offline-bonus.calculate.run') }}"
                          onsubmit="return confirm('Calculer les bonus pour {{ $periodLabel }} ?');">
                        @csrf
                        <input type="hidden" name="period" value="{{ $selectedPeriod }}">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Calculer les bonus
                        </button>
                    </form>
                </div>
            @elseif($selectedPeriod)
                <div class="offline-calc-empty" style="padding-top:1rem;">
                    <p class="title">Aucune ligne PV pour {{ $periodLabel }}</p>
                    <p class="hint">Choisissez un autre mois ou réimportez les PV.</p>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
