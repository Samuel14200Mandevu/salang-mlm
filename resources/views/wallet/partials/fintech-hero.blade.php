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
    $firstName = explode(' ', trim(Auth::user()->name))[0];

    $points = $monthlyCommissions ?? collect();
    $amounts = $points->pluck('total')->map(fn ($v) => (float) $v)->all();
    $sparkMax = max(1, (float) (count($amounts) ? max($amounts) : 0));
    $sparkCoords = [];
    $count = count($points);
    foreach ($points as $i => $row) {
        $x = $count <= 1 ? 50 : ($i / max(1, $count - 1)) * 100;
        $y = 100 - ((float) ($row->total ?? 0) / $sparkMax) * 82 - 8;
        $sparkCoords[] = round($x, 1) . ',' . round($y, 1);
    }
    $sparkLine = count($sparkCoords) > 1 ? implode(' ', $sparkCoords) : '';
@endphp
<section class="member-fintech-hero member-wallet-hero member-wallet-hero--art member-fintech-animate member-fintech-animate--hero">
    <div class="member-fintech-hero__mesh" aria-hidden="true"></div>

    <div class="member-fintech-hero__top member-wallet-hero__top">
        <div class="member-fintech-hero__user">
            <p class="member-fintech-hero__hello">{{ $greeting }}, {{ strtoupper($firstName) }}</p>
            <span class="member-wallet-hero__badge">Portefeuille USD</span>
        </div>
    </div>

    <div class="member-fintech-hero__balance-row member-wallet-hero__balance-row">
        <div class="member-wallet-hero__balance-main">
            <p class="member-fintech-hero__label">Solde disponible</p>
            <p class="member-fintech-hero__balance">
                <span class="member-fintech-hero__currency">$</span>{{ number_format($balance ?? 0, 2) }}
            </p>
            @if($sparkLine)
                <svg class="member-fintech-hero__spark member-wallet-hero__spark" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="memberWalletSparkFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="currentColor" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="currentColor" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <polygon points="0,100 {{ $sparkLine }} 100,100" fill="url(#memberWalletSparkFill)"/>
                    <polyline points="{{ $sparkLine }}" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                </svg>
            @endif
        </div>
        <div class="member-wallet-hero__stage" aria-hidden="true">
            <span class="member-wallet-hero__aurora member-wallet-hero__aurora--a"></span>
            <span class="member-wallet-hero__aurora member-wallet-hero__aurora--b"></span>
            <span class="member-wallet-hero__aurora member-wallet-hero__aurora--c"></span>
            <span class="member-wallet-hero__pedestal"></span>
            <svg class="member-wallet-hero__orbit" viewBox="0 0 200 200" fill="none">
                <defs>
                    <linearGradient id="walletOrbitStroke" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#f0d878" stop-opacity="0"/>
                        <stop offset="40%" stop-color="#fbbf24" stop-opacity="0.95"/>
                        <stop offset="70%" stop-color="#fde68a" stop-opacity="0.8"/>
                        <stop offset="100%" stop-color="#f59e0b" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <circle class="member-wallet-hero__orbit-track" cx="100" cy="100" r="78" stroke="currentColor" stroke-opacity="0.14" stroke-width="1"/>
                <circle class="member-wallet-hero__orbit-arc" cx="100" cy="100" r="78" stroke="url(#walletOrbitStroke)" stroke-width="2.2" stroke-linecap="round"/>
                <circle class="member-wallet-hero__orbit-arc member-wallet-hero__orbit-arc--inner" cx="100" cy="100" r="58" stroke="url(#walletOrbitStroke)" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
            <div class="member-wallet-hero__art">
                <img
                    class="member-wallet-hero__art-img"
                    src="{{ asset('images/portefeuille.png') }}"
                    alt=""
                    width="605"
                    height="501"
                    loading="eager"
                    decoding="async"
                >
            </div>
        </div>
    </div>

    <div class="member-wallet-hero__stats-bar">
        <div class="member-wallet-hero__stat is-pending">
            <span class="member-wallet-hero__stat-icon" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 22h14"/>
                    <path d="M5 2h14"/>
                    <path d="M17 22v-4.2a2 2 0 00-.59-1.4L12 12l-4.41 4.4A2 2 0 007 17.8V22"/>
                    <path d="M7 2v4.2a2 2 0 00.59 1.4L12 12l4.41-4.4A2 2 0 0017 6.2V2"/>
                </svg>
            </span>
            <div class="member-wallet-hero__stat-text">
                <span class="member-wallet-hero__stat-label">En attente</span>
                <span class="member-wallet-hero__stat-value">${{ number_format($pendingBalance ?? 0, 2) }}</span>
            </div>
        </div>
        <div class="member-wallet-hero__stat is-deposit">
            <span class="member-wallet-hero__stat-icon" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-4 4m4-4l4 4"/>
                </svg>
            </span>
            <div class="member-wallet-hero__stat-text">
                <span class="member-wallet-hero__stat-label">Dépôts</span>
                <span class="member-wallet-hero__stat-value is-up">${{ number_format($totalDeposited ?? 0, 2) }}</span>
            </div>
        </div>
        <div class="member-wallet-hero__stat is-withdrawn">
            <span class="member-wallet-hero__stat-icon" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-4-4m4 4l4-4"/>
                </svg>
            </span>
            <div class="member-wallet-hero__stat-text">
                <span class="member-wallet-hero__stat-label">Retiré</span>
                <span class="member-wallet-hero__stat-value">${{ number_format($totalWithdrawn ?? 0, 2) }}</span>
            </div>
        </div>
    </div>
</section>
