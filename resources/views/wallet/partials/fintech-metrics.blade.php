@php
    $cards = $activityOverview ?? [];
    $period = $activityPeriod ?? 'week';
    $periodLabel = $activityPeriodLabel ?? 'Cette semaine';
@endphp
<section class="member-wallet-salang-section member-fintech-animate member-fintech-animate--d3" aria-labelledby="wallet-activity-heading">
    <div class="member-wallet-salang-section__head">
        <h2 id="wallet-activity-heading" class="member-wallet-salang-section__title">Aperçu de votre activité</h2>
        <form method="get" action="{{ route('wallet.index') }}" class="member-wallet-salang-period">
            <label class="sr-only" for="wallet-activity-period">Période</label>
            <span class="member-wallet-salang-period__icon" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
            <select id="wallet-activity-period" name="period" class="member-wallet-salang-period__select" onchange="this.form.submit()">
                <option value="week" @selected($period === 'week')>Cette semaine</option>
                <option value="month" @selected($period === 'month')>Ce mois</option>
                <option value="all" @selected($period === 'all')>Tout</option>
            </select>
            <span class="member-wallet-salang-period__label">{{ $periodLabel }}</span>
        </form>
    </div>

    <div class="member-wallet-salang-grid member-wallet-salang-grid--activity">
        @foreach($cards as $card)
            <article class="member-wallet-activity-card">
                <div class="member-wallet-activity-card__main">
                    @include('wallet.partials.fintech-wallet-icon', ['name' => $card['icon']])
                    <div class="member-wallet-activity-card__body">
                        <span class="member-wallet-activity-card__label">{{ $card['label'] }}</span>
                        <span class="member-wallet-activity-card__value">{{ $card['value'] }}</span>
                        <div class="member-wallet-activity-card__progress-wrap">
                            <div class="member-wallet-activity-card__progress" role="presentation">
                                <span class="member-wallet-activity-card__progress-bar" style="width: {{ $card['percent'] }}%"></span>
                            </div>
                            <span class="member-wallet-activity-card__percent">{{ $card['percent'] }}%</span>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>
