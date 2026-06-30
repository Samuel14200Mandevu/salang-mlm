@extends('admin.layouts.app')

@push('styles')
<style>
    .user-report-row:hover { background: var(--bg-hover); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">👥 Rapport des utilisateurs</h1>
            <p class="text-[var(--text-secondary)] mt-1">Analyse détaillée des utilisateurs</p>
        </div>
        <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm">
            ← Retour
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total utilisateurs</p>
            <p class="text-2xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Actifs</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['active'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-red-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Inactifs</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['inactive'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">PV moyen</p>
            <p class="text-2xl font-bold text-blue-500">{{ number_format($stats['avg_pv'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Statistiques supplémentaires -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-5">
        <div class="card-stats border-l-4 border-purple-500">
            <p class="text-sm text-[var(--text-secondary)]">BV moyen</p>
            <p class="text-2xl font-bold text-purple-500">{{ number_format($stats['avg_bv'] ?? 0) }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Gains totaux</p>
            <p class="text-2xl font-bold text-yellow-500">${{ number_format($stats['total_earnings'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Avec package</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['with_package'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-red-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Sans package</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['without_package'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Grade</th>
                        <th>Package</th>
                        <th class="text-right">PV</th>
                        <th>Statut</th>
                        <th class="text-right">Inscrit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                        <tr class="user-report-row">
                            <td class="font-medium">{{ $user->name }}</td>
                            <td class="text-[var(--text-secondary)]">{{ $user->email }}</td>
                            <td>{{ $user->rank ?? 'Distributor' }}</td>
                            <td>{{ $user->package?->name ?? 'Aucun' }}</td>
                            <td class="text-right">{{ number_format($user->pv_balance ?? 0) }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-right text-sm text-[var(--text-secondary)]">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                Aucun utilisateur
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($users) && $users->hasPages())
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection