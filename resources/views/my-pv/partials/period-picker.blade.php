@php
    $pickerVariant = $pickerVariant ?? 'card';
    $btnClass = $pickerVariant === 'banner'
        ? 'shop-banner-icon-btn my-pv-period-picker__btn--banner'
        : 'my-pv-period-picker__btn';
    $filterClass = !empty($activePeriod) ? 'has-filter' : '';
    if ($pickerVariant === 'banner' && !empty($activePeriod)) {
        $filterClass .= ' is-active';
    }
@endphp
@if($pickerVariant === 'banner')
    <button type="button"
            class="{{ $btnClass }} {{ $filterClass }}"
            data-my-pv-period-toggle
            aria-expanded="false"
            aria-label="Choisir une période">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </button>
@else
    <div class="my-pv-period-picker" data-my-pv-picker>
        <button type="button"
                class="{{ $btnClass }} {{ $filterClass }}"
                data-my-pv-period-toggle
                aria-expanded="false"
                aria-label="Choisir une période">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </button>
        @include('my-pv.partials.period-panel', ['activePeriod' => $activePeriod ?? null])
    </div>
@endif
