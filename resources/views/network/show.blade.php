@extends('layouts.app')

@push('styles')
<style>
    .profile-header {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
    }
    
    .avatar-xxl {
        width: 5rem;
        height: 5rem;
        font-size: 2rem;
    }
    
    .downline-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .downline-card:hover {
        border-color: var(--primary-500);
        transform: translateX(4px);
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.1);
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        flex-shrink: 0;
        color: white;
    }
    .avatar-sm { width: 2rem; height: 2rem; font-size: 0.75rem; }
    .avatar-md { width: 2.5rem; height: 2.5rem; font-size: 0.875rem; }
    .avatar-lg { width: 3.5rem; height: 3.5rem; font-size: 1.25rem; }
    .avatar-xl { width: 4.5rem; height: 4.5rem; font-size: 1.5rem; }
    .avatar-xxl { width: 5rem; height: 5rem; font-size: 2rem; }
    
    .avatar-gradient { background: var(--gradient-primary); }
    .avatar-success { background: #22c55e; }
    .avatar-danger { background: #ef4444; }
    .avatar-info { background: #3b82f6; }
    .avatar-purple { background: #8b5cf6; }
    .avatar-warning { background: #f59e0b; }
    .avatar-gold { background: #eab308; }
    .avatar-neutral { background: #6b7280; }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    
    .rank-level-1 { background: rgba(107, 114, 128, 0.12); color: #6b7280; }
    .rank-level-2 { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .rank-level-3 { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .rank-level-4 { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .rank-level-5 { background: rgba(234, 179, 8, 0.12); color: #eab308; }
    .rank-level-6 { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .rank-level-7 { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    
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
    .btn-sm { padding: 0.375rem 1rem; font-size: 0.75rem; }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    
    @media (max-width: 640px) {
        .profile-header { padding: 1rem; }
        .avatar-xxl { width: 3.5rem; height: 3.5rem; font-size: 1.2rem; }
        .downline-card { padding: 0.5rem 0.75rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- ===== HEADER ===== -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Profil du membre</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Détails et réseau de {{ $member->name }}</p>
        </div>
        <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <!-- ===== PROFIL DU MEMBRE ===== -->
    <div class="profile-header animate-fadeInUp delay-1">
        <div class="flex flex-wrap items-start gap-4">
            <div class="avatar avatar-xxl avatar-gradient">
                {{ strtoupper(substr($member->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">{{ $member->name }}</h2>
                <div class="flex flex-wrap gap-2 mt-1">
                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $member->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span class="badge badge-info">Niveau {{ $memberStats['level'] ?? 1 }}</span>
                    @php
                        $rankInfo = $controller->getUserRankInfo($member);
                        $rankName = $rankInfo['name'];
                        $rankLevel = $rankInfo['level'];
                        $rankColor = $controller->getRankColor($rankLevel);
                    @endphp
                    <span class="badge {{ $rankColor }}">{{ $rankName }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                    <div>
                        <p class="text-xs text-[var(--text-secondary)]">Email</p>
                        <p class="text-sm font-medium">{{ $member->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[var(--text-secondary)]">Code de parrain</p>
                        <p class="text-sm font-mono text-primary-500 font-bold">{{ $member->sponsor_id }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[var(--text-secondary)]">Inscrit le</p>
                        <p class="text-sm">{{ $member->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== STATISTIQUES DU MEMBRE ===== -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 animate-fadeInUp delay-2">
        <div class="card p-3 text-center border-l-4 border-primary-500">
            <p class="text-xl sm:text-2xl font-bold text-primary-500">{{ $memberStats['total_downlines'] ?? 0 }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Filleuls</p>
        </div>
        <div class="card p-3 text-center border-l-4 border-green-500">
            <p class="text-xl sm:text-2xl font-bold text-green-500">{{ $memberStats['active_downlines'] ?? 0 }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</p>
        </div>
        <div class="card p-3 text-center border-l-4 border-blue-500">
            <p class="text-xl sm:text-2xl font-bold text-blue-500">{{ number_format($memberStats['total_pv'] ?? 0) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">PV Total</p>
        </div>
        <div class="card p-3 text-center border-l-4 border-purple-500">
            <p class="text-xl sm:text-2xl font-bold text-purple-500">{{ number_format($member->pv_balance ?? 0) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">PV Personnel</p>
        </div>
    </div>

    <!-- ===== LISTE DES FILLEULS ===== -->
    <div class="card animate-fadeInUp delay-3">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                Filleuls de {{ $member->name }}
                <span class="text-xs text-[var(--text-secondary)] font-normal">({{ $downlines->count() }})</span>
            </h3>
        </div>

        @if($downlines->count() > 0)
            <div class="space-y-2">
                @foreach($downlines as $downline)
                    @php
                        $rankInfo = $controller->getUserRankInfo($downline);
                        $rankName = $rankInfo['name'];
                        $rankLevel = $rankInfo['level'];
                        $rankColor = $controller->getRankColor($rankLevel);
                        $avatarColor = $controller->getAvatarColor($downline);
                        $level = $downline->level ?? 1;
                    @endphp
                    <div class="downline-card flex flex-wrap items-center justify-between gap-2" onclick="navigateToUser({{ $downline->id }})">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="avatar avatar-md {{ $avatarColor }}">
                                {{ strtoupper(substr($downline->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-[var(--text-primary)] text-sm truncate">{{ $downline->name }}</p>
                                <p class="text-[10px] text-[var(--text-secondary)] font-mono truncate">Code: {{ $downline->sponsor_id }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                            <span class="badge {{ $rankColor }} text-[10px]">{{ $rankName }}</span>
                            <span class="badge badge-info text-[10px]">Niv. {{ $level }}</span>
                            <span class="badge {{ $downline->is_active ? 'badge-success' : 'badge-danger' }} text-[10px]">
                                {{ $downline->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="text-xs font-semibold text-primary-500">{{ number_format($downline->pv_balance ?? 0) }} PV</span>
                            <button class="btn btn-primary btn-sm text-[10px]" onclick="event.stopPropagation(); navigateToUser({{ $downline->id }})">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span class="hidden sm:inline">Voir</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-[var(--text-secondary)]">
                <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="font-medium">Aucun filleul</p>
                <p class="text-sm text-[var(--text-tertiary)] mt-1">Ce membre n'a pas encore de filleuls dans son réseau</p>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
// ============================================================
// NAVIGATION - CORRIGÉ
// ============================================================
const BASE_URL = '{{ url("/") }}';

function navigateToUser(userId) {
    window.location.href = BASE_URL + '/network/show/' + userId;
}
</script>
@endpush
@endsection