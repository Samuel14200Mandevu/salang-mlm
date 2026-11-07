@extends('admin.layouts.app')

@push('styles')
<style>
[x-cloak] { display: none !important; }

.user-row {
    cursor: pointer;
}

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
.users-filter-menu a:hover {
    background: var(--bg-secondary);
}
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
.users-filter-menu a.is-active .filter-count {
    color: var(--primary);
}
</style>
@endpush

@push('admin_mobile_greeting')
    @include('admin.layouts.partials.mobile-greeting', [
        'title' => 'Membres',
        'subtitle' => $users->total() . ' comptes · recherche et actions ci-dessous',
    ])
@endpush

@section('content')
    <!-- En-tête -->
    <div class="admin-page-header page-header flex flex-wrap items-center justify-between gap-3">
        <div class="admin-mobile-page-head">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Gestion des membres
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5" id="adminUsersSubtitle">
                {{ $users->total() }} membres enregistrés
                @php
                    $subtitleFilterLabels = [
                        'active' => 'Actifs',
                        'inactive' => 'Inactifs',
                    ];
                    $subtitleFilter = $subtitleFilterLabels[request('filter')] ?? null;
                @endphp
                @if($subtitleFilter)
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">· {{ $subtitleFilter }}</span>
                @endif
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
            </p>
        </div>
        <div class="admin-sticky-toolbar md:contents">
        <div class="admin-page-header-actions flex items-center gap-2">
            <div class="header-search admin-search-full">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Rechercher un membre"
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden xs:inline">Ajouter</span>
            </a>
            <a href="{{ route('admin.pv.import.index') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span class="hidden xs:inline">Importer PV</span>
            </a>
            @php
                $currentFilter = request('filter');
                $filterLabels = [
                    'active' => 'Actifs',
                    'inactive' => 'Inactifs',
                ];
                $filterButtonLabel = $filterLabels[$currentFilter] ?? 'Filtrer';
                $filterQueryBase = array_filter([
                    'search' => request('search'),
                ], fn ($v) => filled($v));
            @endphp
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button"
                        @click="open = !open"
                        class="btn btn-outline btn-sm sm:btn-md {{ $currentFilter ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                        :aria-expanded="open"
                        aria-haspopup="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span class="hidden xs:inline">{{ $filterButtonLabel }}</span>
                    @if($currentFilter)
                        <span class="inline-flex xs:hidden w-2 h-2 rounded-full bg-[var(--primary)]"></span>
                    @endif
                </button>
                <div x-show="open"
                     x-cloak
                     x-transition
                     @click.outside="open = false"
                     class="users-filter-menu">
                    <a href="{{ route('admin.users', array_merge($filterQueryBase, [])) }}"
                       class="{{ ! $currentFilter ? 'is-active' : '' }}">
                        <span>Tous</span>
                    </a>
                    <a href="{{ route('admin.users', array_merge($filterQueryBase, ['filter' => 'active'])) }}"
                       class="{{ $currentFilter === 'active' ? 'is-active' : '' }}">
                        <span>Actifs</span>
                        <span class="filter-count">{{ $stats['active'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.users', array_merge($filterQueryBase, ['filter' => 'inactive'])) }}"
                       class="{{ $currentFilter === 'inactive' ? 'is-active' : '' }}">
                        <span>Inactifs</span>
                        <span class="filter-count">{{ $stats['inactive'] ?? 0 }}</span>
                    </a>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="p-3 sm:p-4 salang-flash-success border rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 salang-flash-error border rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <x-ui.stat-grid class="mb-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <x-ui.stat title="Total" :value="$stats['total'] ?? $users->total()" id="kpi-total" padding="p-3" />
        <x-ui.stat title="Actifs" :value="$stats['active'] ?? 0" id="kpi-active" padding="p-3" />
        <x-ui.stat title="Inactifs" :value="$stats['inactive'] ?? 0" id="kpi-inactive" padding="p-3" />
        <x-ui.stat title="Avec package" :value="$stats['with_package'] ?? 0" id="kpi-with-package" padding="p-3" />
        <x-ui.stat title="KYC vérifié" :value="$stats['with_kyc'] ?? 0" id="kpi-with-kyc" padding="p-3" />
    </x-ui.stat-grid>

    @include('admin.users._mobile_list', ['users' => $users])

    <div id="paginationContainerMobile" class="md:hidden">
        @if($showUsersListPagination ?? $users->hasPages())
            <x-salang-pagination :paginator="$users" />
        @endif
    </div>

    <!-- Tableau desktop -->
    <div class="card p-3 sm:p-4 hidden md:block">
        <div class="table-wrap" id="tableContainer">
            <table class="table table-striped admin-table-desktop-only" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Membre</th>
                        <th>Code Membre</th>
                        <th>Grade</th>
                        <th>Code Parrain</th>
                        <th>PV</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.users._table_rows', ['users' => $users])
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="mt-3 sm:mt-4">
            @if($showUsersListPagination ?? $users->hasPages())
                <x-salang-pagination :paginator="$users" />
            @endif
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            searchTimeout = setTimeout(() => {
                fetchUsers(query);
            }, 300);
        });
    }

    function fetchUsers(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('tableBody');
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
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newTableBody = doc.getElementById('tableBody');

            if (newTableBody) {
                document.getElementById('tableBody').innerHTML = newTableBody.innerHTML;
            }

            const newMobileList = doc.getElementById('adminUserMobileList');
            const mobileList = document.getElementById('adminUserMobileList');
            if (newMobileList && mobileList) {
                mobileList.innerHTML = newMobileList.innerHTML;
            }
            
            const paginationContainer = document.getElementById('paginationContainer');
            const newPagination = doc.getElementById('paginationContainer');
            if (paginationContainer) {
                paginationContainer.innerHTML = newPagination ? newPagination.innerHTML : '';
            }

            const paginationMobile = document.getElementById('paginationContainerMobile');
            const newPaginationMobile = doc.getElementById('paginationContainerMobile');
            if (paginationMobile) {
                paginationMobile.innerHTML = newPaginationMobile ? newPaginationMobile.innerHTML : '';
            }

            if (typeof window.initSalangPagination === 'function') {
                window.initSalangPagination(document);
            }

            const subtitle = document.getElementById('adminUsersSubtitle');
            const newSubtitle = doc.getElementById('adminUsersSubtitle');
            if (subtitle && newSubtitle) {
                subtitle.innerHTML = newSubtitle.innerHTML;
            }

            ['kpi-total', 'kpi-active', 'kpi-inactive', 'kpi-admins', 'kpi-cashiers', 'kpi-with-package'].forEach(id => {
                const newEl = doc.getElementById(id);
                const currentEl = document.getElementById(id);
                if (newEl && currentEl) {
                    currentEl.textContent = newEl.textContent;
                }
            });
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('tableBody').innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-8 text-[var(--ui-stat-danger)]">
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