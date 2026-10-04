@php
    $bands = $commissionBands ?? [];
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-2">
    <div class="dashboard-band-card">
        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Bande A — Historique paye</p>
        <p class="band-value text-green-600">${{ number_format($bands['paid_offline_historical'] ?? 0, 2) }}</p>
    </div>
    <div class="dashboard-band-card">
        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Bande B — Systeme paye</p>
        <p class="band-value text-blue-600">${{ number_format($bands['paid_system'] ?? 0, 2) }}</p>
    </div>
    <div class="dashboard-band-card">
        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Bande C — En attente payable</p>
        <p class="band-value text-orange-600">${{ number_format($bands['pending_payable'] ?? 0, 2) }}</p>
        @if(($bands['pending_payable_count'] ?? 0) > 0)
            <p class="text-[10px] text-[var(--text-secondary)] mt-1">{{ $bands['pending_payable_count'] }} commission(s)</p>
        @endif
    </div>
    <div class="dashboard-band-card">
        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Solde portefeuille</p>
        <p class="band-value text-primary-500">${{ number_format($walletBalance ?? 0, 2) }}</p>
    </div>
</div>
