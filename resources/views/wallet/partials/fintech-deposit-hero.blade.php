<section class="member-fintech-hero member-deposit-hero member-fintech-animate member-fintech-animate--hero">
    <div class="member-fintech-hero__mesh" aria-hidden="true"></div>

    <div class="member-fintech-hero__top">
        <div class="member-fintech-hero__user">
            <a href="{{ route('wallet.index') }}" class="member-deposit-back">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Portefeuille
            </a>
            <p class="member-fintech-hero__hello member-deposit-hero__title">Dépôt</p>
            <span class="member-wallet-hero__badge">USD · Alimentation</span>
        </div>
        <div class="member-fintech-hero__tools">
            <a href="{{ route('withdrawal.index') }}" class="member-fintech-hero__tool" aria-label="Retrait">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="member-fintech-hero__balance-row">
        <div>
            <p class="member-fintech-hero__label">Solde actuel</p>
            <p class="member-fintech-hero__balance">
                <span class="member-fintech-hero__currency">$</span>{{ number_format($balance ?? 0, 2) }}
            </p>
        </div>
    </div>

    <div class="member-fintech-hero__pnl-row">
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Frais</span>
            <span class="member-fintech-hero__pnl-value is-up">0%</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Minimum</span>
            <span class="member-fintech-hero__pnl-value">$1</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Plafond</span>
            <span class="member-fintech-hero__pnl-value">$10k</span>
        </div>
    </div>
</section>
