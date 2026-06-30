@extends('layouts.app')

@push('styles')
<style>
    .rank-progress-container {
        position: relative;
        padding: 0.5rem;
    }
    .rank-progress-bar {
        height: 12px;
        border-radius: var(--radius-full);
        background: var(--bg-secondary);
        overflow: hidden;
        position: relative;
    }
    .rank-progress-fill {
        height: 100%;
        border-radius: var(--radius-full);
        background: var(--gradient-primary);
        transition: width 0.8s ease;
    }
    .rank-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }
    .rank-badge-1 { background: rgba(156,163,175,0.2); color: #9ca3af; }
    .rank-badge-2 { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .rank-badge-3 { background: rgba(16,185,129,0.15); color: #10b981; }
    .rank-badge-4 { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .rank-badge-5 { background: rgba(99,102,241,0.15); color: #6366f1; }
    .rank-badge-6 { background: rgba(139,92,246,0.15); color: #8b5cf6; }
    .rank-badge-7 { background: rgba(236,72,153,0.15); color: #ec4899; }
    .rank-badge-8 { background: rgba(14,165,233,0.15); color: #0ea5e9; }
    .rank-badge-9 { background: rgba(234,179,8,0.15); color: #eab308; }
    .rank-badge-10 { background: rgba(34,197,94,0.15); color: #22c55e; }

    .rank-card {
        transition: all 0.3s ease;
        cursor: default;
    }
    .rank-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    .rank-card.current {
        border: 2px solid var(--primary-500);
        background: rgba(99,102,241,0.05);
    }
    .rank-card.locked {
        opacity: 0.6;
        filter: grayscale(0.5);
    }
    @media (max-width: 640px) {
        .rank-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🏅 Mon Grade</h1>
        <p class="text-[var(--text-secondary)] mt-1">Suivez votre progression et débloquez de nouveaux grades</p>
    </div>

    <!-- Grade actuel -->
    <div class="card animate-fadeInUp delay-1 border-l-4 border-primary-500">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-[var(--text-secondary)]">Votre grade actuel</p>
                <h2 class="text-2xl md:text-3xl font-bold text-primary-500">
                    {{ Auth::user()->rank ?? 'Distributor' }}
                </h2>
                <p class="text-sm text-[var(--text-secondary)]">
                    {{ Auth::user()->pv_balance ?? 0 }} PV • {{ Auth::user()->bv_balance ?? 0 }} BV
                </p>
            </div>
            <div>
                <span class="rank-badge rank-badge-{{ Auth::user()->rank_id ?? 1 }} text-lg px-4 py-2">
                    @if(Auth::user()->rank_id <= 1) 🟤
                    @elseif(Auth::user()->rank_id <= 3) 🔵
                    @elseif(Auth::user()->rank_id <= 5) 🟢
                    @elseif(Auth::user()->rank_id <= 7) 🟣
                    @elseif(Auth::user()->rank_id <= 9) 🔶
                    @else 💎
                    @endif
                    {{ Auth::user()->rank ?? 'Distributor' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Progression -->
    <div class="card animate-fadeInUp delay-2">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="font-semibold text-[var(--text-primary)]">📈 Progression</h3>
                <p class="text-sm text-[var(--text-secondary)]">
                    @if(isset($nextRank))
                        Prochain grade: <strong class="text-primary-500">{{ $nextRank->name }}</strong>
                        ({{ number_format($pvNeeded ?? 0) }} PV restants)
                    @else
                        🎉 Vous avez atteint le grade maximum !
                    @endif
                </p>
            </div>
            <span class="text-sm font-bold text-primary-500">{{ number_format($progress ?? 0, 1) }}%</span>
        </div>

        <div class="rank-progress-container">
            <div class="rank-progress-bar">
                <div class="rank-progress-fill" style="width: {{ $progress ?? 0 }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-[var(--text-secondary)] mt-1">
                <span>{{ number_format($currentPv ?? 0) }} PV</span>
                <span>{{ number_format($nextPv ?? 0) }} PV</span>
            </div>
        </div>
    </div>

    <!-- Liste des grades -->
    <h3 class="text-lg font-semibold text-[var(--text-primary)] mt-4 animate-fadeInUp delay-3">
        📋 Tous les grades
    </h3>

    <div class="rank-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4 animate-fadeInUp delay-4">
        @foreach($ranks ?? [] as $rank)
            @php
                $isCurrent = Auth::user()->rank_id == $rank->id;
                $isLocked = Auth::user()->rank_id < $rank->id;
                $isUnlocked = Auth::user()->rank_id >= $rank->id;
                $progress = $rank->min_pv > 0 ? min(100, (Auth::user()->pv_balance / $rank->min_pv) * 100) : 100;
                $icons = ['🟤', '🔵', '🔵', '🟢', '🟢', '🟣', '🟣', '🔶', '🔶', '💎'];
                $icon = $icons[$rank->id - 1] ?? '🏅';
            @endphp

            <div class="rank-card card text-center p-4 {{ $isCurrent ? 'current' : '' }} {{ $isLocked ? 'locked' : '' }}">
                <div class="text-3xl mb-2">{{ $icon }}</div>
                <h4 class="font-bold text-[var(--text-primary)] text-sm">{{ $rank->name }}</h4>
                <p class="text-xs text-[var(--text-secondary)]">{{ number_format($rank->min_pv) }} PV</p>
                
                @if($isCurrent)
                    <span class="badge badge-success text-xs mt-2">✅ Actuel</span>
                @elseif($isUnlocked)
                    <span class="badge badge-info text-xs mt-2">🔓 Débloqué</span>
                @else
                    <span class="badge badge-danger text-xs mt-2">🔒 {{ number_format($rank->min_pv - Auth::user()->pv_balance) }} PV</span>
                    <div class="mt-2 w-full h-1 bg-[var(--bg-secondary)] rounded-full overflow-hidden">
                        <div class="h-full bg-primary-500 rounded-full" style="width: {{ $progress }}%"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Bonus par grade -->
    <div class="card animate-fadeInUp delay-5">
        <h3 class="font-semibold text-[var(--text-primary)] mb-4">🎁 Bonus par grade</h3>
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>PV minimum</th>
                        <th>Bonus</th>
                        <th class="hidden sm:table-cell">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ranks ?? [] as $rank)
                        <tr class="{{ Auth::user()->rank_id == $rank->id ? 'bg-primary-500/5' : '' }}">
                            <td class="font-medium">
                                <span class="rank-badge rank-badge-{{ $rank->id }} text-xs">
                                    {{ $rank->name }}
                                </span>
                            </td>
                            <td>{{ number_format($rank->min_pv) }}</td>
                            <td class="font-bold text-primary-500">{{ $rank->bonus_percentage }}%</td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-sm">
                                {{ $rank->description ?? 'Bonus supplémentaire sur les commissions' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection