@php
    $todayPv = (float) ($stats['today_pv'] ?? 0);
    $personalNeeded = (float) ($rankProgress['personal_pv_needed'] ?? 0);
@endphp
<div class="member-fintech-grid-wrap member-fintech-animate member-fintech-animate--d3">
    <p class="member-fintech-section-label">Aperçu réseau</p>
    <div class="member-fintech-grid member-fintech-grid--2x3">
        <article class="member-fintech-tile is-highlight">
            <span class="member-fintech-tile__label">PV perso</span>
            <span class="member-fintech-tile__value">{{ number_format($pvPersonnel ?? 0) }}</span>
        </article>
        <article class="member-fintech-tile">
            <span class="member-fintech-tile__label">PV équipe</span>
            <span class="member-fintech-tile__value">{{ number_format($pvCumul ?? 0) }}</span>
        </article>
        <article class="member-fintech-tile">
            <span class="member-fintech-tile__label">Filleuls</span>
            <span class="member-fintech-tile__value">{{ $totalDownlines ?? 0 }}</span>
        </article>
        <article class="member-fintech-tile">
            <span class="member-fintech-tile__label">PV 24h</span>
            <span class="member-fintech-tile__value member-fintech-tile__value--sm">{{ number_format($todayPv, 0) }}</span>
        </article>
        <article class="member-fintech-tile">
            <span class="member-fintech-tile__label">Reste grade</span>
            <span class="member-fintech-tile__value member-fintech-tile__value--sm">{{ number_format($personalNeeded, 0) }}</span>
        </article>
        <article class="member-fintech-tile">
            <span class="member-fintech-tile__label">PV mois</span>
            <span class="member-fintech-tile__value">{{ number_format($rankProgress['monthly_pv'] ?? 0) }}</span>
        </article>
    </div>
</div>
