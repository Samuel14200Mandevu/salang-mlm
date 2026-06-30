@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🎉 Événements</h1>
        <p class="text-[var(--text-secondary)] mt-1">Découvrez les événements à venir</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Événement 1 -->
        <div class="card animate-fadeInUp delay-1 hover:shadow-hover transition">
            <div class="text-5xl mb-4 animate-float">🚀</div>
            <h3 class="text-lg font-bold text-[var(--text-primary)]">Lancement du nouveau package</h3>
            <p class="text-sm text-[var(--text-secondary)] mt-2">Découvrez notre nouveau package Emerald avec des avantages exclusifs.</p>
            <div class="mt-4 flex items-center gap-2 text-sm text-primary-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>15 Juillet 2026</span>
            </div>
            <span class="badge badge-success mt-3">À venir</span>
        </div>

        <!-- Événement 2 -->
        <div class="card animate-fadeInUp delay-2 hover:shadow-hover transition">
            <div class="text-5xl mb-4 animate-float">🏆</div>
            <h3 class="text-lg font-bold text-[var(--text-primary)]">Compétition de parrainage</h3>
            <p class="text-sm text-[var(--text-secondary)] mt-2">Gagnez des bonus exceptionnels en parrainant le plus de membres.</p>
            <div class="mt-4 flex items-center gap-2 text-sm text-primary-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>1-31 Août 2026</span>
            </div>
            <span class="badge badge-warning mt-3">En cours</span>
        </div>

        <!-- Événement 3 -->
        <div class="card animate-fadeInUp delay-3 hover:shadow-hover transition">
            <div class="text-5xl mb-4 animate-float">🎓</div>
            <h3 class="text-lg font-bold text-[var(--text-primary)]">Formation MLM en ligne</h3>
            <p class="text-sm text-[var(--text-secondary)] mt-2">Apprenez les meilleures stratégies pour développer votre réseau.</p>
            <div class="mt-4 flex items-center gap-2 text-sm text-primary-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>5 Septembre 2026</span>
            </div>
            <span class="badge badge-info mt-3">À venir</span>
        </div>
    </div>
</div>
@endsection