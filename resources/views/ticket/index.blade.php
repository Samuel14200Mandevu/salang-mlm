@extends('layouts.app')

@push('styles')
<style>
    .ticket-item {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .ticket-item:hover {
        transform: translateX(4px);
        box-shadow: var(--shadow-sm);
    }
    
    @media (max-width: 640px) {
        .card { padding: 0.75rem; }
        .ticket-item { padding: 0.5rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Centre de tickets</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Contactez le support</p>
        </div>
        <button class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau ticket
        </button>
    </div>

    <div class="card animate-fadeInUp delay-1 p-3 sm:p-4 md:p-6">
        <div class="space-y-2 sm:space-y-3">
            <!-- Ticket 1 -->
            <div class="ticket-item flex flex-wrap items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Probleme de paiement</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">#TICK-001 • 2 jours</p>
                </div>
                <span class="badge badge-warning text-[10px] sm:text-xs flex-shrink-0">En cours</span>
            </div>

            <!-- Ticket 2 -->
            <div class="ticket-item flex flex-wrap items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Question sur les commissions</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">#TICK-002 • 5 jours</p>
                </div>
                <span class="badge badge-success text-[10px] sm:text-xs flex-shrink-0">Resolu</span>
            </div>

            <!-- Ticket 3 -->
            <div class="ticket-item flex flex-wrap items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Demande de retrait</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">#TICK-003 • 1 semaine</p>
                </div>
                <span class="badge badge-danger text-[10px] sm:text-xs flex-shrink-0">Ferme</span>
            </div>

            <!-- Ticket 4 -->
            <div class="ticket-item flex flex-wrap items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Probleme technique</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">#TICK-004 • 2 semaines</p>
                </div>
                <span class="badge badge-success text-[10px] sm:text-xs flex-shrink-0">Resolu</span>
            </div>

            <!-- Ticket 5 -->
            <div class="ticket-item flex flex-wrap items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Demande de modification</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">#TICK-005 • 3 semaines</p>
                </div>
                <span class="badge badge-danger text-[10px] sm:text-xs flex-shrink-0">Ferme</span>
            </div>
        </div>
    </div>
</div>
@endsection