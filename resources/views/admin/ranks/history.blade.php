@extends('admin.layouts.app')

@push('styles')
<style>
    .history-row {
        transition: all 0.2s ease;
    }
    .history-row:hover {
        background: var(--bg-hover);
    }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .filters-wrapper {
            flex-direction: column;
            align-items: stretch;
        }
        .filters-wrapper .input {
            width: 100% !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Promotion History</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Track all rank promotions</p>
        </div>
        <a href="{{ route('admin.ranks') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Ranks
        </a>
    </div>

    <!-- Filters -->
    <div class="filters-wrapper flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="relative flex-1 min-w-[140px] sm:min-w-[180px] max-w-xs sm:max-w-sm">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Search..." class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        <select id="userFilter" class="input w-auto min-w-[160px] sm:min-w-[200px] text-sm sm:text-base">
            <option value="">All Users</option>
            @foreach($users ?? [] as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- History List -->
    <div class="card animate-fadeInUp delay-2 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Date</th>
                        <th class="text-xs sm:text-sm">User</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Old Rank</th>
                        <th class="text-xs sm:text-sm">New Rank</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">PV</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Notes</th>
                    </tr>
                </thead>
                <tbody id="historyTable">
                    @forelse($history ?? [] as $item)
                        <tr class="history-row" data-user="{{ $item->user_id }}">
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $item->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="font-medium text-sm sm:text-base">{{ $item->user?->name ?? 'N/A' }}</td>
                            <td class="hidden sm:table-cell">
                                <span class="badge badge-neutral text-[10px] sm:text-xs">
                                    {{ $item->old_rank_name ?? 'Start' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success text-[10px] sm:text-xs">
                                    {{ $item->new_rank_name }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell text-sm sm:text-base">
                                {{ number_format($item->pv_at_time) }}
                            </td>
                            <td class="hidden lg:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $item->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">No promotion history</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Promotions will appear here when ranks are achieved</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($history) && $history->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $history->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const userFilter = document.getElementById('userFilter');
    const rows = document.querySelectorAll('#historyTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const userId = userFilter.value;

        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const rowUser = row.dataset.user || '';

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (userId && rowUser !== userId) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    userFilter.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection