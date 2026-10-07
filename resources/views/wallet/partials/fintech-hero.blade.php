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
<section class="member-fintech-hero member-wallet-hero member-fintech-animate member-fintech-animate--hero">
    <div class="member-fintech-hero__mesh" aria-hidden="true"></div>

    <div class="member-fintech-hero__top">
        <div class="member-fintech-hero__user">
            <p class="member-fintech-hero__hello">{{ $greeting }}, {{ $firstName }}</p>
            <span class="member-wallet-hero__badge">Portefeuille USD</span>
        </div>
        <div class="member-fintech-hero__tools">
            <a href="{{ route('commissions.index') }}" class="member-fintech-hero__tool" aria-label="Commissions">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </a>
            <a href="{{ route('wallet.deposit') }}" class="member-fintech-hero__tool" aria-label="Dépôt">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="member-fintech-hero__balance-row">
        <div>
            <p class="member-fintech-hero__label">Solde disponible</p>
            <p class="member-fintech-hero__balance">
                <span class="member-fintech-hero__currency">$</span>{{ number_format($balance ?? 0, 2) }}
            </p>
        </div>
        @if($sparkLine)
            <svg class="member-fintech-hero__spark" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
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

    <div class="member-fintech-hero__pnl-row">
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">En attente</span>
            <span class="member-fintech-hero__pnl-value">${{ number_format($pendingBalance ?? 0, 2) }}</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Dépôts</span>
            <span class="member-fintech-hero__pnl-value is-up">${{ number_format($totalDeposited ?? 0, 2) }}</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Retiré</span>
            <span class="member-fintech-hero__pnl-value">${{ number_format($totalWithdrawn ?? 0, 2) }}</span>
        </div>
    </div>
</section>
