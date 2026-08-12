{{-- resources/views/dashboard/levels/level6.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .level-badge-6 {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-block;
        box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
        animation: pulseGlow-6 2s ease-in-out infinite;
    }
    @keyframes pulseGlow-6 {
        0%, 100% { box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); }
        50% { box-shadow: 0 0 40px rgba(139, 92, 246, 0.5); }
    }
    /* Le reste du style est identique au niveau 5 avec des couleurs violet */
    .stat-card-6 {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        transition: all var(--transition-bounce);
        border-top: 3px solid transparent;
    }
    .stat-card-6:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-top-color: #8b5cf6;
    }
    .stat-card-6 .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon-purple-6 { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .stat-icon-blue-6 { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .stat-icon-green-6 { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .stat-icon-orange-6 { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    
    .quick-action-6 {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        text-align: center;
        transition: all var(--transition-bounce);
        text-decoration: none;
        display: block;
        border-left: 3px solid transparent;
    }
    .quick-action-6:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
        border-left-color: #8b5cf6;
    }
    .quick-action-6 .icon {
        width: 1.5rem;
        height: 1.5rem;
        margin: 0 auto 0.25rem;
        display: block;
        color: #8b5cf6;
    }
    .quick-action-6 .label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .rank-progress-6 .fill {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }
    .rank-progress-bar-6 {
        width: 100%;
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    
    .card-6 {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .network-level-6 {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        padding: 0.75rem;
        text-align: center;
        border-bottom: 3px solid transparent;
        transition: all var(--transition-base);
    }
    .network-level-6:hover { transform: translateY(-2px); }
    .network-level-6 .number { font-size: 1.5rem; font-weight: 700; }
    .network-level-6 .label { font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; }
    .network-level-6.l1 { border-bottom-color: #3b82f6; .number { color: #3b82f6; } }
    .network-level-6.l2 { border-bottom-color: #22c55e; .number { color: #22c55e; } }
    .network-level-6.l3 { border-bottom-color: #f59e0b; .number { color: #f59e0b; } }
    .network-level-6.l4 { border-bottom-color: #8b5cf6; .number { color: #8b5cf6; } }
    .network-level-6.l5 { border-bottom-color: #ec4899; .number { color: #ec4899; } }
    .network-level-6.l6 { border-bottom-color: #8b5cf6; .number { color: #8b5cf6; } }
    
    .activity-item-6 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .activity-item-6:last-child { border-bottom: none; }
    
    .condition-item-6 {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.7rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        transition: all var(--transition-base);
    }
    .condition-item-6.met { border-color: #22c55e; background: rgba(34, 197, 94, 0.05); }
    .condition-item-6.unmet { border-color: #ef4444; background: rgba(239, 68, 68, 0.05); }
    .condition-item-6 .check { font-size: 0.8rem; flex-shrink: 0; font-weight: 700; }
    .condition-item-6 .check.met { color: #22c55e; }
    .condition-item-6 .check.unmet { color: #ef4444; }
    .condition-item-6 .current-value { font-size: 0.6rem; color: var(--text-secondary); white-space: nowrap; }
    
    @media (max-width: 640px) {
        .stat-card-6 { padding: 0.75rem; }
        .quick-action-6 { padding: 0.75rem; }
        .quick-action-6 .icon { width: 1.25rem; height: 1.25rem; }
        .network-level-6 .number { font-size: 1.25rem; }
        .card-6 { padding: 0.875rem; }
        .condition-item-6 { font-size: 0.6rem; padding: 0.2rem 0.4rem; flex-wrap: wrap; }
        .condition-item-6 .current-value { font-size: 0.5rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Compte inactif -->
    @if(!$user->is_active)
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 animate-fadeInUp delay-1">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="font-semibold text-yellow-700 dark:text-yellow-300">Compte inactif</span>
            </div>
            <p class="text-sm text-yellow-600 dark:text-yellow-400 flex-1">Activez votre compte pour recevoir des commissions</p>
            <a href="{{ route('activate.index') }}" class="btn btn-primary btn-sm flex-shrink-0">Activer maintenant</a>
        </div>
    </div>
    @endif

    <!-- Message de bienvenue -->
    <div class="bg-gradient-to-r from-purple-500/10 to-purple-500/5 border border-purple-500/20 rounded-xl p-4 animate-fadeInUp delay-1">
        <div>
            <h3 class="font-bold text-[var(--text-primary)] text-base sm:text-lg">Directeur Envolée atteint</h3>
            <p class="text-sm text-[var(--text-secondary)]">
                Vous etes maintenant au niveau 6 - Directeur Envolée. Vous etes un leader exceptionnel.
                Continuez a developper votre reseau pour atteindre le niveau 7 - Saphire Manager.
            </p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">Continuer a acheter</a>
                <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm">Developper votre reseau</a>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-2">
        <div class="stat-card-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Niveau</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-600">{{ $currentRankLevel ?? 1 }}</p>
                </div>
                <div class="stat-icon stat-icon-purple-6">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Revenu mensuel</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">${{ number_format($stats['today_earnings'] ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-blue-6">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total equipe</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $level1 + $level2 + $level3 + $level4 + ($level5 ?? 0) + ($level6 ?? 0) }}</p>
                </div>
                <div class="stat-icon stat-icon-green-6">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Croissance</p>
                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-orange-500">+{{ number_format($rankProgress['progress'] ?? 0, 1) }}%</p>
                </div>
                <div class="stat-icon stat-icon-orange-6">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile + Additional Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-3">
        
        <!-- Profile Card -->
        <div class="card-6">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="avatar avatar-lg sm:avatar-xl avatar-gradient avatar-ring">
                    @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
                        <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="Avatar">
                    @else
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-[var(--text-primary)] truncate">{{ $user->name }}</h3>
                    <p class="text-xs text-[var(--text-secondary)]">Membre</p>
                    <span class="badge badge-success text-[10px] sm:text-xs inline-block mt-0.5">
                        {{ $currentRankName ?? 'Directeur Envolée' }}
                    </span>
                </div>
            </div>
            
            <div class="mt-3 sm:mt-4 grid grid-cols-2 gap-2 text-xs sm:text-sm">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">ID</p>
                    <p class="font-semibold text-[var(--text-primary)]">#{{ $user->id }}</p>
                </div>
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Inscrit</p>
                    <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">
                        {{ $user->created_at->format('d M Y') }}
                    </p>
                </div>
            </div>
            
            <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mon Parrain</p>
                @if($sponsor)
                    <div class="flex items-center gap-3 mt-1">
                        <div class="avatar avatar-md avatar-gradient">
                            {{ strtoupper(substr($sponsor->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                                {{ $sponsor->name }}
                            </p>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                                {{ $sponsor->email }}
                            </p>
                        </div>
                    </div>
                @else
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Aucun parrain</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Vous etes le premier de votre reseau</p>
                @endif
            </div>

            <div class="mt-2 grid grid-cols-2 gap-2">
                <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Filleuls</p>
                    <p class="font-bold text-primary-500 text-sm">{{ $totalDownlines ?? 0 }}</p>
                </div>
                <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mon Code</p>
                    <p class="font-bold text-primary-500 text-xs font-mono truncate">{{ $user->sponsor_id }}</p>
                </div>
            </div>
            
            <div class="mt-2 grid grid-cols-2 gap-2">
                <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">PV Personnel</p>
                    <p class="font-bold text-primary-500 text-sm">{{ number_format($pvPersonnel ?? 0) }}</p>
                </div>
                <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">PV Cumulé</p>
                    <p class="font-bold text-purple-500 text-sm">{{ number_format($pvCumul ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="lg:col-span-3 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
            
            <div class="card-stats">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">PV Personnel</p>
                        <p class="text-lg sm:text-2xl font-bold text-primary-500 truncate">{{ number_format($pvPersonnel ?? 0) }}</p>
                    </div>
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card-stats">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">PV Cumulé</p>
                        <p class="text-lg sm:text-2xl font-bold text-purple-500 truncate">{{ number_format($pvCumul ?? 0) }}</p>
                    </div>
                    <div class="stat-icon stat-icon-purple">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card-stats">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">PV Mensuel</p>
                        <p class="text-lg sm:text-2xl font-bold text-green-500 truncate">{{ number_format($rankProgress['monthly_pv'] ?? 0) }}</p>
                    </div>
                    <div class="stat-icon stat-icon-success">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card-stats">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">PV Mensuel Cumulé</p>
                        <p class="text-lg sm:text-2xl font-bold text-blue-500 truncate">{{ number_format($pvCumul ?? 0) }}</p>
                    </div>
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rank Progress -->
    <div class="card-6 animate-fadeInUp delay-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Progression vers le prochain grade</h3>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    Actuel: <span class="font-bold text-primary-500">{{ $currentRankName }}</span>
                    @if($rankProgress['next'] != 'Maximum Level')
                        -> Prochain: <span class="font-bold text-purple-500">{{ $rankProgress['next'] }}</span>
                    @endif
                </p>
            </div>
            <span class="text-sm font-bold text-primary-500">{{ number_format($rankProgress['progress'] ?? 0, 1) }}%</span>
        </div>
        <div class="rank-progress-bar-6 rank-progress-6">
            <div class="fill" style="width: {{ $rankProgress['progress'] ?? 0 }}%;"></div>
        </div>
        <div class="flex justify-between text-[10px] text-[var(--text-secondary)] mt-1">
            <span>{{ number_format($pvCumul) }} PV (Cumule)</span>
            <span>{{ number_format($rankProgress['next_pv'] ?? 0) }} PV</span>
        </div>

    </div>

    <!-- Chart + Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-5">
        
        <div class="card-6 lg:col-span-2">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Evolution des gains</h3>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ now()->format('Y') }}</span>
            </div>
            
            <div class="h-40 sm:h-48 flex items-end gap-1 sm:gap-2">
                @php 
                    $max = max(array_column($monthlyData ?? [], 'amount') ?: [1]); 
                @endphp
                @forelse($monthlyData ?? [] as $data)
                    @php 
                        $height = ($data['amount'] / max($max, 1)) * 100; 
                    @endphp
                    <div class="flex-1 flex flex-col items-center group relative">
                        <div class="graph-bar w-full bg-purple-500/30 hover:bg-purple-500 transition-all duration-300"
                             style="height: {{ max(8, $height) }}%;">
                            <span class="tooltip">${{ number_format($data['amount'], 2) }}</span>
                        </div>
                        <span class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] mt-1">
                            {{ substr($data['month'] ?? '', 0, 3) }}
                        </span>
                    </div>
                @empty
                    <div class="w-full text-center text-[var(--text-secondary)] py-8">
                        Aucune donnee disponible
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Activites recentes</h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">
                    {{ $recentActivities->count() ?? 0 }}
                </span>
            </div>
            
            <div class="space-y-2 max-h-48 sm:max-h-60 overflow-y-auto custom-scrollbar">
                @forelse($recentActivities ?? [] as $activity)
                    <div class="activity-item-6">
                        <div class="avatar avatar-sm avatar-gradient flex-shrink-0">
                            {{ substr($activity->fromUser?->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm text-[var(--text-primary)] truncate">
                                <span class="font-semibold">{{ $activity->fromUser?->name ?? 'Systeme' }}</span>
                                <span class="text-[var(--text-secondary)]">
                                    {{ $activity->type_label ?? $activity->type ?? 'action' }}
                                </span>
                            </p>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                        @if($activity->amount)
                            <span class="text-xs sm:text-sm font-bold text-green-500 flex-shrink-0">
                                +${{ number_format($activity->amount, 2) }}
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-8 text-sm">
                        Aucune activite recente
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-6">
        <a href="{{ route('network.index') }}" class="quick-action-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="label">Mon reseau</span>
        </a>
        <a href="{{ route('commissions.index') }}" class="quick-action-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="label">Commissions</span>
        </a>
        <a href="{{ route('wallet.index') }}" class="quick-action-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="label">Portefeuille</span>
        </a>
        <a href="{{ route('rank.index') }}" class="quick-action-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <span class="label">Grades</span>
        </a>
    </div>

</div>
@endsection