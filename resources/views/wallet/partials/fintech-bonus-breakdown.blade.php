@php
    $bonusByType = $bonusByType ?? [];
    $bonusMonths = $bonusMonths ?? [];
    $bonusMonth = $bonusMonth ?? '';
    $bonusMonthLabel = $bonusMonthLabel ?? '';
    $activityPeriod = $activityPeriod ?? 'week';
    $bonusIcons = [
        'direct' => 'direct',
        'indirect' => 'indirect',
        'leadership' => 'leadership',
        'cash_pos' => 'cash_pos',
    ];
@endphp
<section class="member-wallet-salang-section member-fintech-animate member-fintech-animate--d4" aria-labelledby="wallet-bonus-heading">
    <div class="member-wallet-salang-section__head">
        <h2 id="wallet-bonus-heading" class="member-wallet-salang-section__title">Détail des bonus</h2>
        <div class="member-wallet-salang-section__tools">
            @if(!empty($bonusMonths))
                <form method="get" action="{{ route('wallet.index') }}" class="member-wallet-salang-period">
                    <input type="hidden" name="period" value="{{ $activityPeriod }}">
                    <label class="sr-only" for="wallet-bonus-month">Mois des bonus</label>
                    <span class="member-wallet-salang-period__icon" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <select id="wallet-bonus-month" name="bonus_month" class="member-wallet-salang-period__select" onchange="this.form.submit()">
                        @foreach($bonusMonths as $value => $label)
                            <option value="{{ $value }}" @selected($bonusMonth === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="member-wallet-salang-period__label">{{ $bonusMonthLabel }}</span>
                </form>
            @endif
            <a href="{{ route('commissions.index') }}" class="member-wallet-salang-section__link">
                Voir tout
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="member-wallet-salang-grid member-wallet-salang-grid--bonus">
        @foreach($bonusByType as $key => $row)
            <a href="{{ route('commissions.index', array_filter(['type' => $key, 'period' => $bonusMonth ?: null])) }}" class="member-wallet-bonus-card">
                @include('wallet.partials.fintech-wallet-icon', ['name' => $bonusIcons[$key] ?? 'document'])
                <div class="member-wallet-bonus-card__text">
                    <span class="member-wallet-bonus-card__label">{{ $row['label'] }}</span>
                    <span class="member-wallet-bonus-card__value">${{ number_format($row['total'], 2) }}</span>
                </div>
                <span class="member-wallet-bonus-card__chevron" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>
        @endforeach
    </div>
</section>
