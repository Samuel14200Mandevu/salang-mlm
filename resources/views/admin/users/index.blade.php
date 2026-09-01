@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.user-row {
    transition: background 0.15s ease, transform 0.1s ease;
    cursor: pointer;
}
.user-row:hover {
    background: var(--bg-hover);
}
.user-row:active {
    transform: scale(0.99);
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}

.badge-success {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.badge-danger {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}
.badge-warning {
    background: rgba(181, 71, 8, 0.12);
    color: #B54708;
    border-color: rgba(181, 71, 8, 0.15);
}
.badge-info {
    background: var(--primary-blue-bg);
    color: var(--primary-blue);
    border-color: var(--primary-blue-border);
}
.badge-neutral {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}
.badge-admin {
    background: var(--primary-blue-bg);
    color: var(--primary-blue);
    border-color: var(--primary-blue-border);
}
.badge-cashier {
    background: rgba(10, 42, 108, 0.08);
    color: var(--primary-blue);
    border-color: rgba(10, 42, 108, 0.12);
}
.badge-cashier-principal {
    background: rgba(10, 42, 108, 0.15);
    color: var(--primary-blue);
    border-color: rgba(10, 42, 108, 0.2);
    font-weight: 700;
}
.badge-user {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}
.badge-sponsor {
    background: rgba(10, 42, 108, 0.08);
    color: var(--primary-blue);
    border-color: rgba(10, 42, 108, 0.12);
    font-family: monospace;
    font-size: 0.7rem;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
    background: var(--primary-blue);
    color: white;
    flex-shrink: 0;
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
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
.btn-md { padding: 0.5rem 1.25rem; font-size: 0.813rem; }

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
}

.btn-danger {
    background: #B91C1C;
    color: white;
    border-color: #B91C1C;
}
.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
}

.btn-warning {
    background: #B54708;
    color: white;
    border-color: #B54708;
}
.btn-warning:hover {
    background: #92400E;
    border-color: #92400E;
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

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.card-stats {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.card-stats:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
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

/* ===== HEADER SEARCH ===== */
.header-search {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-search .search-wrapper {
    position: relative;
}

.header-search .search-wrapper .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

.header-search .search-wrapper .search-input {
    padding: 0.375rem 0.75rem 0.375rem 2.25rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.813rem;
    width: 220px;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, width 0.2s ease;
    outline: none;
}

.header-search .search-wrapper .search-input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
    width: 280px;
}

.header-search .search-wrapper .search-input::placeholder {
    color: var(--text-muted);
}

.header-search .search-wrapper .clear-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 50%;
    display: none;
    transition: background 0.15s ease;
}
.header-search .search-wrapper .clear-btn:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}
.header-search .search-wrapper .clear-btn.visible {
    display: block;
}

.icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
.icon-sm { width: 1rem; height: 1rem; flex-shrink: 0; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }

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
    .btn-sm svg {
        width: 0.875rem;
        height: 0.875rem;
    }
    .badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
    }
    .card-stats {
        padding: 0.625rem;
    }
    .card-stats .text-2xl {
        font-size: 1.25rem;
    }
    .avatar-sm {
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.6rem;
    }
    .icon {
        width: 1rem;
        height: 1rem;
    }
    .card {
        padding: 0.875rem;
    }
    .text-xl {
        font-size: 1.125rem;
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

    <!-- Header with Search -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-5 h-5 sm:w-6 sm:h-6 text-primary-blue mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Utilisateurs
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Gestion complète des utilisateurs du système
                <span class="text-xs text-[var(--text-tertiary)] ml-2" id="resultsCount">
                    ({{ $users->total() }} trouvés)
                </span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div class="header-search">
                <div class="search-wrapper">
                    <span class="search-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Rechercher un utilisateur..."
                           autocomplete="off"
                           value="{{ request('search') }}">
                    <button type="button" id="clearBtn" class="clear-btn {{ request('search') ? 'visible' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
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

    <!-- Flash messages -->
    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics -->
    @php
        $totalUsers = $users->total() ?? 0;
        $activeUsers = isset($stats['active']) ? $stats['active'] : 0;
        $inactiveUsers = isset($stats['inactive']) ? $stats['inactive'] : 0;
        $adminUsers = isset($stats['admins']) ? $stats['admins'] : 0;
        $cashierUsers = isset($stats['cashiers']) ? $stats['cashiers'] : 0;
        $withPackage = isset($stats['with_package']) ? $stats['with_package'] : 0;
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-blue">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            </div>
            <p class="text-lg sm:text-xl font-bold text-primary-blue" id="statTotal">{{ $totalUsers }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#1C7E4A]">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#1C7E4A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</p>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#1C7E4A]">{{ $activeUsers }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#B91C1C]">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Inactifs</p>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#B91C1C]">{{ $inactiveUsers }}</p>
        </div>
        <div class="card-stats border-l-4 border-primary-blue">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Admins</p>
            </div>
            <p class="text-lg sm:text-xl font-bold text-primary-blue">{{ $adminUsers }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#065F9C]">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#065F9C]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Caissiers</p>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#065F9C]">{{ $cashierUsers }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#B54708]">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#B54708]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Avec Package</p>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[#B54708]">{{ $withPackage }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="card animate-fadeInUp delay-2 p-3 sm:p-4">
        <div class="table-wrap" id="tableContainer">
            <table class="table table-striped" id="usersTable">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Code Sponsor</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Rôle</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Package</th>
                        <th class="text-xs sm:text-sm hidden xl:table-cell">Inscrit</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($users as $user)
                        @php
                            $roleName = $user->getRoleNames()->first() ?? 'user';

                            $roleDisplay = 'Utilisateur';
                            $badgeClass = 'badge-user';

                            if($roleName === 'admin') {
                                $roleDisplay = 'Administrateur';
                                $badgeClass = 'badge-admin';
                            } elseif($roleName === 'cashier') {
                                $roleDisplay = 'Caissier';
                                $badgeClass = 'badge-cashier';
                            } elseif($roleName === 'caissier_principal') {
                                $roleDisplay = 'Caissier Principal';
                                $badgeClass = 'badge-cashier-principal';
                            }

                            if($user->hasRole('caissier_principal') && $roleName !== 'caissier_principal') {
                                $roleDisplay = 'Caissier Principal';
                                $badgeClass = 'badge-cashier-principal';
                            } elseif($user->hasRole('cashier') && $roleName !== 'cashier' && $roleName !== 'caissier_principal') {
                                $roleDisplay = 'Caissier';
                                $badgeClass = 'badge-cashier';
                            } elseif($user->hasRole('admin') && $roleName !== 'admin') {
                                $roleDisplay = 'Administrateur';
                                $badgeClass = 'badge-admin';
                            }
                        @endphp
                        <tr class="user-row" onclick="window.location='{{ route('admin.users.show', $user) }}'">
                            <td class="font-mono text-xs sm:text-sm">{{ $user->id }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar-sm hidden sm:flex">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm sm:text-base">{{ $user->name }}</div>
                                        <div class="text-xs text-[var(--text-secondary)]">{{ $user->email }}</div>
                                        @if($user->phone && $user->phone !== 'N/A')
                                            <div class="text-xs text-[var(--text-tertiary)]">{{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell">
                                @if($user->sponsor_id)
                                    <span class="badge badge-sponsor text-[10px] sm:text-xs">
                                        {{ $user->sponsor_id }}
                                    </span>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">-</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell">
                                <span class="badge {{ $badgeClass }} text-[10px] sm:text-xs">
                                    {{ $roleDisplay }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell">
                                @if($user->package)
                                    <span class="text-sm">{{ $user->package->name }}</span>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">-</span>
                                @endif
                            </td>
                            <td class="hidden xl:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="btn btn-primary btn-sm"
                                       title="Voir le profil">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span class="hidden sm:inline">Voir</span>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-warning btn-sm"
                                       title="Modifier">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span class="hidden sm:inline">Modifier</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="noResultsRow">
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucun utilisateur trouvé</p>
                                <p class="text-sm text-[var(--text-tertiary)]">
                                    @if(request('search'))
                                        Aucun résultat ne correspond à votre recherche "{{ request('search') }}"
                                    @else
                                        Commencez par ajouter votre premier utilisateur
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
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
    const clearBtn = document.getElementById('clearBtn');
    const searchForm = document.getElementById('searchForm');

    let searchTimeout = null;
    let currentUrl = window.location.href;

    // Fonction de recherche (navigation avec paramètres)
    function performSearch() {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(function() {
            const search = searchInput.value.trim();

            // Afficher/masquer le bouton clear
            clearBtn.classList.toggle('visible', search.length > 0);

            // Construire l'URL avec le paramètre search
            const baseUrl = '{{ route('admin.users') }}';
            const params = new URLSearchParams();

            if (search) {
                params.append('search', search);
            }

            const newUrl = baseUrl + '?' + params.toString();

            // Rediriger vers la nouvelle URL
            if (newUrl !== currentUrl) {
                window.location.href = newUrl;
            }
        }, 400);
    }

    // Recherche en temps réel
    searchInput.addEventListener('input', performSearch);

    // Raccourci ESC pour effacer
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            clearBtn.classList.remove('visible');
            const baseUrl = '{{ route('admin.users') }}';
            if (window.location.href !== baseUrl) {
                window.location.href = baseUrl;
            }
            this.blur();
        }
    });

    // Bouton clear
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.classList.remove('visible');
        const baseUrl = '{{ route('admin.users') }}';
        if (window.location.href !== baseUrl) {
            window.location.href = baseUrl;
        }
        searchInput.focus();
    });

    // Raccourci / pour focus sur la recherche
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            const active = document.activeElement;
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) {
                return;
            }
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });
});
</script>
@endpush
@endsection