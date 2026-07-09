@extends('admin.layouts.app')

@push('styles')
<style>
    /* ============================================================
       STYLES SPÉCIFIQUES AU DASHBOARD
       ============================================================ */
    
    /* Cartes de statistiques - Version optimisée */
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    
    .stat-card .stat-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .stat-card:hover .stat-icon {
        transform: rotate(-8deg) scale(1.1);
    }
    
    .stat-icon-primary { background: rgba(90, 182, 56, 0.12); color: var(--primary-500); }
    .stat-icon-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .stat-icon-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .stat-icon-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .stat-icon-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .stat-icon-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    
    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-top: 0.25rem;
    }
    
    .stat-card .stat-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
    }
    
    .stat-card .stat-detail {
        font-size: 0.65rem;
        color: var(--text-tertiary);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
    }
    
    /* Quick actions */
    .quick-action {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
        text-decoration: none !important;
        display: block;
    }
    
    .quick-action:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary-500);
    }
    
    .quick-action .quick-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-size: 1.125rem;
        transition: all 0.3s ease;
    }
    
    .quick-action:hover .quick-icon {
        transform: scale(1.1) rotate(-5deg);
    }
    
    .quick-action .quick-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    /* Activity items */
    .activity-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0.75rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    
    .activity-item:hover {
        background: var(--bg-hover);
        border-color: var(--border-color);
        transform: translateX(4px);
    }
    
    .activity-item .activity-avatar {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
        color: white;
        flex-shrink: 0;
        background: var(--gradient-primary);
    }
    
    .activity-item .activity-content {
        flex: 1;
        min-width: 0;
    }
    
    .activity-item .activity-title {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .activity-item .activity-subtitle {
        font-size: 0.7rem;
        color: var(--text-secondary);
    }
    
    .activity-item .activity-time {
        font-size: 0.65rem;
        color: var(--text-tertiary);
        flex-shrink: 0;
    }
    
    .activity-item .activity-amount {
        font-size: 0.875rem;
        font-weight: 700;
        color: #22c55e;
        flex-shrink: 0;
    }
    
    /* Graph bars */
    .graph-container {
        display: flex;
        align-items: flex-end;
        gap: 0.25rem;
        height: 180px;
        padding-top: 1rem;
    }
    
    .graph-bar-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        justify-content: flex-end;
    }
    
    .graph-bar {
        width: 100%;
        min-height: 6px;
        border-radius: 4px 4px 0 0;
        background: var(--primary-500);
        opacity: 0.6;
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        cursor: pointer;
        max-width: 40px;
        margin: 0 auto;
    }
    
    .graph-bar:hover {
        opacity: 1;
        transform: scaleY(1.05);
        transform-origin: bottom;
    }
    
    .graph-bar .tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--bg-card);
        color: var(--text-primary);
        padding: 0.25rem 0.6rem;
        border-radius: var(--radius-sm);
        font-size: 0.6rem;
        opacity: 0;
        pointer-events: none;
        transition: all 0.2s ease;
        border: 1px solid var(--border-color);
        white-space: nowrap;
        box-shadow: var(--shadow-md);
    }
    
    .graph-bar:hover .tooltip {
        opacity: 1;
        transform: translateX(-50%) translateY(-4px);
    }
    
    .graph-label {
        font-size: 0.6rem;
        color: var(--text-tertiary);
        margin-top: 0.25rem;
        text-align: center;
    }
    
    /* Progress bars */
    .progress-item {
        margin-bottom: 0.75rem;
    }
    
    .progress-item:last-child {
        margin-bottom: 0;
    }
    
    .progress-item .progress-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }
    
    .progress-item .progress-header .progress-label {
        color: var(--text-secondary);
    }
    
    .progress-item .progress-header .progress-value {
        font-weight: 700;
        color: var(--primary-500);
    }
    
    .progress-track {
        width: 100%;
        height: 0.5rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-full);
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        border-radius: var(--radius-full);
        background: var(--gradient-primary);
        transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    
    .progress-fill::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 640px) {
        .stat-card {
            padding: 0.875rem;
        }
        .stat-card .stat-value {
            font-size: 1.125rem;
        }
        .stat-card .stat-icon {
            width: 2.25rem;
            height: 2.25rem;
            font-size: 1rem;
        }
        .stat-card:hover .stat-icon {
            transform: rotate(-4deg) scale(1.05);
        }
        .graph-container {
            height: 120px;
            gap: 0.125rem;
        }
        .graph-bar {
            max-width: 24px;
            min-height: 4px;
        }
        .graph-bar .tooltip {
            font-size: 0.5rem;
            padding: 0.125rem 0.4rem;
        }
        .graph-label {
            font-size: 0.5rem;
        }
        .quick-action {
            padding: 0.75rem;
        }
        .quick-action .quick-icon {
            width: 2rem;
            height: 2rem;
            font-size: 0.875rem;
        }
        .quick-action .quick-label {
            font-size: 0.65rem;
        }
        .activity-item {
            padding: 0.5rem 0.625rem;
        }
        .activity-item .activity-avatar {
            width: 1.75rem;
            height: 1.75rem;
            font-size: 0.625rem;
        }
        .activity-item .activity-title {
            font-size: 0.75rem;
        }
        .activity-item .activity-subtitle {
            font-size: 0.625rem;
        }
        .activity-item .activity-amount {
            font-size: 0.75rem;
        }
        .activity-item .activity-time {
            font-size: 0.55rem;
        }
    }
    
    @media (max-width: 380px) {
        .stat-card {
            padding: 0.75rem;
        }
        .stat-card .stat-value {
            font-size: 1rem;
        }
        .stat-card .stat-icon {
            width: 2rem;
            height: 2rem;
            font-size: 0.875rem;
        }
        .stat-card .stat-detail {
            font-size: 0.55rem;
        }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .stat-card .stat-value {
            font-size: 1.25rem;
        }
        .graph-container {
            height: 150px;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- ============================================================
    EN-TÊTE
    ============================================================ -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
            Dashboard Admin
        </h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Bonjour, <span class="font-semibold text-primary-500">{{ Auth::user()->name }}</span>
        </p>
    </div>

    <!-- ============================================================
    STATISTIQUES - 4 CARTES
    ============================================================ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-4">
        
        <!-- Utilisateurs -->
        <div class="stat-card animate-fadeInUp delay-1">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label truncate">Utilisateurs</p>
                    <p class="stat-value text-primary-500">{{ number_format($totalUsers ?? 0) }}</p>
                </div>
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Actifs</span>
                <span class="font-semibold text-green-500">{{ number_format($activeUsers ?? 0) }}</span>
                <span>Inactifs</span>
                <span class="font-semibold text-red-500">{{ number_format(($totalUsers ?? 0) - ($activeUsers ?? 0)) }}</span>
            </div>
        </div>

        <!-- Commissions -->
        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label truncate">Commissions</p>
                    <p class="stat-value text-green-500">${{ number_format($totalCommissions ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>En attente</span>
                <span class="font-semibold text-yellow-500">${{ number_format($pendingCommissions ?? 0, 2) }}</span>
                <span>Payées</span>
                <span class="font-semibold text-green-500">${{ number_format(($totalCommissions ?? 0) - ($pendingCommissions ?? 0), 2) }}</span>
            </div>
        </div>

        <!-- Retraits -->
        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label truncate">Retraits</p>
                    <p class="stat-value text-purple-500">${{ number_format($totalWithdrawn ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>En attente</span>
                <span class="font-semibold text-yellow-500">{{ number_format($pendingWithdrawals ?? 0) }}</span>
                <span>Traités</span>
                <span class="font-semibold text-green-500">{{ number_format(($totalWithdrawals ?? 0) - ($pendingWithdrawals ?? 0)) }}</span>
            </div>
        </div>

        <!-- Packages & Produits -->
        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label truncate">Packages</p>
                    <p class="stat-value text-blue-500">{{ number_format($totalPackages ?? 0) }}</p>
                </div>
                <div class="stat-icon stat-icon-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Vendus</span>
                <span class="font-semibold text-green-500">{{ number_format($soldPackages ?? 0) }}</span>
                <span>Produits</span>
                <span class="font-semibold text-blue-500">{{ number_format($totalProducts ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- ============================================================
    GRAPHIQUE + DISTRIBUTION
    ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
        
        <!-- Graphique des inscriptions mensuelles -->
        <div class="card lg:col-span-2 animate-fadeInUp delay-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Inscriptions mensuelles
                </h3>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                    {{ now()->format('Y') }}
                </span>
            </div>
            
            <div class="graph-container">
                @php 
                    $max = max(array_column($monthlyData ?? [], 'users') ?: [1]); 
                @endphp
                
                @forelse($monthlyData ?? [] as $data)
                    @php 
                        $height = ($data['users'] / max($max, 1)) * 100; 
                    @endphp
                    <div class="graph-bar-wrapper">
                        <div class="graph-bar" style="height: {{ max(8, $height) }}%;">
                            <span class="tooltip">{{ $data['users'] }} inscriptions</span>
                        </div>
                        <span class="graph-label">{{ substr($data['month'], 0, 3) }}</span>
                    </div>
                @empty
                    <div class="w-full text-center text-[var(--text-secondary)] py-8">
                        Aucune donnée disponible
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Distribution des packages -->
        <div class="card animate-fadeInRight delay-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">
                Distribution des packages
            </h3>
            
            @php 
                $totalPackageUsers = $packageDistribution->sum('users_count') ?? 0; 
            @endphp
            
            @forelse($packageDistribution ?? [] as $pkg)
                @php 
                    $percent = $totalPackageUsers > 0 ? ($pkg->users_count / $totalPackageUsers) * 100 : 0; 
                @endphp
                <div class="progress-item">
                    <div class="progress-header">
                        <span class="progress-label">{{ $pkg->name }}</span>
                        <span class="progress-value">{{ $pkg->users_count }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ $percent }}%;"></div>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-4 text-sm">
                    Aucun package disponible
                </p>
            @endforelse
            
            <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                <p class="text-xl sm:text-2xl font-bold text-primary-500">
                    {{ number_format($totalUsers ?? 0) }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                    Total utilisateurs
                </p>
            </div>
        </div>
    </div>

    <!-- ============================================================
    ACTIVITÉS RÉCENTES
    ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
        
        <!-- Derniers inscrits -->
        <div class="card animate-fadeInLeft delay-7">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Derniers inscrits
                </h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">
                    {{ $recentUsers->count() ?? 0 }}
                </span>
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
                                <td class="font-medium text-sm sm:text-base">
                                    {{ $user->name }}
                                </td>
                                <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                    {{ $user->email }}
                                </td>
                                <td class="text-right">
                                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-[var(--text-secondary)] text-sm">
                                    Aucun utilisateur
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 text-right">
                <a href="{{ route('admin.users') }}" 
                   class="text-xs sm:text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                    Voir tous &rarr;
                </a>
            </div>
        </div>

        <!-- Dernières commissions -->
        <div class="card animate-fadeInRight delay-8">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Dernières commissions
                </h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">
                    {{ $recentCommissions->count() ?? 0 }}
                </span>
            </div>
            
            <div class="space-y-1.5 sm:space-y-2 max-h-48 sm:max-h-64 overflow-y-auto custom-scrollbar">
                @forelse($recentCommissions ?? [] as $commission)
                    <div class="activity-item">
                        <div class="activity-avatar">
                            {{ substr($commission->user?->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">
                                <span class="font-semibold">{{ $commission->user?->name ?? 'Système' }}</span>
                                <span class="text-[var(--text-secondary)]">
                                    {{ $commission->type_label ?? 'commission' }}
                                </span>
                            </p>
                            <p class="activity-subtitle">
                                de {{ $commission->fromUser?->name ?? 'Système' }}
                            </p>
                            <p class="activity-time">
                                {{ $commission->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="activity-amount">
                            +${{ number_format($commission->amount, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-4 text-sm">
                        Aucune commission
                    </p>
                @endforelse
            </div>
            
            <div class="mt-3 text-right">
                <a href="{{ route('admin.commissions') }}" 
                   class="text-xs sm:text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                    Voir toutes &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================================
    ACTIONS RAPIDES
    ============================================================ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-9">
        
        <a href="{{ route('admin.users.create') }}" class="quick-action">
            <div class="quick-icon stat-icon-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <p class="quick-label">Ajouter un utilisateur</p>
        </a>
        
        <a href="{{ route('admin.packages.create') }}" class="quick-action">
            <div class="quick-icon stat-icon-success">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </div>
            <p class="quick-label">Créer un package</p>
        </a>
        
        <a href="{{ route('admin.products.create') }}" class="quick-action">
            <div class="quick-icon stat-icon-info">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="quick-label">Ajouter un produit</p>
        </a>
        
        <a href="{{ route('admin.commissions') }}" class="quick-action">
            <div class="quick-icon stat-icon-purple">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="quick-label">Voir commissions</p>
        </a>
        
    </div>
</div>
@endsection