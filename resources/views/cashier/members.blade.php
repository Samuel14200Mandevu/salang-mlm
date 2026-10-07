@extends('cashier.layouts.app')

@section('title', 'Membres')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Gestion des membres
            </h1>
            <p id="membersSubtitle" class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $members->total() }} membres enregistrés
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div class="header-search">
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
            <a href="{{ route('cashier.members.create') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden xs:inline">Nouveau membre</span>
            </a>
        </div>
    </div>

    {{-- STATISTIQUES --}}
    @php
        $totalMembers = $members->total() ?? 0;
        $activeMembers = isset($stats['active']) ? $stats['active'] : 0;
        $inactiveMembers = isset($stats['inactive']) ? $stats['inactive'] : 0;
        $withCommissions = isset($stats['with_commissions']) ? $stats['with_commissions'] : 0;
        $totalOrders = isset($stats['orders']) ? $stats['orders'] : 0;
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--cashier-stat-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--cashier-stat-primary)]">{{ $totalMembers }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--cashier-stat-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--cashier-stat-success)]">{{ $activeMembers }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--cashier-stat-danger)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Inactifs</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--cashier-stat-danger)]">{{ $inactiveMembers }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--cashier-stat-warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Commissions</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--cashier-stat-warning)]">{{ $withCommissions }}</p>
        </div>
    </div>

    {{-- LISTE --}}
    <div class="card p-3 sm:p-4">
        <div class="table-wrap" id="tableContainer">
            <table class="table table-striped" id="membersTable">
                <thead>
                    <tr>
                        <th>Membre</th>
                        <th class="hidden sm:table-cell">Code sponsor</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th class="hidden md:table-cell">Total CASH</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('cashier.members._table_rows', ['members' => $members])
                </tbody>
            </table>
        </div>

        @if($members->hasPages())
            <x-salang-pagination :paginator="$members" id="paginationContainer" class="mt-3 sm:mt-4" />
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
                fetchMembers(query);
            }, 300);
        });
    }

    function fetchMembers(query) {
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
                <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-[var(--cashier-stat-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.text())
            .then((html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newTableBody = doc.getElementById('tableBody');
                const newPagination = doc.getElementById('paginationContainer');
                const newSubtitle = doc.getElementById('membersSubtitle');

                if (newTableBody) {
                    tableBody.innerHTML = newTableBody.innerHTML;
                }

                const paginationContainer = document.getElementById('paginationContainer');
                if (paginationContainer && newPagination) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                } else if (paginationContainer && !newPagination) {
                    paginationContainer.innerHTML = '';
                }

                if (newSubtitle) {
                    const subtitle = document.getElementById('membersSubtitle');
                    if (subtitle) {
                        subtitle.innerHTML = newSubtitle.innerHTML;
                    }
                }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', url.toString());
                }
            })
            .catch(() => {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-8 text-[var(--cashier-stat-danger)]">
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