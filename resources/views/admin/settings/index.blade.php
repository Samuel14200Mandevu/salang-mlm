@extends('admin.layouts.app')

@push('styles')
<style>
    .setting-card:hover { transform: translateY(-4px); transition: all 0.3s ease; }
    
    @media (max-width: 640px) {
        .card { padding: 0.75rem; }
        .stat-icon { width: 2rem; height: 2rem; }
        .stat-icon svg { width: 1rem; height: 1rem; }
        .setting-card h3 { font-size: 0.875rem; }
        .setting-card p { font-size: 0.7rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Parametres</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Configuration de la plateforme</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <!-- Commissions -->
        <a href="{{ route('admin.settings.commission') }}" class="card setting-card hover:border-primary-500 transition p-3 sm:p-4">
            <div class="flex items-center gap-3">
                <div class="stat-icon stat-icon-primary flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commissions</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Taux et seuils</p>
                </div>
            </div>
        </a>

        <!-- Paiements -->
        <a href="{{ route('admin.settings.payment') }}" class="card setting-card hover:border-primary-500 transition p-3 sm:p-4 animate-fadeInUp delay-2">
            <div class="flex items-center gap-3">
                <div class="stat-icon stat-icon-success flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Paiements</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Methodes et frais</p>
                </div>
            </div>
        </a>

        <!-- General -->
        <a href="{{ route('admin.settings') }}" class="card setting-card hover:border-primary-500 transition p-3 sm:p-4 animate-fadeInUp delay-3">
            <div class="flex items-center gap-3">
                <div class="stat-icon stat-icon-info flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">General</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Configuration du site</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Maintenance -->
    <div class="card animate-fadeInUp delay-4 p-3 sm:p-4 md:p-6">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Maintenance</h3>
        <div class="flex flex-wrap gap-2 sm:gap-3">
            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Vider le cache
                </button>
            </form>
        </div>
    </div>
</div>
@endsection