@extends('admin.layouts.app')

@push('styles')
<style>
    .withdrawal-row {
        transition: all 0.2s ease;
    }
    .withdrawal-row:hover {
        background: var(--bg-hover);
    }
    
    .filters-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        .btn-sm svg {
            width: 0.875rem;
            height: 0.875rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .card-stats {
            padding: 0.75rem;
        }
        .card-stats .text-2xl {
            font-size: 1.25rem;
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
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Withdrawals</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Track and manage withdrawal requests</p>
        </div>
        <a href="{{ route('admin.withdrawals.export') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export
        </a>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Pending</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-blue-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Processing</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ $stats['processing'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Amount</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Fees</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">${{ number_format($stats['total_fees'] ?? 0, 2) }}</p>
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
            <option value="failed">Failed</option>
        </select>
        
        <select id="methodFilter" class="input w-auto min-w-[100px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">All Methods</option>
            @foreach($methods ?? [] as $method)
                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
            @endforeach
        </select>
        
        <input type="date" id="dateFrom" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base" placeholder="From">
        <input type="date" id="dateTo" class="input w-auto min-w-[100px] sm:min-w-[130px] text-sm sm:text-base" placeholder="To">
    </div>

    <!-- Withdrawals List -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">User</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Email</th>
                        <th class="text-xs sm:text-sm">Amount</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Method</th>
                        <th class="text-xs sm:text-sm">Status</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Date</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="withdrawalsTable">
                    @forelse($withdrawals ?? [] as $withdrawal)
                        <tr class="withdrawal-row" 
                            data-status="{{ $withdrawal->status }}"
                            data-method="{{ $withdrawal->method }}"
                            data-date="{{ $withdrawal->created_at->format('Y-m-d') }}">
                            <td class="font-mono text-xs sm:text-sm">#{{ $withdrawal->id }}</td>
                            <td class="font-medium text-sm sm:text-base">{{ $withdrawal->user?->name ?? 'N/A' }}</td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">{{ $withdrawal->user?->email ?? 'N/A' }}</td>
                            <td class="font-bold text-sm sm:text-base">${{ number_format($withdrawal->amount, 2) }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">{{ ucfirst($withdrawal->method) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : ($withdrawal->status == 'processing' ? 'badge-info' : 'badge-danger')) }} text-[10px] sm:text-xs">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $withdrawal->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.withdrawals.show', $withdrawal->id) }}" 
                                       class="btn btn-outline btn-sm btn-icon" title="View">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($withdrawal->status == 'pending' || $withdrawal->status == 'processing')
                                        <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" 
                                                    onclick="return confirm('Approve this withdrawal?')" title="Approve">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <button onclick="showRejectModal('{{ $withdrawal->id }}')" 
                                                class="btn btn-danger btn-sm btn-icon" title="Reject">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">No withdrawal requests</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Withdrawals will appear here when requested</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($withdrawals) && $withdrawals->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-[var(--bg-card)] rounded-xl shadow-2xl max-w-md w-full mx-3 sm:mx-4 p-4 sm:p-6 border border-[var(--border-color)]">
        <div class="text-center">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-red-500 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">Reject Withdrawal</h3>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-1 sm:mt-2">
                Please provide the reason for rejection.
            </p>
        </div>

        <form id="rejectForm" method="POST">
            @csrf
            <div class="mt-3 sm:mt-4">
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">
                    Rejection Reason *
                </label>
                <textarea name="reason" rows="3" class="input text-sm sm:text-base" placeholder="Reason for rejection..." required></textarea>
            </div>
            <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                <button type="submit" class="btn btn-danger w-full sm:flex-1 text-sm sm:text-base py-2 sm:py-2.5">
                    Reject
                </button>
                <button type="button" onclick="closeRejectModal()" class="btn btn-outline w-full sm:flex-1 text-sm sm:text-base py-2 sm:py-2.5">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal(withdrawalId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '{{ route("admin.withdrawals.reject", ["id" => ":id"]) }}'.replace(':id', withdrawalId);
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRejectModal();
});

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const methodFilter = document.getElementById('methodFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const rows = document.querySelectorAll('#withdrawalsTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;
        const method = methodFilter.value;
        const from = dateFrom.value;
        const to = dateTo.value;

        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status || '';
            const rowMethod = row.dataset.method || '';
            const rowDate = row.dataset.date || '';

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (method && rowMethod !== method) show = false;
            if (from && rowDate < from) show = false;
            if (to && rowDate > to) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
    methodFilter.addEventListener('change', filterRows);
    dateFrom.addEventListener('change', filterRows);
    dateTo.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection