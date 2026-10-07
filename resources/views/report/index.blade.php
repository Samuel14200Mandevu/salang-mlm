@extends('layouts.app')

@section('title', 'Rapports')

@section('content')
<div class="member-reports member-reports--fintech">
    <div class="member-fintech-shell">
        @include('report.partials.fintech-hero')
        @include('report.partials.fintech-metrics')
        @include('report.partials.report-links')
    </div>

    <div class="member-reports-classic space-y-4 sm:space-y-6">
        <div class="member-page-intro animate-fadeInUp">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Rapports &amp; statistiques</h1>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Synthèse de votre activité et accès aux historiques</p>
                </div>
                <a href="{{ route('profile.help') }}" class="btn btn-outline text-sm">← Centre d'aide</a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
            <div class="card-stats border-l-4 border-primary-500">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Commissions versées</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">${{ number_format($totalEarnings ?? 0, 2) }}</p>
            </div>
            <div class="card-stats border-l-4 border-green-500">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total retiré</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($totalWithdrawn ?? 0, 2) }}</p>
            </div>
            <div class="card-stats border-l-4 border-blue-500">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Transactions</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ number_format($transactionsCount ?? 0) }}</p>
            </div>
            <div class="card-stats border-l-4 border-purple-500">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Commandes</p>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ number_format($ordersCount ?? 0) }}</p>
            </div>
        </div>

        <div class="report-grid grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <a href="{{ route('wallet.index') }}" class="report-card animate-fadeInUp delay-1">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="stat-icon stat-icon-primary flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Portefeuille</h3>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Solde disponible · ${{ number_format($balance ?? 0, 2) }}</p>
                        <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold">Voir →</span>
                    </div>
                </div>
            </a>

            <a href="{{ route('wallet.transactions') }}" class="report-card animate-fadeInUp delay-2">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="stat-icon stat-icon-info flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Transactions</h3>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Historique complet du portefeuille</p>
                        <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold">Voir →</span>
                    </div>
                </div>
            </a>

            <a href="{{ route('withdrawal.index') }}" class="report-card animate-fadeInUp delay-3">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="stat-icon stat-icon-warning flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Retraits</h3>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Demandes et historique de retrait</p>
                        <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold">Voir →</span>
                    </div>
                </div>
            </a>

            <a href="{{ route('commissions.index') }}" class="report-card animate-fadeInUp delay-4">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="stat-icon stat-icon-success flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commissions</h3>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Gains par type et période</p>
                        <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold">Voir →</span>
                    </div>
                </div>
            </a>

            <a href="{{ route('my-pv.index') }}" class="report-card animate-fadeInUp delay-5">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="stat-icon stat-icon-purple flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Mes PV</h3>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Volume personnel et mouvements</p>
                        <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold">Voir →</span>
                    </div>
                </div>
            </a>

            <a href="{{ route('subscriptions.index') }}" class="report-card animate-fadeInUp delay-6">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="stat-icon stat-icon-success flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Packages</h3>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Achats et abonnements</p>
                        <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold">Voir →</span>
                    </div>
                </div>
            </a>

            <a href="{{ route('report.network') }}" class="report-card sm:col-span-2 animate-fadeInUp delay-7">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="stat-icon stat-icon-primary flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Réseau &amp; ventes filleuls</h3>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Performance de votre équipe</p>
                        <span class="inline-block mt-1 sm:mt-2 text-xs sm:text-sm text-primary-500 font-semibold">Voir →</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
