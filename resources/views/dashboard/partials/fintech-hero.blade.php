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
@endphp
<section class="member-fintech-hero member-dashboard-hero member-fintech-animate member-fintech-animate--hero">
    <div class="member-fintech-hero__mesh" aria-hidden="true"></div>

    <div class="member-fintech-hero__top">
        <div class="member-fintech-hero__user">
            <p class="member-fintech-hero__hello">{{ $greeting }}, {{ $firstName }}</p>
            <span class="{{ $currentRankBadgeClass ?? \App\Support\MlmRank::badgeClass($dashboardLevelNumber) }} member-fintech-hero__grade">
                {{ $currentRankName }}
            </span>
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

    <div class="member-fintech-hero__balance-row">
        <div>
            <p class="member-fintech-hero__label">PV personnel</p>
            <p class="member-fintech-hero__balance">
                {{ number_format($pvPersonnel ?? 0, 0) }}<span class="member-fintech-hero__currency"> PV</span>
            </p>
        </div>
        @if($sparkLine)
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
                {{ $todayPv >= 0 ? '+' : '' }}{{ number_format($todayPv, 0) }} PV
            </span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">PV mois</span>
            <span class="member-fintech-hero__pnl-value">{{ number_format($rankProgress['monthly_pv'] ?? 0, 0) }} PV</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">PV équipe</span>
            <span class="member-fintech-hero__pnl-value">{{ number_format($pvCumul ?? 0, 0) }} PV</span>
        </div>
    </div>

    <div class="member-fintech-hero__goal">
        <div class="member-fintech-hero__goal-labels">
            <span>PV personnel</span>
            <span>
                @if($personalTarget > 0)
                    {{ number_format($pvPersonnel ?? 0) }} / {{ number_format($personalTarget) }}
                @else
                    {{ number_format($personalPct, 0) }}%
                @endif
            </span>
        </div>
        <div class="member-fintech-hero__goal-bar" role="progressbar" aria-valuenow="{{ $personalPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progression PV personnel">
            <span style="width: {{ $personalPct }}%;"></span>
        </div>
    </div>
</section>
