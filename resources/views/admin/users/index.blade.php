@extends('admin.layouts.app')

@push('styles')
<style>
    .user-row {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .user-row:hover {
        background: var(--bg-hover);
        transform: translateX(4px);
    }
    .status-badge {
        transition: all 0.3s ease;
    }
    
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-admin {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
    }
    .badge-cashier {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .badge-cashier-principal {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
        font-weight: 600;
    }
    .badge-user {
        background: rgba(107, 114, 128, 0.12);
        color: #6b7280;
    }
    .badge-sponsor {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
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
        font-weight: 700;
        font-size: 0.75rem;
        background: var(--gradient-primary);
        color: white;
        flex-shrink: 0;
    }
    
    .icon {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
    }
    .icon-sm {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }
    .icon-xs {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
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
        .filter-container {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-container .relative {
            max-width: 100% !important;
        }
        .filter-container select {
            width: 100% !important;
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
        .icon-sm {
            width: 0.875rem;
            height: 0.875rem;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-primary-500 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Utilisateurs
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Gestion complète des utilisateurs du système
                @if(request('search'))
                    <span class="text-primary-500 font-medium">
                        - Résultats pour "{{ request('search') }}"
                    </span>
                @endif
                <span class="text-xs text-[var(--text-tertiary)] ml-2">
                    ({{ $users->total() }} trouvés)
                </span>
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
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
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Filtres server-side -->
    <div class="card animate-fadeInUp delay-1 p-3 sm:p-4">
        <form method="GET" action="{{ route('admin.users') }}" class="filter-container flex flex-wrap items-center gap-2 sm:gap-3">
            <div class="relative flex-1 min-w-[140px] sm:min-w-[200px]">
                <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" 
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Rechercher par nom, email, code sponsor ou téléphone..."
                       class="input pl-7 sm:pl-9 text-sm sm:text-base">
            </div>
            
            <select name="role" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base">
                <option value="">Tous les rôles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                <option value="caissier_principal" {{ request('role') == 'caissier_principal' ? 'selected' : '' }}>Caissier Principal</option>
                <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Caissier</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Utilisateur</option>
            </select>
            
            <select name="status" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
            </select>
            
            @if(isset($packages) && count($packages) > 0)
                <select name="package" class="input w-auto min-w-[130px] text-sm sm:text-base">
                    <option value="">Tous les packages</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" {{ request('package') == $package->id ? 'selected' : '' }}>
                            {{ $package->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="btn btn-primary btn-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filtrer
            </button>
            
            <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Réinitialiser
            </a>
        </form>
    </div>

    <!-- Statistiques -->
    @php
        $totalUsers = $users->total() ?? 0;
        $activeUsers = isset($stats['active']) ? $stats['active'] : 0;
        $inactiveUsers = isset($stats['inactive']) ? $stats['inactive'] : 0;
        $adminUsers = isset($stats['admins']) ? $stats['admins'] : 0;
        $cashierUsers = isset($stats['cashiers']) ? $stats['cashiers'] : 0;
        $withPackage = isset($stats['with_package']) ? $stats['with_package'] : 0;
    @endphp
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3 animate-fadeInUp delay-2">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            </div>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $totalUsers }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</p>
            </div>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $activeUsers }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-red-500">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Inactifs</p>
            </div>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">{{ $inactiveUsers }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Admins</p>
            </div>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $adminUsers }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-blue-500">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Caissiers</p>
            </div>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ $cashierUsers }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Avec Package</p>
            </div>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">{{ $withPackage }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
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
                <tbody>
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
                            <td class="font-mono text-xs sm:text-sm">#{{ $user->id }}</td>
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
                                    <span class="text-sm sm:text-base">{{ $user->package->name }}</span>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">-</span>
                                @endif
                            </td>
                            <td class="hidden xl:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge status-badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
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
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucun utilisateur trouvé</p>
                                <p class="text-sm text-[var(--text-tertiary)]">
                                    @if(request('search') || request('role') || request('status') || request('package'))
                                        Aucun résultat ne correspond à vos critères de recherche
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
            <div class="mt-3 sm:mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection