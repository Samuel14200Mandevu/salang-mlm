{{-- resources/views/admin/wallets/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
/* ===== CARTES ===== */
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

/* ===== TABLE ===== */
.wallet-row {
    transition: background 0.1s ease;
}
.wallet-row:hover {
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

/* ===== BADGES ===== */
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
.badge-neutral {
    background: #EEF0F3;
    color: var(--text-secondary);
    border-color: #DCDEE3;
}
.badge-warning {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}

/* ===== BOUTONS ===== */
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

.btn-primary {
    background: var(--primary-navy);
    color: white;
    border-color: var(--primary-navy);
}
.btn-primary:hover {
    background: var(--primary-navy-dark);
    border-color: var(--primary-navy-dark);
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

/* ===== INPUTS ===== */
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
    min-width: 11.5rem;
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

/* ===== HEADER AVEC RECHERCHE À DROITE ===== */
@media (min-width: 768px) {
    .header-with-search {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
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

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }

/* ===== RESPONSIVE ===== */
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
    $filterActive = filled($statusFilter);
    $filterButtonLabel = $statusFilter === 'active' ? 'Actifs' : ($statusFilter === 'inactive' ? 'Inactifs' : 'Filtrer');
    $filterQueryBase = array_filter(['search' => request('search')], fn ($v) => filled($v));
    $filterUrl = fn (array $overrides = []) => route('admin.wallets', array_filter(
        array_merge($filterQueryBase, $overrides),
        fn ($v) => $v !== null && $v !== ''
    ));
    $inactiveWallets = max(0, ($totalWallets ?? 0) - ($activeWallets ?? 0));
    $searchOpen = filled(request('search'));
    $subtitleBits = [$wallets->total().' portefeuilles'];
    if ($statusFilter === 'active') {
        $subtitleBits[] = 'Actifs';
    } elseif ($statusFilter === 'inactive') {
        $subtitleBits[] = 'Inactifs';
    }
    if (request('search')) {
        $subtitleBits[] = '« '.request('search').' »';
    }
    $walletsSubtitle = implode(' · ', $subtitleBits);
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- Mobile : bandeau Portefeuilles + outils (pas de relocation JS) --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1>Portefeuilles</h1>
                <p id="adminWalletsSubtitleMobile">{{ $walletsSubtitle }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <button type="button"
                        class="admin-banner-icon-btn{{ $searchOpen ? ' is-active' : '' }}"
                        data-admin-search-toggle="1"
                        aria-controls="adminWalletsSearchPanel"
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
                        <a href="{{ $filterUrl(['status' => null]) }}" class="{{ ! $filterActive ? 'is-active' : '' }}">
                            <span>Tous</span>
                            <span class="filter-count">{{ $totalWallets ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'active']) }}" class="{{ $statusFilter === 'active' ? 'is-active' : '' }}">
                            <span>Actifs</span>
                            <span class="filter-count">{{ $activeWallets ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'inactive']) }}" class="{{ $statusFilter === 'inactive' ? 'is-active' : '' }}">
                            <span>Inactifs</span>
                            <span class="filter-count">{{ $inactiveWallets }}</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('admin.wallets.export', request()->query()) }}" class="btn btn-outline btn-sm btn-round-icon" title="Exporter" aria-label="Exporter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </a>
            </div>
        </div>

        <div id="adminWalletsSearchPanel"
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

    {{-- Desktop : titre + recherche + icônes --}}
    <div class="admin-page-header page-header header-with-search hidden md:flex animate-fadeInUp">
        <div class="header-left admin-mobile-page-head">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Portefeuilles</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5" id="adminWalletsSubtitle">
                {{ $walletsSubtitle }}
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
                    <a href="{{ $filterUrl(['status' => null]) }}" class="{{ ! $filterActive ? 'is-active' : '' }}">
                        <span>Tous</span>
                        <span class="filter-count">{{ $totalWallets ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'active']) }}" class="{{ $statusFilter === 'active' ? 'is-active' : '' }}">
                        <span>Actifs</span>
                        <span class="filter-count">{{ $activeWallets ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'inactive']) }}" class="{{ $statusFilter === 'inactive' ? 'is-active' : '' }}">
                        <span>Inactifs</span>
                        <span class="filter-count">{{ $inactiveWallets }}</span>
                    </a>
                </div>
            </div>

            <a href="{{ route('admin.wallets.export', request()->query()) }}" class="btn btn-outline btn-sm btn-round-icon" title="Exporter" aria-label="Exporter">
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
        <x-ui.stat title="Total" :value="$totalWallets ?? 0" id="kpi-total" />
        <x-ui.stat title="Solde total" :value="number_format($totalBalance ?? 0, 2) . ' $'" id="kpi-balance" />
        <x-ui.stat title="En attente" :value="number_format($pendingBalance ?? 0, 2) . ' $'" id="kpi-pending" />
        <x-ui.stat title="Actifs" :value="$activeWallets ?? 0" id="kpi-active" />
    </x-ui.stat-grid>

    <!-- Liste des portefeuilles -->
    <div class="card p-3 sm:p-4 animate-fadeInUp" style="animation-delay: 0.1s;">
        <div class="hidden md:block">
            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Solde</th>
                            <th class="hidden sm:table-cell">En attente</th>
                            <th class="hidden md:table-cell">Retiré</th>
                            <th class="hidden md:table-cell">Déposé</th>
                            <th class="hidden lg:table-cell">Devise</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="walletsTable">
                        @forelse($wallets as $wallet)
                            <tr class="wallet-row">
                                <td class="font-medium text-sm">
                                    {{ $wallet->user?->name ?? 'N/A' }}
                                    <span class="text-[10px] text-[var(--text-secondary)] block">
                                        {{ $wallet->user?->email ?? '' }}
                                    </span>
                                </td>
                                <td class="font-bold text-[var(--ui-stat-success)] text-sm">
                                    {{ number_format($wallet->balance, 2) }} $
                                </td>
                                <td class="hidden sm:table-cell text-[var(--ui-stat-warning)] text-sm">
                                    {{ number_format($wallet->pending_balance, 2) }} $
                                </td>
                                <td class="hidden md:table-cell text-[var(--text-secondary)] text-sm">
                                    {{ number_format($wallet->total_withdrawn, 2) }} $
                                </td>
                                <td class="hidden md:table-cell text-[var(--text-secondary)] text-sm">
                                    {{ number_format($wallet->total_deposited, 2) }} $
                                </td>
                                <td class="hidden lg:table-cell">
                                    <span class="badge badge-neutral text-[10px] sm:text-xs">
                                        {{ $wallet->currency ?? 'USD' }}
                                    </span>
                                    @if(!$wallet->is_active)
                                        <span class="badge badge-warning text-[10px] sm:text-xs ml-1">
                                            Bloquer
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.wallets.show', $wallet->id) }}"
                                           class="btn btn-outline btn-sm btn-icon" title="Voir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-base sm:text-lg font-medium">Aucun portefeuille</p>
                                    <p class="text-sm text-[var(--text-tertiary)]">
                                        @if(request('search') || request('status'))
                                            Aucun résultat pour ces critères
                                        @else
                                            Les portefeuilles apparaîtront lors de l'inscription des utilisateurs
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('admin.wallets._mobile_list', ['wallets' => $wallets])

        @if($wallets->hasPages())
            <x-salang-pagination :paginator="$wallets" id="paginationContainer" class="mt-3 sm:mt-4" />
        @endif
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
    let searchTimeout;

    function currentStatus() {
        return new URL(window.location.href).searchParams.get('status') || '';
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

    // Recherche avec debounce (desktop + mobile)
    searchInputs.forEach(function (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            syncSearchInputs(searchInput, query);
            searchTimeout = setTimeout(() => {
                fetchWallets(query, currentStatus());
            }, 300);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                syncSearchInputs(searchInput, '');
                fetchWallets('', currentStatus());
                this.blur();
            }
        });
    });

    // Bouton clear (desktop)
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInputs.forEach(function (el) { el.value = ''; });
            clearBtn.classList.remove('visible');
            fetchWallets('', currentStatus());
            const desktop = document.getElementById('searchInput');
            if (desktop) desktop.focus();
        });
    }

    function fetchWallets(search, status) {
        const url = new URL(window.location.href);
        
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('walletsTable');
        if (!tableBody) return;

        const mobileEl = document.getElementById('walletsMobileList');

        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-[var(--primary-navy)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Recherche en cours…</span>
                    </div>
                </td>
            </tr>
        `;

        if (mobileEl) {
            mobileEl.innerHTML = `
                <div class="flex items-center justify-center gap-3 py-8 text-sm text-[var(--text-secondary)]">
                    <svg class="animate-spin h-5 w-5 text-[var(--primary-navy)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Recherche en cours…</span>
                </div>
            `;
        }

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newTableBody = doc.getElementById('walletsTable');
            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            const newMobileList = doc.getElementById('walletsMobileList');
            if (newMobileList && mobileEl) {
                mobileEl.innerHTML = newMobileList.innerHTML;
            }

            const newPagination = doc.getElementById('paginationContainer');
            const paginationContainer = document.getElementById('paginationContainer');
            if (newPagination && paginationContainer) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            }

            // Mise à jour du titre et du compte (desktop + mobile)
            const newSubtitle = doc.getElementById('adminWalletsSubtitle');
            const newSubtitleMobile = doc.getElementById('adminWalletsSubtitleMobile');
            const subtitle = document.getElementById('adminWalletsSubtitle');
            const subtitleMobile = document.getElementById('adminWalletsSubtitleMobile');
            if (subtitle && newSubtitle) {
                subtitle.innerHTML = newSubtitle.innerHTML;
            }
            if (subtitleMobile && newSubtitleMobile) {
                subtitleMobile.innerHTML = newSubtitleMobile.innerHTML;
            }

            // KPI — ids stables sur le <p> valeur de x-ui.stat (textContent = valeur seule)
            ['kpi-total', 'kpi-balance', 'kpi-pending', 'kpi-active'].forEach(function (id) {
                const newEl = doc.getElementById(id);
                const currentEl = document.getElementById(id);
                if (newEl && currentEl) {
                    currentEl.textContent = newEl.textContent;
                }
            });
        })
        .catch(error => {
            console.error('Erreur de recherche:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-8 text-[var(--ui-stat-danger)]">
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
});
</script>
@endpush
@endsection