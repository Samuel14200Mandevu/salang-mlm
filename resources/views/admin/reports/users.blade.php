@extends('admin.layouts.app')

@push('styles')
<style>
    .user-report-row:hover { background: var(--bg-hover); }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Users Report</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Detailed user analysis</p>
        </div>
        <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total Users</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ number_format($stats['total'] ?? 0) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Active</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ number_format($stats['active'] ?? 0) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-red-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Inactive</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">{{ number_format($stats['inactive'] ?? 0) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-blue-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Avg PV</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ number_format($stats['avg_pv'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Avg BV</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ number_format($stats['avg_bv'] ?? 0) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total Earnings</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">${{ number_format($stats['total_earnings'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">With Package</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ number_format($stats['with_package'] ?? 0) }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-red-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Without Package</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">{{ number_format($stats['without_package'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Name</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Email</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Rank</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Package</th>
                        <th class="text-xs sm:text-sm text-right">PV</th>
                        <th class="text-xs sm:text-sm">Status</th>
                        <th class="text-xs sm:text-sm text-right hidden xl:table-cell">Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                        <tr class="user-report-row">
                            <td class="font-medium text-sm sm:text-base">{{ $user->name }}</td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden sm:table-cell">{{ $user->email }}</td>
                            <td class="hidden md:table-cell text-sm sm:text-base">
                                {{ $user->rank?->name ?? 'Distributor' }}
                            </td>
                            <td class="hidden lg:table-cell text-sm sm:text-base">{{ $user->package?->name ?? 'None' }}</td>
                            <td class="text-right text-sm sm:text-base">{{ number_format($user->pv_balance ?? 0) }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-right text-[var(--text-secondary)] text-xs sm:text-sm hidden xl:table-cell">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">No users</p>
                                <p class="text-sm text-[var(--text-tertiary)]">No users registered yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($users) && $users->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection