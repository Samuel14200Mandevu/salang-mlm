@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 640px) {
        .card { padding: 0.75rem; }
        .avatar-md { width: 2rem; height: 2rem; font-size: 0.7rem; }
        .text-2xl { font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Centre de messages</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Vos messages et notifications</p>
    </div>

    <div class="card animate-fadeInUp delay-1 p-3 sm:p-4 md:p-6">
        <div class="space-y-2 sm:space-y-3">
            <!-- Message 1 -->
            <div class="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="avatar avatar-md avatar-gradient flex-shrink-0">A</div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Administrateur</p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Votre demande de retrait a ete approuvee.</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5 sm:mt-1">Il y a 2 heures</p>
                </div>
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></span>
            </div>

            <!-- Message 2 -->
            <div class="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="avatar avatar-md avatar-info flex-shrink-0">S</div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Support</p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Nous avons repondu a votre ticket #TICK-001.</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5 sm:mt-1">Il y a 1 jour</p>
                </div>
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-gray-400 rounded-full flex-shrink-0 mt-2"></span>
            </div>

            <!-- Message 3 -->
            <div class="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="avatar avatar-md avatar-success flex-shrink-0">J</div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Jean Dupont</p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Merci pour votre parrainage !</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5 sm:mt-1">Il y a 3 jours</p>
                </div>
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></span>
            </div>

            <!-- Message 4 -->
            <div class="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="avatar avatar-md avatar-warning flex-shrink-0">M</div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Marie Martin</p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Felicitation pour votre promotion !</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5 sm:mt-1">Il y a 5 jours</p>
                </div>
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-gray-400 rounded-full flex-shrink-0 mt-2"></span>
            </div>

            <!-- Message 5 -->
            <div class="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="avatar avatar-md avatar-danger flex-shrink-0">S</div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Systeme</p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Nouveau bonus de parrainage disponible.</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5 sm:mt-1">Il y a 1 semaine</p>
                </div>
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></span>
            </div>
        </div>
    </div>
</div>
@endsection