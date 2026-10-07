@php
    $methodLabels = [
        'crypto' => 'Crypto',
        'mobile_money' => 'Mobile Money',
        'bank' => 'Banque',
    ];
    $statusLabels = [
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'completed' => 'Terminé',
        'rejected' => 'Refusé',
        'cancelled' => 'Annulé',
        'failed' => 'Échec',
    ];
    $total = $withdrawals instanceof \Illuminate\Pagination\AbstractPaginator
        ? $withdrawals->total()
        : ($withdrawals->count() ?? 0);
@endphp
<section class="member-fintech-panel member-fintech-feed member-withdraw-history member-fintech-animate member-fintech-animate--d4">
    <div class="member-fintech-panel__head">
        <div>
            <h2 class="member-fintech-panel__title">Historique retraits</h2>
            <p class="member-fintech-panel__sub">Dernières demandes</p>
        </div>
        <span class="member-fintech-panel__badge">{{ $total }}</span>
    </div>

    @if(($withdrawals ?? collect())->count() > 0)
        <ul class="member-fintech-feed__list">
            @foreach($withdrawals as $withdrawal)
                @php
                    $status = $statusLabels[$withdrawal->status] ?? ucfirst($withdrawal->status);
                    $method = $methodLabels[$withdrawal->method] ?? ucfirst($withdrawal->method);
                    $statusClass = match ($withdrawal->status) {
                        'completed' => 'is-credit',
                        'pending', 'approved' => 'is-muted',
                        default => 'is-debit',
                    };
                @endphp
                <li class="member-fintech-tx">
                    <span class="member-fintech-tx__icon member-withdraw-tx__icon">R</span>
                    <div class="member-fintech-tx__body">
                        <p class="member-fintech-tx__title">{{ $method }}</p>
                        <p class="member-fintech-tx__meta">
                            {{ $withdrawal->created_at->format('d/m/Y H:i') }} · {{ $status }}
                            @if($withdrawal->net_amount)
                                · Net {{ number_format($withdrawal->net_amount, 2) }} $
                            @endif
                        </p>
                    </div>
                    <span class="member-fintech-tx__amount is-debit">
                        −${{ number_format($withdrawal->amount, 2) }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="member-fintech-feed__empty">Aucune demande de retrait</p>
    @endif

    @if($withdrawals instanceof \Illuminate\Pagination\AbstractPaginator && $withdrawals->hasPages())
        <div class="member-wallet-feed-pagination">
            <x-salang-pagination :paginator="$withdrawals" />
        </div>
    @endif
</section>
