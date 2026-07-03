@extends('admin.layouts.app')

@push('styles')
<style>
    .stat-icon svg { width: 1.25rem; height: 1.25rem; }
    .admin-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); }
    .quick-action:hover { transform: scale(1.02); }
    
    @media (max-width: 640px) {
        .stat-icon { width: 2rem; height: 2rem; }
        .stat-icon svg { width: 1rem; height: 1rem; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Dashboard Admin</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Bonjour, <span class="font-semibold text-primary-500">{{ Auth::user()->name }}</span>
        </p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-4">
        <!-- Utilisateurs -->
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Utilisateurs</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500 truncate">{{ $totalUsers ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-primary flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2 sm:mt-3 flex justify-between text-[10px] sm:text-xs border-t border-[var(--border-color)] pt-2">
                <span class="text-[var(--text-secondary)]">Actifs</span>
                <span class="font-semibold text-green-500">{{ $activeUsers ?? 0 }}</span>
                <span class="text-[var(--text-secondary)]">Inactifs</span>
                <span class="font-semibold text-red-500">{{ ($totalUsers ?? 0) - ($activeUsers ?? 0) }}</span>
            </div>
        </div>

        <!-- Commissions -->
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-green-500">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Commissions</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500 truncate">${{ number_format($totalCommissions ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-success flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2 sm:mt-3 flex justify-between text-[10px] sm:text-xs border-t border-[var(--border-color)] pt-2">
                <span class="text-[var(--text-secondary)]">En attente</span>
                <span class="font-semibold text-yellow-500">${{ number_format($pendingCommissions ?? 0, 2) }}</span>
                <span class="text-[var(--text-secondary)]">Payees</span>
                <span class="font-semibold text-green-500">${{ number_format(($totalCommissions ?? 0) - ($pendingCommissions ?? 0), 2) }}</span>
            </div>
        </div>

        <!-- Retraits -->
        <div class="card-stats animate-fadeInUp delay-3 border-l-4 border-purple-500">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Retraits</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500 truncate">${{ number_format($totalWithdrawn ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-purple flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2 sm:mt-3 flex justify-between text-[10px] sm:text-xs border-t border-[var(--border-color)] pt-2">
                <span class="text-[var(--text-secondary)]">En attente</span>
                <span class="font-semibold text-yellow-500">{{ $pendingWithdrawals ?? 0 }}</span>
                <span class="text-[var(--text-secondary)]">Traites</span>
                <span class="font-semibold text-green-500">{{ ($totalWithdrawals ?? 0) - ($pendingWithdrawals ?? 0) }}</span>
            </div>
        </div>

        <!-- Packages -->
        <div class="card-stats animate-fadeInUp delay-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Packages</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500 truncate">{{ $totalPackages ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-info flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2 sm:mt-3 flex justify-between text-[10px] sm:text-xs border-t border-[var(--border-color)] pt-2">
                <span class="text-[var(--text-secondary)]">Vendus</span>
                <span class="font-semibold text-green-500">{{ $soldPackages ?? 0 }}</span>
                <span class="text-[var(--text-secondary)]">Produits</span>
                <span class="font-semibold text-blue-500">{{ $totalProducts ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Graphique + Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
        <div class="card lg:col-span-2 animate-fadeInUp delay-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Inscriptions mensuelles</h3>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ now()->format('Y') }}</span>
            </div>
            <div class="h-32 sm:h-40 md:h-48 flex items-end gap-1 sm:gap-2">
                @php $max = max(array_column($monthlyData ?? [], 'users') ?: [1]); @endphp
                @foreach($monthlyData ?? [] as $data)
                    @php $height = ($data['users'] / max($max, 1)) * 100; @endphp
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="graph-bar w-full bg-primary-500/30 hover:bg-primary-500"
                             style="height: {{ max(8, $height) }}%">
                            <span class="tooltip">{{ $data['users'] }} inscriptions</span>
                        </div>
                        <span class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] mt-1">{{ substr($data['month'], 0, 3) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card animate-fadeInRight delay-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Distribution</h3>
            <div class="space-y-2 sm:space-y-3">
                @foreach($packageDistribution ?? [] as $pkg)
                    @php $percent = ($pkg->users_count / max($packageDistribution->sum('users_count'), 1)) * 100; @endphp
                    <div>
                        <div class="flex justify-between text-xs sm:text-sm">
                            <span class="text-[var(--text-secondary)] truncate">{{ $pkg->name }}</span>
                            <span class="font-semibold text-primary-500 flex-shrink-0">{{ $pkg->users_count }}</span>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-fill" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                <p class="text-xl sm:text-2xl font-bold text-primary-500">{{ $totalUsers ?? 0 }}</p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total utilisateurs</p>
            </div>
        </div>
    </div>

    <!-- Activites recentes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
        <div class="card animate-fadeInLeft delay-7">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Derniers inscrits</h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $recentUsers->count() ?? 0 }}</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th class="hidden sm:table-cell">Email</th>
                            <th class="text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers ?? [] as $user)
                            <tr>
                                <td class="font-medium text-sm sm:text-base">{{ $user->name }}</td>
                                <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">{{ $user->email }}</td>
                                <td class="text-right">
                                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-[var(--text-secondary)] text-sm">Aucun utilisateur</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-right">
                <a href="{{ route('admin.users') }}" class="text-xs sm:text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                    Voir tous &rarr;
                </a>
            </div>
        </div>

        <div class="card animate-fadeInRight delay-8">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Dernieres commissions</h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $recentCommissions->count() ?? 0 }}</span>
            </div>
            <div class="space-y-1.5 sm:space-y-2 max-h-48 sm:max-h-64 overflow-y-auto custom-scrollbar">
                @forelse($recentCommissions ?? [] as $commission)
                    <div class="activity-item p-2 sm:p-3">
                        <div class="avatar avatar-sm avatar-gradient flex-shrink-0">
                            {{ substr($commission->user?->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm text-[var(--text-primary)] truncate">
                                <span class="font-semibold">{{ $commission->user?->name ?? 'Systeme' }}</span>
                                <span class="text-[var(--text-secondary)]">{{ $commission->type_label ?? 'commission' }}</span>
                            </p>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                                de {{ $commission->fromUser?->name ?? 'Systeme' }}
                            </p>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $commission->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-green-500 flex-shrink-0">+${{ number_format($commission->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-4 text-sm">Aucune commission</p>
                @endforelse
            </div>
            <div class="mt-3 text-right">
                <a href="{{ route('admin.commissions') }}" class="text-xs sm:text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                    Voir toutes &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-9">
        <a href="{{ route('admin.users.create') }}" class="card-stats text-center p-3 sm:p-4 quick-action">
            <div class="stat-icon stat-icon-primary mx-auto mb-1 sm:mb-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <p class="text-[10px] sm:text-sm font-semibold text-[var(--text-primary)]">Ajouter un utilisateur</p>
        </a>
        <a href="{{ route('admin.packages.create') }}" class="card-stats text-center p-3 sm:p-4 quick-action">
            <div class="stat-icon stat-icon-success mx-auto mb-1 sm:mb-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </div>
            <p class="text-[10px] sm:text-sm font-semibold text-[var(--text-primary)]">Creer un package</p>
        </a>
        <a href="{{ route('admin.products.create') }}" class="card-stats text-center p-3 sm:p-4 quick-action">
            <div class="stat-icon stat-icon-info mx-auto mb-1 sm:mb-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="text-[10px] sm:text-sm font-semibold text-[var(--text-primary)]">Ajouter un produit</p>
        </a>
        <a href="{{ route('admin.commissions') }}" class="card-stats text-center p-3 sm:p-4 quick-action">
            <div class="stat-icon stat-icon-purple mx-auto mb-1 sm:mb-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-[10px] sm:text-sm font-semibold text-[var(--text-primary)]">Voir commissions</p>
        </a>
    </div>
</div>
@endsection