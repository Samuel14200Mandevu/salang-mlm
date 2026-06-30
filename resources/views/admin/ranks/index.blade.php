@extends('admin.layouts.app')

@push('styles')
<style>
    .rank-row:hover { background: var(--bg-hover); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🏅 Gestion des Rangs</h1>
            <p class="text-[var(--text-secondary)] mt-1">Configurez les grades et leurs bonus</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.ranks.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 animate-fadeIn">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total rangs</p>
            <p class="text-2xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Actifs</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['active'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Niveau max</p>
            <p class="text-2xl font-bold text-blue-500">{{ $ranks->max('min_pv') ?? 0 }} PV</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Bonus max</p>
            <p class="text-2xl font-bold text-purple-500">{{ $ranks->max('bonus_percentage') ?? 0 }}%</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-5">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Grade</th>
                        <th>PV minimum</th>
                        <th>BV minimum</th>
                        <th>Bonus (%)</th>
                        <th>Utilisateurs</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ranks ?? [] as $rank)
                        @php
                            $userCount = $stats['users_by_rank'][$rank->id] ?? 0;
                        @endphp
                        <tr class="rank-row">
                            <td class="font-mono text-sm">#{{ $rank->id }}</td>
                            <td class="font-medium">
                                <span class="rank-badge rank-badge-{{ $rank->id }} text-xs">
                                    @if($rank->id <= 1) 🟤
                                    @elseif($rank->id <= 3) 🔵
                                    @elseif($rank->id <= 5) 🟢
                                    @elseif($rank->id <= 7) 🟣
                                    @elseif($rank->id <= 9) 🔶
                                    @else 💎
                                    @endif
                                    {{ $rank->name }}
                                </span>
                            </td>
                            <td>{{ number_format($rank->min_pv) }}</td>
                            <td>{{ number_format($rank->min_bv ?? 0) }}</td>
                            <td class="font-bold text-primary-500">{{ $rank->bonus_percentage }}%</td>
                            <td>
                                <span class="badge badge-neutral">{{ $userCount }} utilisateur(s)</span>
                            </td>
                            <td>
                                <span class="badge {{ $rank->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $rank->is_active ? 'Actif' : ' Inactif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.ranks.edit', $rank) }}" 
                                       class="btn btn-outline btn-sm btn-icon" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.ranks.toggle-status', $rank) }}" 
                                       class="btn btn-outline btn-sm btn-icon" 
                                       title="{{ $rank->is_active ? 'Désactiver' : 'Activer' }}">
                                        @if($rank->is_active)
                                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <form action="{{ route('admin.ranks.destroy', $rank) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer définitivement ce rang ?')">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                Aucun rang configuré
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection