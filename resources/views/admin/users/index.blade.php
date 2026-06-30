@extends('admin.layouts.app')

@push('styles')
<style>
    .user-row:hover { background: var(--bg-hover); }
    .status-badge { transition: all 0.3s ease; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">👥 Utilisateurs</h1>
            <p class="text-[var(--text-secondary)] mt-1">Gérez tous les utilisateurs de la plateforme</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 animate-fadeIn">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Filtres et recherche -->
    <div class="flex flex-wrap items-center gap-3 animate-fadeInUp delay-1">
        <div class="relative flex-1 min-w-[200px] max-w-sm">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" 
                   id="searchInput"
                   placeholder="Rechercher un utilisateur..."
                   class="input pl-9">
        </div>
        <select class="input w-auto min-w-[130px]">
            <option value="">Tous les rôles</option>
            <option value="admin">Admin</option>
            <option value="user">Utilisateur</option>
        </select>
        <select class="input w-auto min-w-[130px]">
            <option value="">Tous les statuts</option>
            <option value="active">Actif</option>
            <option value="inactive">Inactif</option>
        </select>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-2">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th class="hidden md:table-cell">Rôle</th>
                        <th class="hidden lg:table-cell">Package</th>
                        <th class="hidden md:table-cell">Inscrit le</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse($users as $user)
                        <tr class="user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                            <td class="font-mono text-sm">#{{ $user->id }}</td>
                            <td class="font-medium">{{ $user->name }}</td>
                            <td class="text-[var(--text-secondary)]">{{ $user->email }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge {{ $user->is_admin ? 'badge-purple' : 'badge-neutral' }}">
                                    {{ $user->is_admin ? 'Admin' : 'Utilisateur' }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell">{{ $user->package?->name ?? '-' }}</td>
                            <td class="hidden md:table-cell text-sm text-[var(--text-secondary)]">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge status-badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $user->is_active ? '✅ Actif' : '❌ Inactif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-outline btn-sm btn-icon" 
                                       title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.users.toggle-status', $user) }}" 
                                       class="btn btn-outline btn-sm btn-icon" 
                                       title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                        @if($user->is_active)
                                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Supprimer définitivement cet utilisateur ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm btn-icon text-red-500 hover:text-red-700" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="mt-4" id="paginationContainer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('.user-row');
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        rows.forEach(row => {
            const name = row.dataset.name || '';
            const email = row.dataset.email || '';
            const match = name.includes(query) || email.includes(query);
            row.style.display = match ? '' : 'none';
        });
    });
});
</script>
@endpush
@endsection