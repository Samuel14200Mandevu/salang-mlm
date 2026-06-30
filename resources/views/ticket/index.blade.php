@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🎫 Centre de tickets</h1>
            <p class="text-[var(--text-secondary)] mt-1">Contactez le support</p>
        </div>
        <button class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau ticket
        </button>
    </div>

    <div class="card animate-fadeInUp delay-1">
        <div class="space-y-3">
            <!-- Ticket 1 -->
            <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div>
                    <p class="font-medium text-[var(--text-primary)]">Problème de paiement</p>
                    <p class="text-xs text-[var(--text-secondary)]">#TICK-001 • 2 jours</p>
                </div>
                <span class="badge badge-warning">En cours</span>
            </div>

            <!-- Ticket 2 -->
            <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div>
                    <p class="font-medium text-[var(--text-primary)]">Question sur les commissions</p>
                    <p class="text-xs text-[var(--text-secondary)]">#TICK-002 • 5 jours</p>
                </div>
                <span class="badge badge-success">Résolu</span>
            </div>

            <!-- Ticket 3 -->
            <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div>
                    <p class="font-medium text-[var(--text-primary)]">Demande de retrait</p>
                    <p class="text-xs text-[var(--text-secondary)]">#TICK-003 • 1 semaine</p>
                </div>
                <span class="badge badge-danger">Fermé</span>
            </div>
        </div>
    </div>
</div>
@endsection