{{-- resources/views/admin/withdrawals/show.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-light);
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-row .label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
    }
    .detail-row .value {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
        text-align: right;
        word-break: break-word;
    }
    
    @media (max-width: 640px) {
        .card {
            padding: 0.75rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .btn {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        .detail-row {
            flex-direction: column;
            gap: 0.125rem;
            padding: 0.375rem 0;
        }
        .detail-row .value {
            text-align: left;
        }
        .action-buttons {
            flex-direction: column;
        }
        .action-buttons .btn {
            width: 100%;
        }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .detail-grid {
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
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Withdrawal #{{ $withdrawal->id }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Complete details of the withdrawal request
            </p>
        </div>
        <a href="{{ route('admin.withdrawals') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        
        <!-- General Information -->
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4 border-b border-[var(--border-color)] pb-2">
                General Information
            </h3>
            
            <div class="space-y-1">
                <div class="detail-row">
                    <span class="label">ID</span>
                    <span class="value font-mono">#{{ $withdrawal->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">User</span>
                    <span class="value font-medium">{{ $withdrawal->user?->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Email</span>
                    <span class="value text-sm">{{ $withdrawal->user?->email ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Amount</span>
                    <span class="value font-bold text-green-500">${{ number_format($withdrawal->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Fee (2.5%)</span>
                    <span class="value text-red-500">${{ number_format($withdrawal->fee, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Net Amount</span>
                    <span class="value font-bold text-primary-500">${{ number_format($withdrawal->net_amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Method</span>
                    <span class="value">
                        <span class="badge badge-info text-[10px] sm:text-xs">{{ ucfirst($withdrawal->method) }}</span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : ($withdrawal->status == 'processing' ? 'badge-info' : 'badge-danger')) }} text-[10px] sm:text-xs">
                            {{ ucfirst($withdrawal->status) }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">Created At</span>
                    <span class="value text-sm">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($withdrawal->completed_at)
                <div class="detail-row">
                    <span class="label">Completed At</span>
                    <span class="value text-sm">{{ $withdrawal->completed_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Payment Details -->
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4 border-b border-[var(--border-color)] pb-2">
                Payment Details
            </h3>
            
            <div class="space-y-1">
                @if($withdrawal->payment_address)
                <div class="detail-row">
                    <span class="label">Payment Address</span>
                    <span class="value font-mono text-xs break-all">{{ $withdrawal->payment_address }}</span>
                </div>
                @endif

                @if($withdrawal->phone_number)
                <div class="detail-row">
                    <span class="label">Phone Number</span>
                    <span class="value">{{ $withdrawal->phone_number }}</span>
                </div>
                @endif

                @if($withdrawal->bank_details)
                <div class="detail-row">
                    <span class="label">Bank Details</span>
                    <span class="value text-sm whitespace-pre-line">{{ $withdrawal->bank_details }}</span>
                </div>
                @endif

                @if($withdrawal->notes)
                <div class="detail-row">
                    <span class="label">Notes</span>
                    <span class="value text-sm">{{ $withdrawal->notes }}</span>
                </div>
                @endif

                @if($withdrawal->status == 'pending' || $withdrawal->status == 'processing')
                <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
                    <div class="action-buttons flex flex-col sm:flex-row gap-2 sm:gap-3">
                        @if($withdrawal->status == 'pending')
                        <form action="{{ route('admin.withdrawals.process', $withdrawal->id) }}" method="POST" class="w-full sm:flex-1">
                            @csrf
                            <button type="submit" class="btn btn-info w-full text-sm sm:text-base py-2 sm:py-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Mettre en traitement
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" class="w-full sm:flex-1">
                            @csrf
                            <button type="submit" class="btn btn-success w-full text-sm sm:text-base py-2 sm:py-2.5" 
                                    onclick="return confirm('Approve this withdrawal?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approve
                            </button>
                        </form>
                        <button onclick="showRejectModal('{{ $withdrawal->id }}')" 
                                class="btn btn-danger w-full sm:flex-1 text-sm sm:text-base py-2 sm:py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
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
</script>
@endpush
@endsection