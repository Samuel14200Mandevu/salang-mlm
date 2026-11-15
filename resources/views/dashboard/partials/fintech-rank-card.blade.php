@php
    $usesPersonal = (bool) ($rankProgress['uses_personal_pv'] ?? false);
    $metricLabel = $usesPersonal ? 'PV personnel' : 'PV cumulés';
    $teamPct = min(100, max(0, (float) ($rankProgress['team_progress'] ?? $rankProgress['progress'] ?? 0)));
    $hasNext = isset($rankProgress['next']) && ($rankProgress['next'] ?? '') !== 'Maximum Level';
    $teamTarget = (float) ($rankProgress['team_target_pv'] ?? $rankProgress['next_pv'] ?? 0);
    $teamNeeded = (float) ($rankProgress['team_pv_needed'] ?? $rankProgress['pv_needed'] ?? 0);
    $currentMetricPv = (float) ($rankProgress['current_pv'] ?? ($usesPersonal ? ($pvPersonnel ?? 0) : ($pvCumul ?? 0)));
@endphp
<section class="member-fintech-panel member-fintech-rank member-fintech-animate member-fintech-animate--d4">
    <div class="member-fintech-panel__head">
        <div>
            <h2 class="member-fintech-panel__title">{{ $metricLabel }}</h2>
            @if($hasNext)
                <p class="member-fintech-panel__sub">Objectif {{ $rankProgress['next'] }}</p>
            @else
                <p class="member-fintech-panel__sub">Grade maximum</p>
            @endif
        </div>
        <span class="member-fintech-rank__pct"><span data-count-up="{{ $teamPct }}" data-count-decimals="1" data-count-suffix="%">{{ number_format($teamPct, 1) }}%</span></span>
    </div>
    <div class="member-fintech-rank__bar" role="progressbar" aria-valuenow="{{ $teamPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progression {{ $metricLabel }}">
        <span class="member-fintech-rank__fill" style="width: {{ $teamPct }}%;"></span>
    </div>
    <div class="member-fintech-rank__foot">
        <span><span data-count-up="{{ $currentMetricPv }}" data-count-decimals="0">{{ number_format($currentMetricPv) }}</span> {{ $metricLabel }}</span>
        @if($hasNext && $teamTarget > 0)
            <span><span data-count-up="{{ $teamTarget }}" data-count-decimals="0">{{ number_format($teamTarget) }}</span> PV cible</span>
        @endif
    </div>
    @if($teamNeeded > 0)
        <p class="member-fintech-rank__hint">
            Encore <strong><span data-count-up="{{ $teamNeeded }}" data-count-decimals="0">{{ number_format($teamNeeded) }}</span> {{ $metricLabel }}</strong> pour le prochain grade
        </p>
    @endif
    <a href="{{ route('rank.index') }}" class="member-fintech-rank__link">Voir mon parcours →</a>
</section>
