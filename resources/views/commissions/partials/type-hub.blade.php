@php
    $bonusByType = $bonusByType ?? [];
    $bonusMonth = $bonusMonth ?? '';
    $bonusMonthLabel = $bonusMonthLabel ?? '';
    $selectedType = $selectedType ?? '';
    $typeOrder = ['direct', 'indirect', 'leadership', 'cash_pos'];
    $typeMeta = [
        'direct' => ['tone' => 'direct', 'icon' => 'direct'],
        'indirect' => ['tone' => 'indirect', 'icon' => 'indirect'],
        'leadership' => ['tone' => 'leadership', 'icon' => 'leadership'],
        'cash_pos' => ['tone' => 'cash_pos', 'icon' => 'cash_pos'],
    ];
@endphp
<section class="commissions-type-hub" aria-labelledby="commissions-type-hub-title">
    <div class="commissions-type-hub__head">
        <div>
            <h2 id="commissions-type-hub-title" class="commissions-type-hub__title">Mes bonus</h2>
            @if($bonusMonthLabel)
                <p class="commissions-type-hub__month">{{ $bonusMonthLabel }}</p>
            @endif
        </div>
    </div>

    <div class="commissions-type-hub__grid commissions-type-hub__grid--4">
        @foreach($typeOrder as $key)
            @php
                $row = $bonusByType[$key] ?? [
                    'label' => $key === 'cash_pos' ? 'Profit POS' : ucfirst($key),
                    'total' => 0,
                    'count' => 0,
                ];
                $isActive = $selectedType === $key;
                $metaHint = $key === 'cash_pos'
                    ? ((int) ($row['count'] ?? 0)).' ligne(s) · cash inclus'
                    : ((int) ($row['count'] ?? 0)).' ligne(s)';
            @endphp
            <a
                href="{{ route('commissions.index', array_filter(['type' => $key, 'period' => $bonusMonth ?: null])) }}"
                class="commissions-type-card commissions-type-card--{{ $typeMeta[$key]['tone'] }}{{ $isActive ? ' is-active' : '' }}"
                @if($isActive) aria-current="page" @endif
            >
                <span class="commissions-type-card__icon" aria-hidden="true">
                    @include('wallet.partials.fintech-wallet-icon', ['name' => $typeMeta[$key]['icon']])
                </span>
                <span class="commissions-type-card__text">
                    <span class="commissions-type-card__label">{{ $row['label'] }}</span>
                    <span class="commissions-type-card__value">${{ number_format((float) ($row['total'] ?? 0), 2) }}</span>
                    <span class="commissions-type-card__meta">{{ $metaHint }}</span>
                </span>
                <span class="commissions-type-card__chevron" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>
        @endforeach
    </div>
</section>
