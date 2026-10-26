@php
    $bands = $commissionBands ?? [];
@endphp
<div class="dashboard-bands-layout animate-fadeInUp delay-2">
    <div class="dashboard-band-card dashboard-band-card--highlight">
        <p class="dashboard-band-card__label">Solde portefeuille</p>
        <p class="band-value"><span data-count-up="{{ (float) ($walletBalance ?? 0) }}" data-count-decimals="2" data-count-prefix="$">${{ number_format($walletBalance ?? 0, 2) }}</span></p>
        <p class="dashboard-band-card__meta">Disponible pour retrait ou achat</p>
    </div>
    <div class="dashboard-bands-stack">
        <div class="dashboard-band-card">
            <p class="dashboard-band-card__label">Bande A — Historique paye</p>
            <p class="band-value band-value--neutral"><span data-count-up="{{ (float) ($bands['paid_offline_historical'] ?? 0) }}" data-count-decimals="2" data-count-prefix="$">${{ number_format($bands['paid_offline_historical'] ?? 0, 2) }}</span></p>
        </div>
        <div class="dashboard-band-card">
            <p class="dashboard-band-card__label">Bande B — Systeme paye</p>
            <p class="band-value band-value--neutral"><span data-count-up="{{ (float) ($bands['paid_system'] ?? 0) }}" data-count-decimals="2" data-count-prefix="$">${{ number_format($bands['paid_system'] ?? 0, 2) }}</span></p>
        </div>
        <div class="dashboard-band-card">
            <p class="dashboard-band-card__label">Bande C — En attente payable</p>
            <p class="band-value"><span data-count-up="{{ (float) ($bands['pending_payable'] ?? 0) }}" data-count-decimals="2" data-count-prefix="$">${{ number_format($bands['pending_payable'] ?? 0, 2) }}</span></p>
            @if(($bands['pending_payable_count'] ?? 0) > 0)
                <p class="dashboard-band-card__meta">{{ $bands['pending_payable_count'] }} commission(s)</p>
            @endif
        </div>
    </div>
</div>
