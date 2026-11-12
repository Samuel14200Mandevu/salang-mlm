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
    $searchValue = $search ?? request('search');
    $searchOpen = filled($searchValue);
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE : loupe comme « Toutes les commissions » --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Calculer les bonus</h1>
                <p class="page-subtitle">{{ $subtitle }}@if(filled($searchValue)) <span class="text-xs opacity-90">· « {{ $searchValue }} »</span>@endif</p>
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
                <button type="button"
                        class="admin-banner-icon-btn"
                        data-offline-calc-toggle="1"
                        aria-controls="offlineCalcMonthCard"
                        aria-expanded="false"
                        aria-label="Calculer les bonus du mois"
                        title="Calculer les bonus du mois">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </button>
                <a href="{{ route('cashier.commissions') }}"
                   class="btn btn-outline btn-sm btn-round-icon" title="Commissions" aria-label="Commissions">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
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
                <input type="text"
                       id="searchInputMobile"
                       class="search-input"
                       placeholder="Rechercher un membre"
                       autocomplete="off"
                       value="{{ $searchValue }}"
                       aria-label="Rechercher un membre">
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Calculer les bonus</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1" id="offlineCalcSubtitle">{{ $subtitle }}@if(filled($searchValue)) · « {{ $searchValue }} »@endif</p>
        </div>
        <div class="flex gap-2 flex-wrap header-actions">
            <div class="header-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Rechercher un membre"
                           autocomplete="off"
                           value="{{ $searchValue }}"
                           aria-label="Rechercher un membre">
                </div>
            </div>
            <button type="button"
                    class="btn btn-outline btn-sm btn-round-icon"
                    data-offline-calc-toggle="1"
                    aria-controls="offlineCalcMonthCard"
                    aria-expanded="false"
                    aria-label="Calculer les bonus du mois"
                    title="Calculer les bonus du mois">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </button>
            <a href="{{ route('cashier.commissions') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
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

    {{-- FORMULAIRE (affiché via bouton calendrier) --}}
    <div class="card offline-calc-card" id="offlineCalcMonthCard" hidden aria-hidden="true">
        <div class="card-title">Calculer les bonus du mois</div>

        @if($periodsWithPv->isEmpty())
            <div class="offline-calc-empty">
                <p class="title">Aucun PV historique</p>
                <p class="hint">Importez d’abord le fichier Excel (admin → Import PV).</p>
            </div>
        @else
            <div class="offline-calc-grid">
                <form method="GET" action="{{ route('cashier.offline-bonus.calculate') }}" class="form-group">
                    @if(filled($searchValue))
                        <input type="hidden" name="search" value="{{ $searchValue }}">
                    @endif
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
                    <form method="POST"
                          action="{{ route('cashier.offline-bonus.calculate.run') }}"
                          id="offlineCalcBonusForm">
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

    @if($selectedPeriod && (($summary['members'] ?? 0) > 0))
    <div class="card" id="offlineCalcMembersCard">
        <div class="card-title" style="font-size:1rem;font-weight:700;margin-bottom:1rem;">
            Membres — {{ $periodLabel }}
            <span class="text-sm font-medium text-[var(--text-tertiary)]" id="offlineCalcMembersCount">({{ $membersWithPv->count() }})</span>
        </div>
        <div class="table-wrap">
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Code Membre</th>
                        <th>Nom &amp; Post-Nom</th>
                        <th>Code Parrain</th>
                        <th>Nom &amp; Post-Nom</th>
                        <th>Grade</th>
                        <th class="col-amount">PV</th>
                    </tr>
                </thead>
                <tbody id="offlineCalcTableBody">
                    @forelse($membersWithPv as $row)
                        @php
                            $user = $row['user'] ?? null;
                            $rankLevel = (int) ($user?->rank_level ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="commission-member">{{ $user?->sponsor_id ?? '—' }}</span></td>
                            <td><span class="commission-member">{{ $user?->name ?? '—' }}</span></td>
                            <td>{{ $user?->parrain?->sponsor_id ?? ($user?->parrainSponsorCode() ?: '—') }}</td>
                            <td>{{ $user?->parrain?->name ?? '—' }}</td>
                            <td>
                                <span class="{{ \App\Support\MlmRank::badgeClass($rankLevel) }}">
                                    {{ \App\Support\MlmRank::label($rankLevel) }}
                                </span>
                            </td>
                            <td class="col-amount">{{ number_format($row['pv'] ?? 0, 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                Aucun membre trouvé pour cette recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot id="offlineCalcTableFoot">
                    <tr class="font-semibold">
                        <td colspan="6" style="text-align:right;">Total PV</td>
                        <td class="col-amount">{{ number_format($membersWithPv->sum('pv'), 1) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calcCard = document.getElementById('offlineCalcMonthCard');
    const calcToggles = document.querySelectorAll('[data-offline-calc-toggle]');

    function setCalcCardOpen(open) {
        if (!calcCard) return;
        calcCard.hidden = !open;
        calcCard.setAttribute('aria-hidden', open ? 'false' : 'true');
        calcToggles.forEach(function (btn) {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.classList.toggle('is-active', open);
            if (btn.classList.contains('btn-outline')) {
                btn.classList.toggle('border-[var(--primary)]', open);
                btn.classList.toggle('text-[var(--primary)]', open);
            }
        });
        if (open) {
            calcCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    calcToggles.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const open = calcCard ? calcCard.hidden : true;
            setCalcCardOpen(open);
        });
    });

    const calcForm = document.getElementById('offlineCalcBonusForm');
    if (calcForm) {
        calcForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const form = this;
            if (typeof window.showConfirmDialog !== 'function') {
                form.submit();
                return;
            }
            window.showConfirmDialog({
                type: 'primary',
                title: 'Calculer les bonus',
                message: 'Calculer les bonus Direct, Indirect et Leadership pour {{ $periodLabel }} à partir des PV importés ?',
                confirmText: 'Calculer',
                onConfirm: function () {
                    form.submit();
                },
                form: form
            });
        });
    }

    const searchInputs = [
        document.getElementById('searchInput'),
        document.getElementById('searchInputMobile'),
    ].filter(Boolean);
    if (!searchInputs.length) return;

    let searchTimeout;

    searchInputs.forEach(function (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            searchInputs.forEach(function (other) {
                if (other !== searchInput) {
                    other.value = query;
                }
            });
            searchTimeout = setTimeout(function () {
                fetchMembers(query);
            }, 300);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                this.value = '';
                searchInputs.forEach(function (other) {
                    if (other !== searchInput) other.value = '';
                });
                clearTimeout(searchTimeout);
                fetchMembers('');
            }
        });
    });

    function fetchMembers(query) {
        const tableBody = document.getElementById('offlineCalcTableBody');
        if (!tableBody) return;

        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }

        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-[var(--primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Recherche en cours…</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (response) { return response.text(); })
        .then(function (html) {
            const doc = new DOMParser().parseFromString(html, 'text/html');

            const newTableBody = doc.getElementById('offlineCalcTableBody');
            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            const count = document.getElementById('offlineCalcMembersCount');
            const newCount = doc.getElementById('offlineCalcMembersCount');
            if (count && newCount) {
                count.textContent = newCount.textContent;
            }

            const foot = document.getElementById('offlineCalcTableFoot');
            const newFoot = doc.getElementById('offlineCalcTableFoot');
            if (foot && newFoot) {
                foot.innerHTML = newFoot.innerHTML;
            }

            const subtitle = document.getElementById('offlineCalcSubtitle');
            const newSubtitle = doc.getElementById('offlineCalcSubtitle');
            if (subtitle && newSubtitle) {
                subtitle.innerHTML = newSubtitle.innerHTML;
            }

            window.history.replaceState({}, '', url.toString());
        })
        .catch(function (error) {
            console.error(error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-8 text-[var(--ui-stat-danger,#b32a2a)]">
                        Une erreur est survenue lors de la recherche
                    </td>
                </tr>
            `;
        });
    }
});
</script>
@endpush
