@extends('layouts.app')

@push('styles')
<style>
    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    .detail-card:hover {
        border-color: var(--primary-500);
    }
    .detail-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        font-weight: 600;
    }
    .detail-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-top: 0.25rem;
    }
    .timeline-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .timeline-item:last-child {
        border-bottom: none;
    }
    .timeline-dot {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        margin-top: 0.25rem;
        flex-shrink: 0;
    }
    .timeline-dot-success { background: #22c55e; }
    .timeline-dot-pending { background: #f59e0b; }
    .timeline-dot-info { background: #3b82f6; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                💰 Commission #{{ $commission->id }}
            </h1>
            <p class="text-[var(--text-secondary)] mt-1">Détails de la commission</p>
        </div>
        <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm">
            ← Retour à la liste
        </a>
    </div>

    <!-- Détails -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fadeInUp delay-1">
        <!-- Informations générales -->
        <div class="detail-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-[var(--text-primary)]">📋 Informations</h3>
                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                    {{ $commission->status == 'paid' ? '✅ Payé' : '⏳ En attente' }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="detail-label">Type</p>
                    <p class="detail-value">
                        <span class="type-badge type-badge-{{ $commission->type }}">
                            @if($commission->type == 'direct') 👤 Direct
                            @elseif($commission->type == 'indirect') 👥 Indirect
                            @elseif($commission->type == 'leadership') 👑 Leadership
                            @elseif($commission->type == 'retail') 🛍️ Retail
                            @else 🎁 Bonus
                            @endif
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

        <!-- Source -->
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">👤 Source</h3>

            @if($commission->fromUser)
                <div class="flex items-center gap-4 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <div class="avatar avatar-lg avatar-gradient">
                        {{ substr($commission->fromUser->name, 0, 2) }}
                    </div>
                    <div>
                        <p class="font-semibold text-[var(--text-primary)]">{{ $commission->fromUser->name }}</p>
                        <p class="text-sm text-[var(--text-secondary)]">{{ $commission->fromUser->email }}</p>
                        <p class="text-xs text-[var(--text-secondary)]">
                            Sponsor: {{ $commission->fromUser->sponsor?->name ?? 'Aucun' }}
                        </p>
                    </div>
                </div>
            @else
                <p class="text-[var(--text-secondary)]">Système / Automatique</p>
            @endif

            @if($commission->package)
                <div class="mt-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-sm text-[var(--text-secondary)]">Package associé</p>
                    <p class="font-semibold text-[var(--text-primary)]">{{ $commission->package->name }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">${{ number_format($commission->package->price, 2) }}</p>
                </div>
            @endif

            @if($commission->order)
                <div class="mt-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-sm text-[var(--text-secondary)]">Commande associée</p>
                    <p class="font-semibold text-[var(--text-primary)]">#{{ $commission->order->order_number }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">${{ number_format($commission->order->total, 2) }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Description -->
    @if($commission->description)
    <div class="detail-card animate-fadeInUp delay-2">
        <h3 class="font-semibold text-[var(--text-primary)] mb-2">📝 Description</h3>
        <p class="text-[var(--text-secondary)]">{{ $commission->description }}</p>
    </div>
    @endif

    <!-- Timeline -->
    <div class="detail-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] mb-4">⏳ Chronologie</h3>

        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-success"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)]">Commission créée</p>
                <p class="text-sm text-[var(--text-secondary)]">{{ $commission->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @if($commission->status == 'paid')
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-success"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)]">Commission payée</p>
                <p class="text-sm text-[var(--text-secondary)]">{{ $commission->paid_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
            </div>
        </div>
        @else
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-pending"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)]">En attente de paiement</p>
                <p class="text-sm text-[var(--text-secondary)]">La commission sera payée automatiquement lors du prochain cycle</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection