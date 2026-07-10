@extends('layouts.app')

@push('styles')
<style>
    .stat-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    .stat-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    .stat-icon-primary { background: rgba(90, 182, 56, 0.12); color: var(--primary-500); }
    .stat-icon-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .stat-icon-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .stat-icon-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .stat-icon-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .stat-icon-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    
    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
        cursor: default;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .graph-bar {
        border-radius: 4px 4px 0 0;
        min-height: 8px;
        position: relative;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .graph-bar .tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--bg-card);
        color: var(--text-primary);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        z-index: 10;
    }
    .graph-bar:hover .tooltip {
        opacity: 1;
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
    }
    .avatar-sm { width: 2rem; height: 2rem; font-size: 0.75rem; }
    .avatar-md { width: 2.5rem; height: 2.5rem; font-size: 0.875rem; }
    .avatar-lg { width: 3.5rem; height: 3.5rem; font-size: 1.25rem; }
    .avatar-xl { width: 4.5rem; height: 4.5rem; font-size: 1.5rem; }
    .avatar-gradient {
        background: var(--gradient-primary);
        color: white;
    }
    .avatar-ring {
        border: 3px solid var(--primary-500);
        box-shadow: 0 0 0 4px rgba(90, 182, 56, 0.15);
    }
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .activity-item:hover {
        background: var(--bg-hover);
        border-color: var(--border-color);
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-neutral {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }
    .badge-primary {
        background: rgba(90, 182, 56, 0.12);
        color: var(--primary-500);
    }
    .badge-purple {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
    }
    .badge-gold {
        background: rgba(234, 179, 8, 0.12);
        color: #eab308;
    }
    .badge-pink {
        background: rgba(236, 72, 153, 0.12);
        color: #ec4899;
    }
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 4px;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm { padding: 0.375rem 1rem; font-size: 0.75rem; }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    
    /* Progress bar for rank */
    .rank-progress {
        width: 100%;
        height: 8px;
        background: var(--bg-secondary);
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    .rank-progress .fill {
        height: 100%;
        border-radius: 9999px;
        background: var(--gradient-primary);
        transition: width 0.8s ease;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .animate-fadeInLeft { animation: fadeInLeft 0.6s ease forwards; }
    .animate-fadeInRight { animation: fadeInRight 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    .delay-7 { animation-delay: 0.35s; }
    .delay-8 { animation-delay: 0.40s; }
    
    @media (max-width: 640px) {
        .stat-icon { width: 2.25rem; height: 2.25rem; }
        .stat-icon svg { width: 1rem; height: 1rem; }
        .card-stats { padding: 0.75rem; }
        .avatar-xl { width: 3.5rem; height: 3.5rem; font-size: 1.25rem; }
        .avatar-lg { width: 2.75rem; height: 2.75rem; font-size: 1rem; }
        .card { padding: 0.875rem; }
        .dashboard-grid { grid-template-columns: 1fr !important; }
    }
    
    @media (max-width: 480px) {
        .stat-icon { width: 2rem; height: 2rem; }
        .card-stats { padding: 0.625rem; }
        .card-stats .text-2xl { font-size: 1.125rem; }
        .activity-item { padding: 0.375rem 0.5rem; }
        .card { padding: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Tableau de bord</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Bon retour, <span class="font-semibold text-primary-500">{{ Auth::user()->name }}</span>
        </p>
    </div>

    <!-- Main Statistics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-4">
        
        <div class="card-stats animate-fadeInUp delay-1">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Portefeuille</p>
                    <p class="text-base sm:text-xl md:text-2xl font-bold text-primary-500 truncate">
                        ${{ number_format($walletBalance ?? 0, 2) }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-primary flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card-stats animate-fadeInUp delay-2">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Commissions</p>
                    <p class="text-base sm:text-xl md:text-2xl font-bold text-green-500 truncate">
                        ${{ number_format($totalCommission ?? 0, 2) }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-success flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card-stats animate-fadeInUp delay-3">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Filleuls</p>
                    <p class="text-base sm:text-xl md:text-2xl font-bold text-blue-500 truncate">
                        {{ $totalDownlines ?? 0 }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-info flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        {{-- ✅ GRADE CORRIGÉ --}}
        <div class="card-stats animate-fadeInUp delay-4">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Grade</p>
                    <p class="text-base sm:text-xl md:text-2xl font-bold text-purple-500 truncate">
                        {{-- ✅ Utiliser getOriginal pour la colonne string --}}
                        {{ Auth::user()->getOriginal('rank') ?? 'Distributor' }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-purple flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile + Additional Stats -->
    <div class="dashboard-grid grid grid-cols-1 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Profile Card -->
        <div class="card animate-fadeInLeft delay-2">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="avatar avatar-lg sm:avatar-xl avatar-gradient avatar-ring flex-shrink-0">
                    @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                        <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" alt="Avatar">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-[var(--text-primary)] truncate">{{ Auth::user()->name }}</h3>
                    <p class="text-xs text-[var(--text-secondary)]">Membre</p>
                    {{-- ✅ GRADE CORRIGÉ --}}
                    <span class="badge badge-success text-[10px] sm:text-xs inline-block mt-0.5">
                        {{ Auth::user()->getOriginal('rank') ?? 'Distributor' }}
                    </span>
                </div>
            </div>
            
            <div class="mt-3 sm:mt-4 grid grid-cols-2 gap-2 text-xs sm:text-sm">
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">ID</p>
                    <p class="font-semibold text-[var(--text-primary)]">#{{ Auth::user()->id }}</p>
                </div>
                <div>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Inscrit</p>
                    <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">
                        {{ Auth::user()->created_at->format('d M Y') }}
                    </p>
                </div>
            </div>
            
            <!-- Sponsor Information -->
            <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mon Parrain</p>
                <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base truncate">
                    {{ $sponsor?->name ?? 'Aucun' }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">
                    {{ $sponsor?->email ?? '--' }}
                </p>
            </div>

            <!-- Downlines and Code -->
            <div class="mt-2 grid grid-cols-2 gap-2">
                <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Filleuls</p>
                    <p class="font-bold text-primary-500 text-sm">{{ $totalDownlines ?? 0 }}</p>
                </div>
                <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mon Code</p>
                    <p class="font-bold text-primary-500 text-xs font-mono truncate">{{ Auth::user()->sponsor_id }}</p>
                </div>
            </div>
            
            <!-- PV Info -->
            <div class="mt-2 grid grid-cols-2 gap-2">
                <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">PV</p>
                    <p class="font-bold text-primary-500 text-sm">{{ number_format(Auth::user()->pv_balance ?? 0) }}</p>
                </div>
                <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">BV</p>
                    <p class="font-bold text-primary-500 text-sm">{{ number_format(Auth::user()->bv_balance ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- 3 Additional Stats -->
        <div class="lg:col-span-3 grid grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
            
            <div class="card-stats animate-fadeInUp delay-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">PV Total</p>
                        <p class="text-lg sm:text-2xl font-bold text-primary-500 truncate">
                            {{ number_format(Auth::user()->pv_balance ?? 0) }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-primary flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card-stats animate-fadeInUp delay-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Niveau 1</p>
                        <p class="text-lg sm:text-2xl font-bold text-blue-500 truncate">
                            {{ $level1 ?? 0 }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-info flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card-stats animate-fadeInUp delay-5">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Package</p>
                        <p class="text-sm sm:text-2xl font-bold text-green-500 truncate">
                            {{ Auth::user()->package?->name ?? 'Starter' }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-success flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rank Progress -->
    <div class="card animate-fadeInUp delay-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Progression vers le prochain grade
                </h3>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{-- ✅ GRADE CORRIGÉ --}}
                    Actuel: <span class="font-bold text-primary-500">{{ Auth::user()->getOriginal('rank') ?? 'Distributor' }}</span>
                    @if(isset($rankProgress['next']) && $rankProgress['next'] != 'Maximum Level')
                        → Prochain: <span class="font-bold text-purple-500">{{ $rankProgress['next'] }}</span>
                    @endif
                </p>
            </div>
            <span class="text-sm font-bold text-primary-500">
                {{ number_format($rankProgress['progress'] ?? 0, 1) }}%
            </span>
        </div>
        
        <div class="rank-progress">
            <div class="fill" style="width: {{ $rankProgress['progress'] ?? 0 }}%"></div>
        </div>
        
        <div class="flex justify-between text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">
            <span>{{ number_format($rankProgress['current_pv'] ?? 0) }} PV</span>
            <span>{{ number_format($rankProgress['next_pv'] ?? 0) }} PV</span>
        </div>
        
        @if(isset($rankProgress['pv_needed']) && $rankProgress['pv_needed'] > 0)
            <p class="text-xs text-[var(--text-secondary)] mt-2">
                <span class="text-yellow-500 font-semibold">{{ number_format($rankProgress['pv_needed']) }} PV</span> 
                nécessaires pour atteindre le grade suivant
            </p>
        @elseif(isset($rankProgress['next']) && $rankProgress['next'] == 'Maximum Level')
            <p class="text-xs text-green-500 mt-2 font-semibold">
                🏆 Vous avez atteint le grade maximum !
            </p>
        @endif
    </div>

    <!-- Chart + Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
        
        <div class="card lg:col-span-2 animate-fadeInUp delay-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Évolution des gains</h3>
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
                        <div class="graph-bar w-full bg-primary-500/30 hover:bg-primary-500 transition-all duration-300"
                             style="height: {{ max(8, $height) }}%;">
                            <span class="tooltip">${{ number_format($data['amount'], 2) }}</span>
                        </div>
                        <span class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] mt-1">
                            {{ substr($data['month'] ?? '', 0, 3) }}
                        </span>
                    </div>
                @empty
                    <div class="w-full text-center text-[var(--text-secondary)] py-8">
                        Aucune donnée disponible
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card animate-fadeInRight delay-7">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Activités récentes</h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">
                    {{ $recentActivities->count() ?? 0 }}
                </span>
            </div>
            
            <div class="space-y-2 max-h-48 sm:max-h-60 overflow-y-auto custom-scrollbar">
                @forelse($recentActivities ?? [] as $activity)
                    <div class="activity-item p-2 sm:p-3">
                        <div class="avatar avatar-sm avatar-gradient flex-shrink-0">
                            {{ substr($activity->fromUser?->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm text-[var(--text-primary)] truncate">
                                <span class="font-semibold">{{ $activity->fromUser?->name ?? 'Système' }}</span>
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
                        Aucune activité récente
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-8">
        
        <a href="{{ route('subscriptions.index') }}" class="card-stats text-center p-3 sm:p-4" style="text-decoration: none; display: block;">
            <div class="stat-icon stat-icon-primary mx-auto mb-1 sm:mb-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </div>
            <p class="text-xs sm:text-sm font-semibold text-[var(--text-primary)]">Packages</p>
        </a>
        
        <a href="{{ route('products.index') }}" class="card-stats text-center p-3 sm:p-4" style="text-decoration: none; display: block;">
            <div class="stat-icon stat-icon-success mx-auto mb-1 sm:mb-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="text-xs sm:text-sm font-semibold text-[var(--text-primary)]">Boutique</p>
        </a>
        
        <a href="{{ route('withdrawal.index') }}" class="card-stats text-center p-3 sm:p-4" style="text-decoration: none; display: block;">
            <div class="stat-icon stat-icon-warning mx-auto mb-1 sm:mb-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs sm:text-sm font-semibold text-[var(--text-primary)]">Retrait</p>
        </a>
        
        <a href="{{ route('network.index') }}" class="card-stats text-center p-3 sm:p-4" style="text-decoration: none; display: block;">
            <div class="stat-icon stat-icon-purple mx-auto mb-1 sm:mb-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-xs sm:text-sm font-semibold text-[var(--text-primary)]">Mon Équipe</p>
        </a>
        
    </div>
</div>
@endsection