<section class="member-fintech-hero member-reports-hero member-fintech-animate member-fintech-animate--hero">
    <div class="member-fintech-hero__mesh" aria-hidden="true"></div>

    <div class="member-fintech-hero__top">
        <div class="member-fintech-hero__user">
            <a href="{{ route('profile.help') }}" class="member-reports-back">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Aide
            </a>
            <p class="member-fintech-hero__hello member-reports-hero__title">Rapports</p>
            <span class="member-wallet-hero__badge">Statistiques &amp; exports</span>
        </div>
        <div class="member-fintech-hero__tools">
            <a href="{{ route('wallet.export') }}" class="member-fintech-hero__tool" aria-label="Exporter">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="member-fintech-hero__balance-row">
        <div>
            <p class="member-fintech-hero__label">Commissions versées</p>
            <p class="member-fintech-hero__balance">
                <span class="member-fintech-hero__currency">$</span>{{ number_format($totalEarnings ?? 0, 2) }}
            </p>
        </div>
    </div>

    <div class="member-fintech-hero__pnl-row">
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Solde</span>
            <span class="member-fintech-hero__pnl-value is-up">${{ number_format($balance ?? 0, 2) }}</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Retiré</span>
            <span class="member-fintech-hero__pnl-value">${{ number_format($totalWithdrawn ?? 0, 2) }}</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Opérations</span>
            <span class="member-fintech-hero__pnl-value">{{ number_format($transactionsCount ?? 0) }}</span>
        </div>
    </div>
</section>
