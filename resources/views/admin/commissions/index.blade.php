{{-- resources/views/admin/commissions/index.blade.php --}}
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
.commission-row {
    transition: background 0.1s ease;
}
.commission-row:hover {
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

/* Types de commissions */
.type-badge-direct {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
}
.type-badge-indirect {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
}
.type-badge-leadership {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}
.type-badge-retail {
    background: #E6F4EC;
    color: #1F7B4D;
    border-color: #B8DFCC;
}
.type-badge-global {
    background: #E8F0F8;
    color: #065F9C;
    border-color: #C8DCE8;
}
.type-badge-binary {
    background: #FDE8E8;
    color: #B32A2A;
    border-color: #F5C8C8;
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

.admin-page-header.header-with-search {
    position: relative;
    z-index: 30;
    overflow: visible;
}
.admin-page-header-actions,
.admin-sticky-toolbar {
    overflow: visible !important;
}
.admin-kpi-rail.stats-grid {
    position: relative;
    z-index: 1;
}

.users-filter-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 0.35rem);
    min-width: 13rem;
    max-height: min(70vh, 28rem);
    overflow-y: auto;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 0.65rem;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
    padding: 0.35rem;
    z-index: 80;
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

/* ===== HEADER AVEC RECHERCHE À DROITE ===== */
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

.amount-positive {
    color: #1F7B4D;
    font-weight: 700;
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
        grid-template-columns: 1fr !important;
    }
    .header-right {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
@endpush

@push('admin_mobile_greeting')
    @include('admin.layouts.partials.mobile-greeting', [
        'title' => 'Commissions',
        'subtitle' => $commissions->total() . ' lignes · filtres et recherche ci-dessous',
    ])
@endpush

@section('content')
    <!-- En-tête avec recherche à droite -->
    <div class="admin-page-header page-header header-with-search animate-fadeInUp">
        <div class="header-left admin-mobile-page-head">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Commissions</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5" id="adminCommissionsSubtitle">
                {{ $commissions->total() }} commissions
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
                @if(request('type'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Type: {{ ucfirst(request('type')) }}
                    </span>
                @endif
                @if(request('status'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Statut: {{ ucfirst(request('status')) }}
                    </span>
                @endif
            </p>
        </div>
        @php
            $typeFilter = request('type');
            $statusFilter = request('status');
            $periodFilter = request('period');
            $filterActive = filled($typeFilter) || filled($statusFilter) || filled($periodFilter);
            $statusLabels = [
                'pending' => 'En attente',
                'paid' => 'Payé',
                'cancelled' => 'Annulé',
            ];
            $filterButtonLabel = filled($typeFilter)
                ? ucfirst(str_replace('_', ' ', $typeFilter))
                : ($statusLabels[$statusFilter] ?? (filled($periodFilter) ? $periodFilter : 'Filtrer'));
            $filterQueryBase = array_filter([
                'search' => request('search'),
                'type' => $typeFilter,
                'status' => $statusFilter,
                'period' => $periodFilter,
            ], fn ($v) => filled($v));
            $filterUrl = function (array $overrides = []) use ($filterQueryBase) {
                return route('admin.commissions', array_filter(
                    array_merge($filterQueryBase, $overrides),
                    fn ($v) => $v !== null && $v !== ''
                ));
            };
        @endphp
        <div class="admin-sticky-toolbar md:contents">
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
                           value="{{ request('search') }}">
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
                    <a href="{{ $filterUrl(['type' => null, 'status' => null, 'period' => null]) }}" class="{{ ! $filterActive ? 'is-active' : '' }}">
                        <span>Tous</span>
                    </a>

                    <div class="products-filter-sep">Type</div>
                    <a href="{{ $filterUrl(['type' => null]) }}" class="{{ ! $typeFilter ? 'is-active' : '' }}">
                        <span>Tous types</span>
                    </a>
                    @foreach($types as $type)
                        <a href="{{ $filterUrl(['type' => $type]) }}" class="{{ $typeFilter === $type ? 'is-active' : '' }}">
                            <span>{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                        </a>
                    @endforeach

                    <div class="products-filter-sep">Statut</div>
                    <a href="{{ $filterUrl(['status' => null]) }}" class="{{ ! $statusFilter ? 'is-active' : '' }}">
                        <span>Tous statuts</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'pending']) }}" class="{{ $statusFilter === 'pending' ? 'is-active' : '' }}">
                        <span>En attente</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'paid']) }}" class="{{ $statusFilter === 'paid' ? 'is-active' : '' }}">
                        <span>Payé</span>
                    </a>
                    <a href="{{ $filterUrl(['status' => 'cancelled']) }}" class="{{ $statusFilter === 'cancelled' ? 'is-active' : '' }}">
                        <span>Annulé</span>
                    </a>

                    @if($periods->isNotEmpty())
                        <div class="products-filter-sep">Période</div>
                        <a href="{{ $filterUrl(['period' => null]) }}" class="{{ ! $periodFilter ? 'is-active' : '' }}">
                            <span>Toutes périodes</span>
                        </a>
                        @foreach($periods as $period)
                            <a href="{{ $filterUrl(['period' => $period]) }}" class="{{ $periodFilter == $period ? 'is-active' : '' }}">
                                <span>{{ $period }}</span>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Rapport Individuel --}}
            <a href="{{ route('admin.commissions.export-pdf', request()->query()) }}" class="btn btn-outline btn-sm btn-round-icon" title="PDF détaillé" aria-label="PDF détaillé">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </a>

            {{-- Rapport Global --}}
            <a href="{{ route('admin.commissions.export-global-pdf', request()->query()) }}" class="btn btn-primary btn-sm btn-round-icon" title="PDF global" aria-label="PDF global">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </a>
        </div>
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

    <!-- Statistiques -->
    <div class="admin-kpi-rail stats-grid grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp" style="animation-delay: 0.05s;">
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total payé</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--ui-stat-success)]">
                {{ number_format($stats['total_paid'] ?? 0, 2) }} $
            </p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--ui-stat-warning)]">
                {{ number_format($stats['total_pending'] ?? 0, 2) }} $
            </p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Annulé</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--ui-stat-danger)]">
                {{ number_format($stats['total_cancelled'] ?? 0, 2) }} $
            </p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total commissions</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--primary-navy)]">
                {{ $stats['total_count'] ?? 0 }}
            </p>
        </div>
    </div>

    @include('admin.commissions._mobile_list', ['commissions' => $commissions])

    <x-salang-pagination :paginator="$commissions" id="paginationContainerMobile" class="md:hidden" />

    <!-- Liste des commissions desktop -->
    <div class="card p-3 sm:p-4 animate-fadeInUp hidden md:block" style="animation-delay: 0.1s;">
        <div class="table-wrap">
            <table class="table table-striped admin-table-desktop-only">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th class="hidden md:table-cell">De</th>
                        <th class="hidden sm:table-cell">Type</th>
                        <th>Montant</th>
                        <th class="hidden lg:table-cell">Statut</th>
                        <th class="hidden xl:table-cell">Période</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="commissionsTable">
                    @forelse($commissions as $commission)
                        @php
                            $typeLabels = [
                                'direct' => 'Direct',
                                'indirect' => 'Indirect',
                                'leadership' => 'Leadership',
                                'retail' => 'Retail',
                                'global' => 'Global',
                                'binary' => 'Binaire',
                            ];
                            $typeClass = 'type-badge-' . $commission->type;
                            $typeLabel = $typeLabels[$commission->type] ?? ucfirst(str_replace('_', ' ', $commission->type));

                            $statusLabels = [
                                'paid' => 'Payé',
                                'pending' => 'En attente',
                                'cancelled' => 'Annulé',
                            ];
                            $statusClasses = [
                                'paid' => 'badge-success',
                                'pending' => 'badge-warning',
                                'cancelled' => 'badge-danger',
                            ];
                        @endphp
                        <tr class="commission-row">
                            <td class="font-medium text-sm">
                                {{ $commission->user?->name ?? 'N/A' }}
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden md:table-cell">
                                {{ $commission->fromUser?->name ?? 'Système' }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="badge {{ $typeClass }} text-[10px] sm:text-xs">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="amount-positive text-sm">
                                +{{ number_format($commission->amount, 2) }} $
                            </td>
                            <td class="hidden lg:table-cell">
                                <span class="badge {{ $statusClasses[$commission->status] ?? 'badge-warning' }} text-[10px] sm:text-xs">
                                    {{ $statusLabels[$commission->status] ?? ucfirst($commission->status) }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden xl:table-cell">
                                {{ $commission->period ?? 'N/A' }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.commissions.show', $commission->id) }}"
                                   class="btn btn-outline btn-sm btn-icon" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)]">
                                    @if(request('search') || request('type') || request('status') || request('period'))
                                        Aucun résultat pour ces critères
                                    @else
                                        Les commissions apparaîtront ici une fois générées
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-salang-pagination :paginator="$commissions" id="paginationContainer" class="mt-3 sm:mt-4" />
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    let searchTimeout;

    function currentParam(name) {
        return new URL(window.location.href).searchParams.get(name) || '';
    }

    // Recherche avec debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            clearBtn.classList.toggle('visible', query.length > 0);
            
            searchTimeout = setTimeout(() => {
                fetchCommissions(query, currentParam('type'), currentParam('status'), currentParam('period'));
            }, 300);
        });

        // Raccourci ESC
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                clearBtn.classList.remove('visible');
                fetchCommissions('', currentParam('type'), currentParam('status'), currentParam('period'));
                this.blur();
            }
        });
    }

    // Bouton clear
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.classList.remove('visible');
            fetchCommissions('', currentParam('type'), currentParam('status'), currentParam('period'));
            searchInput.focus();
        });
    }

    function fetchCommissions(search, type, status, period) {
        const url = new URL(window.location.href);
        
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        
        if (type) {
            url.searchParams.set('type', type);
        } else {
            url.searchParams.delete('type');
        }
        
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        
        if (period) {
            url.searchParams.set('period', period);
        } else {
            url.searchParams.delete('period');
        }
        
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('commissionsTable');
        const mobileList = document.getElementById('adminCommissionsMobileList');
        const loadingMobile = '<div class="admin-empty-state"><p>Recherche en cours…</p></div>';

        if (mobileList) {
            mobileList.innerHTML = loadingMobile;
        }

        if (!tableBody) return;

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

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newTableBody = doc.getElementById('commissionsTable');
            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            const newMobileList = doc.getElementById('adminCommissionsMobileList');
            if (newMobileList && mobileList) {
                mobileList.innerHTML = newMobileList.innerHTML;
            }

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

            const subtitle = document.getElementById('adminCommissionsSubtitle');
            const newSubtitle = doc.getElementById('adminCommissionsSubtitle');
            if (subtitle && newSubtitle) {
                subtitle.innerHTML = newSubtitle.innerHTML;
            }

            // Mise à jour des statistiques
            const statValues = doc.querySelectorAll('.card-stats .text-xl');
            const currentStats = document.querySelectorAll('.card-stats .text-xl');
            if (statValues.length === currentStats.length && statValues.length > 0) {
                statValues.forEach((stat, index) => {
                    if (currentStats[index]) {
                        currentStats[index].textContent = stat.textContent;
                    }
                });
            }
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
        });
    }
});
</script>
@endpush
@endsection