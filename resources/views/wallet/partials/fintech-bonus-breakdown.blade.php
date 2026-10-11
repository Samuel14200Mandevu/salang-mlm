@php
    $bonusByType = $bonusByType ?? [];
@endphp
@if(!empty($bonusByType))
<div class="member-fintech-grid-wrap member-fintech-animate member-fintech-animate--d4">
    <p class="member-fintech-section-label">Détail des bonus</p>
    <div class="member-fintech-grid member-fintech-grid--2x2">
            @foreach($bonusByType as $key => $row)
                <article class="member-fintech-tile {{ $loop->first ? 'is-highlight' : '' }}">
                    <span class="member-fintech-tile__label">{{ $row['label'] }}</span>
                    <span class="member-fintech-tile__value member-fintech-tile__value--sm">${{ number_format($row['total'], 2) }}</span>
                    @if(($row['count'] ?? 0) > 0)
                        <span class="member-fintech-tile__meta">{{ $row['count'] }} paiement{{ $row['count'] > 1 ? 's' : '' }}</span>
                    @endif
                </article>
            @endforeach
    </div>
</div>
@endif
