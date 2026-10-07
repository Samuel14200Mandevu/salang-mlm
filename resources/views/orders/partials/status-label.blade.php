<span class="order-status order-status-{{ $status }}">
    @if($status === 'pending') En attente
    @elseif($status === 'processing') En traitement
    @elseif($status === 'completed') Terminée
    @elseif($status === 'cancelled') Annulée
    @else {{ ucfirst($status) }}
    @endif
</span>
