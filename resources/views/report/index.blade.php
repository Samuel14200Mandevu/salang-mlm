@extends('layouts.app')

@section('title', 'Rapports')



@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Reports</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">View your statistics and performance</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Earnings</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">${{ number_format($totalEarnings ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Withdrawn</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($totalWithdrawn ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Transactions</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ number_format($transactionsCount ?? 0) }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Packages</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ number_format($packagesCount ?? 0) }}</p>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="report-grid grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        
        <!-- E-Wallet History -->
        <a href="{{ route('wallet.index') }}" class="report-card animate-fadeInUp delay-1">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="stat-icon stat-icon-primary flex-shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">E-Wallet History</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Your electronic wallet history</p>
                    <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold transition">
                        View details →
                    </span>
                </div>
            </div>
        </a>

        <!-- Cash Wallet History -->
        <a href="{{ route('wallet.index') }}" class="report-card animate-fadeInUp delay-2">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="stat-icon stat-icon-success flex-shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Cash Wallet History</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Your cash wallet history</p>
                    <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold transition">
                        View details →
                    </span>
                </div>
            </div>
        </a>

        <!-- Withdrawal History -->
        <a href="{{ route('withdrawal.index') }}" class="report-card animate-fadeInUp delay-3">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="stat-icon stat-icon-warning flex-shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Withdrawal History</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Your withdrawal history</p>
                    <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold transition">
                        View details →
                    </span>
                </div>
            </div>
        </a>

        <!-- My Transactions -->
        <a href="{{ route('wallet.transactions') }}" class="report-card animate-fadeInUp delay-4">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="stat-icon stat-icon-info flex-shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">My Transactions</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Complete transaction history</p>
                    <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold transition">
                        View details →
                    </span>
                </div>
            </div>
        </a>

        <!-- My PV Details -->
        <a href="{{ route('rank.index') }}" class="report-card animate-fadeInUp delay-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="stat-icon stat-icon-purple flex-shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">My PV Details</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Your Volume Points details</p>
                    <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold transition">
                        View details →
                    </span>
                </div>
            </div>
        </a>

        <!-- Package History -->
        <a href="{{ route('subscriptions.index') }}" class="report-card animate-fadeInUp delay-6">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="stat-icon stat-icon-success flex-shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Package History</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Your package purchase history</p>
                    <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold transition">
                        View details →
                    </span>
                </div>
            </div>
        </a>

        <!-- Downline Sales Report (full width) -->
        <a href="{{ route('report.earnings') }}" class="report-card md:col-span-2 animate-fadeInUp delay-7">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="stat-icon stat-icon-primary flex-shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Downline Sales Report</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Sales report from your network</p>
                    <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold transition">
                        View details →
                    </span>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection