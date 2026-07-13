{{-- resources/views/wallet/index.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .transaction-item:hover { transform: translateX(4px); }
    
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
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    
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
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
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
    
    @media (max-width: 640px) {
        .card-stats { padding: 0.75rem; }
        .card-stats .text-3xl { font-size: 1.5rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .card { padding: 0.875rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.875rem; }
        .stats-grid { grid-template-columns: 1fr !important; }
        .wallet-actions { flex-direction: column; }
        .wallet-actions .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">My Wallet</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Manage your funds and transactions</p>
    </div>

    <!-- Balance -->
    <div class="stats-grid grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Available Balance</p>
            <p class="text-2xl sm:text-3xl font-bold text-primary-500">${{ number_format(Auth::user()->wallet?->balance ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-yellow-500">
            <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Pending</p>
            <p class="text-2xl sm:text-3xl font-bold text-yellow-500">${{ number_format(Auth::user()->wallet?->pending_balance ?? 0, 2) }}</p>
        </div>
        <div class="card-stats animate-fadeInUp delay-3 border-l-4 border-blue-500">
            <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Total Withdrawn</p>
            <p class="text-2xl sm:text-3xl font-bold text-blue-500">${{ number_format(Auth::user()->wallet?->total_withdrawn ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Transactions -->
    <div class="card animate-fadeInUp delay-4">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Transaction History</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $transactions->count() ?? 0 }} transactions</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Date</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Description</th>
                        <th class="text-xs sm:text-sm text-right">Amount</th>
                        <th class="text-xs sm:text-sm">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr class="transition-colors">
                            <td class="text-[var(--text-secondary)] text-[10px] sm:text-sm">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="badge {{ $transaction->type == 'commission' ? 'badge-success' : ($transaction->type == 'deposit' ? 'badge-info' : 'badge-warning') }} text-[10px] sm:text-xs">
                                    {{ $transaction->type_label ?? $transaction->type }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell text-xs sm:text-sm">{{ $transaction->description ?? '-' }}</td>
                            <td class="text-right font-bold text-xs sm:text-sm {{ $transaction->amount > 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $transaction->status == 'completed' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">No transactions</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Your transactions will appear here</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($transactions) && $transactions->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="wallet-actions flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <a href="{{ route('wallet.deposit') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Deposit
        </a>
        <a href="{{ route('withdrawal.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Withdraw
        </a>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
            </svg>
            Buy Subscription
        </a>
    </div>
</div>
@endsection