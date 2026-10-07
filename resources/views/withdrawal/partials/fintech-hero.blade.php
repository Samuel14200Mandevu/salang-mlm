@php
    $pending = $pendingWithdrawals ?? ($stats['total_pending'] ?? 0);
    $feePct = 2.5;
@endphp
<section class="member-fintech-hero member-withdraw-hero member-fintech-animate member-fintech-animate--hero">
    <div class="member-fintech-hero__mesh" aria-hidden="true"></div>

    <div class="member-fintech-hero__top">
        <div class="member-fintech-hero__user">
            <a href="{{ route('wallet.index') }}" class="member-withdraw-back">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Portefeuille
            </a>
            <p class="member-fintech-hero__hello member-withdraw-hero__title">Retrait</p>
            <span class="member-wallet-hero__badge">USD · Retrait</span>
        </div>
        <div class="member-fintech-hero__tools">
            <a href="{{ route('kyc.index') }}" class="member-fintech-hero__tool" aria-label="Vérification KYC">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
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
    </div>

    <div class="member-fintech-hero__pnl-row">
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">En cours</span>
            <span class="member-fintech-hero__pnl-value">${{ number_format($pending, 2) }}</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Frais</span>
            <span class="member-fintech-hero__pnl-value">{{ $feePct }}%</span>
        </div>
        <div class="member-fintech-hero__pnl">
            <span class="member-fintech-hero__pnl-label">Retiré</span>
            <span class="member-fintech-hero__pnl-value">${{ number_format($stats['total_withdrawn'] ?? 0, 2) }}</span>
        </div>
    </div>
</section>
