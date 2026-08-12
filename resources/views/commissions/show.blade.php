@extends('layouts.app')

@push('styles')
<style>
    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .detail-card:hover { border-color: var(--primary-500); }
    .detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        font-weight: 600;
    }
    .detail-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-top: 0.25rem;
    }
    .timeline-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .timeline-item:last-child { border-bottom: none; }
    .timeline-dot {
        width: 0.625rem;
        height: 0.625rem;
        border-radius: 50%;
        margin-top: 0.25rem;
        flex-shrink: 0;
    }
    .timeline-dot-success { background: #22c55e; }
    .timeline-dot-pending { background: #f59e0b; }
    .timeline-dot-info { background: #3b82f6; }
    
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.7rem;
        font-weight: 600;
    }
    .type-badge-direct { background: rgba(99,102,241,0.15); color: #6366f1; }
    .type-badge-indirect { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-leadership { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .type-badge-retail { background: rgba(34,197,94,0.15); color: #22c55e; }
    .type-badge-bonus { background: rgba(236,72,153,0.15); color: #ec4899; }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
    }
    .avatar-lg { width: 3rem; height: 3rem; font-size: 1rem; }
    .avatar-gradient {
        background: var(--gradient-primary);
        color: white;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm { padding: 0.375rem 1rem; font-size: 0.75rem; }
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    
    @media (max-width: 640px) {
        .detail-card { padding: 0.75rem; }
        .detail-value { font-size: 0.95rem; }
        .avatar-lg { width: 2.5rem; height: 2.5rem; font-size: 0.75rem; }
        .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .detail-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Commission #{{ $commission->id }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Détails de la commission</p>
        </div>
        <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à la liste
        </a>
    </div>

    <!-- Details -->
    <div class="detail-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="detail-card">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Informations</h3>
                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                    {{ $commission->status == 'paid' ? 'Payé' : 'En attente' }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <p class="detail-label">Type</p>
                    <p class="detail-value text-sm">
                        <span class="type-badge type-badge-{{ $commission->type }}">
                            {{ ucfirst($commission->type) }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="detail-label">Montant</p>
                    <p class="detail-value text-green-500">+${{ number_format($commission->amount, 2) }}</p>
                </div>
                <div>
                    <p class="detail-label">Pourcentage</p>
                    <p class="detail-value">{{ $commission->percentage }}%</p>
                </div>
                <div>
                    <p class="detail-label">Date</p>
                    <p class="detail-value text-sm">{{ $commission->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Source</h3>

            @if($commission->fromUser)
                <div class="flex items-center gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <div class="avatar avatar-lg avatar-gradient">
                        {{ substr($commission->fromUser->name, 0, 2) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">{{ $commission->fromUser->name }}</p>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)] truncate">{{ $commission->fromUser->email }}</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                            Parrain: 
                            @php
                                $parrain = App\Models\User::find($commission->fromUser->parrain_id);
                            @endphp
                            {{ $parrain?->name ?? 'Aucun' }}
                        </p>
                    </div>
                </div>
            @else
                <p class="text-[var(--text-secondary)] text-sm">Système / Automatique</p>
            @endif

            @if($commission->package)
                <div class="mt-2 sm:mt-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Package associé</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">{{ $commission->package->name }}</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">${{ number_format($commission->package->price, 2) }}</p>
                </div>
            @endif

            @if($commission->order)
                <div class="mt-2 sm:mt-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Commande associée</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">#{{ $commission->order->order_number }}</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">${{ number_format($commission->order->total, 2) }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Description -->
    @if($commission->description)
    <div class="detail-card animate-fadeInUp delay-2">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">Description</h3>
        <p class="text-[var(--text-secondary)] text-sm">{{ $commission->description }}</p>
    </div>
    @endif

    <!-- Timeline -->
    <div class="detail-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Chronologie</h3>

        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-success"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Commission créée</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">{{ $commission->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @if($commission->status == 'paid')
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-success"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Commission payée</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">{{ $commission->paid_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
            </div>
        </div>
        @else
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-pending"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">En attente de paiement</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">La commission sera payée automatiquement lors du prochain cycle</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection