@php
    $bonusByType = $bonusByType ?? [];
    $bonusIcons = [
        'direct' => 'direct',
        'indirect' => 'indirect',
        'sponsor' => 'sponsor',
        'cash_pos' => 'cash_pos',
    ];
@endphp
<section class="member-wallet-salang-section member-fintech-animate member-fintech-animate--d4" aria-labelledby="wallet-bonus-heading">
    <div class="member-wallet-salang-section__head">
        <h2 id="wallet-bonus-heading" class="member-wallet-salang-section__title">Détail des bonus</h2>
        <a href="{{ route('commissions.index') }}" class="member-wallet-salang-section__link">
            Voir tout
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="member-wallet-salang-grid member-wallet-salang-grid--bonus">
        @foreach($bonusByType as $key => $row)
            <a href="{{ route('commissions.index', ['type' => $key]) }}" class="member-wallet-bonus-card">
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
