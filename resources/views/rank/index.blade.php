{{-- resources/views/rank/index.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .rank-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .rank-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .rank-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 1rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .rank-badge-gold {
        background: rgba(234, 179, 8, 0.15);
        color: #eab308;
    }
    .rank-badge-purple {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }
    .rank-badge-blue {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    .rank-badge-green {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }
    .rank-badge-gray {
        background: rgba(107, 114, 128, 0.15);
        color: #6b7280;
    }
    
    .progress-container {
        width: 100%;
        height: 8px;
        background: var(--bg-secondary);
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    .progress-container .progress-fill {
        height: 100%;
        border-radius: 9999px;
        background: var(--gradient-primary);
        transition: width 1s ease;
    }
    
    .condition-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.625rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }
    .condition-item.met {
        color: #22c55e;
    }
    .condition-item.unmet {
        color: #94a3b8;
    }
    
    .rank-history-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    .rank-history-item:hover {
        background: var(--bg-hover);
    }
    .rank-history-item.promotion {
        border-left-color: #22c55e;
    }
    .rank-history-item.demotion {
        border-left-color: #f59e0b;
    }
    .rank-history-item.update {
        border-left-color: #3b82f6;
    }
    
    .rank-distribution-bar {
        height: 6px;
        border-radius: 9999px;
        background: var(--bg-secondary);
        overflow: hidden;
    }
    .rank-distribution-bar .fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.8s ease;
    }
    
    .rank-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 0.5rem;
        margin: 0.5rem 0;
    }
    .rank-grid .rank-card-item {
        background: var(--bg-card);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        user-select: none;
    }
    .rank-grid .rank-card-item:hover {
        transform: translateY(-4px);
        border-color: var(--primary-500);
        box-shadow: var(--shadow-hover);
    }
    .rank-grid .rank-card-item.active {
        border-color: var(--primary-500);
        background: rgba(90, 182, 56, 0.08);
        box-shadow: 0 0 0 3px rgba(90, 182, 56, 0.15);
    }
    .rank-grid .rank-card-item .rank-level-badge {
        display: inline-block;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.6rem;
        line-height: 1.5rem;
        text-align: center;
        margin-bottom: 0.25rem;
    }
    .rank-grid .rank-card-item .rank-level-badge.level-1 { background: rgba(156,163,175,0.2); color: #6b7280; }
    .rank-grid .rank-card-item .rank-level-badge.level-2 { background: rgba(99,102,241,0.15); color: #6366f1; }
    .rank-grid .rank-card-item .rank-level-badge.level-3 { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .rank-grid .rank-card-item .rank-level-badge.level-4 { background: rgba(16,185,129,0.15); color: #10b981; }
    .rank-grid .rank-card-item .rank-level-badge.level-5 { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .rank-grid .rank-card-item .rank-level-badge.level-6 { background: rgba(236,72,153,0.15); color: #ec4899; }
    .rank-grid .rank-card-item .rank-level-badge.level-7 { background: rgba(139,92,246,0.15); color: #8b5cf6; }
    .rank-grid .rank-card-item .rank-level-badge.level-8 { background: rgba(34,197,94,0.15); color: #22c55e; }
    .rank-grid .rank-card-item .rank-level-badge.level-9 { background: rgba(234,179,8,0.15); color: #eab308; }
    
    .rank-grid .rank-card-item .rank-name {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-primary);
        display: block;
    }
    .rank-grid .rank-card-item .rank-pv {
        font-size: 0.6rem;
        color: var(--text-secondary);
        display: block;
    }
    .rank-grid .rank-card-item .rank-bonus {
        font-size: 0.6rem;
        color: var(--primary-500);
        font-weight: 700;
        display: block;
    }
    .rank-grid .rank-card-item .rank-check {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        font-size: 0.7rem;
        font-weight: 700;
    }
    .rank-grid .rank-card-item .rank-check.unlocked {
        color: #22c55e;
    }
    .rank-grid .rank-card-item .rank-check.locked {
        color: #94a3b8;
    }
    .rank-grid .rank-card-item .rank-check.current {
        color: var(--primary-500);
    }
    .rank-grid .rank-card-item .current-badge {
        position: absolute;
        bottom: 0.25rem;
        left: 50%;
        transform: translateX(-50%);
        background: var(--primary-500);
        color: white;
        font-size: 0.5rem;
        padding: 0.1rem 0.4rem;
        border-radius: 9999px;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .rank-detail-container {
        margin-top: 0.5rem;
        display: none;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .rank-detail-container.visible {
        display: block;
        animation: fadeInUp 0.4s ease forwards;
    }
    .rank-detail-container.closing {
        animation: fadeOutDown 0.3s ease forwards;
    }
    
    .rank-detail-card {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        border: 1px solid var(--border-color);
    }
    .rank-detail-card .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }
    .rank-detail-card .detail-header .detail-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .rank-detail-card .detail-header .detail-level {
        font-size: 0.75rem;
        color: var(--text-secondary);
        background: var(--bg-card);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
    }
    .rank-detail-card .detail-body {
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.6;
    }
    .rank-detail-card .detail-body .detail-section {
        margin-bottom: 0.75rem;
        background: var(--bg-card);
        border-radius: var(--radius-sm);
        padding: 0.75rem;
        border: 1px solid var(--border-light);
    }
    .rank-detail-card .detail-body .detail-section .section-title {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
        padding-bottom: 0.25rem;
        border-bottom: 2px solid var(--primary-500);
        display: inline-block;
    }
    .rank-detail-card .detail-body .detail-section .section-description {
        font-size: 0.8rem;
        color: var(--text-secondary);
        padding: 0.25rem 0;
    }
    .rank-detail-card .detail-body ul {
        list-style: none;
        padding: 0;
        margin: 0.25rem 0;
    }
    .rank-detail-card .detail-body ul li {
        padding: 0.25rem 0 0.25rem 1.5rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
        position: relative;
        border-bottom: 1px solid var(--border-light);
        transition: all 0.2s ease;
    }
    .rank-detail-card .detail-body ul li:last-child {
        border-bottom: none;
    }
    .rank-detail-card .detail-body ul li::before {
        content: "▸";
        color: var(--primary-500);
        position: absolute;
        left: 0;
        font-weight: 700;
    }
    .rank-detail-card .detail-body .detail-conditions .cond {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        border: 1px solid var(--border-light);
        transition: all 0.2s ease;
        background: var(--bg-secondary);
    }
    .rank-detail-card .detail-body .detail-conditions .cond .cond-label {
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.7rem;
    }
    .rank-detail-card .detail-body .detail-conditions .cond .cond-value {
        font-weight: 600;
        padding: 0.15rem 0.6rem;
        border-radius: 4px;
        font-size: 0.65rem;
        text-align: right;
    }
    .rank-detail-card .detail-body .detail-conditions .cond .cond-value.met {
        color: #22c55e;
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.15);
    }
    .rank-detail-card .detail-body .detail-conditions .cond .cond-value.unmet {
        color: #64748b;
        background: rgba(100, 116, 139, 0.08);
        border: 1px solid rgba(100, 116, 139, 0.12);
    }
    .rank-detail-card .detail-body .condition-option {
        border-left: 3px solid var(--border-color);
        background: var(--bg-secondary);
        border-radius: 6px;
        margin: 0.15rem 0;
    }
    .rank-detail-card .detail-body .condition-option.met {
        border-left-color: #22c55e;
        background: rgba(34, 197, 94, 0.04);
    }
    .rank-detail-card .detail-body .condition-option.unmet {
        border-left-color: #94a3b8;
        background: rgba(148, 163, 184, 0.04);
    }
    .rank-detail-card .detail-pv-required {
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-top: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        flex-wrap: wrap;
        gap: 0.5rem;
        background: var(--bg-card);
        border-radius: var(--radius-sm);
    }
    .rank-detail-card .detail-pv-required .pv-label {
        color: var(--text-secondary);
        font-weight: 500;
    }
    .rank-detail-card .detail-pv-required .pv-value {
        font-weight: 700;
        padding: 0.15rem 0.6rem;
        border-radius: 4px;
        font-size: 0.7rem;
    }
    .rank-detail-card .detail-pv-required .pv-value.met {
        color: #22c55e;
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.15);
    }
    .rank-detail-card .detail-pv-required .pv-value.unmet {
        color: #64748b;
        background: rgba(100, 116, 139, 0.08);
        border: 1px solid rgba(100, 116, 139, 0.12);
    }
    .rank-detail-card .detail-bonus-display {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.15rem;
        padding: 0.5rem 0.75rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-light);
    }
    .rank-detail-card .detail-bonus-display .bonus-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary-500);
    }
    .rank-detail-card .detail-bonus-display .bonus-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .rank-detail-card .detail-status {
        margin-top: 0.75rem;
        padding: 0.6rem 0.75rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        text-align: center;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .rank-detail-card .detail-status.locked {
        background: rgba(148, 163, 184, 0.08);
        border-color: rgba(148, 163, 184, 0.15);
        color: #64748b;
    }
    .rank-detail-card .detail-status.current {
        background: rgba(34, 197, 94, 0.08);
        border-color: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }
    .rank-detail-card .detail-status.unlocked {
        background: rgba(59, 130, 246, 0.08);
        border-color: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    
    .detail-close-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .detail-close-btn:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }
    .detail-close-btn .close-text {
        font-size: 0.65rem;
        font-weight: 500;
    }
    
    .rank-click-hint {
        font-size: 0.65rem;
        color: var(--text-tertiary);
        text-align: center;
        margin-top: 0.25rem;
        font-style: italic;
        transition: all 0.3s ease;
    }
    .rank-click-hint.hidden {
        opacity: 0;
        max-height: 0;
        margin: 0;
        overflow: hidden;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeOutDown {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(10px); }
    }
    
    @media (max-width: 640px) {
        .rank-card { padding: 0.875rem; }
        .rank-badge { font-size: 0.65rem; padding: 0.25rem 0.625rem; }
        .rank-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
        .rank-grid .rank-card-item { padding: 0.5rem; }
        .rank-grid .rank-card-item .rank-name { font-size: 0.6rem; }
        .rank-detail-card { padding: 0.75rem; }
        .rank-detail-card .detail-header .detail-title { font-size: 1rem; }
        .rank-detail-card .detail-body { font-size: 0.75rem; }
        .rank-detail-card .detail-body .detail-conditions .cond { flex-direction: column; align-items: flex-start; gap: 0.15rem; }
        .rank-detail-card .detail-body .detail-conditions .cond .cond-value { font-size: 0.6rem; width: 100%; text-align: left; }
        .rank-detail-card .detail-body .detail-section { padding: 0.5rem; }
        .rank-detail-card .detail-pv-required { flex-direction: column; align-items: flex-start; gap: 0.25rem; }
        .detail-close-btn .close-text { display: none; }
    }
</style>
@endpush

@section('content')
@php
    use App\Models\Rank;
    
    $userRank = Auth::user()->rank;
    $userRankLevel = 1;
    $userRankName = 'Distributeur';
    $userRankId = null;
    
    if ($userRank) {
        if (is_object($userRank) && method_exists($userRank, 'getAttribute')) {
            $userRankLevel = $userRank->level ?? 1;
            $userRankName = $userRank->name ?? 'Distributeur';
            $userRankId = $userRank->id ?? null;
        } elseif (is_string($userRank)) {
            $userRankName = $userRank;
            $rankModel = Rank::where('name', $userRank)->first();
            if ($rankModel) {
                $userRankLevel = $rankModel->level ?? 1;
                $userRankId = $rankModel->id ?? null;
            }
        } elseif (is_int($userRank) || is_numeric($userRank)) {
            $rankModel = Rank::find($userRank);
            if ($rankModel) {
                $userRankLevel = $rankModel->level ?? 1;
                $userRankName = $rankModel->name ?? 'Distributeur';
                $userRankId = $rankModel->id ?? null;
            }
        }
    }
    
    if ($userRankName === 'Distributeur' && Auth::user()->getOriginal('rank')) {
        $originalRank = Auth::user()->getOriginal('rank');
        if (is_string($originalRank)) {
            $userRankName = $originalRank;
            $rankModel = Rank::where('name', $originalRank)->first();
            if ($rankModel) {
                $userRankLevel = $rankModel->level ?? 1;
                $userRankId = $rankModel->id ?? null;
            }
        }
    }
    
    $nextRank = Rank::where('level', '>', $userRankLevel)->orderBy('level')->first();
    $currentPv = Auth::user()->pv_balance ?? 0;
    $nextPv = $nextRank ? $nextRank->min_pv : 0;
    $pvNeeded = $nextRank ? max(0, $nextPv - $currentPv) : 0;
    $progress = ($nextRank && $nextPv > 0) ? min(100, ($currentPv / $nextPv) * 100) : 0;
@endphp

<div class="space-y-4 sm:space-y-6">
    
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Grade</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Suivez votre progression dans le systeme de grades
        </p>
    </div>

    <div class="rank-card animate-fadeInUp delay-1">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Grade actuel</p>
                <h2 class="text-2xl sm:text-3xl font-bold text-primary-500">
                    {{ $userRankName }}
                </h2>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    Niveau {{ $userRankLevel }}
                </p>
            </div>
            <div>
                @if($nextRank)
                    <span class="rank-badge rank-badge-blue">
                        Prochain grade: {{ $nextRank->name }}
                    </span>
                @else
                    <span class="rank-badge rank-badge-gold">
                        Niveau maximum atteint
                    </span>
                @endif
            </div>
        </div>

        @if($nextRank)
            <div class="mt-3 sm:mt-4">
                <div class="flex justify-between text-xs sm:text-sm">
                    <span class="text-[var(--text-secondary)]">{{ number_format($currentPv) }} PV</span>
                    <span class="text-[var(--text-secondary)]">{{ number_format($progress, 1) }}%</span>
                    <span class="text-[var(--text-secondary)]">{{ number_format($nextPv) }} PV</span>
                </div>
                <div class="progress-container">
                    <div class="progress-fill" style="width: {{ $progress }}%;"></div>
                </div>
                <p class="text-xs text-[var(--text-secondary)] mt-1">
                    <span class="text-yellow-500 font-semibold">{{ number_format($pvNeeded) }} PV</span> 
                    necessaires pour atteindre le grade suivant
                </p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-2">
        <div class="rank-card text-center">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">PV Total</p>
            <p class="text-lg sm:text-2xl font-bold text-primary-500">{{ number_format(Auth::user()->pv_balance ?? 0) }}</p>
            <p class="text-[10px] text-[var(--text-tertiary)]">Cumul depuis l'inscription</p>
        </div>
        <div class="rank-card text-center">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">PV Mensuel</p>
            <p class="text-lg sm:text-2xl font-bold text-blue-500">{{ number_format(Auth::user()->monthly_pv ?? 0) }}</p>
            <p class="text-[10px] text-[var(--text-tertiary)]">Ventes du mois en cours</p>
        </div>
        <div class="rank-card text-center">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">PV Equipe</p>
            <p class="text-lg sm:text-2xl font-bold text-purple-500">{{ number_format(Auth::user()->team_pv ?? 0) }}</p>
            <p class="text-[10px] text-[var(--text-tertiary)]">Ventes de votre reseau</p>
        </div>
        <div class="rank-card text-center">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Promotions</p>
            <p class="text-lg sm:text-2xl font-bold text-green-500">{{ $rankStats['total_promotions'] ?? 0 }}</p>
            <p class="text-[10px] text-[var(--text-tertiary)]">Nombre de promotions</p>
        </div>
    </div>

    <div class="rank-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">
            Tous les grades
        </h3>
        <p class="text-xs text-[var(--text-secondary)] mb-2">
            Cliquez sur un grade pour voir les details complets
        </p>
        
        <div class="rank-grid" id="rankGrid">
            @php
                $allRanksFromDB = Rank::orderBy('level')->get();
                
                $allRanksData = [
                    1 => [
                        'name' => 'Distributeur',
                        'pv' => 0,
                        'bonus' => '0%',
                        'class' => 'level-1',
                        'description' => 'Grade de base, point de depart dans le systeme Salang',
                        'commission_types' => ['Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'Inscription', 'value' => 'Validée']
                        ],
                        'pv_payment' => 'Aucun PV requis'
                    ],
                    2 => [
                        'name' => 'Qualification',
                        'pv' => 100,
                        'bonus' => '6%',
                        'class' => 'level-2',
                        'description' => 'Premier grade actif, débutez vos commissions',
                        'commission_types' => ['Bonus Direct (6%)', 'Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'PV Personnel', 'value' => '≥ 100 PV'],
                            ['label' => 'PV Mensuel', 'value' => '≥ 20 PV']
                        ],
                        'pv_payment' => 'PV mensuel ≥ 20 PV'
                    ],
                    3 => [
                        'name' => 'Cumul Directeur',
                        'pv' => 200,
                        'bonus' => '22%',
                        'class' => 'level-3',
                        'description' => 'Grade intermédiaire, augmentez vos commissions',
                        'commission_types' => ['Bonus Direct (22%)', 'Bonus Indirect', 'Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'PV Personnel', 'value' => '≥ 200 PV'],
                            ['label' => 'PV Mensuel', 'value' => '≥ 20 PV']
                        ],
                        'pv_payment' => 'PV mensuel ≥ 20 PV'
                    ],
                    4 => [
                        'name' => 'Directeur',
                        'pv' => 1000,
                        'bonus' => '26%',
                        'class' => 'level-4',
                        'description' => 'Grade de leader, commencez à développer votre réseau',
                        'commission_types' => ['Bonus Direct (26%)', 'Bonus Indirect', 'Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'Être niveau 4', 'value' => 'Avoir ≥ 1000 PV personnel'],
                            ['label' => 'Option 1', 'value' => 'Avoir 3 filleuls directs de niveau 4 avec ≥ 1000 PV'],
                            ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 3 avec un total ≥ 2200 PV']
                        ],
                        'pv_payment' => 'PV mensuel ≥ 25 PV'
                    ],
                    5 => [
                        'name' => 'Manager Senior',
                        'pv' => 3800,
                        'bonus' => '30%',
                        'class' => 'level-5',
                        'description' => 'Grade de manager, optimisez les commissions de votre réseau',
                        'commission_types' => ['Bonus Direct (30%)', 'Bonus Indirect', 'Bonus Leadership (0.5%)', 'Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'Être niveau 5', 'value' => 'Avoir 3 filleuls directs de niveau 4 avec ≥ 3800 PV'],
                            ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 4 avec ≥ 7800 PV'],
                            ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 4 et 4 filleuls de niveau 3 avec ≥ 3800 PV'],
                            ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 4 et 6 filleuls de niveau 3 avec ≥ 3800 PV']
                        ],
                        'pv_payment' => 'PV mensuel ≥ 30 PV'
                    ],
                    6 => [
                        'name' => 'Directeur Envolée',
                        'pv' => 16000,
                        'bonus' => '34%',
                        'class' => 'level-6',
                        'description' => 'Grade de directeur, développez un réseau profond',
                        'commission_types' => ['Bonus Direct (34%)', 'Bonus Indirect', 'Bonus Leadership (1.1%)', 'Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'Être niveau 6', 'value' => 'Avoir 3 filleuls directs de niveau 5 avec ≥ 16000 PV'],
                            ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 5 avec ≥ 35000 PV'],
                            ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 5 et 4 filleuls de niveau 4 avec ≥ 16000 PV'],
                            ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 5 et 6 filleuls de niveau 4 avec ≥ 16000 PV']
                        ],
                        'pv_payment' => 'PV mensuel ≥ 50 PV'
                    ],
                    7 => [
                        'name' => 'Saphire Manager',
                        'pv' => 73000,
                        'bonus' => '40%',
                        'class' => 'level-7',
                        'description' => 'Grade saphir, accédez aux primes mondiales',
                        'commission_types' => ['Bonus Direct (40%)', 'Bonus Indirect', 'Bonus Leadership (1.8%)', 'Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'Être niveau 7', 'value' => 'Avoir 3 filleuls directs de niveau 6 avec ≥ 73000 PV'],
                            ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 6 avec ≥ 145000 PV'],
                            ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 6 et 4 filleuls de niveau 5 avec ≥ 73000 PV'],
                            ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 6 et 6 filleuls de niveau 5 avec ≥ 73000 PV']
                        ],
                        'pv_payment' => 'PV mensuel ≥ 100 PV'
                    ],
                    8 => [
                        'name' => 'Diamant Bleu',
                        'pv' => 280000,
                        'bonus' => '43%',
                        'class' => 'level-8',
                        'description' => 'Grade diamant, primes mondiales significatives',
                        'commission_types' => ['Bonus Direct (43%)', 'Bonus Indirect', 'Bonus Leadership (2.6%)', 'Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'Être niveau 8', 'value' => 'Avoir 3 filleuls directs de niveau 7 avec ≥ 280000 PV'],
                            ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 7 avec ≥ 580000 PV'],
                            ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 7 et 4 filleuls de niveau 6 avec ≥ 280000 PV'],
                            ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 7 et 6 filleuls de niveau 6 avec ≥ 280000 PV']
                        ],
                        'pv_payment' => 'PV mensuel ≥ 200 PV'
                    ],
                    9 => [
                        'name' => 'Perle Diamant',
                        'pv' => 400000,
                        'bonus' => '45%',
                        'class' => 'level-9',
                        'description' => 'Grade ultime, bonuses mondiaux maximum',
                        'commission_types' => ['Bonus Direct (45%)', 'Bonus Indirect', 'Bonus Leadership (3.5%)', 'Bonus Consommateur (6%)'],
                        'conditions' => [
                            ['label' => 'Être niveau 9', 'value' => 'Avoir 3 filleuls directs de niveau 8 avec ≥ 400000 PV'],
                            ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 8 avec ≥ 780000 PV'],
                            ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 8 et 4 filleuls de niveau 7 avec ≥ 400000 PV'],
                            ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 8 et 6 filleuls de niveau 7 avec ≥ 400000 PV']
                        ],
                        'pv_payment' => 'PV mensuel ≥ 300 PV'
                    ]
                ];
                
                if (!$allRanksFromDB->isEmpty()) {
                    foreach ($allRanksFromDB as $rank) {
                        if (isset($allRanksData[$rank->level])) {
                            $allRanksData[$rank->level]['name'] = $rank->name;
                            $allRanksData[$rank->level]['bonus'] = $rank->bonus_percentage ?? $allRanksData[$rank->level]['bonus'];
                            $allRanksData[$rank->level]['pv'] = $rank->min_pv ?? $allRanksData[$rank->level]['pv'];
                        }
                    }
                }
                
                $allRanks = [];
                foreach ($allRanksData as $level => $data) {
                    $allRanks[] = [
                        'level' => $level,
                        'name' => $data['name'],
                        'pv' => $data['pv'],
                        'bonus' => $data['bonus'],
                        'class' => $data['class']
                    ];
                }
                
                $rankDetailsForJS = [];
                foreach ($allRanksData as $level => $data) {
                    $rankDetailsForJS[$level] = [
                        'name' => $data['name'],
                        'level' => $level,
                        'pv_required' => $data['pv'],
                        'bonus' => $data['bonus'],
                        'description' => $data['description'],
                        'commission_types' => $data['commission_types'],
                        'conditions' => $data['conditions'],
                        'pv_payment' => $data['pv_payment'],
                        'isUnlocked' => $level <= $userRankLevel
                    ];
                }
            @endphp
            
            @foreach($allRanks as $rank)
                @php
                    $isUnlocked = $rank['level'] <= $userRankLevel;
                    $isCurrent = $rank['level'] == $userRankLevel;
                @endphp
                <div class="rank-card-item {{ $isCurrent ? 'active' : '' }}" 
                     data-level="{{ $rank['level'] }}"
                     onclick="toggleRankDetail({{ $rank['level'] }})">
                    <span class="rank-level-badge {{ $rank['class'] }}">{{ $rank['level'] }}</span>
                    <span class="rank-name">{{ $rank['name'] }}</span>
                    <span class="rank-pv">{{ number_format($rank['pv']) }} PV</span>
                    <span class="rank-bonus">Bonus: {{ $rank['bonus'] }}</span>
                    <span class="rank-check {{ $isCurrent ? 'current' : ($isUnlocked ? 'unlocked' : 'locked') }}">
                        {{ $isCurrent ? '●' : ($isUnlocked ? '✓' : '') }}
                    </span>
                </div>
            @endforeach
        </div>

        <p class="rank-click-hint" id="rankClickHint">Cliquez sur un grade pour afficher les details</p>

        <div class="rank-detail-container" id="rankDetailContainer">
            <div class="rank-detail-card" id="rankDetail">
            </div>
        </div>
    </div>

    <div class="rank-card animate-fadeInUp delay-4">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2 sm:mb-3">
            Distribution des grades
        </h3>
        <p class="text-xs text-[var(--text-secondary)] mb-2">
            Repartition des membres par grade dans la plateforme
        </p>
        <div class="space-y-1.5 sm:space-y-2">
            @forelse($rankDistribution ?? [] as $name => $count)
                @php
                    $total = $rankDistribution->sum() ?? 1;
                    $percent = $total > 0 ? ($count / $total) * 100 : 0;
                    $colors = [
                        'Perle Diamant' => '#eab308',
                        'Diamant Bleu' => '#8b5cf6',
                        'Saphire Manager' => '#3b82f6',
                        'Directeur Envolée' => '#22c55e',
                        'Manager Senior' => '#14b8a6',
                        'Directeur' => '#f59e0b',
                        'Cumul Directeur' => '#f97316',
                        'Qualification' => '#6b7280',
                        'Distributeur' => '#9ca3af',
                    ];
                    $color = $colors[$name] ?? '#6b7280';
                @endphp
                <div>
                    <div class="flex justify-between text-xs sm:text-sm">
                        <span class="text-[var(--text-secondary)]">{{ $name }}</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ $count }}</span>
                    </div>
                    <div class="rank-distribution-bar">
                        <div class="fill" style="width: {{ $percent }}%; background: {{ $color }};"></div>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-4 text-sm">
                    Aucune donnee de distribution disponible
                </p>
            @endforelse
        </div>
    </div>

    @if(isset($lastMonthRank))
        <div class="rank-card animate-fadeInUp delay-5">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-1">
                Grade du mois dernier
            </h3>
            <p class="text-xs text-[var(--text-secondary)] mb-2">
                Comparaison avec le mois precedent pour suivre votre evolution
            </p>
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-lg font-bold text-[var(--text-secondary)]">
                    {{ $lastMonthRank['rank_name'] ?? 'Distributeur' }}
                </span>
                <span class="text-xs text-[var(--text-secondary)]">
                    ({{ $lastMonth ?? now()->subMonth()->format('F Y') }})
                </span>
                @php
                    $lastLevel = $lastMonthRank['rank']?->level ?? 1;
                @endphp
                @if($userRankLevel > $lastLevel)
                    <span class="rank-badge rank-badge-green text-xs">Promotion</span>
                    <span class="text-xs text-[var(--text-secondary)]">Vous avez progresse !</span>
                @elseif($userRankLevel < $lastLevel)
                    <span class="rank-badge rank-badge-purple text-xs">Retrogradation</span>
                    <span class="text-xs text-[var(--text-secondary)]">Maintenez vos performances</span>
                @else
                    <span class="rank-badge rank-badge-gray text-xs">Stable</span>
                    <span class="text-xs text-[var(--text-secondary)]">Continuez vos efforts</span>
                @endif
            </div>
        </div>
    @endif

    <div class="rank-card animate-fadeInUp delay-6">
        <div class="flex items-center justify-between mb-2 sm:mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                Historique des grades
            </h3>
            <a href="{{ route('rank.history') }}" class="text-xs sm:text-sm text-primary-500 hover:text-primary-600 transition font-medium">
                Voir tout →
            </a>
        </div>
        <p class="text-xs text-[var(--text-secondary)] mb-2">
            Suivi de votre progression dans le systeme de grades
        </p>
        <div class="space-y-1.5 sm:space-y-2 max-h-48 overflow-y-auto custom-scrollbar">
            @forelse($history ?? [] as $item)
                @php
                    $oldLevel = $item->oldRank?->level ?? 0;
                    $newLevel = $item->newRank?->level ?? 0;
                    $type = $newLevel > $oldLevel ? 'promotion' : ($newLevel < $oldLevel ? 'demotion' : 'update');
                    $typeLabel = $newLevel > $oldLevel ? 'Promotion' : ($newLevel < $oldLevel ? 'Retrogradation' : 'Mise a jour');
                @endphp
                <div class="rank-history-item {{ $type }}">
                    <div>
                        <span class="text-xs sm:text-sm font-medium text-[var(--text-primary)]">
                            {{ $item->old_rank_name ?? 'Distributeur' }}
                        </span>
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 inline mx-1 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-xs sm:text-sm font-bold text-primary-500">
                            {{ $item->new_rank_name ?? 'Distributeur' }}
                        </span>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                            {{ $item->created_at->diffForHumans() }}
                        </span>
                        <span class="rank-badge text-[8px] sm:text-[10px] {{ $type === 'promotion' ? 'rank-badge-green' : ($type === 'demotion' ? 'rank-badge-purple' : 'rank-badge-gray') }}">
                            {{ $typeLabel }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-4 text-sm">
                    Aucun historique de grade
                </p>
            @endforelse
        </div>
    </div>

</div>

@push('scripts')
<script>
const rankDetails = @json($rankDetailsForJS);
const userLevel = {{ $userRankLevel }};

let currentOpenLevel = null;

function toggleRankDetail(level) {
    if (currentOpenLevel === level) {
        closeRankDetail();
        return;
    }
    showRankDetail(level);
}

function closeRankDetail() {
    const container = document.getElementById('rankDetailContainer');
    const hint = document.getElementById('rankClickHint');
    
    container.classList.remove('visible');
    container.classList.add('closing');
    
    document.querySelectorAll('.rank-grid .rank-card-item').forEach(el => {
        el.classList.remove('active');
    });
    
    hint.classList.remove('hidden');
    currentOpenLevel = null;
    
    setTimeout(() => {
        container.classList.remove('closing');
    }, 300);
}

function showRankDetail(level) {
    const data = rankDetails[level];
    if (!data) {
        console.warn('Aucune donnee pour le niveau', level);
        return;
    }
    
    const container = document.getElementById('rankDetailContainer');
    const content = document.getElementById('rankDetail');
    const hint = document.getElementById('rankClickHint');
    const isUnlocked = level <= userLevel;
    const isCurrent = level == userLevel;
    
    hint.classList.add('hidden');
    container.classList.remove('closing');
    container.classList.add('visible');
    
    document.querySelectorAll('.rank-grid .rank-card-item').forEach(el => {
        el.classList.remove('active');
        if (parseInt(el.dataset.level) === level) {
            el.classList.add('active');
        }
    });
    
    currentOpenLevel = level;
    
    let statusClass = 'locked';
    let statusText = 'Ce grade n\'est pas encore debloque. Continuez a progresser !';
    if (isCurrent) {
        statusClass = 'current';
        statusText = 'Vous avez atteint ce grade. Felicitations !';
    } else if (isUnlocked) {
        statusClass = 'unlocked';
        statusText = 'Ce grade est debloque. Vous pouvez le voir dans votre progression.';
    }
    
    let conditionsHtml = '';
    if (data.conditions && data.conditions.length > 0) {
        data.conditions.forEach((cond) => {
            const isMet = isCurrent;
            const label = cond.label || 'Condition';
            const value = cond.value || cond;
            
            conditionsHtml += `
                <div class="cond ${isMet ? 'met' : 'unmet'}">
                    <span class="cond-label">${label}</span>
                    <span class="cond-value ${isMet ? 'met' : 'unmet'}">
                        ${value}
                        ${isMet ? ' ✓' : ' ✗'}
                    </span>
                </div>
            `;
        });
    } else {
        conditionsHtml = `
            <div class="cond">
                <span class="cond-label">Aucune condition specifique</span>
                <span class="cond-value met">✓</span>
            </div>
        `;
    }
    
    let commissionsHtml = '';
    if (data.commission_types && data.commission_types.length > 0) {
        data.commission_types.forEach(type => {
            commissionsHtml += `<li>${type}</li>`;
        });
    } else {
        commissionsHtml = `<li>Aucune commission specifique</li>`;
    }
    
    let bonusDisplay = data.bonus || '0%';
    if (!bonusDisplay.includes('%')) {
        bonusDisplay = bonusDisplay + '%';
    }
    
    content.innerHTML = `
        <div class="detail-header">
            <span class="detail-title">
                ${isCurrent ? '★ ' : ''} ${data.name}
                ${isCurrent ? '<span style="font-size:0.7rem;color:var(--primary-500);font-weight:600;margin-left:0.5rem;">(Votre grade actuel)</span>' : ''}
            </span>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <span class="detail-level">Niveau ${data.level}</span>
                <button class="detail-close-btn" onclick="closeRankDetail()" title="Fermer les details">
                    <span class="close-text">Fermer</span>
                    <span>✕</span>
                </button>
            </div>
        </div>
        
        <div class="detail-body">
            <div class="detail-section">
                <div class="section-title">Description</div>
                <div class="section-description">${data.description || 'Grade du systeme Salang'}</div>
            </div>
            
            <div class="detail-section">
                <div class="section-title">Commissions disponibles</div>
                <ul>
                    ${commissionsHtml}
                </ul>
            </div>
            
            <div class="detail-section">
                <div class="section-title">Conditions d'accès</div>
                <div class="detail-conditions">
                    ${conditionsHtml}
                    <div class="cond" style="border-top:2px solid var(--border-color);padding-top:0.4rem;margin-top:0.3rem;">
                        <span class="cond-label" style="font-weight:700;color:var(--text-primary);">PV Minimum requis</span>
                        <span class="cond-value ${isCurrent ? 'met' : 'unmet'}">
                            ${data.pv_required || 0} PV
                            ${isCurrent ? ' ✓' : ' ✗'}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <div class="section-title">Taux de commission</div>
                <div class="detail-bonus-display">
                    <span class="bonus-value">${bonusDisplay}</span>
                    <span class="bonus-label">sur le BV personnel</span>
                </div>
            </div>
            
            <div class="detail-pv-required">
                <span class="pv-label">PV mensuel requis pour toucher les commissions</span>
                <span class="pv-value ${isCurrent ? 'met' : 'unmet'}">
                    ${data.pv_payment || 'PV mensuel requis'}
                    ${isCurrent ? ' ✓' : ' ✗'}
                </span>
            </div>
        </div>
        
        <div class="detail-status ${statusClass}">
            ${statusText}
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', function() {
    if (userLevel > 0) {
        setTimeout(function() {
            showRankDetail(userLevel);
        }, 300);
    }
});
</script>
@endpush
@endsection