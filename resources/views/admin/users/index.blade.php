@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-navy: #0F2B4F;
    --primary-navy-dark: #091E3B;
    --primary-navy-light: #1A3F6A;
    --bg-base: #F5F6F8;
    --bg-card: #FFFFFF;
    --bg-secondary: #EEF0F3;
    --bg-hover: #E8EAEE;
    --text-primary: #1A1A1E;
    --text-secondary: #4A4A52;
    --text-tertiary: #7A7A82;
    --border-color: #DCDEE3;
    --border-light: #E8EAEE;
    --success: #1F7B4D;
    --danger: #B32A2A;
    --warning: #A65A0E;
    --info: #0A2A6C;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-base);
    color: var(--text-primary);
}

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
    border-radius: 8px;
    padding: 0.875rem 1rem;
    transition: border-color 0.15s ease;
}

/* ===== TABLE ===== */
.user-row {
    transition: background 0.1s ease;
    cursor: pointer;
}
.user-row:hover {
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
.badge-neutral {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}
.badge-admin {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
}
.badge-cashier {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
}
.badge-cashier-principal {
    background: #D4DFED;
    color: var(--primary-navy);
    border-color: #B0C5DB;
}
.badge-user {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}
.badge-sponsor {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
    font-family: monospace;
    font-size: 0.7rem;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
    background: var(--primary-navy);
    color: white;
    flex-shrink: 0;
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
.btn-md { padding: 0.5rem 1.25rem; font-size: 0.813rem; }

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

.btn-warning {
    background: #A65A0E;
    color: white;
    border-color: #A65A0E;
}
.btn-warning:hover {
    background: #87480B;
    border-color: #87480B;
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

.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-secondary:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

/* ===== RECHERCHE ===== */
.header-search .search-wrapper {
    position: relative;
}

.header-search .search-wrapper .search-input {
    padding: 0.375rem 0.75rem 0.375rem 2.25rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.813rem;
    width: 220px;
    transition: border-color 0.15s ease, width 0.2s ease;
    outline: none;
}

.header-search .search-wrapper .search-input:focus {
    border-color: var(--primary-navy);
    width: 280px;
}

.header-search .search-wrapper .search-input::placeholder {
    color: var(--text-tertiary);
}

/* ===== RESPONSIVE ===== */
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
    .badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
    }
    .card-stats {
        padding: 0.625rem;
    }
    .avatar-sm {
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.6rem;
    }
    .card {
        padding: 0.875rem;
    }
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

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Gestion des utilisateurs
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $users->total() }} utilisateurs enregistrés
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
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="p-3 sm:p-4 bg-[#E6F4EC] border border-[#B8DFCC] rounded-lg text-[#1F7B4D] text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-[#FDE8E8] border border-[#F5C8C8] rounded-lg text-[#B32A2A] text-sm flex items-center gap-2">
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

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--primary-navy)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-navy)]">{{ $totalUsers }}</p>
        </div>
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#1F7B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#1F7B4D]">{{ $activeUsers }}</p>
        </div>
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[#B32A2A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Inactifs</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#B32A2A]">{{ $inactiveUsers }}</p>
        </div>
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--primary-navy)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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
                <svg class="w-4 h-4 text-[#A65A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Avec package</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#A65A0E]">{{ $withPackage }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card p-3 sm:p-4">
        <div class="table-wrap" id="tableContainer">
            <table class="table table-striped" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th class="hidden sm:table-cell">Code sponsor</th>
                        <th class="hidden md:table-cell">Rôle</th>
                        <th class="hidden lg:table-cell">Package</th>
                        <th class="hidden xl:table-cell">Inscrit</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.users._table_rows', ['users' => $users])
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-3 sm:mt-4" id="paginationContainer">
                {{ $users->appends(request()->query())->links() }}
            </div>
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
            const newPagination = doc.getElementById('paginationContainer');
            
            if (newTableBody) {
                document.getElementById('tableBody').innerHTML = newTableBody.innerHTML;
            }
            
            if (newPagination) {
                const paginationContainer = document.getElementById('paginationContainer');
                if (paginationContainer) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                }
            }

            // Mise à jour du titre
            const title = document.querySelector('h1');
            const subtitle = document.querySelector('.text-sm.text-\\[var\\(--text-secondary\\)\\]');
            if (title && subtitle) {
                const totalMatch = html.match(/(\d+)\s+utilisateurs?/);
                if (totalMatch) {
                    subtitle.textContent = totalMatch[0];
                }
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('tableBody').innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-8 text-[#B32A2A]">
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