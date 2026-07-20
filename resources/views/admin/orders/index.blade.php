{{-- resources/views/admin/orders/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .order-row {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .order-row:hover {
        background: var(--bg-hover);
        transform: translateX(4px);
    }
    
    .order-status {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.65rem;
        font-weight: 600;
    }
    .order-status-pending {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    .order-status-processing {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    .order-status-completed {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }
    .order-status-cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    
    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    .btn-md {
        padding: 0.625rem 1.5rem;
        font-size: 0.875rem;
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
    }
    .btn-success {
        background: #22c55e;
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.4);
    }
    
    .input {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .badge-neutral {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }
    
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }
    .table thead th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }
    
    .filters-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.65rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        .card-stats {
            padding: 0.75rem;
        }
        .card-stats .text-2xl {
            font-size: 1.25rem;
        }
        .order-status {
            font-size: 0.55rem;
            padding: 0.1rem 0.4rem;
        }
        .card {
            padding: 0.875rem;
        }
        .filters-wrapper {
            flex-direction: column;
            align-items: stretch;
        }
        .filters-wrapper .input {
            width: 100% !important;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        .table thead th, .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.6rem;
        }
        .btn-sm {
            padding: 0.125rem 0.375rem;
            font-size: 0.6rem;
        }
        .btn-sm svg {
            width: 0.75rem;
            height: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Gestion des commandes</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gérez toutes les commandes de la plateforme</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('admin.orders.export') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-5 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $totalOrders ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ $processingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $completedCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-red-500 animate-fadeInUp delay-5">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Annulées</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">{{ $cancelledCount ?? 0 }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-wrapper animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[120px] sm:min-w-[150px] max-w-xs sm:max-w-sm">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher..." class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        <select id="statusFilter" class="input w-auto min-w-[100px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="processing">En traitement</option>
            <option value="completed">Terminée</option>
            <option value="cancelled">Annulée</option>
        </select>
        <select id="paymentFilter" class="input w-auto min-w-[100px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les paiements</option>
            <option value="pending">En attente</option>
            <option value="completed">Payé</option>
            <option value="failed">Échoué</option>
        </select>
        <input type="date" id="dateFrom" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base" placeholder="Du">
        <input type="date" id="dateTo" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base" placeholder="Au">
    </div>

    <!-- Liste des commandes -->
    <div class="card animate-fadeInUp delay-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commandes</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $orders->total() ?? 0 }} commandes</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">N° commande</th>
                        <th class="text-xs sm:text-sm">Client</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Email</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Articles</th>
                        <th class="text-xs sm:text-sm text-right">Total</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm">Paiement</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTable">
                    @forelse($orders ?? [] as $order)
                        <tr class="order-row" 
                            data-status="{{ $order->status }}"
                            data-payment="{{ $order->payment_status }}"
                            data-date="{{ $order->created_at->format('Y-m-d') }}">
                            <td>
                                <span class="font-mono font-semibold text-[var(--text-primary)] text-xs sm:text-sm">
                                    #{{ $order->order_number }}
                                </span>
                            </td>
                            <td class="font-medium text-sm sm:text-base">
                                {{ $order->user?->name ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->user?->email ?? 'N/A' }}
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->items->count() }} article(s)
                            </td>
                            <td class="text-right font-bold text-primary-500 text-sm sm:text-base">
                                ${{ number_format($order->total, 2) }}
                            </td>
                            <td>
                                <span class="order-status order-status-{{ $order->status }}">
                                    @if($order->status == 'pending') En attente
                                    @elseif($order->status == 'processing') En traitement
                                    @elseif($order->status == 'completed') Terminée
                                    @elseif($order->status == 'cancelled') Annulée
                                    @else {{ ucfirst($order->status) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : ($order->payment_status == 'pending' ? 'badge-warning' : 'badge-danger') }} text-[10px] sm:text-xs">
                                    {{ $order->payment_status == 'completed' ? 'Payé' : ($order->payment_status == 'pending' ? 'En attente' : 'Échoué') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-primary btn-sm" title="Voir">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($order->status == 'pending' || $order->status == 'processing')
                                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-success btn-sm" title="Marquer comme terminée">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($order->status == 'pending')
                                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Annuler" onclick="return confirm('Annuler cette commande ?')">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commande</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les commandes apparaîtront ici</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var statusFilter = document.getElementById('statusFilter');
    var paymentFilter = document.getElementById('paymentFilter');
    var dateFrom = document.getElementById('dateFrom');
    var dateTo = document.getElementById('dateTo');
    var rows = document.querySelectorAll('#ordersTable tr');

    function filterRows() {
        var search = searchInput.value.trim().toLowerCase();
        var status = statusFilter.value;
        var payment = paymentFilter.value;
        var from = dateFrom.value;
        var to = dateTo.value;

        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            var rowStatus = row.dataset.status || '';
            var rowPayment = row.dataset.payment || '';
            var rowDate = row.dataset.date || '';

            var show = true;

            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (payment && rowPayment !== payment) show = false;
            if (from && rowDate < from) show = false;
            if (to && rowDate > to) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
    paymentFilter.addEventListener('change', filterRows);
    dateFrom.addEventListener('change', filterRows);
    dateTo.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection