@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
<style>
    .offline-pay-period-menu .form-label {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-tertiary);
    }
    .offline-pay-period-menu .form-control {
        width: 100%;
        min-height: 2.35rem;
        border-radius: var(--radius-md, 8px);
        padding: 0.4rem 0.7rem;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.8125rem;
    }

</style>
@endpush

@section('title', 'Payer bonus → portefeuilles')

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
    $currentStatus = $status ?? request('status', 'pending');
    $filterActive = in_array($currentStatus, ['pending', 'paid'], true);
    $filterLabels = [
        'pending' => 'En attente',
        'paid' => 'Payés',
        'all' => 'Tous',
    ];
    $filterButtonLabel = $filterLabels[$currentStatus] ?? 'Filtrer';
    $filterQueryBase = array_filter([
        'period' => $selectedPeriod,
        'search' => $search ?? request('search'),
    ], fn ($v) => filled($v));
    $searchValue = $search ?? request('search');
    $searchOpen = filled($searchValue);
    $subtitleHtml = ($totals['count'] ?? 0).' membre(s) · À créditer $'.number_format($totals['pending'] ?? 0, 2)
        .($filterActive ? ' · '.e($filterButtonLabel) : '')
        .($selectedPeriod ? ' · '.e($periodLabel) : '')
        .(filled($searchValue)
            ? ' <span class="text-xs opacity-90">· « '.e($searchValue).' »</span>'
            : '');
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE : loupe comme Calculer les bonus --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Paiement des bonus</h1>
                <p class="page-subtitle" id="offlinePaySubtitleMobile">{!! $subtitleHtml !!}</p>
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
                    <div x-show="open"
                         x-cloak
                         x-transition
                         @click.outside="open = false"
                         class="users-filter-menu">
                        <form method="GET" action="{{ route('cashier.offline-bonus.pay') }}" class="px-2 pt-1 pb-2 offline-pay-period-menu">
                            @if(filled($searchValue))
                                <input type="hidden" name="search" value="{{ $searchValue }}">
                            @endif
                            <input type="hidden" name="status" value="{{ $currentStatus }}">
                            <label class="form-label" for="offlinePayPeriodMobile">Période</label>
                            <input type="month"
                                   id="offlinePayPeriodMobile"
                                   name="period"
                                   class="form-control"
                                   value="{{ $selectedPeriod ?: date('Y-m') }}"
                                   onchange="this.form.submit()">
                        </form>
                        <a href="{{ route('cashier.offline-bonus.pay', array_merge($filterQueryBase, ['status' => 'all'])) }}"
                           class="filter-link {{ $currentStatus === 'all' ? 'is-active' : '' }}">
                            <span>Tous</span>
                            <span class="filter-count">{{ $stats['all'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('cashier.offline-bonus.pay', array_merge($filterQueryBase, ['status' => 'pending'])) }}"
                           class="filter-link {{ $currentStatus === 'pending' ? 'is-active' : '' }}">
                            <span>En attente</span>
                            <span class="filter-count">{{ $stats['pending'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('cashier.offline-bonus.pay', array_merge($filterQueryBase, ['status' => 'paid'])) }}"
                           class="filter-link {{ $currentStatus === 'paid' ? 'is-active' : '' }}">
                            <span>Payés</span>
                            <span class="filter-count">{{ $stats['paid'] ?? 0 }}</span>
                        </a>
                    </div>
                </div>
                @if($selectedPeriod && ($stats['all'] ?? 0) > 0)
                    <a href="{{ route('cashier.offline-bonus.pay.global-pdf', ['period' => $selectedPeriod]) }}"
                       class="admin-banner-icon-btn btn-pdf-icon"
                       title="Rapport global"
                       aria-label="Rapport global">
                        <x-pdf-icon size="lg" />
                    </a>
                @endif
                <a href="{{ route('cashier.guichet.index') }}"
                   class="btn btn-primary btn-sm btn-round-icon" title="Retraits guichet" aria-label="Retraits guichet">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 0118 0z"/>
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
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Paiement des bonus</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1" id="offlinePaySubtitle">{!! $subtitleHtml !!}</p>
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
            <div class="relative shrink-0" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button"
                        @click="open = !open"
                        class="btn btn-outline btn-sm btn-round-icon {{ $filterActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        title="{{ $filterButtonLabel }}"
                        aria-label="{{ $filterButtonLabel }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
                <div x-show="open"
                     x-cloak
                     x-transition
                     @click.outside="open = false"
                     class="users-filter-menu">
                    <form method="GET" action="{{ route('cashier.offline-bonus.pay') }}" class="px-2 pt-1 pb-2 offline-pay-period-menu">
                        @if(filled($searchValue))
                            <input type="hidden" name="search" value="{{ $searchValue }}">
                        @endif
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                        <label class="form-label" for="offlinePayPeriodDesktop">Période</label>
                        <input type="month"
                               id="offlinePayPeriodDesktop"
                               name="period"
                               class="form-control"
                               value="{{ $selectedPeriod ?: date('Y-m') }}"
                               onchange="this.form.submit()">
                    </form>
                    <a href="{{ route('cashier.offline-bonus.pay', array_merge($filterQueryBase, ['status' => 'all'])) }}"
                       class="filter-link {{ $currentStatus === 'all' ? 'is-active' : '' }}">
                        <span>Tous</span>
                        <span class="filter-count">{{ $stats['all'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('cashier.offline-bonus.pay', array_merge($filterQueryBase, ['status' => 'pending'])) }}"
                       class="filter-link {{ $currentStatus === 'pending' ? 'is-active' : '' }}">
                        <span>En attente</span>
                        <span class="filter-count">{{ $stats['pending'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('cashier.offline-bonus.pay', array_merge($filterQueryBase, ['status' => 'paid'])) }}"
                       class="filter-link {{ $currentStatus === 'paid' ? 'is-active' : '' }}">
                        <span>Payés</span>
                        <span class="filter-count">{{ $stats['paid'] ?? 0 }}</span>
                    </a>
                </div>
            </div>
            @if($selectedPeriod && ($stats['all'] ?? 0) > 0)
                <a href="{{ route('cashier.offline-bonus.pay.global-pdf', ['period' => $selectedPeriod]) }}"
                   class="btn btn-outline btn-sm"
                   title="Rapport global">
                    <x-pdf-icon />
                    Rapport Global
                </a>
            @endif
            <a href="{{ route('cashier.guichet.index') }}"
               class="btn btn-primary btn-sm btn-round-icon"
               title="Retraits guichet"
               aria-label="Retraits guichet">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 0118 0z"/>
                </svg>
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
    <div class="commission-stats">
        <div class="commission-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="number">{{ $totals['count'] ?? 0 }}</div>
            <div class="label">Membres</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($totals['pending'] ?? 0, 2) }}</div>
            <div class="label">À créditer</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($totals['paid'] ?? 0, 2) }}</div>
            <div class="label">Déjà payé</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div class="number">${{ number_format($totals['in_wallet'] ?? 0, 2) }}</div>
            <div class="label">Dans portefeuille</div>
        </div>
    </div>

    @if($selectedPeriod && ($totals['pending'] ?? 0) > 0)
        <form method="POST" action="{{ route('cashier.offline-bonus.pay.period') }}"
              onsubmit="return confirm('Créditer TOUS les bonus en attente de {{ $selectedPeriod }} ?');">
            @csrf
            <input type="hidden" name="period" value="{{ $selectedPeriod }}">
            <button type="submit" class="btn btn-primary w-full md:w-auto">
                Tout envoyer — ${{ number_format($totals['pending'], 2) }}
            </button>
        </form>
    @endif

    {{-- TABLEAU — clic ligne → détail + actions --}}
    <div class="card">
        <div class="table-wrap">
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Code Membre</th>
                        <th>Nom &amp; Post-Nom</th>
                        <th>Code Parrain</th>
                        <th>Nom &amp; Post-Nom</th>
                        <th class="col-amount">À payer</th>
                        <th class="col-amount">Payé</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="offlinePayTableBody">
                    @forelse($byMember as $row)
                        @php
                            $isPending = $row['pending_amount'] > 0;
                            $user = $row['user'] ?? null;
                            $detailUrl = $user
                                ? route('cashier.offline-bonus.pay.show', ['userId' => $user->id, 'period' => $selectedPeriod])
                                : null;
                        @endphp
                        <tr @if($detailUrl) class="commission-row" role="link" tabindex="0"
                            onclick="window.location='{{ $detailUrl }}'"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location='{{ $detailUrl }}';}"
                            @endif>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="commission-member">{{ $user?->sponsor_id ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="commission-member">{{ $user?->name ?? '—' }}</span>
                            </td>
                            <td>{{ $user?->parrain?->sponsor_id ?? ($user?->parrainSponsorCode() ?: '—') }}</td>
                            <td>{{ $user?->parrain?->name ?? '—' }}</td>
                            <td class="col-amount {{ $isPending ? 'text-[var(--ui-stat-success)]' : '' }}">
                                ${{ number_format($row['pending_amount'], 2) }}
                            </td>
                            <td class="col-amount">${{ number_format($row['paid_amount'], 2) }}</td>
                            <td>
                                @if($isPending)
                                    <span class="badge badge-warning">En attente</span>
                                @else
                                    <span class="badge badge-success">Payé</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucun bonus</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">
                                    @if(filled($search ?? request('search')))
                                        Aucun résultat pour cette recherche.
                                    @else
                                        Lancez d’abord le calcul pour cette période.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
                if (other !== searchInput) other.value = query;
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
        const tableBody = document.getElementById('offlinePayTableBody');
        if (!tableBody) return;

        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }

        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
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
            const newTableBody = doc.getElementById('offlinePayTableBody');
            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            const subtitle = document.getElementById('offlinePaySubtitle');
            const newSubtitle = doc.getElementById('offlinePaySubtitle');
            if (subtitle && newSubtitle) {
                subtitle.innerHTML = newSubtitle.innerHTML;
            }

            const subtitleMobile = document.getElementById('offlinePaySubtitleMobile');
            const newSubtitleMobile = doc.getElementById('offlinePaySubtitleMobile');
            if (subtitleMobile && newSubtitleMobile) {
                subtitleMobile.innerHTML = newSubtitleMobile.innerHTML;
            }

            window.history.replaceState({}, '', url.toString());
        })
        .catch(function (error) {
            console.error(error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-8 text-[var(--ui-stat-danger,#b32a2a)]">
                        Une erreur est survenue lors de la recherche
                    </td>
                </tr>
            `;
        });
    }
});
</script>
@endpush
