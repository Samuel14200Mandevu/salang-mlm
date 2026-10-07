<div class="admin-user-list md:hidden" id="adminOrdersMobileList">
@forelse($orders ?? [] as $order)
    <a href="{{ route('admin.orders.show', $order) }}" class="admin-entity-card">
        <div class="admin-entity-card__top">
            <div class="min-w-0">
                <div class="admin-entity-card__name">#{{ $order->order_number }}</div>
                <div class="admin-entity-card__sub">{{ $order->user?->name ?? 'N/A' }}</div>
            </div>
            <span class="font-bold text-[var(--primary-navy)] text-sm shrink-0">{{ number_format($order->total, 2) }} €</span>
        </div>
        <div class="admin-entity-card__meta">
            <span class="order-status order-status-{{ $order->status }}">
                @if($order->status == 'pending') En attente
                @elseif($order->status == 'processing') En traitement
                @elseif($order->status == 'completed') Terminée
                @elseif($order->status == 'cancelled') Annulée
                @else {{ ucfirst($order->status) }}
                @endif
            </span>
            <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : ($order->payment_status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                {{ $order->payment_status == 'completed' ? 'Payé' : ($order->payment_status == 'pending' ? 'Paiement att.' : 'Paiement échoué') }}
            </span>
            <span class="text-xs text-[var(--text-secondary)]">{{ $order->items->count() }} art.</span>
        </div>
    </a>
@empty
    <div class="admin-empty-state">
        <p>Aucune commande</p>
    </div>
@endforelse
</div>
