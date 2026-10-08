@extends('admin.layouts.app')

@push('styles')
<style>
.user-row {
    cursor: pointer;
}

@media (max-width: 640px) {
    .grid-cols-6 {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 480px) {
    .grid-cols-6 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .grid-cols-6 {
        grid-template-columns: repeat(3, 1fr);
    }
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
                Gestion des utilisateurs
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5" id="adminUsersSubtitle">
                {{ $users->total() }} utilisateurs/membres enregistrés
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
                           placeholder="Rechercher un utilisateur"
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

    <!-- Statistiques -->
    @php
        $totalUsers = $users->total() ?? 0;
        $activeUsers = isset($stats['active']) ? $stats['active'] : 0;
        $inactiveUsers = isset($stats['inactive']) ? $stats['inactive'] : 0;
        $adminUsers = isset($stats['admins']) ? $stats['admins'] : 0;
        $cashierUsers = isset($stats['cashiers']) ? $stats['cashiers'] : 0;
        $withPackage = isset($stats['with_package']) ? $stats['with_package'] : 0;
    @endphp

    <div class="admin-kpi-rail grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-primary)]">{{ $totalUsers }}</p>
        </div>
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-success)]">{{ $activeUsers }}</p>
        </div>
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-danger)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Inactifs</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-danger)]">{{ $inactiveUsers }}</p>
        </div>
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Admins</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-navy)]">{{ $adminUsers }}</p>
        </div>
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#1A4A7A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Caissiers</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#1A4A7A]">{{ $cashierUsers }}</p>
        </div>
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Avec package</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-warning)]">{{ $withPackage }}</p>
        </div>
    </div>

    @include('admin.users._mobile_list', ['users' => $users])

    <x-salang-pagination :paginator="$users" id="paginationContainerMobile" class="md:hidden" />

    <!-- Tableau desktop -->
    <div class="card p-3 sm:p-4 hidden md:block">
        <div class="table-wrap" id="tableContainer">
            <table class="table table-striped admin-table-desktop-only" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur/Membre</th>
                        <th class="hidden sm:table-cell">Code</th>
                        <th class="hidden md:table-cell">Rôle</th>
                        <th class="hidden lg:table-cell">Package</th>
                        <th class="hidden xl:table-cell">Grade</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.users._table_rows', ['users' => $users])
                </tbody>
            </table>
        </div>

        <x-salang-pagination :paginator="$users" id="paginationContainer" class="mt-3 sm:mt-4" />
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
            const newPagination = doc.getElementById('paginationContainer');
            
            if (newTableBody) {
                document.getElementById('tableBody').innerHTML = newTableBody.innerHTML;
            }

            const newMobileList = doc.getElementById('adminUserMobileList');
            const mobileList = document.getElementById('adminUserMobileList');
            if (newMobileList && mobileList) {
                mobileList.innerHTML = newMobileList.innerHTML;
            }
            
            if (newPagination) {
                const paginationContainer = document.getElementById('paginationContainer');
                if (paginationContainer) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                }
                const paginationMobile = document.getElementById('paginationContainerMobile');
                const newPaginationMobile = doc.getElementById('paginationContainerMobile');
                if (paginationMobile && newPaginationMobile) {
                    paginationMobile.innerHTML = newPaginationMobile.innerHTML;
                }
            }

            const subtitle = document.getElementById('adminUsersSubtitle');
            const newSubtitle = doc.getElementById('adminUsersSubtitle');
            if (subtitle && newSubtitle) {
                subtitle.innerHTML = newSubtitle.innerHTML;
            }
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