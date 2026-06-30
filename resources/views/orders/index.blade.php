@extends('layouts.app')

@push('styles')
<style>
    .order-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary-500);
    }
    .order-status {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.7rem;
        font-weight: 600;
    }
    .order-status-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .order-status-processing { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .order-status-completed { background: rgba(34,197,94,0.15); color: #22c55e; }
    .order-status-cancelled { background: rgba(239,68,68,0.15); color: #ef4444; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📦 Mes Commandes</h1>
            <p class="text-[var(--text-secondary)] mt-1">Suivez toutes vos commandes</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle commande
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total commandes</p>
            <p class="text-2xl font-bold text-primary-500">{{ $orders->total() ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-2xl font-bold text-yellow-500">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Livrées</p>
            <p class="text-2xl font-bold text-green-500">{{ $completedCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Dépenses totales</p>
            <p class="text-2xl font-bold text-purple-500">${{ number_format($totalSpent ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-3 animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[150px] max-w-sm">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher une commande..." class="input pl-9">
        </div>
        <select id="statusFilter" class="input w-auto min-w-[140px]">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="processing">En traitement</option>
            <option value="completed">Livrée</option>
            <option value="cancelled">Annulée</option>
        </select>
        <input type="date" id="dateFrom" class="input w-auto min-w-[130px]">
        <input type="date" id="dateTo" class="input w-auto min-w-[130px]">
    </div>

    <!-- Liste des commandes -->
    <div class="card animate-fadeInUp delay-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th class="hidden sm:table-cell">Articles</th>
                        <th class="text-right">Total</th>
                        <th>Statut</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="ordersTable">
                    @forelse($orders ?? [] as $order)
                        <tr class="order-card" 
                            data-status="{{ $order->status }}"
                            data-date="{{ $order->created_at->format('Y-m-d') }}">
                            <td>
                                <span class="font-mono font-semibold text-[var(--text-primary)]">
                                    #{{ $order->order_number }}
                                </span>
                            </td>
                            <td class="text-sm text-[var(--text-secondary)]">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden sm:table-cell text-sm text-[var(--text-secondary)]">
                                {{ $order->items->count() }} article(s)
                            </td>
                            <td class="text-right font-bold text-primary-500">
                                ${{ number_format($order->total, 2) }}
                            </td>
                            <td>
                                <span class="order-status order-status-{{ $order->status }}">
                                    @if($order->status == 'pending') ⏳ En attente
                                    @elseif($order->status == 'processing') 🔄 En traitement
                                    @elseif($order->status == 'completed') ✅ Livrée
                                    @elseif($order->status == 'cancelled') ❌ Annulée
                                    @else 📦 {{ $order->status }}
                                    @endif
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-primary btn-sm">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                Aucune commande pour le moment.
                                <br>
                                <span class="text-xs">Commencez à acheter des produits dans notre boutique !</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const rows = document.querySelectorAll('#ordersTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;
        const from = dateFrom.value;
        const to = dateTo.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status || '';
            const rowDate = row.dataset.date || '';

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (from && rowDate < from) show = false;
            if (to && rowDate > to) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
    dateFrom.addEventListener('change', filterRows);
    dateTo.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection