@php
    $series = $performanceChart ?? [];
    $hasActivity = collect($series)->contains(
        fn ($row) => ((float) ($row['pv'] ?? 0)) > 0 || ((float) ($row['commission'] ?? 0)) > 0
    );
    $periodPv = collect($series)->sum(fn ($row) => (float) ($row['pv'] ?? 0));
    $periodCommission = collect($series)->sum(fn ($row) => (float) ($row['commission'] ?? 0));
@endphp
<section class="member-fintech-panel member-fintech-performance member-fintech-animate member-fintech-animate--d5">
    <div class="member-fintech-panel__head">
        <div class="member-fintech-performance__titles">
            <h2 class="member-fintech-panel__title">Performance</h2>
            <p class="member-fintech-performance__sub">PV crédités et commissions payées</p>
        </div>
        <span class="member-fintech-panel__badge">6 mois</span>
    </div>

    <div class="member-fintech-performance__legend" aria-hidden="true">
        <span class="member-fintech-performance__key is-pv">PV crédités</span>
        <span class="member-fintech-performance__key is-comm">Commissions</span>
    </div>

    @if($hasActivity)
        @include('dashboard.partials.fintech-performance-chart', ['series' => $series])
        <div class="member-fintech-performance__totals">
            <div>
                <span class="member-fintech-performance__totals-label">Total PV</span>
                <span class="member-fintech-performance__totals-value">{{ number_format($periodPv, 0) }} PV</span>
            </div>
            <div>
                <span class="member-fintech-performance__totals-label">Total gains</span>
                <span class="member-fintech-performance__totals-value is-comm">${{ number_format($periodCommission, 2) }}</span>
            </div>
        </div>
    @else
        <p class="member-fintech-performance__empty">
            Vos PV et commissions apparaîtront ici après vos premières ventes ou bonus réseau.
        </p>
        <div class="member-fintech-performance__empty-actions">
            <a href="{{ route('products.index') }}" class="member-fintech-performance__empty-link">Boutique</a>
            <a href="{{ route('commissions.index') }}" class="member-fintech-performance__empty-link">Commissions</a>
        </div>
    @endif
</section>

<section class="member-fintech-panel member-fintech-feed member-fintech-animate member-fintech-animate--d6">
    <div class="member-fintech-panel__head">
        <h2 class="member-fintech-panel__title">Mouvements PV</h2>
        <a href="{{ route('my-pv.index') }}" class="member-fintech-panel__link">Tout voir</a>
    </div>
    <ul class="member-fintech-feed__list">
        @forelse(($recentPvActivities ?? collect())->take(6) as $entry)
            <li class="member-fintech-tx">
                <span class="member-fintech-tx__icon">{{ strtoupper(substr($entry->type_label ?? 'P', 0, 1)) }}</span>
                <div class="member-fintech-tx__body">
                    <p class="member-fintech-tx__title">{{ $entry->type_label }}</p>
                    <p class="member-fintech-tx__meta">
                        {{ $entry->date?->format('d/m/Y') ?? '—' }}
                        @if($entry->period)
                            · {{ $entry->period }}
                        @endif
                    </p>
                </div>
                @if((float) $entry->amount != 0)
                    @php $isCredit = (float) $entry->amount > 0; @endphp
                    <span class="member-fintech-tx__amount {{ $isCredit ? 'is-credit' : 'is-debit' }}">
                        {{ $isCredit ? '+' : '' }}{{ number_format($entry->amount, 0) }} PV
                    </span>
                @else
                    <span class="member-fintech-tx__amount is-muted">—</span>
                @endif
            </li>
        @empty
            <li class="member-fintech-feed__empty">Aucun mouvement PV récent</li>
        @endforelse
    </ul>
</section>
