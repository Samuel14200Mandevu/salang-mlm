@extends('admin.layouts.app')

@push('styles')
<style>
    .wallet-row {
        transition: all 0.2s ease;
    }
    .wallet-row:hover {
        background: var(--bg-hover);
    }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
        .card-stats {
            padding: 0.75rem;
        }
        .card-stats .text-2xl {
            font-size: 1.25rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
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
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Wallets</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Manage user wallets</p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Wallets</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $totalWallets ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Balance</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($totalBalance ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Pending Balance</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">${{ number_format($pendingBalance ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Active Wallets</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $activeWallets ?? 0 }}</p>
        </div>
    </div>

    <!-- Search -->
    <div class="relative animate-fadeInUp delay-5 max-w-xs sm:max-w-sm">
        <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </span>
        <input type="text" 
               id="searchInput"
               placeholder="Search by user..."
               class="input pl-7 sm:pl-9 text-sm sm:text-base">
    </div>

    <!-- Wallet List -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">User</th>
                        <th class="text-xs sm:text-sm">Balance</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Pending</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Withdrawn</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Deposited</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Currency</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="walletsTable">
                    @forelse($wallets as $wallet)
                        <tr class="wallet-row" data-user="{{ strtolower($wallet->user?->name ?? '') }}">
                            <td class="font-medium text-sm sm:text-base">
                                {{ $wallet->user?->name ?? 'N/A' }}
                            </td>
                            <td class="font-bold text-green-500 text-sm sm:text-base">
                                ${{ number_format($wallet->balance, 2) }}
                            </td>
                            <td class="hidden sm:table-cell text-yellow-500 text-sm sm:text-base">
                                ${{ number_format($wallet->pending_balance, 2) }}
                            </td>
                            <td class="hidden md:table-cell text-blue-500 text-sm sm:text-base">
                                ${{ number_format($wallet->total_withdrawn, 2) }}
                            </td>
                            <td class="hidden md:table-cell text-purple-500 text-sm sm:text-base">
                                ${{ number_format($wallet->total_deposited, 2) }}
                            </td>
                            <td class="hidden lg:table-cell">
                                <span class="badge badge-neutral text-[10px] sm:text-xs">
                                    {{ $wallet->currency ?? 'USD' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.wallets.show', $wallet->id) }}" 
                                   class="btn btn-outline btn-sm btn-icon" title="View">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">No wallets</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Wallets will appear when users register</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wallets->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $wallets->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('#walletsTable tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        rows.forEach(function(row) {
            const user = row.dataset.user || '';
            const match = user.includes(query);
            row.style.display = match ? '' : 'none';
        });
    });
});
</script>
@endpush
@endsection