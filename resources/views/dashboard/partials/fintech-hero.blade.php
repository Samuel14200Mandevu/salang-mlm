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
    $rankLevel = (int) ($dashboardLevelNumber ?? $user->rank_level ?? 0);
    $rankImageUrl = \App\Support\MlmRank::imageUrl($rankLevel);

    $bonusByType = $bonusByType ?? [];
    $bonusTotal = (float) ($bonusTotal ?? collect($bonusByType)->sum('total'));
    $bonusMonthLabel = $bonusMonthLabel ?? '';
    $bonusRows = [
        'direct' => $bonusByType['direct'] ?? ['label' => 'Direct', 'total' => 0],
        'indirect' => $bonusByType['indirect'] ?? ['label' => 'Indirect', 'total' => 0],
        'leadership' => $bonusByType['leadership'] ?? ['label' => 'Leadership', 'total' => 0],
    ];
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
            <p class="member-fintech-hero__label">Total bonus</p>
            <p class="member-fintech-hero__balance" data-bonus-amount>
                <span class="member-fintech-hero__currency">$</span><span
                    data-count-up="{{ $bonusTotal }}"
                    data-count-decimals="2"
                >{{ number_format($bonusTotal, 2) }}</span>
            </p>
            @if($bonusMonthLabel)
                <p class="member-fintech-hero__bonus-month">{{ $bonusMonthLabel }}</p>
            @endif
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

    <div class="member-fintech-hero__bonus-block" data-bonus-privacy>
        <div class="member-fintech-hero__bonus-block-head">
            <button
                type="button"
                class="member-fintech-hero__privacy-toggle"
                data-bonus-privacy-toggle
                aria-pressed="false"
                aria-label="Masquer les montants"
                title="Masquer / afficher"
            >
                <svg class="member-fintech-hero__privacy-icon member-fintech-hero__privacy-icon--open" width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg class="member-fintech-hero__privacy-icon member-fintech-hero__privacy-icon--closed" width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true" hidden>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        </div>

        <div class="member-fintech-hero__pnl-row member-fintech-hero__pnl-row--bonus" aria-label="Répartition des bonus">
            @foreach($bonusRows as $key => $row)
                <a
                    href="{{ route('commissions.index', array_filter(['type' => $key, 'period' => ($bonusMonth ?? null) ?: null])) }}"
                    class="member-fintech-hero__pnl"
                >
                    <span class="member-fintech-hero__pnl-label">{{ $row['label'] }}</span>
                    <span class="member-fintech-hero__pnl-value is-up" data-bonus-amount>
                        $<span
                            data-count-up="{{ (float) ($row['total'] ?? 0) }}"
                            data-count-decimals="2"
                        >{{ number_format((float) ($row['total'] ?? 0), 2) }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<script>
(function () {
    var root = document.querySelector('[data-bonus-privacy]');
    if (!root) return;
    var btn = root.querySelector('[data-bonus-privacy-toggle]');
    if (!btn) return;
    var key = 'salang_member_bonus_privacy_hidden';
    var openIcon = btn.querySelector('.member-fintech-hero__privacy-icon--open');
    var closedIcon = btn.querySelector('.member-fintech-hero__privacy-icon--closed');
    var section = document.querySelector('.member-fintech-hero.member-dashboard-hero') || root;

    function apply(hidden) {
        root.classList.toggle('is-amounts-hidden', hidden);
        section.classList.toggle('is-amounts-hidden', hidden);
        btn.setAttribute('aria-pressed', hidden ? 'true' : 'false');
        btn.setAttribute('aria-label', hidden ? 'Afficher les montants' : 'Masquer les montants');
        if (openIcon) openIcon.hidden = hidden;
        if (closedIcon) closedIcon.hidden = !hidden;
        try { localStorage.setItem(key, hidden ? '1' : '0'); } catch (e) {}
    }

    var initial = false;
    try { initial = localStorage.getItem(key) === '1'; } catch (e) {}
    apply(initial);

    btn.addEventListener('click', function () {
        apply(!root.classList.contains('is-amounts-hidden'));
    });
})();
</script>
