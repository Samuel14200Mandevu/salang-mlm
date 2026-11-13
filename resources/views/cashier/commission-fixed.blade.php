@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
<style>
    .bonus-total-wallet { color: var(--primary-navy, var(--primary-blue, #0A2A6C)) !important; }
    .bonus-total-pending { color: var(--ui-stat-warning) !important; }
    .bonus-total-neutral { color: var(--text-secondary) !important; }
    .amount-paid { color: var(--ui-stat-success, #1C7E4A) !important; font-weight: 700; }
    .amount-reste { color: var(--primary-navy, var(--primary-blue, #0A2A6C)) !important; font-weight: 700; }
    .users-filter-menu .products-filter-sep {
        padding: 0.4rem 0.65rem 0.2rem;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
    }
</style>
@endpush

@section('title', 'Toutes les commissions')

@section('content')
@php
    $memberRows = $members ?? $commissions;
    $typeFilter = request('type');
    $statusFilter = request('status');
    $periodFilter = $selectedPeriod ?? request('period');
    if ($periodFilter === 'all') {
        $periodFilter = null;
    }
    $periodIsAll = request('period') === 'all';
    $periods = $periods ?? collect();
    $searchOpen = filled(request('search'));
    $statusLabels = [
        'pending' => 'En attente',
        'paid' => 'En portefeuille',
        'cancelled' => 'Annulé',
    ];
    $filterActive = filled($typeFilter) || filled($statusFilter) || (filled($periodFilter) && ! $periodIsAll);
    $filterButtonLabel = filled($typeFilter)
        ? ucfirst(str_replace('_', ' ', $typeFilter))
        : ($statusLabels[$statusFilter] ?? (filled($periodFilter) ? $periodFilter : 'Filtrer'));
    $filterQueryBase = array_filter([
        'search' => request('search'),
        'type' => $typeFilter,
        'status' => $statusFilter,
        'period' => $periodIsAll ? 'all' : $periodFilter,
    ], fn ($v) => filled($v));
    $filterUrl = function (array $overrides = []) use ($filterQueryBase) {
        return route('cashier.commissions', array_filter(
            array_merge($filterQueryBase, $overrides),
            fn ($v) => $v !== null && $v !== ''
        ));
    };
    $subtitleBits = [($memberRows->total() ?? 0).' membres'];
    if ($typeFilter) {
        $subtitleBits[] = ucfirst(str_replace('_', ' ', $typeFilter));
    }
    if ($statusFilter && isset($statusLabels[$statusFilter])) {
        $subtitleBits[] = $statusLabels[$statusFilter];
    }
    if ($periodFilter) {
        $subtitleBits[] = $periodFilter;
    }
    if (request('search')) {
        $subtitleBits[] = '« '.request('search').' »';
    }
    $commissionSubtitle = implode(' · ', $subtitleBits);
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Toutes les commissions</h1>
                <p id="listSubtitleMobile" class="page-subtitle">{{ $commissionSubtitle }}</p>
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
                    <div x-show="open"
                         x-cloak
                         x-transition
                         @click.outside="open = false"
                         class="users-filter-menu">
                        <a href="{{ $filterUrl(['type' => null, 'status' => null, 'period' => null]) }}" class="filter-link {{ ! $filterActive ? 'is-active' : '' }}">
                            <span>Tout</span>
                        </a>
                        <div class="products-filter-sep">Type</div>
                        @foreach($types ?? [] as $type)
                            <a href="{{ $filterUrl(['type' => $type]) }}" class="filter-link {{ $typeFilter == $type ? 'is-active' : '' }}">
                                <span>{{ $type === 'cash_pos' ? 'Cash POS' : ucfirst($type) }}</span>
                            </a>
                        @endforeach
                        <div class="products-filter-sep">Statut</div>
                        <a href="{{ $filterUrl(['status' => null]) }}" class="filter-link {{ ! $statusFilter ? 'is-active' : '' }}"><span>Tous statuts</span></a>
                        <a href="{{ $filterUrl(['status' => 'pending']) }}" class="filter-link {{ $statusFilter === 'pending' ? 'is-active' : '' }}"><span>En attente</span></a>
                        <a href="{{ $filterUrl(['status' => 'paid']) }}" class="filter-link {{ $statusFilter === 'paid' ? 'is-active' : '' }}"><span>En portefeuille</span></a>
                        <a href="{{ $filterUrl(['status' => 'cancelled']) }}" class="filter-link {{ $statusFilter === 'cancelled' ? 'is-active' : '' }}"><span>Annulé</span></a>
                        @if($periods->isNotEmpty())
                            <div class="products-filter-sep">Période</div>
                            <a href="{{ $filterUrl(['period' => 'all']) }}" class="filter-link {{ $periodIsAll ? 'is-active' : '' }}"><span>Toutes périodes</span></a>
                            @foreach($periods as $period)
                                <a href="{{ $filterUrl(['period' => $period]) }}" class="filter-link {{ $periodFilter == $period ? 'is-active' : '' }}"><span>{{ $period }}</span></a>
                            @endforeach
                        @endif
                    </div>
                </div>
                <a href="{{ route('cashier.offline-bonus.calculate') }}"
                   class="btn btn-primary btn-sm btn-round-icon"
                   title="Bonus"
                   aria-label="Bonus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </a>
                <a href="{{ route('cashier.commissions.export-global-pdf', request()->query()) }}"
                   class="btn btn-sm btn-round-icon btn-pdf-icon"
                   title="PDF global"
                   aria-label="PDF global">
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
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Rechercher un membre" autocomplete="off" value="{{ request('search') }}">
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Toutes les commissions</h1>
            <p id="listSubtitle" class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">{{ $commissionSubtitle }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap header-actions">
            <div class="header-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="searchInputDesktop" class="search-input"
                           placeholder="Rechercher un membre" autocomplete="off" value="{{ request('search') }}">
                </div>
            </div>
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button"
                        @click="open = !open"
                        class="btn btn-outline btn-sm sm:btn-md {{ $filterActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        aria-label="Filtrer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>{{ $filterButtonLabel }}</span>
                </button>
                <div x-show="open"
                     x-cloak
                     x-transition
                     @click.outside="open = false"
                     class="users-filter-menu">
                    <a href="{{ $filterUrl(['type' => null, 'status' => null, 'period' => null]) }}" class="filter-link {{ ! $filterActive ? 'is-active' : '' }}"><span>Tout</span></a>
                    <div class="products-filter-sep">Type</div>
                    @foreach($types ?? [] as $type)
                        <a href="{{ $filterUrl(['type' => $type]) }}" class="filter-link {{ $typeFilter == $type ? 'is-active' : '' }}">
                            <span>{{ $type === 'cash_pos' ? 'Cash POS' : ucfirst($type) }}</span>
                        </a>
                    @endforeach
                    <div class="products-filter-sep">Statut</div>
                    <a href="{{ $filterUrl(['status' => null]) }}" class="filter-link {{ ! $statusFilter ? 'is-active' : '' }}"><span>Tous statuts</span></a>
                    <a href="{{ $filterUrl(['status' => 'pending']) }}" class="filter-link {{ $statusFilter === 'pending' ? 'is-active' : '' }}"><span>En attente</span></a>
                    <a href="{{ $filterUrl(['status' => 'paid']) }}" class="filter-link {{ $statusFilter === 'paid' ? 'is-active' : '' }}"><span>En portefeuille</span></a>
                    <a href="{{ $filterUrl(['status' => 'cancelled']) }}" class="filter-link {{ $statusFilter === 'cancelled' ? 'is-active' : '' }}"><span>Annulé</span></a>
                    @if($periods->isNotEmpty())
                        <div class="products-filter-sep">Période</div>
                        <a href="{{ $filterUrl(['period' => 'all']) }}" class="filter-link {{ $periodIsAll ? 'is-active' : '' }}"><span>Toutes périodes</span></a>
                        @foreach($periods as $period)
                            <a href="{{ $filterUrl(['period' => $period]) }}" class="filter-link {{ $periodFilter == $period ? 'is-active' : '' }}"><span>{{ $period }}</span></a>
                        @endforeach
                    @endif
                </div>
            </div>
            <a href="{{ route('cashier.offline-bonus.calculate') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Bonus
            </a>
            <a href="{{ route('cashier.commissions.export-global-pdf', request()->query()) }}"
               class="btn btn-sm btn-round-icon btn-pdf-icon"
               title="PDF global"
               aria-label="PDF global">
                <x-pdf-icon size="lg" />
            </a>
        </div>
    </div>

    {{-- STATS (même design que Retraits guichet) --}}
    <div class="commission-stats">
        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['total_wallet'] ?? $stats['paid_commissions'] ?? 0, 2) }}</div>
            <div class="label">En portefeuille</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['total_paid_out'] ?? 0, 2) }}</div>
            <div class="label">Payé</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number">${{ number_format($stats['total_pending'] ?? $stats['pending_commissions'] ?? 0, 2) }}</div>
            <div class="label">En attente</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="number">{{ $stats['member_count'] ?? $stats['total_members'] ?? 0 }}</div>
            <div class="label">Membres</div>
        </div>
    </div>

    {{-- Liste mobile --}}
    <div class="admin-user-list md:hidden" id="cashierCommissionsMobileList">
        @forelse($memberRows as $row)
            @php
                $detailUrl = route('cashier.commissions.member', array_filter(
                    array_merge(
                        ['user' => $row->user_id],
                        request()->only(['search', 'type', 'status', 'period'])
                    ),
                    fn ($v) => $v !== null && $v !== ''
                ));
                $n = $memberRows->firstItem() ? ($memberRows->firstItem() + $loop->index) : $loop->iteration;
                $bonusTone = $row->bonus_tone ?? 'neutral';
                $bonusClass = match ($bonusTone) {
                    'wallet' => 'bonus-total-wallet',
                    'pending' => 'bonus-total-pending',
                    default => 'bonus-total-neutral',
                };
            @endphp
            <a href="{{ $detailUrl }}" class="admin-entity-card">
                <div class="admin-entity-card__top">
                    <div class="min-w-0">
                        <div class="admin-entity-card__name">{{ $n }}. {{ $row->name }}</div>
                        <div class="admin-entity-card__sub font-mono">{{ $row->code }} · G{{ $row->grade }}</div>
                    </div>
                    <span class="text-sm font-bold shrink-0 {{ $bonusClass }}">{{ number_format($row->total, 2) }} $</span>
                </div>
                <div class="admin-entity-card__meta flex flex-wrap gap-x-2 gap-y-1 text-xs text-[var(--text-secondary)]">
                    <span>Parrain {{ $row->parrain_code }}</span>
                    <span>{{ $row->parrain_name ?? '—' }}</span>
                    <span>PV {{ $row->pv === null ? '—' : number_format($row->pv, 0) }}</span>
                    <span class="amount-paid">Payé {{ number_format($row->paid ?? 0, 2) }} $</span>
                    <span class="amount-reste">Reste {{ number_format($row->reste ?? 0, 2) }} $</span>
                </div>
            </a>
        @empty
            <div class="admin-empty-state"><p>Aucune commission</p></div>
        @endforelse
    </div>
    <x-salang-pagination :paginator="$memberRows" id="paginationContainerMobile" class="md:hidden" />

    {{-- TABLEAU DESKTOP (même structure que admin) --}}
    <div class="card hidden md:block">
        <div class="table-wrap">
            <table class="commission-table table table-striped">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Code membre</th>
                        <th>Nom &amp; Post-Nom</th>
                        <th>Grade</th>
                        <th>Code parrain</th>
                        <th>Nom &amp; Post-Nom</th>
                        <th class="col-amount" title="PV du mois sélectionné">PV</th>
                        <th class="col-amount">Bonus Total</th>
                        <th class="col-amount" title="Retraits complétés">Payé</th>
                        <th class="col-amount" title="Bonus Total − Payé">Reste</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($memberRows as $row)
                        @php
                            $detailUrl = route('cashier.commissions.member', array_filter(
                                array_merge(
                                    ['user' => $row->user_id],
                                    request()->only(['search', 'type', 'status', 'period'])
                                ),
                                fn ($v) => $v !== null && $v !== ''
                            ));
                            $n = $memberRows->firstItem() ? ($memberRows->firstItem() + $loop->index) : $loop->iteration;
                            $bonusTone = $row->bonus_tone ?? 'neutral';
                            $bonusClass = match ($bonusTone) {
                                'wallet' => 'bonus-total-wallet',
                                'pending' => 'bonus-total-pending',
                                default => 'bonus-total-neutral',
                            };
                        @endphp
                        <tr class="commission-row"
                            role="link"
                            tabindex="0"
                            onclick="window.location='{{ $detailUrl }}'"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location='{{ $detailUrl }}';}">
                            <td>{{ $n }}</td>
                            <td class="font-mono text-xs">{{ $row->code }}</td>
                            <td class="font-medium commission-member">{{ $row->name }}</td>
                            <td>{{ $row->grade }}</td>
                            <td class="font-mono text-xs text-[var(--text-secondary)]">{{ $row->parrain_code }}</td>
                            <td>{{ $row->parrain_name ?? '—' }}</td>
                            <td class="col-amount">{{ $row->pv === null ? '—' : number_format($row->pv, 0) }}</td>
                            <td class="col-amount font-semibold {{ $bonusClass }}">{{ number_format($row->total, 2) }} $</td>
                            <td class="col-amount amount-paid">{{ number_format($row->paid ?? 0, 2) }} $</td>
                            <td class="col-amount amount-reste">{{ number_format($row->reste ?? 0, 2) }} $</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-[var(--text-secondary)]">
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Aucun résultat pour ces critères</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="mt-4">
            <x-salang-pagination :paginator="$memberRows" />
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = [document.getElementById('searchInput'), document.getElementById('searchInputDesktop')].filter(Boolean);
    let t;
    function go(q) {
        const url = new URL(window.location.href);
        if (q) url.searchParams.set('search', q); else url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location = url.toString();
    }
    inputs.forEach(function (input) {
        input.addEventListener('input', function () {
            clearTimeout(t);
            const q = this.value.trim();
            inputs.forEach(function (other) { if (other !== input) other.value = q; });
            t = setTimeout(function () { go(q); }, 400);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); clearTimeout(t); go(this.value.trim()); }
        });
    });
});
</script>
@endpush
@endsection
