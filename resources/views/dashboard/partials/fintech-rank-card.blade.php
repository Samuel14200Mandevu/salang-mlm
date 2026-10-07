@php
    $teamPct = min(100, max(0, (float) ($rankProgress['team_progress'] ?? $rankProgress['progress'] ?? 0)));
    $hasNext = isset($rankProgress['next']) && ($rankProgress['next'] ?? '') !== 'Maximum Level';
    $teamTarget = (float) ($rankProgress['team_target_pv'] ?? $rankProgress['next_pv'] ?? 0);
    $teamNeeded = (float) ($rankProgress['team_pv_needed'] ?? $rankProgress['pv_needed'] ?? 0);
@endphp
<section class="member-fintech-panel member-fintech-rank member-fintech-animate member-fintech-animate--d4">
    <div class="member-fintech-panel__head">
        <div>
            <h2 class="member-fintech-panel__title">PV équipe</h2>
            @if($hasNext)
                <p class="member-fintech-panel__sub">Objectif {{ $rankProgress['next'] }}</p>
            @else
                <p class="member-fintech-panel__sub">Grade maximum</p>
            @endif
        </div>
        <span class="member-fintech-rank__pct">{{ number_format($teamPct, 1) }}%</span>
    </div>
    <div class="member-fintech-rank__bar" role="progressbar" aria-valuenow="{{ $teamPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progression PV équipe">
        <span class="member-fintech-rank__fill" style="width: {{ $teamPct }}%;"></span>
    </div>
    <div class="member-fintech-rank__foot">
        <span>{{ number_format($pvCumul ?? 0) }} PV équipe</span>
        @if($hasNext && $teamTarget > 0)
            <span>{{ number_format($teamTarget) }} PV cible</span>
        @endif
    </div>
    @if($teamNeeded > 0)
        <p class="member-fintech-rank__hint">
            Encore <strong>{{ number_format($teamNeeded) }} PV équipe</strong> pour le prochain grade
        </p>
    @endif
    <a href="{{ route('rank.index') }}" class="member-fintech-rank__link">Voir mon parcours →</a>
</section>
