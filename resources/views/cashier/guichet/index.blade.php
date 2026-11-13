@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
@endpush

@section('title', 'Retraits guichet')

@section('content')
@php
    $currentStatus = $status ?? request('status', 'pending');
    $filterActive = in_array($currentStatus, ['pending', 'completed'], true);
    $filterLabels = [
        'pending' => 'En attente',
        'completed' => 'Terminés',
        'all' => 'Tous',
    ];
    $filterButtonLabel = $filterLabels[$currentStatus] ?? 'Filtrer';
    $filterQueryBase = array_filter([
        'search' => request('search'),
    ], fn ($v) => filled($v));
    $searchOpen = filled(request('search'));
    $subtitleHtml = ($stats['pending'] ?? 0).' en attente · $'.number_format($stats['pending_amount'] ?? 0, 2)
        .($filterActive ? ' · '.e($filterButtonLabel) : '')
        .(request('search')
            ? ' <span class="text-xs opacity-90">· Résultats pour "'.e(request('search')).'"</span>'
            : '');
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE : loupe + Filtrer (comme Membres) --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Retraits guichet</h1>
                <p class="page-subtitle">{!! $subtitleHtml !!}</p>
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
                        <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'all'])) }}"
                           class="filter-link {{ $currentStatus === 'all' ? 'is-active' : '' }}">
                            <span>Tous</span>
                            <span class="filter-count">{{ $stats['all'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'pending'])) }}"
                           class="filter-link {{ $currentStatus === 'pending' ? 'is-active' : '' }}">
                            <span>En attente</span>
                            <span class="filter-count">{{ $stats['pending'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'completed'])) }}"
                           class="filter-link {{ $currentStatus === 'completed' ? 'is-active' : '' }}">
                            <span>Terminés</span>
                            <span class="filter-count">{{ $stats['completed'] ?? 0 }}</span>
                        </a>
                    </div>
                </div>
                <a href="{{ route('cashier.offline-bonus.pay') }}"
                   class="btn btn-outline btn-sm btn-round-icon"
                   title="Paiement des bonus"
                   aria-label="Paiement des bonus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </a>
                <a href="{{ route('cashier.guichet.export-daily-pdf', ['date' => now()->toDateString()]) }}"
                   class="btn btn-sm btn-round-icon btn-pdf-icon"
                   title="Rapport journalier PDF"
                   aria-label="Rapport journalier PDF">
                    <x-pdf-icon size="lg" />
                </a>
            </div>
        </div>
        <div id="adminSearchPanel"
             class="admin-search-panel{{ $searchOpen ? ' is-open' : '' }}"
             data-admin-search-panel="1"
             aria-hidden="{{ $searchOpen ? 'false' : 'true' }}">
            <form method="GET" action="{{ route('cashier.guichet.index') }}" class="search-wrapper admin-shop-search">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" class="search-input"
                       placeholder="Rechercher un membre"
                       autocomplete="off"
                       value="{{ request('search') }}">
            </form>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Retraits guichet</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">{!! $subtitleHtml !!}</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('cashier.guichet.index') }}" class="header-search">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="search-input"
                           placeholder="Rechercher un membre"
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </form>
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
                    <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'all'])) }}"
                       class="filter-link {{ $currentStatus === 'all' ? 'is-active' : '' }}">
                        <span>Tous</span>
                        <span class="filter-count">{{ $stats['all'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'pending'])) }}"
                       class="filter-link {{ $currentStatus === 'pending' ? 'is-active' : '' }}">
                        <span>En attente</span>
                        <span class="filter-count">{{ $stats['pending'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('cashier.guichet.index', array_merge($filterQueryBase, ['status' => 'completed'])) }}"
                       class="filter-link {{ $currentStatus === 'completed' ? 'is-active' : '' }}">
                        <span>Terminés</span>
                        <span class="filter-count">{{ $stats['completed'] ?? 0 }}</span>
                    </a>
                </div>
            </div>
            <a href="{{ route('cashier.offline-bonus.pay') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Paiement des bonus</span>
            </a>
            <a href="{{ route('cashier.guichet.export-daily-pdf', ['date' => now()->toDateString()]) }}"
               class="btn btn-outline btn-sm sm:btn-md">
                <x-pdf-icon />
                <span>Rapport journalier</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- STATS --}}
    <div class="commission-stats">
        <div class="commission-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">{{ $stats['pending'] }}</div>
            <div class="label">En attente</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($stats['pending_amount'], 2) }}</div>
            <div class="label">Montant dû</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">{{ $stats['completed_today'] }}</div>
            <div class="label">Remis aujourd’hui</div>
        </div>
    </div>

    {{-- TABLEAU --}}
    <div class="card">
        <div class="table-wrap">
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Membre</th>
                        <th class="col-amount">Montant</th>
                        <th>Statut</th>
                        <th class="hidden sm:table-cell">Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $w)
                        @php
                            $memberLabel = $w->user
                                ? trim(($w->user->sponsor_id ? $w->user->sponsor_id.' : ' : '').$w->user->name)
                                : '—';
                            $detailUrl = route('cashier.guichet.show', $w->id);
                        @endphp
                        <tr class="commission-row"
                            role="link"
                            tabindex="0"
                            data-href="{{ $detailUrl }}"
                            onclick="window.location.href=this.dataset.href"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href=this.dataset.href}">
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $w->id }}</td>
                            <td>
                                <span class="commission-member">{{ $memberLabel }}</span>
                            </td>
                            <td class="col-amount text-[var(--ui-stat-success)]">
                                ${{ number_format($w->amount, 2) }}
                            </td>
                            <td>
                                @if($w->status === 'pending')
                                    <span class="badge badge-warning">En attente</span>
                                @elseif($w->status === 'completed')
                                    <span class="badge badge-success">Remis</span>
                                @else
                                    <span class="badge badge-secondary">{{ $w->status }}</span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-tertiary)]">
                                {{ $w->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <a href="{{ $detailUrl }}" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">
                                    {{ $w->status === 'pending' ? 'Traiter' : 'Reçu' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucun retrait guichet</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">
                                    @if(request('search'))
                                        Aucun résultat pour cette recherche.
                                    @else
                                        Les demandes membres apparaîtront ici.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="mt-4">
                <x-salang-pagination :paginator="$withdrawals" />
            </div>
        @endif
    </div>
</div>
@endsection
