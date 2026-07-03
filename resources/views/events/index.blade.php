@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 640px) {
        .card { padding: 0.75rem; }
        .text-5xl { font-size: 2.5rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Evenements</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Decouvrez les evenements a venir</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <!-- Evenement 1 -->
        <div class="card animate-fadeInUp delay-1 hover:shadow-hover transition p-3 sm:p-4">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 text-primary-500 mb-3 sm:mb-4 animate-float mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <h3 class="text-base sm:text-lg font-bold text-[var(--text-primary)] text-center">Lancement du nouveau package</h3>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-2 text-center">Decouvrez notre nouveau package Emerald avec des avantages exclusifs.</p>
            <div class="mt-3 sm:mt-4 flex items-center justify-center gap-2 text-xs sm:text-sm text-primary-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>15 Juillet 2026</span>
            </div>
            <div class="text-center mt-3">
                <span class="badge badge-success text-[10px] sm:text-xs">A venir</span>
            </div>
        </div>

        <!-- Evenement 2 -->
        <div class="card animate-fadeInUp delay-2 hover:shadow-hover transition p-3 sm:p-4">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 text-yellow-500 mb-3 sm:mb-4 animate-float mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <h3 class="text-base sm:text-lg font-bold text-[var(--text-primary)] text-center">Competition de parrainage</h3>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-2 text-center">Gagnez des bonus exceptionnels en parrainant le plus de membres.</p>
            <div class="mt-3 sm:mt-4 flex items-center justify-center gap-2 text-xs sm:text-sm text-primary-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>1-31 Aout 2026</span>
            </div>
            <div class="text-center mt-3">
                <span class="badge badge-warning text-[10px] sm:text-xs">En cours</span>
            </div>
        </div>

        <!-- Evenement 3 -->
        <div class="card animate-fadeInUp delay-3 hover:shadow-hover transition p-3 sm:p-4">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 text-blue-500 mb-3 sm:mb-4 animate-float mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <h3 class="text-base sm:text-lg font-bold text-[var(--text-primary)] text-center">Formation MLM en ligne</h3>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-2 text-center">Apprenez les meilleures strategies pour developper votre reseau.</p>
            <div class="mt-3 sm:mt-4 flex items-center justify-center gap-2 text-xs sm:text-sm text-primary-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>5 Septembre 2026</span>
            </div>
            <div class="text-center mt-3">
                <span class="badge badge-info text-[10px] sm:text-xs">A venir</span>
            </div>
        </div>
    </div>
</div>
@endsection