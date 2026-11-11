{{-- resources/views/admin/orders/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
[x-cloak] { display: none !important; }

.users-filter-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 0.35rem);
    min-width: 12rem;
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

.order-row {
    transition: background 0.1s ease;
}
.order-row:hover {
    background: var(--bg-hover);
}

.order-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.order-status-pending {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}
.order-status-processing {
    background: #E8F0F8;
    color: #065F9C;
    border-color: #C8DCE8;
}
.order-status-completed {
    background: #E6F4EC;
    color: #1F7B4D;
    border-color: #B8DFCC;
}
.order-status-cancelled {
    background: #FDE8E8;
    color: #B32A2A;
    border-color: #F5C8C8;
}

.card-stats {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    transition: border-color 0.15s ease;
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

.btn-primary {
    background: var(--primary-navy);
    color: white;
    border-color: var(--primary-navy);
}
.btn-primary:hover {
    background: var(--primary-navy-dark);
    border-color: var(--primary-navy-dark);
}

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

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1.25rem;
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
.badge-warning {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}
.badge-danger {
    background: #FDE8E8;
    color: #B32A2A;
    border-color: #F5C8C8;
}
.badge-info {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
}
.badge-neutral {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
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
.table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }
.delay-4 { animation-delay: 0.2s; }
.delay-5 { animation-delay: 0.25s; }

@media (max-width: 640px) {
    .header-search {
        width: 100%;
    }
    .header-search .search-wrapper {
        flex: 1;
    }
    .header-search .search-wrapper .search-input {
        width: 100%;
    }
    .header-search .search-wrapper .search-input:focus {
        width: 100%;
    }
    .table thead th, .table tbody td {
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.65rem;
    }
    .card-stats {
        padding: 0.625rem;
    }
    .order-status {
        font-size: 0.55rem;
        padding: 0.1rem 0.4rem;
    }
    .card {
        padding: 0.875rem;
    }
    .stats-grid {
        grid-template-columns: 1fr 1fr !important;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}
</style>
@endpush

@section('content')
@php
    $statusFilter = request('status');
    $filterActive = filled($statusFilter);
    $statusLabels = [
        'pending' => 'En attente',
        'processing' => 'En traitement',
        'completed' => 'Terminées',
        'cancelled' => 'Annulées',
    ];
    $filterButtonLabel = $statusLabels[$statusFilter] ?? 'Filtrer';
    $filterQueryBase = array_filter(['search' => request('search')], fn ($v) => filled($v));
    $filterUrl = fn (array $overrides = []) => route('admin.orders.index', array_filter(
        array_merge($filterQueryBase, $overrides),
        fn ($v) => $v !== null && $v !== ''
    ));
    $searchOpen = filled(request('search'));
    $subtitleBits = [($orders->total() ?? 0).' commandes'];
    if ($statusFilter && isset($statusLabels[$statusFilter])) {
        $subtitleBits[] = $statusLabels[$statusFilter];
    }
    if (request('search')) {
        $subtitleBits[] = '« '.request('search').' »';
    }
    $ordersSubtitle = implode(' · ', $subtitleBits);
@endphp

    {{-- Mobile : bandeau Commandes + outils (pas de relocation JS) --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1>Commandes</h1>
                <p id="adminOrdersSubtitleMobile">{{ $ordersSubtitle }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <button type="button"
                        class="admin-banner-icon-btn{{ $searchOpen ? ' is-active' : '' }}"
                        data-admin-search-toggle="1"
                        aria-controls="adminOrdersSearchPanel"
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
                            <span class="filter-count">{{ $totalOrders ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'pending']) }}" class="{{ $statusFilter === 'pending' ? 'is-active' : '' }}">
                            <span>En attente</span>
                            <span class="filter-count">{{ $pendingCount ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'processing']) }}" class="{{ $statusFilter === 'processing' ? 'is-active' : '' }}">
                            <span>En traitement</span>
                            <span class="filter-count">{{ $processingCount ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'completed']) }}" class="{{ $statusFilter === 'completed' ? 'is-active' : '' }}">
                            <span>Terminées</span>
                            <span class="filter-count">{{ $completedCount ?? 0 }}</span>
                        </a>
                        <a href="{{ $filterUrl(['status' => 'cancelled']) }}" class="{{ $statusFilter === 'cancelled' ? 'is-active' : '' }}">
                            <span>Annulées</span>
                            <span class="filter-count">{{ $cancelledCount ?? 0 }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="adminOrdersSearchPanel"
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
                       placeholder="Rechercher une commande"
                       autocomplete="off"
                       value="{{ request('search') }}"
                       aria-label="Rechercher une commande">
            </div>
        </div>
    </div>

    {{-- Desktop : titre + recherche + filtres --}}
    <div class="admin-page-header page-header flex flex-wrap items-center justify-between gap-3 hidden md:flex animate-fadeInUp">
        <div class="admin-mobile-page-head">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Commandes</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5" id="adminOrdersSubtitle">
                {{ $ordersSubtitle }}
            </p>
        </div>
        <div class="admin-page-header-actions flex items-center gap-2">
            <div class="header-search admin-search-full">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Rechercher une commande"
                           autocomplete="off"
                           value="{{ request('search') }}"
                           aria-label="Rechercher une commande">
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
                        <span class="filter-count">{{ $totalOrders ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'pending']) }}" class="{{ $statusFilter === 'pending' ? 'is-active' : '' }}">
                        <span>En attente</span>
                        <span class="filter-count">{{ $pendingCount ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'processing']) }}" class="{{ $statusFilter === 'processing' ? 'is-active' : '' }}">
                        <span>En traitement</span>
                        <span class="filter-count">{{ $processingCount ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'completed']) }}" class="{{ $statusFilter === 'completed' ? 'is-active' : '' }}">
                        <span>Terminées</span>
                        <span class="filter-count">{{ $completedCount ?? 0 }}</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'cancelled']) }}" class="{{ $statusFilter === 'cancelled' ? 'is-active' : '' }}">
                        <span>Annulées</span>
                        <span class="filter-count">{{ $cancelledCount ?? 0 }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="admin-kpi-rail stats-grid grid grid-cols-2 md:grid-cols-5 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-navy)]">{{ $totalOrders ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-warning)]">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-3 hidden md:block">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-info)]">{{ $processingCount ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-success)]">{{ $completedCount ?? 0 }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-5">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Annulées</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-danger)]">{{ $cancelledCount ?? 0 }}</p>
        </div>
    </div>

    @include('admin.orders._mobile_list', ['orders' => $orders])

    @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
    <x-salang-pagination :paginator="$orders" id="paginationContainerMobile" class="md:hidden" />
    @endif

    <!-- Orders List desktop -->
    <div class="card animate-fadeInUp delay-3 hidden md:block">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commandes</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $orders->total() ?? 0 }} commandes</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped admin-table-desktop-only">
                <thead>
                    <tr>
                        <th>N° commande</th>
                        <th>Client</th>
                        <th class="hidden md:table-cell">Email</th>
                        <th class="hidden sm:table-cell">Articles</th>
                        <th class="text-right">Total</th>
                        <th>Statut</th>
                        <th>Paiement</th>
                    </tr>
                </thead>
                <tbody id="ordersTable">
                    @forelse($orders ?? [] as $order)
                        <tr class="order-row cursor-pointer hover:bg-[var(--bg-secondary)]"
                            data-search="{{ strtolower($order->order_number . ' ' . ($order->user?->name ?? '') . ' ' . ($order->user?->email ?? '')) }}"
                            onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                            <td>
                                <span class="font-mono font-semibold text-[var(--text-primary)] text-xs sm:text-sm">
                                    #{{ $order->order_number }}
                                </span>
                            </td>
                            <td class="font-medium text-sm">
                                {{ $order->user?->name ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->user?->email ?? 'N/A' }}
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->items->count() }} article(s)
                            </td>
                            <td class="text-right font-bold text-[var(--primary-navy)] text-sm">
                                {{ number_format($order->total, 2) }} €
                            </td>
                            <td>
                                <span class="order-status order-status-{{ $order->status }}">
                                    @if($order->status == 'pending') En attente
                                    @elseif($order->status == 'processing') En traitement
                                    @elseif($order->status == 'completed') Terminée
                                    @elseif($order->status == 'cancelled') Annulée
                                    @else {{ ucfirst($order->status) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : ($order->payment_status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $order->payment_status == 'completed' ? 'Payé' : ($order->payment_status == 'pending' ? 'En attente' : 'Échoué') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commande</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les commandes apparaîtront ici</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-salang-pagination :paginator="$orders" id="paginationContainer" class="mt-3 sm:mt-4" />
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

    searchInputs.forEach(function (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            syncSearchInputs(searchInput, query);
            searchTimeout = setTimeout(() => {
                fetchOrders(query, currentStatus());
            }, 300);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                syncSearchInputs(searchInput, '');
                fetchOrders('', currentStatus());
                this.blur();
            }
        });
    });

    // Focus avec la touche / (desktop)
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

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInputs.forEach(function (el) { el.value = ''; });
            clearBtn.classList.remove('visible');
            fetchOrders('', currentStatus());
            const desktop = document.getElementById('searchInput');
            if (desktop) desktop.focus();
        });
    }

    function fetchOrders(query, status) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('ordersTable');
        const mobileList = document.getElementById('adminOrdersMobileList');
        const loadingMobile = '<div class="admin-empty-state"><p>Recherche en cours…</p></div>';

        if (mobileList) {
            mobileList.innerHTML = loadingMobile;
        }

        if (!tableBody) return;

        // Afficher un indicateur de chargement
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
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

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Mettre à jour le tableau
            const newTableBody = doc.getElementById('ordersTable');
            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            const newMobileList = doc.getElementById('adminOrdersMobileList');
            if (newMobileList && mobileList) {
                mobileList.innerHTML = newMobileList.innerHTML;
            }

            // Mettre à jour la pagination
            const newPagination = doc.getElementById('paginationContainer');
            const paginationContainer = document.getElementById('paginationContainer');
            if (newPagination && paginationContainer) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            }
            const newPaginationMobile = doc.getElementById('paginationContainerMobile');
            const paginationMobile = document.getElementById('paginationContainerMobile');
            if (newPaginationMobile && paginationMobile) {
                paginationMobile.innerHTML = newPaginationMobile.innerHTML;
            }

            const subtitle = document.getElementById('adminOrdersSubtitle');
            const newSubtitle = doc.getElementById('adminOrdersSubtitle');
            if (subtitle && newSubtitle) {
                subtitle.innerHTML = newSubtitle.innerHTML;
            }
            const subtitleMobile = document.getElementById('adminOrdersSubtitleMobile');
            const newSubtitleMobile = doc.getElementById('adminOrdersSubtitleMobile');
            if (subtitleMobile && newSubtitleMobile) {
                subtitleMobile.innerHTML = newSubtitleMobile.innerHTML;
            }

            // Mettre à jour les statistiques
            updateStats(doc);
        })
        .catch(error => {
            console.error('Erreur de recherche:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-8 text-[var(--ui-stat-danger)]">
                        Une erreur est survenue lors de la recherche
                    </td>
                </tr>
            `;
        });
    }

    function updateStats(doc) {
        const statValues = doc.querySelectorAll('.card-stats .text-lg, .card-stats .text-xl');
        const currentStats = document.querySelectorAll('.card-stats .text-lg, .card-stats .text-xl');
        
        if (statValues.length === currentStats.length && statValues.length > 0) {
            statValues.forEach((stat, index) => {
                if (currentStats[index]) {
                    currentStats[index].textContent = stat.textContent;
                }
            });
        }
    }

    // Recharger les données lors du retour sur la page
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && searchInputs.length) {
            const currentQuery = (document.getElementById('searchInput') || searchInputs[0]).value.trim();
            if (!currentQuery) {
                fetchOrders('', currentStatus());
            }
        }
    });
});
</script>
@endpush
@endsection