@extends('admin.layouts.app')

@push('styles')
<style>
    .rank-row:hover { background: var(--bg-hover); }
    .rank-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .btn-sm svg { width: 0.875rem; height: 0.875rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
        .rank-badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Gestion des Rangs</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Configurez les grades et leurs bonus</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('admin.ranks.create') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden xs:inline">Ajouter</span>
                <span class="inline xs:hidden">+</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total rangs</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Actifs</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $stats['active'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau max</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ $ranks->max('min_pv') ?? 0 }} PV</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Bonus max</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $ranks->max('bonus_percentage') ?? 0 }}%</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-5 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">Grade</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">PV min</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">BV min</th>
                        <th class="text-xs sm:text-sm">Bonus</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Utilisateurs</th>
                        <th class="text-xs sm:text-sm hidden xl:table-cell">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ranks ?? [] as $rank)
                        @php
                            $userCount = $stats['users_by_rank'][$rank->id] ?? 0;
                        @endphp
                        <tr class="rank-row">
                            <td class="font-mono text-xs sm:text-sm">#{{ $rank->id }}</td>
                            <td class="font-medium text-sm sm:text-base">
                                <span class="rank-badge">
                                    {{ $rank->name }}
                                </span>
                            </td>
                            <td class="hidden sm:table-cell text-sm sm:text-base">{{ number_format($rank->min_pv) }}</td>
                            <td class="hidden md:table-cell text-sm sm:text-base">{{ number_format($rank->min_bv ?? 0) }}</td>
                            <td class="font-bold text-primary-500 text-sm sm:text-base">{{ $rank->bonus_percentage }}%</td>
                            <td class="hidden lg:table-cell">
                                <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $userCount }} utilisateur(s)</span>
                            </td>
                            <td class="hidden xl:table-cell">
                                <span class="badge {{ $rank->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                    {{ $rank->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.ranks.edit', $rank) }}" 
                                       class="btn btn-outline btn-sm btn-icon" title="Modifier">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.ranks.toggle-status', $rank) }}" 
                                       class="btn btn-outline btn-sm btn-icon" 
                                       title="{{ $rank->is_active ? 'Desactiver' : 'Activer' }}">
                                        @if($rank->is_active)
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <form action="{{ route('admin.ranks.destroy', $rank) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer definitivement ce rang ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm btn-icon text-red-500 hover:text-red-700" title="Supprimer">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                Aucun rang configure
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection