@extends('cashier.layouts.app')

@push('styles')
<style>
    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .detail-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-secondary);
        font-weight: 600;
    }

    .detail-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-top: 0.25rem;
    }

    .detail-value.amount {
        font-size: 1.5rem;
        font-weight: 800;
    }

    .detail-value.amount-positive { color: #22c55e; }

    .avatar-lg {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        background: var(--primary);
        color: white;
        flex-shrink: 0;
    }

    .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .badge-success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .badge-secondary { background: rgba(107, 114, 128, 0.12); color: #6b7280; }

    .badge-status-pending { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .badge-status-approved { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .badge-status-paid { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-status-rejected { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-status-cancelled { background: rgba(107, 114, 128, 0.12); color: #6b7280; }

    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.6rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .type-badge-pos { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .type-badge-direct { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
    .type-badge-indirect { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .type-badge-leadership { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .type-badge-sponsor { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .type-badge-cash_pos { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .type-badge-default { background: var(--bg-secondary); color: var(--text-secondary); }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: var(--primary-hover, #091E3B);
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-success {
        background: #22c55e;
        color: #FFFFFF;
    }
    .btn-success:hover {
        background: #16a34a;
    }

    .btn-danger {
        background: #b32a2a;
        color: #FFFFFF;
    }
    .btn-danger:hover {
        background: #8f2121;
    }

    .btn-warning {
        background: #d97706;
        color: #FFFFFF;
    }
    .btn-warning:hover {
        background: #b45309;
    }

    .btn-info {
        background: #2563eb;
        color: #FFFFFF;
    }
    .btn-info:hover {
        background: #1d4ed8;
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .badge-cash {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.15);
    }

    .badge-source-pos { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-source-mlm { background: rgba(59, 130, 246, 0.12); color: #2563eb; }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-md, 8px);
        padding: 1.75rem;
        max-width: 450px;
        width: 90%;
        border: 1px solid var(--border-color);
        transform: scale(0.95);
        transition: transform 0.25s ease;
    }
    .modal-overlay.active .modal-box {
        transform: scale(1);
    }
    .modal-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
    }
    .modal-icon-success { background: rgba(34, 197, 94, 0.10); color: #16a34a; }
    .modal-icon-danger { background: rgba(179, 42, 42, 0.10); color: #b32a2a; }
    .modal-icon-warning { background: rgba(245, 158, 11, 0.10); color: #d97706; }
    .modal-icon-info { background: rgba(59, 130, 246, 0.10); color: #2563eb; }
    .modal-title {
        text-align: center;
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .modal-text {
        text-align: center;
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.25rem;
        line-height: 1.6;
    }
    .modal-text .text-danger { color: #b32a2a; }
    .modal-text .text-warning { color: #d97706; }
    .modal-text .text-success { color: #16a34a; }
    .modal-text .text-info { color: #2563eb; }
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .modal-actions .btn {
        min-width: 100px;
        justify-content: center;
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    @media (max-width: 640px) {
        .detail-card { padding: 0.875rem; }
        .detail-value.amount { font-size: 1.25rem; }
        .card { padding: 0.875rem; }
        .btn-sm { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
        .type-badge { font-size: 0.6rem; padding: 0.1rem 0.4rem; }
        .modal-box { padding: 1.25rem; }
        .modal-actions { flex-direction: column; }
        .modal-actions .btn { width: 100%; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
    }
</style>
@endpush

@section('title', 'Détails de la commission')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Commission #{{ $commission->id }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Détails de la commission
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('cashier.commissions') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>

            @if($commission->status == 'pending')
                <button type="button" onclick="openApproveModal()" class="btn btn-info btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approuver
                </button>
                <button type="button" onclick="openRejectModal()" class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
                <button type="button" onclick="openCancelModal()" class="btn btn-warning btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annuler
                </button>
            @endif

            @if($commission->status == 'approved')
                <button type="button" onclick="openPayModal()" class="btn btn-success btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Payer
                </button>
            @endif
        </div>
    </div>

    {{-- INFORMATIONS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="detail-card text-center">
            <p class="detail-label">Montant</p>
            <p class="detail-value amount amount-positive">
                {{ $commission->amount > 0 ? '+' : '' }}${{ number_format($commission->amount, 2) }}
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                @if($commission->source == 'pos')
                    <span class="badge-cash">CASH</span> Commission POS
                @else
                    Commission MLM
                @endif
            </p>
        </div>

        <div class="detail-card text-center">
            <p class="detail-label">Type</p>
            <p class="detail-value">
                @php
                    $typeClass = 'type-badge-' . $commission->type;
                    if (!in_array($commission->type, ['cash_pos', 'direct', 'indirect', 'leadership', 'sponsor', 'pos'])) {
                        $typeClass = 'type-badge-default';
                    }
                    $typeLabel = $commission->type_label ?? ucfirst(str_replace('_', ' ', $commission->type));
                    if ($commission->type == 'cash_pos') {
                        $typeLabel = 'CASH POS';
                    }
                @endphp
                <span class="type-badge {{ $typeClass }}">
                    {{ $typeLabel }}
                </span>
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                @if($commission->source == 'pos')
                    <span class="badge badge-source-pos">POS</span>
                @else
                    <span class="badge badge-source-mlm">MLM</span>
                @endif
            </p>
        </div>

        <div class="detail-card text-center">
            <p class="detail-label">Statut</p>
            <p class="detail-value">
                @if($commission->status == 'pending')
                    <span class="badge badge-warning">En attente</span>
                @elseif($commission->status == 'approved')
                    <span class="badge badge-info">Approuvée</span>
                @elseif($commission->status == 'paid')
                    <span class="badge badge-success">Payée</span>
                @elseif($commission->status == 'rejected')
                    <span class="badge badge-danger">Rejetée</span>
                @elseif($commission->status == 'cancelled')
                    <span class="badge badge-secondary">Annulée</span>
                @else
                    <span class="badge badge-warning">{{ ucfirst($commission->status) }}</span>
                @endif
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                @if($commission->paid_at)
                    Payée le {{ $commission->paid_at->format('d/m/Y H:i') }}
                @elseif($commission->status == 'approved')
                    <span class="text-[#2563eb]">Prête à être payée</span>
                @elseif($commission->status == 'pending')
                    <span class="text-[#d97706]">En attente d'approbation</span>
                @elseif($commission->status == 'rejected')
                    <span class="text-[#b32a2a]">Rejetée</span>
                @elseif($commission->status == 'cancelled')
                    <span class="text-[#6b7280]">Annulée</span>
                @else
                    Non payée
                @endif
            </p>
        </div>
    </div>

    {{-- DÉTAILS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Bénéficiaire</h3>
            <div class="flex items-center gap-3 p-3 bg-[var(--bg-secondary)] rounded-md">
                <div class="avatar-lg">
                    {{ $commission->user?->name ? substr($commission->user->name, 0, 2) : 'N/A' }}
                </div>
                <div>
                    <p class="font-semibold text-[var(--text-primary)] text-sm">{{ $commission->user?->name ?? 'N/A' }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">{{ $commission->user?->email ?? 'Email non disponible' }}</p>
                    <p class="text-[10px] text-[var(--text-secondary)]">ID: #{{ $commission->user_id }}</p>
                    @if($commission->user)
                        <p class="text-[10px] text-[var(--text-secondary)]">Code: {{ $commission->user->sponsor_id ?? 'N/A' }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Source</h3>
            <div class="flex items-center gap-3 p-3 bg-[var(--bg-secondary)] rounded-md">
                <div class="avatar-lg">
                    {{ $commission->fromUser?->name ? substr($commission->fromUser->name, 0, 2) : 'S' }}
                </div>
                <div>
                    <p class="font-semibold text-[var(--text-primary)] text-sm">{{ $commission->fromUser?->name ?? 'Système' }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">{{ $commission->fromUser?->email ?? 'Système' }}</p>
                    @if($commission->fromUser)
                        <p class="text-[10px] text-[var(--text-secondary)]">Code: {{ $commission->fromUser->sponsor_id ?? 'N/A' }}</p>
                    @endif
                </div>
            </div>
            @if($commission->order)
                <div class="mt-2 p-3 bg-[var(--bg-secondary)] rounded-md">
                    <p class="text-xs text-[var(--text-secondary)]">Commande associée</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm">#{{ $commission->order->order_number }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">${{ number_format($commission->order->total, 2) }}</p>
                </div>
            @endif
            @if($commission->package)
                <div class="mt-2 p-3 bg-[var(--bg-secondary)] rounded-md">
                    <p class="text-xs text-[var(--text-secondary)]">Package associé</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm">{{ $commission->package->name }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- DESCRIPTION --}}
    @if($commission->description)
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">Description</h3>
            <p class="text-[var(--text-secondary)] text-sm">{{ $commission->description }}</p>
        </div>
    @endif

    {{-- NOTES --}}
    @if($commission->notes)
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">Notes</h3>
            <p class="text-[var(--text-secondary)] text-sm">{{ $commission->notes }}</p>
        </div>
    @endif

    {{-- INFO --}}
    <div class="detail-card" style="background: rgba(59, 130, 246, 0.03); border-color: rgba(59, 130, 246, 0.12);">
        <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm">
            <span class="text-[var(--text-secondary)]">Commission #{{ $commission->id }}</span>
            <span class="text-[var(--text-tertiary)]">•</span>
            <span class="text-[var(--text-secondary)]">Créée le {{ $commission->created_at->format('d/m/Y H:i') }}</span>
            @if($commission->updated_at != $commission->created_at)
                <span class="text-[var(--text-tertiary)]">•</span>
                <span class="text-[var(--text-secondary)]">Dernière modification {{ $commission->updated_at->format('d/m/Y H:i') }}</span>
            @endif
        </div>
    </div>
</div>

{{-- MODAL APPROUVER --}}
<div id="approveModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-info">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer l'approbation</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-info">approuver</strong> cette commission ?
            <br>
            Une fois approuvée, la commission sera <strong class="text-success">prête à être payée</strong>.
        </p>
        <form id="approveForm" action="{{ route('cashier.commissions.approve', $commission->id) }}" method="POST" class="modal-actions">
            @csrf
            <button type="button" onclick="closeApproveModal()" class="btn btn-outline btn-sm">Annuler</button>
            <button type="submit" class="btn btn-info btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Approuver
            </button>
        </form>
    </div>
</div>

{{-- MODAL REJETER --}}
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer le rejet</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">rejeter</strong> cette commission ?
            <br>
            Cette action est <strong class="text-danger">irréversible</strong>.
        </p>
        <form id="rejectForm" action="{{ route('cashier.commissions.reject', $commission->id) }}" method="POST" class="modal-actions">
            @csrf
            <button type="button" onclick="closeRejectModal()" class="btn btn-outline btn-sm">Annuler</button>
            <button type="submit" class="btn btn-danger btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Rejeter
            </button>
        </form>
    </div>
</div>

{{-- MODAL ANNULER --}}
<div id="cancelModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer l'annulation</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-warning">annuler</strong> cette commission ?
            <br>
            La commission sera marquée comme <strong class="text-warning">annulée</strong>.
        </p>
        <form id="cancelForm" action="{{ route('cashier.commissions.cancel', $commission->id) }}" method="POST" class="modal-actions">
            @csrf
            <button type="button" onclick="closeCancelModal()" class="btn btn-outline btn-sm">Annuler</button>
            <button type="submit" class="btn btn-warning btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
                </svg>
                Annuler
            </button>
        </form>
    </div>
</div>

{{-- MODAL PAYER --}}
<div id="payModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-success">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer le paiement</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-success">payer</strong> cette commission ?
            <br>
            Le montant de <strong class="text-success">${{ number_format($commission->amount, 2) }}</strong> sera crédité.
        </p>
        <form id="payForm" action="{{ route('cashier.commissions.pay', $commission->id) }}" method="POST" class="modal-actions">
            @csrf
            <button type="button" onclick="closePayModal()" class="btn btn-outline btn-sm">Annuler</button>
            <button type="submit" class="btn btn-success btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Payer
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openApproveModal() {
    document.getElementById('approveModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeApproveModal() {
    document.getElementById('approveModal').classList.remove('active');
    document.body.style.overflow = '';
}

function openRejectModal() {
    document.getElementById('rejectModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
    document.body.style.overflow = '';
}

function openCancelModal() {
    document.getElementById('cancelModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeCancelModal() {
    document.getElementById('cancelModal').classList.remove('active');
    document.body.style.overflow = '';
}

function openPayModal() {
    document.getElementById('payModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closePayModal() {
    document.getElementById('payModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});
</script>
@endpush
@endsection