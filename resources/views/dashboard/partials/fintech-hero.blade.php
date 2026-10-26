@php
    $hour = (int) now()->format('G');
    if ($hour >= 5 && $hour < 12) {
        $greeting = 'Bonjour';
    } elseif ($hour >= 12 && $hour < 18) {
        $greeting = 'Bon après-midi';
    } elseif ($hour >= 18 && $hour < 22) {
        $greeting = 'Bonsoir';
    } else {
        $greeting = 'Bonne nuit';
    }
    $firstName = explode(' ', trim($user->name))[0];

    $todayPv = (float) ($stats['today_pv'] ?? 0);
    $points = $monthlyPvData ?? [];
    $amounts = array_column($points, 'amount');
    $sparkMax = max(1, (float) (count($amounts) ? max($amounts) : 0));
    $sparkCoords = [];
    $count = count($points);
    foreach ($points as $i => $row) {
        $x = $count <= 1 ? 50 : ($i / max(1, $count - 1)) * 100;
        $y = 100 - ((float) ($row['amount'] ?? 0) / $sparkMax) * 82 - 8;
        $sparkCoords[] = round($x, 1) . ',' . round($y, 1);
    }
    $sparkLine = count($sparkCoords) > 1 ? implode(' ', $sparkCoords) : '';
    $personalPct = min(100, max(0, (float) ($rankProgress['personal_progress'] ?? 0)));
    $personalTarget = (float) ($rankProgress['personal_target_pv'] ?? 0);
    $rankLevel = (int) ($dashboardLevelNumber ?? $user->rank_level ?? 0);
    $rankImageUrl = \App\Support\MlmRank::imageUrl($rankLevel);
@endphp
<section class="member-fintech-hero member-dashboard-hero member-fintech-animate member-fintech-animate--hero{{ $rankImageUrl ? ' has-grade-stage' : '' }}">
    <div class="member-fintech-hero__mesh" aria-hidden="true"></div>
    @if($rankImageUrl)
        <div class="member-dashboard-hero__bloom" aria-hidden="true"></div>
    @endif

    <div class="member-fintech-hero__top">
        <div class="member-fintech-hero__user">
            <p class="member-fintech-hero__hello">{{ $greeting }}, {{ $firstName }}</p>
        </div>
        <div class="member-fintech-hero__tools">
            <a href="{{ route('commissions.index') }}" class="member-fintech-hero__tool" aria-label="Commissions">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </a>
            <a href="{{ route('my-pv.index') }}" class="member-fintech-hero__tool" aria-label="Historique PV">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="member-fintech-hero__balance-row member-dashboard-hero__balance-row{{ $rankImageUrl ? ' has-grade-badge' : '' }}">
        @if($rankImageUrl)
            <div class="member-dashboard-hero__stage" aria-hidden="true">
                <span class="member-dashboard-hero__aurora member-dashboard-hero__aurora--a"></span>
                <span class="member-dashboard-hero__aurora member-dashboard-hero__aurora--b"></span>
                <span class="member-dashboard-hero__aurora member-dashboard-hero__aurora--c"></span>
                <svg class="member-dashboard-hero__orbit" viewBox="0 0 200 200" fill="none">
                    <defs>
                        <linearGradient id="gradeOrbitStroke" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="var(--dashboard-accent)" stop-opacity="0"/>
                            <stop offset="35%" stop-color="var(--dashboard-warm-soft)" stop-opacity="0.95"/>
                            <stop offset="65%" stop-color="var(--color-accent-300, #f0d878)" stop-opacity="0.85"/>
                            <stop offset="100%" stop-color="var(--dashboard-accent)" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <circle class="member-dashboard-hero__orbit-track" cx="100" cy="100" r="78" stroke="currentColor" stroke-opacity="0.12" stroke-width="1"/>
                    <circle class="member-dashboard-hero__orbit-arc" cx="100" cy="100" r="78" stroke="url(#gradeOrbitStroke)" stroke-width="2.25" stroke-linecap="round"/>
                    <circle class="member-dashboard-hero__orbit-arc member-dashboard-hero__orbit-arc--inner" cx="100" cy="100" r="58" stroke="url(#gradeOrbitStroke)" stroke-width="1.25" stroke-linecap="round"/>
                </svg>
                <svg class="member-dashboard-hero__beams" viewBox="0 0 200 200" preserveAspectRatio="xMidYMid slice">
                    <defs>
                        <linearGradient id="gradeBeamA" x1="0%" y1="50%" x2="100%" y2="50%">
                            <stop offset="0%" stop-color="var(--dashboard-accent)" stop-opacity="0"/>
                            <stop offset="45%" stop-color="var(--dashboard-warm-soft)" stop-opacity="0.7"/>
                            <stop offset="100%" stop-color="var(--dashboard-accent)" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="gradeBeamB" x1="100%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="var(--color-accent-300, #f0d878)" stop-opacity="0"/>
                            <stop offset="50%" stop-color="var(--dashboard-accent)" stop-opacity="0.55"/>
                            <stop offset="100%" stop-color="var(--color-accent-400, #e8c547)" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <path class="member-dashboard-hero__beam" d="M20 130 C 55 95, 90 70, 130 55 C 150 48, 165 40, 185 28" fill="none" stroke="url(#gradeBeamA)" stroke-width="2.5" stroke-linecap="round"/>
                    <path class="member-dashboard-hero__beam" d="M15 95 C 50 88, 95 100, 140 78 C 160 68, 175 55, 192 42" fill="none" stroke="url(#gradeBeamB)" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                <span class="member-dashboard-hero__glow"></span>
                <span class="member-dashboard-hero__sparkles">
                    <i></i><i></i><i></i><i></i><i></i><i></i>
                </span>
                <span class="member-dashboard-hero__ghost member-dashboard-hero__ghost--a"></span>
                <span class="member-dashboard-hero__ghost member-dashboard-hero__ghost--b"></span>
                <span class="member-dashboard-hero__ghost member-dashboard-hero__ghost--c"></span>
                <span class="member-dashboard-hero__pedestal">
                    <span class="member-dashboard-hero__pedestal-plate member-dashboard-hero__pedestal-plate--outer"></span>
                    <span class="member-dashboard-hero__pedestal-plate member-dashboard-hero__pedestal-plate--mid"></span>
                    <span class="member-dashboard-hero__pedestal-plate member-dashboard-hero__pedestal-plate--inner"></span>
                    <span class="member-dashboard-hero__pedestal-shine"></span>
                </span>
                <div class="member-dashboard-hero__badge-wrap">
                    <img
                        src="{{ $rankImageUrl }}"
                        alt=""
                        class="member-dashboard-hero__badge-img"
                        width="168"
                        height="210"
                        loading="eager"
                        decoding="async"
                    >
                    <span class="member-dashboard-hero__badge-sheen"></span>
                </div>
            </div>
            <span class="sr-only">Grade {{ $currentRankName }}</span>
        @endif
        <div class="member-dashboard-hero__pv">
            <p class="member-fintech-hero__label">PV personnel</p>
            <p class="member-fintech-hero__balance">
                <span
                    data-count-up="{{ (float) ($pvPersonnel ?? 0) }}"
                    data-count-decimals="0"
                >{{ number_format($pvPersonnel ?? 0, 0) }}</span><span class="member-fintech-hero__currency"> PV</span>
            </p>
        </div>
        @if(! $rankImageUrl && $sparkLine)
            <svg class="member-fintech-hero__spark" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="memberSparkFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="currentColor" stop-opacity="0.35"/>
                        <stop offset="100%" stop-color="currentColor" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <polygon points="0,100 {{ $sparkLine }} 100,100" fill="url(#memberSparkFill)"/>
                <polyline points="{{ $sparkLine }}" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
            </svg>
        @endif
    </div>

    <div class="member-fintech-hero__pnl-row">
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">24h</span>
            <span class="member-fintech-hero__pnl-value {{ $todayPv >= 0 ? 'is-up' : 'is-down' }}">
                <span
                    data-count-up="{{ $todayPv }}"
                    data-count-decimals="0"
                    data-count-signed
                    data-count-suffix=" PV"
                >{{ $todayPv >= 0 ? '+' : '' }}{{ number_format($todayPv, 0) }} PV</span>
            </span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">PV mois</span>
            <span class="member-fintech-hero__pnl-value">
                <span
                    data-count-up="{{ (float) ($rankProgress['monthly_pv'] ?? 0) }}"
                    data-count-decimals="0"
                    data-count-suffix=" PV"
                >{{ number_format($rankProgress['monthly_pv'] ?? 0, 0) }} PV</span>
            </span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">PV équipe</span>
            <span class="member-fintech-hero__pnl-value">
                <span
                    data-count-up="{{ (float) ($pvCumul ?? 0) }}"
                    data-count-decimals="0"
                    data-count-suffix=" PV"
                >{{ number_format($pvCumul ?? 0, 0) }} PV</span>
            </span>
        </div>
    </div>

    <div class="member-fintech-hero__goal">
        <div class="member-fintech-hero__goal-labels">
            <span>PV personnel</span>
            <span>
                @if($personalTarget > 0)
                    <span data-count-up="{{ (float) ($pvPersonnel ?? 0) }}" data-count-decimals="0">{{ number_format($pvPersonnel ?? 0) }}</span>
                    /
                    <span data-count-up="{{ $personalTarget }}" data-count-decimals="0">{{ number_format($personalTarget) }}</span>
                @else
                    <span data-count-up="{{ $personalPct }}" data-count-decimals="0" data-count-suffix="%">{{ number_format($personalPct, 0) }}%</span>
                @endif
            </span>
        </div>
        <div class="member-fintech-hero__goal-bar" role="progressbar" aria-valuenow="{{ $personalPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progression PV personnel">
            <span style="width: {{ $personalPct }}%;"></span>
        </div>
    </div>
</section>
