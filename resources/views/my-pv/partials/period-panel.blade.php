@php
    $panelClass = $panelClass ?? '';
@endphp
<div class="my-pv-period-panel {{ $panelClass }}"
     data-my-pv-period-panel
     role="dialog"
     aria-label="Filtrer par période"
     aria-hidden="true">
    <p class="my-pv-period-panel__title">Période</p>
    <div class="my-pv-period-panel__list">
        <a href="{{ route('my-pv.index') }}"
           class="my-pv-period-option {{ empty($activePeriod) ? 'is-active' : '' }}">
            Toutes les périodes
        </a>
        @foreach($periods as $period)
            @php
                $periodLabel = $period;
                if (preg_match('/^(\d{4})-(\d{1,2})/', (string) $period, $periodParts)) {
                    $periodLabel = str_pad($periodParts[2], 2, '0', STR_PAD_LEFT) . '/' . $periodParts[1];
                }
            @endphp
            <a href="{{ route('my-pv.index', ['period' => $period]) }}"
               class="my-pv-period-option {{ ($activePeriod ?? null) === $period ? 'is-active' : '' }}">
                {{ $periodLabel }}
            </a>
        @endforeach
    </div>
</div>
