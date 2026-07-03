@extends('admin.layouts.app')

@push('styles')
<style>
    .user-row {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .user-row:hover {
        background: var(--bg-hover);
        transform: translateX(4px);
    }
    .status-badge {
        transition: all 0.3s ease;
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
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Utilisateurs</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Liste de tous les utilisateurs</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="hidden xs:inline">Ajouter</span>
            <span class="inline xs:hidden">+</span>
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="relative flex-1 min-w-[140px] sm:min-w-[200px] max-w-xs sm:max-w-sm">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" 
                   id="searchInput"
                   placeholder="Rechercher..."
                   class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        <select id="roleFilter" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base">
            <option value="">Tous les roles</option>
            <option value="1">Admin</option>
            <option value="0">Utilisateur</option>
        </select>
        <select id="statusFilter" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base">
            <option value="">Tous les statuts</option>
            <option value="1">Actif</option>
            <option value="0">Inactif</option>
        </select>
        @if(isset($packages) && count($packages) > 0)
            <select id="packageFilter" class="input w-auto min-w-[130px] text-sm sm:text-base">
                <option value="">Tous les packages</option>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <!-- Statistiques -->
    @php
        $totalUsers = $users->total() ?? 0;
        $activeUsers = 0;
        $inactiveUsers = 0;
        $adminUsers = 0;
        
        if(isset($stats)) {
            $activeUsers = isset($stats['active']) ? $stats['active'] : 0;
            $inactiveUsers = isset($stats['inactive']) ? $stats['inactive'] : 0;
            $adminUsers = isset($stats['admins']) ? $stats['admins'] : 0;
        }
    @endphp
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-2">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $totalUsers }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Actifs</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $activeUsers }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-red-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Inactifs</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">{{ $inactiveUsers }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Admins</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $adminUsers }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">Nom</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Email</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Role</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Package</th>
                        <th class="text-xs sm:text-sm hidden xl:table-cell">Inscrit</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse($users as $user)
                        <tr class="user-row" 
                            data-name="{{ strtolower($user->name) }}" 
                            data-email="{{ strtolower($user->email) }}"
                            data-role="{{ $user->is_admin ? '1' : '0' }}"
                            data-status="{{ $user->is_active ? '1' : '0' }}"
                            data-package="{{ $user->package_id ?? '' }}"
                            onclick="window.location='{{ route('admin.users.show', $user) }}'">
                            <td class="font-mono text-xs sm:text-sm">#{{ $user->id }}</td>
                            <td class="font-medium text-sm sm:text-base">{{ $user->name }}</td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden sm:table-cell">{{ $user->email }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge {{ $user->is_admin ? 'badge-purple' : 'badge-neutral' }} text-[10px] sm:text-xs">
                                    {{ $user->is_admin ? 'Admin' : 'Utilisateur' }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-sm sm:text-base">{{ $user->package?->name ?? '-' }}</td>
                            <td class="hidden xl:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge status-badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-primary btn-sm">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span class="hidden sm:inline">Voir</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Aucun utilisateur
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-3 sm:mt-4" id="paginationContainer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var roleFilter = document.getElementById('roleFilter');
    var statusFilter = document.getElementById('statusFilter');
    var packageFilter = document.getElementById('packageFilter');
    var rows = document.querySelectorAll('.user-row');
    
    function filterRows() {
        var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var role = roleFilter ? roleFilter.value : '';
        var status = statusFilter ? statusFilter.value : '';
        var packageId = packageFilter ? packageFilter.value : '';
        
        rows.forEach(function(row) {
            var name = row.dataset.name || '';
            var email = row.dataset.email || '';
            var rowRole = row.dataset.role || '';
            var rowStatus = row.dataset.status || '';
            var rowPackage = row.dataset.package || '';
            
            var show = true;
            
            if (query && !name.includes(query) && !email.includes(query)) show = false;
            if (role && rowRole !== role) show = false;
            if (status && rowStatus !== status) show = false;
            if (packageId && rowPackage !== packageId) show = false;
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    if (searchInput) searchInput.addEventListener('input', filterRows);
    if (roleFilter) roleFilter.addEventListener('change', filterRows);
    if (statusFilter) statusFilter.addEventListener('change', filterRows);
    if (packageFilter) packageFilter.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection