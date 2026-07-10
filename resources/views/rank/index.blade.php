@extends('layouts.app')

@push('styles')
<style>
    .rank-progress-container {
        position: relative;
        padding: 0.5rem 0;
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
    .rank-badge-1 { background: rgba(156, 163, 175, 0.2); color: #9ca3af; }
    .rank-badge-2 { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .rank-badge-3 { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .rank-badge-4 { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .rank-badge-5 { background: rgba(99, 102, 241, 0.15); color: #6366f1; }
    .rank-badge-6 { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
    .rank-badge-7 { background: rgba(236, 72, 153, 0.15); color: #ec4899; }
    .rank-badge-8 { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
    .rank-badge-9 { background: rgba(234, 179, 8, 0.15); color: #eab308; }
    .rank-badge-10 { background: rgba(34, 197, 94, 0.15); color: #22c55e; }

    .rank-card {
        transition: all 0.3s ease;
        cursor: default;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        text-align: center;
    }
    .rank-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    .rank-card.current {
        border: 2px solid var(--primary-500);
        background: rgba(90, 182, 56, 0.04);
    }
    .rank-card.locked {
        opacity: 0.6;
        filter: grayscale(0.4);
    }
    .rank-card .rank-icon {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }
    .rank-card .rank-icon svg {
        width: 2.5rem;
        height: 2.5rem;
        margin: 0 auto;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-neutral {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }
    .table thead th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }
    .table tbody tr.bg-primary-500\/5 {
        background: rgba(90, 182, 56, 0.05);
    }
    
    .progress-mini {
        width: 100%;
        height: 0.25rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-full);
        overflow: hidden;
        margin-top: 0.375rem;
    }
    .progress-mini-fill {
        height: 100%;
        border-radius: var(--radius-full);
        background: var(--gradient-primary);
        transition: width 0.6s ease;
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
    .delay-5 { animation-delay: 0.25s; }
    
    @media (max-width: 640px) {
        .rank-grid {
            grid-template-columns: 1fr 1fr;
        }
        .rank-card .rank-icon {
            font-size: 2rem;
        }
        .rank-card .rank-icon svg {
            width: 2rem;
            height: 2rem;
        }
        .card {
            padding: 0.875rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .rank-badge {
            font-size: 0.65rem;
            padding: 0.125rem 0.5rem;
        }
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.65rem;
        }
        .rank-header {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .rank-header .rank-status {
            margin-left: 0 !important;
            margin-top: 0.5rem;
        }
        .rank-progress-container .rank-progress-fill {
            height: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .rank-grid {
            grid-template-columns: 1fr 1fr;
        }
        .card {
            padding: 0.75rem;
        }
        .rank-card {
            padding: 0.75rem;
        }
        .rank-card .rank-icon {
            font-size: 1.75rem;
        }
        .rank-card .rank-icon svg {
            width: 1.75rem;
            height: 1.75rem;
        }
        .rank-card h4 {
            font-size: 0.75rem;
        }
        .rank-card p {
            font-size: 0.6rem;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">My Rank</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Track your progress and unlock new ranks</p>
    </div>

  <!-- Current Rank -->
<div class="rank-header card animate-fadeInUp delay-1 border-l-4 border-primary-500">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Your current rank</p>
            <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-primary-500">
                {{-- Utiliser la colonne rank directement --}}
                {{ Auth::user()->rank ?? 'Distributor' }}
            </h2>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                {{ number_format(Auth::user()->pv_balance ?? 0) }} PV • {{ number_format(Auth::user()->bv_balance ?? 0) }} BV
            </p>
        </div>
        <div class="rank-status">
            <span class="rank-badge rank-badge-{{ Auth::user()->rank_id ?? 1 }} text-base sm:text-lg px-3 sm:px-4 py-1.5 sm:py-2">
                {{-- Utiliser la colonne rank directement --}}
                {{ Auth::user()->rank ?? 'Distributor' }}
            </span>
        </div>
    </div>
</div>

    <!-- Progress -->
    <div class="card animate-fadeInUp delay-2">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 sm:mb-4">
            <div>
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Progress</h3>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    @if(isset($nextRank))
                        Next rank: <strong class="text-primary-500">{{ $nextRank->name }}</strong>
                        ({{ number_format($pvNeeded ?? 0) }} PV needed)
                    @else
                        You have reached the maximum rank!
                    @endif
                </p>
            </div>
            <span class="text-xs sm:text-sm font-bold text-primary-500">{{ number_format($progress ?? 0, 1) }}%</span>
        </div>

        <div class="rank-progress-container">
            <div class="rank-progress-bar">
                <div class="rank-progress-fill" style="width: {{ $progress ?? 0 }}%"></div>
            </div>
            <div class="flex justify-between text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">
                <span>{{ number_format($currentPv ?? 0) }} PV</span>
                <span>{{ number_format($nextPv ?? 0) }} PV</span>
            </div>
        </div>
    </div>

    <!-- All Ranks -->
    <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)] mt-3 sm:mt-4 animate-fadeInUp delay-3">
        All Ranks
    </h3>

    <div class="rank-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 md:gap-4 animate-fadeInUp delay-4">
        @foreach($ranks ?? [] as $rank)
            @php
                $isCurrent = Auth::user()->rank_id == $rank->id;
                $isLocked = Auth::user()->rank_id < $rank->id;
                $isUnlocked = Auth::user()->rank_id >= $rank->id;
                $progress = $rank->min_pv > 0 ? min(100, (Auth::user()->pv_balance / $rank->min_pv) * 100) : 100;
                $rankLevel = $rank->id;
            @endphp

            <div class="rank-card {{ $isCurrent ? 'current' : '' }} {{ $isLocked ? 'locked' : '' }}">
                <div class="rank-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-[var(--text-primary)] text-xs sm:text-sm">{{ $rank->name }}</h4>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ number_format($rank->min_pv) }} PV</p>
                
                @if($isCurrent)
                    <span class="badge badge-success text-[10px] sm:text-xs mt-1.5 sm:mt-2 inline-block">Current</span>
                @elseif($isUnlocked)
                    <span class="badge badge-info text-[10px] sm:text-xs mt-1.5 sm:mt-2 inline-block">Unlocked</span>
                @else
                    <span class="badge badge-danger text-[10px] sm:text-xs mt-1.5 sm:mt-2 inline-block">
                        {{ number_format(max(0, $rank->min_pv - Auth::user()->pv_balance)) }} PV
                    </span>
                    <div class="progress-mini mt-1.5 sm:mt-2">
                        <div class="progress-mini-fill" style="width: {{ $progress }}%"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Rank Bonus -->
    <div class="card animate-fadeInUp delay-5">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Rank Bonuses</h3>
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Rank</th>
                        <th class="text-xs sm:text-sm">Min PV</th>
                        <th class="text-xs sm:text-sm">Bonus</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ranks ?? [] as $rank)
                        <tr class="{{ Auth::user()->rank_id == $rank->id ? 'bg-primary-500/5' : '' }}">
                            <td class="font-medium text-xs sm:text-sm">
                                <span class="rank-badge rank-badge-{{ $rank->id }} text-[10px] sm:text-xs">
                                    {{ $rank->name }}
                                </span>
                            </td>
                            <td class="text-xs sm:text-sm">{{ number_format($rank->min_pv) }}</td>
                            <td class="font-bold text-primary-500 text-xs sm:text-sm">{{ $rank->bonus_percentage }}%</td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $rank->description ?? 'Additional bonus on commissions' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection