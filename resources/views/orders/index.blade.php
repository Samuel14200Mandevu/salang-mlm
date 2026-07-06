@extends('layouts.app')

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
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">My Orders</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Track all your orders</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Order
        </a>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Orders</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $orders->total() ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Pending</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Completed</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $completedCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Spent</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">${{ number_format($totalSpent ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-wrapper animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[120px] sm:min-w-[150px] max-w-xs sm:max-w-sm">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Search..." class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        <select id="statusFilter" class="input w-auto min-w-[100px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <input type="date" id="dateFrom" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base" placeholder="From">
        <input type="date" id="dateTo" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base" placeholder="To">
    </div>

    <!-- Orders List -->
    <div class="card animate-fadeInUp delay-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Order #</th>
                        <th class="text-xs sm:text-sm">Date</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Items</th>
                        <th class="text-xs sm:text-sm text-right">Total</th>
                        <th class="text-xs sm:text-sm">Status</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="ordersTable">
                    @forelse($orders ?? [] as $order)
                        <tr class="order-row" 
                            data-status="{{ $order->status }}"
                            data-date="{{ $order->created_at->format('Y-m-d') }}">
                            <td>
                                <span class="font-mono font-semibold text-[var(--text-primary)] text-xs sm:text-sm">
                                    #{{ $order->order_number }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $order->items->count() }} item(s)
                            </td>
                            <td class="text-right font-bold text-primary-500 text-sm sm:text-base">
                                ${{ number_format($order->total, 2) }}
                            </td>
                            <td>
                                <span class="order-status order-status-{{ $order->status }}">
                                    @if($order->status == 'pending') Pending
                                    @elseif($order->status == 'processing') Processing
                                    @elseif($order->status == 'completed') Completed
                                    @elseif($order->status == 'cancelled') Cancelled
                                    @else {{ ucfirst($order->status) }}
                                    @endif
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-primary btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">No orders yet</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Start shopping to place your first order</p>
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
    var dateFrom = document.getElementById('dateFrom');
    var dateTo = document.getElementById('dateTo');
    var rows = document.querySelectorAll('#ordersTable tr');

    function filterRows() {
        var search = searchInput.value.trim().toLowerCase();
        var status = statusFilter.value;
        var from = dateFrom.value;
        var to = dateTo.value;

        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            var rowStatus = row.dataset.status || '';
            var rowDate = row.dataset.date || '';

            var show = true;

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