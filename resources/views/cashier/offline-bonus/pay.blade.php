@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
<style>
    .offline-pay-tools .header-search {
        flex: 1 1 18rem;
        min-width: 14rem;
        max-width: 28rem;
    }
    .offline-pay-tools .header-search .search-wrapper {
        width: 100%;
    }
    .offline-pay-tools .header-search .search-wrapper .search-input {
        width: 100% !important;
        min-height: 2.4rem;
        padding: 0.5rem 0.9rem 0.5rem 2.4rem;
        font-size: 0.875rem;
        border-radius: 999px;
    }
    .offline-pay-tools .header-search .search-wrapper .search-input:focus {
        width: 100% !important;
    }
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
    $searchOpen = filled($search ?? request('search'));
    $subtitleHtml = ($totals['count'] ?? 0).' membre(s) · À créditer $'.number_format($totals['pending'] ?? 0, 2)
        .($filterActive ? ' · '.e($filterButtonLabel) : '')
        .($selectedPeriod ? ' · '.e($periodLabel) : '')
        .(filled($search ?? request('search'))
            ? ' <span class="text-xs opacity-90">· Résultats pour "'.e($search ?? request('search')).'"</span>'
            : '');
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Paiement des bonus</h1>
                <p class="page-subtitle">{!! $subtitleHtml !!}</p>
            </div>
            <div class="admin-title-banner__tools">
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
                            @if(filled($search ?? request('search')))
                                <input type="hidden" name="search" value="{{ $search ?? request('search') }}">
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
                <a href="{{ route('cashier.guichet.index') }}"
                   class="btn btn-primary btn-sm btn-round-icon" title="Retraits guichet" aria-label="Retraits guichet">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 0118 0z"/>
                    </svg>
                </a>
            </div>
        </div>
        <div class="admin-search-panel is-open" aria-hidden="false">
            <form method="GET" action="{{ route('cashier.offline-bonus.pay') }}" class="search-wrapper admin-shop-search">
                <input type="hidden" name="period" value="{{ $selectedPeriod }}">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" class="search-input"
                       placeholder="Rechercher un membre"
                       autocomplete="off"
                       value="{{ $search ?? request('search') }}">
            </form>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP : recherche + 2 icônes --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Paiement des bonus</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">{!! $subtitleHtml !!}</p>
        </div>
        <div class="offline-pay-tools flex items-center gap-2 flex-1 justify-end min-w-0">
            <form method="GET" action="{{ route('cashier.offline-bonus.pay') }}" class="header-search">
                <input type="hidden" name="period" value="{{ $selectedPeriod }}">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="search-input"
                           placeholder="Rechercher un membre"
                           autocomplete="off"
                           value="{{ $search ?? request('search') }}">
                </div>
            </form>
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
                        @if(filled($search ?? request('search')))
                            <input type="hidden" name="search" value="{{ $search ?? request('search') }}">
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
            <a href="{{ route('cashier.guichet.index') }}"
               class="btn btn-primary btn-sm btn-round-icon shrink-0"
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
            <div class="number">{{ $totals['count'] }}</div>
            <div class="label">Membres</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($totals['pending'], 2) }}</div>
            <div class="label">À créditer</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($totals['paid'], 2) }}</div>
            <div class="label">Déjà payé</div>
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

    {{-- TABLEAU --}}
    <div class="card">
        <div class="table-wrap">
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>Code Membre</th>
                        <th>Nom &amp; Post-Nom</th>
                        <th class="col-amount">À payer</th>
                        <th class="col-amount">Payé</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byMember as $row)
                        @php
                            $isPending = $row['pending_amount'] > 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="commission-member">{{ $row['user']?->sponsor_id ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="commission-member">{{ $row['user']?->name ?? '—' }}</span>
                            </td>
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
                            <td>
                                @if($isPending && $row['user'])
                                    <form method="POST" action="{{ route('cashier.offline-bonus.pay.member', $row['user']->id) }}"
                                          onsubmit="return confirm('Créditer ${{ number_format($row['pending_amount'], 2) }} à {{ $row['user']->name }} ?');">
                                        @csrf
                                        <input type="hidden" name="period" value="{{ $selectedPeriod }}">
                                        <button type="submit" class="btn btn-primary btn-sm">Envoyer</button>
                                    </form>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
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
