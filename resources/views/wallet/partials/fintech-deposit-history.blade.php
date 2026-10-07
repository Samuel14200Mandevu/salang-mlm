@php
    $statusLabels = [
        'pending' => 'En attente',
        'completed' => 'Terminé',
        'failed' => 'Échec',
        'cancelled' => 'Annulé',
    ];
    $count = ($deposits ?? collect())->count();
@endphp
<section class="member-fintech-panel member-fintech-feed member-deposit-history member-fintech-animate member-fintech-animate--d4">
    <div class="member-fintech-panel__head">
        <div>
            <h2 class="member-fintech-panel__title">Historique dépôts</h2>
            <p class="member-fintech-panel__sub">Dernières opérations</p>
        </div>
        <span class="member-fintech-panel__badge">{{ $count }}</span>
    </div>

    @if($count > 0)
        <ul class="member-fintech-feed__list">
            @foreach($deposits as $deposit)
                <li class="member-fintech-tx">
                    <span class="member-fintech-tx__icon member-deposit-tx__icon">D</span>
                    <div class="member-fintech-tx__body">
                        <p class="member-fintech-tx__title">Dépôt</p>
                        <p class="member-fintech-tx__meta">
                            {{ $deposit->created_at->format('d/m/Y H:i') }}
                            · {{ $statusLabels[$deposit->status] ?? ucfirst($deposit->status) }}
                        </p>
                    </div>
                    <span class="member-fintech-tx__amount is-credit">
                        +${{ number_format($deposit->amount, 2) }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="member-fintech-feed__empty">Aucun dépôt effectué</p>
    @endif
</section>
