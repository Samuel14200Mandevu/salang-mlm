@extends('admin.layouts.app')

@push('styles')
<style>
    .report-stat:hover { transform: translateY(-4px); transition: all 0.3s ease; }
    
    @media (max-width: 640px) {
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.7rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Reports & Statistics</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Complete platform analysis</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'users']) }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
            </a>
        </div>
    </div>

    <!-- Global Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats report-stat p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Users</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ number_format($stats['total_users'] ?? 0) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ number_format($stats['active_users'] ?? 0) }} active</p>
        </div>
        <div class="card-stats report-stat p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Commissions</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Pending: ${{ number_format($stats['pending_commissions'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats report-stat p-3 sm:p-4 border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Sales</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">${{ number_format($stats['total_sales'] ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ number_format($stats['total_packages_sold'] ?? 0) }} packages sold</p>
        </div>
        <div class="card-stats report-stat p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Withdrawals</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">${{ number_format($stats['total_withdrawn'] ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total withdrawn by members</p>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="card animate-fadeInUp delay-5 p-3 sm:p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Monthly Evolution</h3>
            <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Last 12 months</span>
        </div>
        <div class="h-40 sm:h-48 md:h-56 flex items-end gap-1 sm:gap-2">
            @php 
                $maxSales = max(array_column($monthlySales ?? [], 'sales') ?: [1]);
                $maxCommissions = max(array_column($monthlySales ?? [], 'commissions') ?: [1]);
                $max = max($maxSales, $maxCommissions, 1);
            @endphp
            @foreach($monthlySales ?? [] as $data)
                @php 
                    $salesHeight = ($data['sales'] / $max) * 100;
                    $commissionsHeight = ($data['commissions'] / $max) * 100;
                @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="flex items-end gap-0.5 sm:gap-1 w-full" style="height: {{ max(8, $salesHeight) }}%">
                        <div class="w-1/2 bg-primary-500/70 hover:bg-primary-500 transition rounded-t-sm"
                             style="height: 100%">
                            <span class="tooltip">Sales: ${{ number_format($data['sales'], 2) }}</span>
                        </div>
                        <div class="w-1/2 bg-green-500/70 hover:bg-green-500 transition rounded-t-sm"
                             style="height: {{ max(8, $commissionsHeight) }}%">
                            <span class="tooltip">Commissions: ${{ number_format($data['commissions'], 2) }}</span>
                        </div>
                    </div>
                    <span class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] mt-1">{{ substr($data['month'], 0, 3) }}</span>
                </div>
            @endforeach
        </div>
        <div class="flex justify-center gap-3 sm:gap-4 mt-3 sm:mt-4 text-[10px] sm:text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-primary-500 rounded"></span> Sales</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-500 rounded"></span> Commissions</span>
        </div>
    </div>

    <!-- Commissions by Type & Users by Rank -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-6">
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Commissions by Type</h3>
            @php 
                $totalCommissionType = $commissionByType->sum('total') ?? 1;
            @endphp
            @forelse($commissionByType ?? [] as $item)
                @php 
                    $percent = ($item->total / max($totalCommissionType, 1)) * 100;
                @endphp
                <div class="mb-2 sm:mb-3">
                    <div class="flex justify-between text-xs sm:text-sm">
                        <span class="text-[var(--text-secondary)]">{{ ucfirst($item->type) }}</span>
                        <span class="font-semibold text-primary-500">${{ number_format($item->total, 2) }}</span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-fill" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] text-sm py-4">No commission data</p>
            @endforelse
        </div>

        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Users by Rank</h3>
            @php 
                $totalUsersByRank = $usersByRank->sum('count') ?? 1;
            @endphp
            @forelse($usersByRank ?? [] as $item)
                @php 
                    $percent = ($item->count / max($totalUsersByRank, 1)) * 100;
                @endphp
                <div class="mb-2 sm:mb-3">
                    <div class="flex justify-between text-xs sm:text-sm">
                        <span class="text-[var(--text-secondary)]">{{ $item->rank ?? 'Not defined' }}</span>
                        <span class="font-semibold text-primary-500">{{ $item->count }}</span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-fill bg-purple-500" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] text-sm py-4">No rank data</p>
            @endforelse
        </div>
    </div>

    <!-- Top Sponsors & Package Revenue -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-7">
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Top 10 Sponsors</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-xs sm:text-sm">#</th>
                            <th class="text-xs sm:text-sm">Name</th>
                            <th class="text-xs sm:text-sm hidden sm:table-cell">Email</th>
                            <th class="text-xs sm:text-sm text-right">Sponsors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSponsors ?? [] as $index => $sponsor)
                            <tr>
                                <td class="text-xs sm:text-sm">{{ $index + 1 }}</td>
                                <td class="font-medium text-sm sm:text-base">{{ $sponsor->name }}</td>
                                <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden sm:table-cell">{{ $sponsor->email }}</td>
                                <td class="text-right font-bold text-primary-500 text-sm sm:text-base">{{ $sponsor->total_sponsors }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-[var(--text-secondary)] text-sm">No sponsors data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Revenue by Package</h3>
            <div class="space-y-2 sm:space-y-3">
                @forelse($packageRevenue ?? [] as $package)
                    <div>
                        <div class="flex justify-between text-xs sm:text-sm">
                            <span class="text-[var(--text-secondary)]">{{ $package->name }}</span>
                            <span class="font-semibold text-primary-500">${{ number_format($package->total_revenue ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] sm:text-xs text-[var(--text-secondary)]">
                            <span>{{ $package->users_count ?? 0 }} users</span>
                            <span>${{ number_format($package->price ?? 0, 2) }} / package</span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] text-sm py-4">No package data</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-8">
        <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Sales Report
        </a>
        <a href="{{ route('admin.reports.commissions') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Commissions Report
        </a>
        <a href="{{ route('admin.reports.users') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Users Report
        </a>
    </div>
</div>
@endsection