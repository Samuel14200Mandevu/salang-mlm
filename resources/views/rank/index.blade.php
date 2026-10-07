{{-- resources/views/rank/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Mon rang')



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

<div class="rank-page space-y-4 sm:space-y-6">

    <div class="member-page-intro rank-page-intro-desktop animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Grade</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Suivez votre progression dans le systeme de grades
        </p>
    </div>

    <div class="rank-mobile-banner animate-fadeInUp">
        <div class="shop-catalog-banner rank-catalog-banner">
            <div class="shop-catalog-banner__text">
                <p class="shop-catalog-banner__eyebrow">Niveau {{ $userRankLevel }} · {{ $userRankName }}</p>
                <p class="shop-catalog-banner__title">Mon rang</p>
                @if($nextRank)
                    <p class="shop-catalog-banner__sub">Prochain · {{ $nextRank->name }} · {{ number_format($progress, 0) }}%</p>
                @else
                    <p class="shop-catalog-banner__sub">Grade maximum atteint</p>
                @endif
            </div>
            <div class="shop-catalog-banner__tools">
                <a href="{{ route('rank.history') }}" class="shop-banner-icon-btn" aria-label="Historique des grades">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="rank-card rank-current-card animate-fadeInUp delay-1">
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

    <div class="rank-stats stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-2">
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
                            ['label' => 'Option 2', 'value' => 'Avoir 2 branches distinctes (niv. 5+) et 4 autres branches (niv. 4+), rang max dans chaque jambe, ≥ 16000 PV groupe'],
                            ['label' => 'Option 3', 'value' => 'Avoir 1 branche (niv. 5+) et 6 autres branches (niv. 4+), jambes distinctes, ≥ 16000 PV groupe']
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
                        'Diamant Bleu' => '#3d8a2a',
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