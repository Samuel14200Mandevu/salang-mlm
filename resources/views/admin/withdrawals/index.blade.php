{{-- resources/views/admin/withdrawals/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1.25rem;
    transition: border-color 0.15s ease;
}

.card-stats {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 0.875rem 1.125rem;
    transition: border-color 0.15s ease;
}
.card-stats:hover {
    border-color: var(--primary-navy);
}

.withdrawal-row {
    transition: background 0.1s ease;
}
.withdrawal-row:hover {
    background: var(--bg-hover);
}

.table-wrap { overflow-x: auto; }
.table { 
    width: 100%; 
    border-collapse: collapse; 
    font-size: 0.875rem; 
}
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
}
.table-striped tbody tr:nth-child(even) { 
    background: var(--bg-secondary); 
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success {
    background: #E6F4EC;
    color: #1F7B4D;
    border-color: #B8DFCC;
}
.badge-danger {
    background: #FDE8E8;
    color: #B32A2A;
    border-color: #F5C8C8;
}
.badge-warning {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}
.badge-info {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
}
.badge-secondary {
    background: #EEF0F3;
    color: var(--text-secondary);
    border-color: #DCDEE3;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-success {
    background: #1F7B4D;
    color: white;
    border-color: #1F7B4D;
}
.btn-success:hover {
    background: #16633D;
    border-color: #16633D;
}

.btn-danger {
    background: #B32A2A;
    color: white;
    border-color: #B32A2A;
}
.btn-danger:hover {
    background: #8F2121;
    border-color: #8F2121;
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease;
    outline: none;
}
.input:focus {
    border-color: var(--primary-navy);
}
.input::placeholder {
    color: var(--text-tertiary);
}

[x-cloak] { display: none !important; }

.users-filter-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 0.35rem);
    min-width: 12.5rem;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 0.65rem;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
    padding: 0.35rem;
    z-index: 40;
}
.users-filter-menu a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.5rem 0.65rem;
    border-radius: 0.45rem;
    font-size: 0.8125rem;
    color: var(--text-primary);
    text-decoration: none;
}
.users-filter-menu a:hover { background: var(--bg-secondary); }
.users-filter-menu a.is-active {
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    font-weight: 600;
}
.users-filter-menu .filter-count {
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--text-tertiary);
}
.users-filter-menu a.is-active .filter-count { color: var(--primary); }
.users-filter-menu .products-filter-sep {
    padding: 0.4rem 0.65rem 0.2rem;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-tertiary);
}

.header-with-search {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
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
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
}
.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.modal-box {
    background: var(--bg-card);
    border-radius: 10px;
    padding: 1.75rem;
    max-width: 440px;
    width: 90%;
    border: 1px solid var(--border-color);
    transform: scale(0.95);
    transition: transform 0.25s ease;
}
.modal-overlay.active .modal-box {
    transform: scale(1);
}
.modal-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
}
.modal-icon-danger {
    background: #FDE8E8;
    color: #B32A2A;
}
.modal-title {
    text-align: center;
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.375rem;
}
.modal-text {
    text-align: center;
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 1.25rem;
    line-height: 1.6;
}
.modal-text strong {
    color: var(--text-primary);
}
.modal-text .text-danger {
    color: #B32A2A;
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.modal-actions .btn {
    min-width: 90px;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }

@media (max-width: 768px) {
    .header-with-search {
        flex-direction: column;
        align-items: stretch;
    }
    .header-right {
        flex-wrap: wrap;
    }
    .search-wrapper {
        flex: 1;
        min-width: 0;
        max-width: none;
    }
    .stats-grid {
        grid-template-columns: 1fr 1fr !important;
    }
}

@media (max-width: 640px) {
    .table thead th, .table tbody td {
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.65rem;
    }
    .badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
    }
    .card {
        padding: 0.875rem;
    }
    .card-stats {
        padding: 0.625rem 0.75rem;
    }
    .card-stats .text-xl {
        font-size: 1.125rem;
    }
    .modal-box {
        padding: 1.25rem;
    }
    .modal-actions {
        flex-direction: column;
    }
    .modal-actions .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
    .header-right {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
@endpush

@section('content')
@php
    $statusFilter = request('status');
    $methodFilter = request('method');
    $filterActive = filled($statusFilter) || filled($methodFilter);
    $statusLabels = [
        'pending' => 'En attente',
        'processing' => 'En traitement',
        'completed' => 'Terminé',
        'failed' => 'Échoué',
    ];
    $filterButtonLabel = $statusLabels[$statusFilter] ?? (filled($methodFilter) ? ucfirst($methodFilter) : 'Filtrer');
    $filterQueryBase = array_filter([
        'search' => request('search'),
        'status' => $statusFilter,
        'method' => $methodFilter,
    ], fn ($v) => filled($v));
    $filterUrl = function (array $overrides = []) use ($filterQueryBase) {
        return route('admin.withdrawals', array_filter(
            array_merge($filterQueryBase, $overrides),
            fn ($v) => $v !== null && $v !== ''
        ));
    };
    $searchOpen = filled(request('search'));
    $subtitleBits = [$withdrawals->total().' retraits'];
    if ($statusFilter && isset($statusLabels[$statusFilter])) {
        $subtitleBits[] = $statusLabels[$statusFilter];
    }
    if ($methodFilter) {
        $subtitleBits[] = ucfirst($methodFilter);
    }
    if (request('search')) {
        $subtitleBits[] = '« '.request('search').' »';
    }
    $withdrawalsSubtitle = implode(' · ', $subtitleBits);
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- Mobile : bandeau Retraits + outils (pas de relocation JS) --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1>Retraits</h1>
                <p id="adminWithdrawalsSubtitleMobile">{{ $withdrawalsSubtitle }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <button type="button"
                        class="admin-banner-icon-btn{{ $searchOpen ? ' is-active' : '' }}"
                        data-admin-search-toggle="1"
                        aria-controls="adminWithdrawalsSearchPanel"
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
                            aria-label="{{ $filterButtonLabel }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="users-filter-menu">
                        <a href="{{ $filterUrl(['status' => null, 'method' => null]) }}" class="{{ ! $filterActive ? 'is-active' : '' }}">
                            <span>Tous</span>
                        </a>
                        <div class="products-filter-sep">Statut</div>
                        <a href="{{ $filterUrl(['status' => 'pending']) }}" class="{{ $statusFilter === 'pending' ? 'is-active' : '' }}">
                            <span>En attente</span>
                            <span class="filter-count">{{ $stats['pending'] ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'processing']) }}" class="{{ $statusFilter === 'processing' ? 'is-active' : '' }}">
                            <span>En traitement</span>
                            <span class="filter-count">{{ $stats['processing'] ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'completed']) }}" class="{{ $statusFilter === 'completed' ? 'is-active' : '' }}">
                            <span>Terminé</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'failed']) }}" class="{{ $statusFilter === 'failed' ? 'is-active' : '' }}">
                            <span>Échoué</span>
                        </a>
                        @if(isset($methods) && $methods->isNotEmpty())
                            <div class="products-filter-sep">Méthode</div>
                            <a href="{{ $filterUrl(['method' => null]) }}" class="{{ ! $methodFilter ? 'is-active' : '' }}">
                                <span>Toutes</span>
                            </a>
                            @foreach($methods as $method)
                                <a href="{{ $filterUrl(['method' => $method]) }}" class="{{ $methodFilter === $method ? 'is-active' : '' }}">
                                    <span>{{ ucfirst($method) }}</span>
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>

                <a href="{{ route('admin.withdrawals.export', request()->query()) }}" class="btn btn-outline btn-sm btn-round-icon" title="Exporter" aria-label="Exporter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </a>
            </div>
        </div>

        <div id="adminWithdrawalsSearchPanel"
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
                       placeholder="Rechercher un utilisateur"
                       autocomplete="off"
                       value="{{ request('search') }}"
                       aria-label="Rechercher un utilisateur">
            </div>
        </div>
    </div>

    {{-- Desktop : titre + recherche + filtres --}}
    <div class="admin-page-header page-header header-with-search hidden md:flex animate-fadeInUp">
        <div class="header-left admin-mobile-page-head">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Retraits</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5" id="subtitleCount">
                {{ $withdrawalsSubtitle }}
            </p>
        </div>
        <div class="header-right admin-page-header-actions">
            <div class="header-search admin-search-full">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInput"
                           placeholder="Rechercher un utilisateur"
                           class="search-input"
                           autocomplete="off"
                           value="{{ request('search') }}"
                           aria-label="Rechercher un utilisateur">
                    <button type="button" id="clearBtn" class="clear-btn {{ request('search') ? 'visible' : '' }}" title="Effacer" aria-label="Effacer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
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
                <div x-show="open" x-cloak x-transition @click.outside="open = false" class="users-filter-menu">
                    <a href="{{ $filterUrl(['status' => null, 'method' => null]) }}" class="{{ ! $filterActive ? 'is-active' : '' }}">
                        <span>Tous</span>
                    </a>
                    <div class="products-filter-sep">Statut</div>
                    <a href="{{ $filterUrl(['status' => 'pending']) }}" class="{{ $statusFilter === 'pending' ? 'is-active' : '' }}">
                        <span>En attente</span>
                        <span class="filter-count">{{ $stats['pending'] ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'processing']) }}" class="{{ $statusFilter === 'processing' ? 'is-active' : '' }}">
                        <span>En traitement</span>
                        <span class="filter-count">{{ $stats['processing'] ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'completed']) }}" class="{{ $statusFilter === 'completed' ? 'is-active' : '' }}">
                        <span>Terminé</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'failed']) }}" class="{{ $statusFilter === 'failed' ? 'is-active' : '' }}">
                        <span>Échoué</span>
                    </a>
                    @if(isset($methods) && $methods->isNotEmpty())
                        <div class="products-filter-sep">Méthode</div>
                        <a href="{{ $filterUrl(['method' => null]) }}" class="{{ ! $methodFilter ? 'is-active' : '' }}">
                            <span>Toutes</span>
                        </a>
                        @foreach($methods as $method)
                            <a href="{{ $filterUrl(['method' => $method]) }}" class="{{ $methodFilter === $method ? 'is-active' : '' }}">
                                <span>{{ ucfirst($method) }}</span>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>

            <a href="{{ route('admin.withdrawals.export', request()->query()) }}" class="btn btn-outline btn-sm btn-round-icon" title="Exporter" aria-label="Exporter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="p-3 sm:p-4 salang-flash-success border rounded-lg text-sm flex items-center gap-2 animate-fadeInUp">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 salang-flash-error border rounded-lg text-sm flex items-center gap-2 animate-fadeInUp">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistiques — ids sur la valeur (x-ui.stat) pour sync AJAX -->
    <x-ui.stat-grid class="mb-4 animate-fadeInUp" style="animation-delay: 0.05s;">
        <x-ui.stat title="En attente" :value="$stats['pending'] ?? 0" id="kpi-pending" />
        <x-ui.stat title="En traitement" :value="$stats['processing'] ?? 0" id="kpi-processing" />
        <x-ui.stat title="Montant total" :value="number_format($stats['total_amount'] ?? 0, 2) . ' $'" id="kpi-total-amount" />
        <x-ui.stat title="Frais totaux" :value="number_format($stats['total_fees'] ?? 0, 2) . ' $'" id="kpi-total-fees" />
    </x-ui.stat-grid>

    <!-- Liste des retraits -->
    <div class="card p-3 sm:p-4 animate-fadeInUp" style="animation-delay: 0.1s;">
        <div class="hidden md:block">
            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th class="hidden sm:table-cell">Email</th>
                            <th>Montant</th>
                            <th class="hidden md:table-cell">Méthode</th>
                            <th>Statut</th>
                            <th class="hidden lg:table-cell">Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="withdrawalsTableBody">
                        @include('admin.withdrawals.partials.table_rows', ['withdrawals' => $withdrawals])
                    </tbody>
                </table>
            </div>
        </div>

        @include('admin.withdrawals._mobile_list', ['withdrawals' => $withdrawals])

        <x-salang-pagination :paginator="$withdrawals" id="paginationContainer" class="mt-3 sm:mt-4" />
    </div>

</div>

<!-- Modal Rejet -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title">Rejeter le retrait</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">rejeter</strong> ce retrait ?
            <br>
            Veuillez indiquer la raison du rejet.
        </p>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                    Motif du rejet *
                </label>
                <textarea name="reason" rows="3" class="input w-full text-sm" placeholder="Raison du rejet..." required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeRejectModal()" class="btn btn-outline btn-sm flex-1">
                    Annuler
                </button>
                <button type="submit" class="btn btn-danger btn-sm flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = [
        document.getElementById('searchInput'),
        document.getElementById('searchInputMobile'),
    ].filter(Boolean);
    const clearBtn = document.getElementById('clearBtn');
    const tableBody = document.getElementById('withdrawalsTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    let searchTimeout;
    let currentPage = 1;

    const mobileEl = document.getElementById('withdrawalsMobileList');

    function currentParam(name) {
        return new URL(window.location.href).searchParams.get(name) || '';
    }

    function syncSearchInputs(source, query) {
        searchInputs.forEach(function (other) {
            if (other !== source) {
                other.value = query;
            }
        });
        if (clearBtn) {
            clearBtn.classList.toggle('visible', query.length > 0);
        }
    }

    function getSearchQuery() {
        const desktop = document.getElementById('searchInput');
        if (desktop) return desktop.value.trim();
        return searchInputs[0] ? searchInputs[0].value.trim() : '';
    }

    // Fonction pour mettre à jour le tableau, mobile, pagination, KPI
    function updateTable(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Mettre à jour le tableau
        const newTableBody = doc.getElementById('withdrawalsTableBody');
        if (newTableBody) {
            tableBody.innerHTML = newTableBody.innerHTML;
        }

        // Sync mobile (indépendant de la table)
        const newMobile = doc.getElementById('withdrawalsMobileList');
        if (newMobile && mobileEl) {
            mobileEl.innerHTML = newMobile.innerHTML;
        }

        // Mettre à jour la pagination
        const newPagination = doc.getElementById('paginationContainer');
        if (newPagination && paginationContainer) {
            paginationContainer.innerHTML = newPagination.innerHTML;
        }

        // Mettre à jour le compteur (desktop + mobile)
        const newSubtitle = doc.querySelector('#subtitleCount');
        const subtitle = document.getElementById('subtitleCount');
        if (newSubtitle && subtitle) {
            subtitle.innerHTML = newSubtitle.innerHTML;
        }
        const newSubtitleMobile = doc.getElementById('adminWithdrawalsSubtitleMobile');
        const subtitleMobile = document.getElementById('adminWithdrawalsSubtitleMobile');
        if (newSubtitleMobile && subtitleMobile) {
            subtitleMobile.innerHTML = newSubtitleMobile.innerHTML;
        }

        // KPI — ids stables sur le <p> valeur de x-ui.stat
        ['kpi-pending', 'kpi-processing', 'kpi-total-amount', 'kpi-total-fees'].forEach(function (id) {
            const newEl = doc.getElementById(id);
            const currentEl = document.getElementById(id);
            if (newEl && currentEl) {
                currentEl.textContent = newEl.textContent;
            }
        });
    }

    // Fonction pour récupérer les données
    function fetchData(url) {
        if (mobileEl) {
            mobileEl.innerHTML = `
                <div class="flex items-center justify-center gap-3 py-8 text-sm text-[var(--text-secondary)]">
                    <svg class="animate-spin h-5 w-5 text-[var(--primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Recherche en cours…</span>
                </div>
            `;
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.text();
        })
        .then(html => {
            updateTable(html);
        })
        .catch(error => {
            console.error('Erreur:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-8 text-[var(--ui-stat-danger)]">
                        Une erreur est survenue lors de la recherche
                    </td>
                </tr>
            `;
            if (mobileEl) {
                mobileEl.innerHTML = `
                    <div class="py-8 text-center text-sm text-[var(--ui-stat-danger)]">
                        Une erreur est survenue lors de la recherche
                    </div>
                `;
            }
        });
    }

    // Fonction pour construire l'URL avec les paramètres
    function buildUrl(page) {
        const params = new URLSearchParams();
        const search = getSearchQuery();
        const status = currentParam('status');
        const method = currentParam('method');

        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (method) params.set('method', method);
        if (page && page > 1) params.set('page', page);

        const queryString = params.toString();
        return window.location.pathname + (queryString ? '?' + queryString : '');
    }

    // Recherche avec debounce (desktop + mobile)
    searchInputs.forEach(function (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            syncSearchInputs(searchInput, query);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                const url = buildUrl(currentPage);
                fetchData(url);
            }, 300);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                syncSearchInputs(searchInput, '');
                currentPage = 1;
                const url = buildUrl(currentPage);
                fetchData(url);
                this.blur();
            }
        });
    });

    // Raccourci / pour focus (desktop)
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            const active = document.activeElement;
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) {
                return;
            }
            const desktop = document.getElementById('searchInput');
            if (!desktop) return;
            e.preventDefault();
            desktop.focus();
            desktop.select();
        }
    });

    // Bouton clear (desktop)
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInputs.forEach(function (el) { el.value = ''; });
            clearBtn.classList.remove('visible');
            currentPage = 1;
            const url = buildUrl(currentPage);
            fetchData(url);
            const desktop = document.getElementById('searchInput');
            if (desktop) desktop.focus();
        });
    }

    // Gestion de la pagination (délégation d'événements)
    if (paginationContainer) {
        paginationContainer.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            
            const url = new URL(link.href);
            const page = url.searchParams.get('page');
            if (page) {
                e.preventDefault();
                currentPage = parseInt(page) || 1;
                const fetchUrl = buildUrl(currentPage);
                fetchData(fetchUrl);
            }
        });
    }
});

// ============================================================
// MODAL DE REJET
// ============================================================
function openRejectModal(withdrawalId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '{{ route("admin.withdrawals.reject", ["id" => ":id"]) }}'.replace(':id', withdrawalId);
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});
</script>
@endpush
@endsection