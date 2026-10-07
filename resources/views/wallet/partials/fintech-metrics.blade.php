@php
    $stats = $transactionStats ?? [];
@endphp
<div class="member-fintech-rail-wrap member-fintech-animate member-fintech-animate--d3">
    <p class="member-fintech-section-label">Aperçu</p>
    <div class="member-fintech-rail" tabindex="0">
        <div class="member-fintech-rail__track">
            <article class="member-fintech-tile is-highlight">
                <span class="member-fintech-tile__label">Commissions</span>
                <span class="member-fintech-tile__value member-fintech-tile__value--sm">${{ number_format($stats['total_commission'] ?? 0, 0) }}</span>
            </article>
            <article class="member-fintech-tile">
                <span class="member-fintech-tile__label">Dépôts</span>
                <span class="member-fintech-tile__value member-fintech-tile__value--sm">${{ number_format($stats['total_deposit'] ?? 0, 0) }}</span>
            </article>
            <article class="member-fintech-tile">
                <span class="member-fintech-tile__label">Retraits</span>
                <span class="member-fintech-tile__value member-fintech-tile__value--sm">${{ number_format(abs($stats['total_withdrawal'] ?? 0), 0) }}</span>
            </article>
            <article class="member-fintech-tile">
                <span class="member-fintech-tile__label">Opérations</span>
                <span class="member-fintech-tile__value">{{ ($stats['count_commission'] ?? 0) + ($stats['count_deposit'] ?? 0) + ($stats['count_withdrawal'] ?? 0) }}</span>
            </article>
        </div>
    </div>
</div>
