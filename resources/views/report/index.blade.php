@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]"> Rapports</h1>
        <p class="text-[var(--text-secondary)] mt-1">Consultez vos statistiques et performances</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- E-Wallet -->
        <div class="card animate-fadeInUp delay-1 hover:border-primary-500/50 transition">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-[var(--text-primary)]"> E-Wallet History</h3>
                    <p class="text-sm text-[var(--text-secondary)]">Historique de votre portefeuille électronique</p>
                    <a href="#" class="inline-block mt-2 text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>

        <!-- Cash Wallet -->
        <div class="card animate-fadeInUp delay-2 hover:border-primary-500/50 transition">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-success">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-[var(--text-primary)]"> Cash Wallet History</h3>
                    <p class="text-sm text-[var(--text-secondary)]">Historique de votre portefeuille cash</p>
                    <a href="#" class="inline-block mt-2 text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>

        <!-- Withdrawals -->
        <div class="card animate-fadeInUp delay-3 hover:border-primary-500/50 transition">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-warning">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-[var(--text-primary)]"> Withdrawal History</h3>
                    <p class="text-sm text-[var(--text-secondary)]">Historique de vos retraits</p>
                    <a href="#" class="inline-block mt-2 text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <div class="card animate-fadeInUp delay-4 hover:border-primary-500/50 transition">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-info">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-[var(--text-primary)]"> My Transactions</h3>
                    <p class="text-sm text-[var(--text-secondary)]">Historique complet de vos transactions</p>
                    <a href="#" class="inline-block mt-2 text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>

        <!-- PV Details -->
        <div class="card animate-fadeInUp delay-5 hover:border-primary-500/50 transition">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-purple">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-[var(--text-primary)]"> My PV Details</h3>
                    <p class="text-sm text-[var(--text-secondary)]">Détails de vos Points de Volume</p>
                    <a href="#" class="inline-block mt-2 text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>

        <!-- Package History -->
        <div class="card animate-fadeInUp delay-6 hover:border-primary-500/50 transition">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-success">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-[var(--text-primary)]"> Package History</h3>
                    <p class="text-sm text-[var(--text-secondary)]">Historique de vos packages</p>
                    <a href="#" class="inline-block mt-2 text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>

        <!-- Downline Sales -->
        <div class="card md:col-span-2 animate-fadeInUp delay-7 hover:border-primary-500/50 transition">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-[var(--text-primary)]"> Downline Sales Report</h3>
                    <p class="text-sm text-[var(--text-secondary)]">Rapport des ventes de votre réseau</p>
                    <a href="#" class="inline-block mt-2 text-sm text-primary-500 hover:text-primary-600 font-semibold transition">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection