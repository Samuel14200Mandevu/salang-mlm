@extends('layouts.app')

@section('title', 'Commission #' . $commission->id)

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