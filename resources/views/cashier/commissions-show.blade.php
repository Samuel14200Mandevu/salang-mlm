@extends('cashier.layouts.app')

@push('styles')
<style>
    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .detail-card:hover {
        border-color: var(--primary-500);
    }
    .detail-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
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
        background: var(--gradient-primary);
        color: white;
        flex-shrink: 0;
    }
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-secondary { background: rgba(107, 114, 128, 0.12); color: #6b7280; }
    
    .badge-status {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge-status-approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .badge-status-paid { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .badge-status-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .badge-status-cancelled { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
    
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.7rem;
        font-weight: 600;
    }
    .type-badge-pos { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .type-badge-direct { background: rgba(99,102,241,0.15); color: #6366f1; }
    .type-badge-indirect { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-leadership { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .type-badge-sponsor { background: rgba(34,197,94,0.15); color: #22c55e; }
    .type-badge-purchase { background: rgba(139,92,246,0.15); color: #8b5cf6; }
    .type-badge-pos_transaction { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .type-badge-new_client { background: rgba(34,197,94,0.15); color: #22c55e; }
    .type-badge-cash_pos { background: rgba(34,197,94,0.15); color: #22c55e; }
    
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
    .btn-success { background: #22c55e; color: white; }
    .btn-success:hover { background: #16a34a; transform: translateY(-2px); }
    .btn-danger { background: #ef4444; color: white; }
    .btn-danger:hover { background: #dc2626; transform: translateY(-2px); }
    .btn-warning { background: #f59e0b; color: white; }
    .btn-warning:hover { background: #d97706; transform: translateY(-2px); }
    .btn-info { background: #3b82f6; color: white; }
    .btn-info:hover { background: #2563eb; transform: translateY(-2px); }
    
    .card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem; }
    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; }
    .table thead th { padding: 0.5rem 0.75rem; text-align: left; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); background: var(--bg-secondary); border-bottom: 2px solid var(--border-color); }
    .table tbody td { padding: 0.5rem 0.75rem; color: var(--text-primary); vertical-align: middle; border-bottom: 1px solid var(--border-light); }
    .table tbody tr:hover { background: var(--bg-hover); }
    
    .badge-cash {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    
    .badge-source-pos { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-source-mlm { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    
    /* ============================================================
       MODAL STYLES
       ============================================================ */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        box-shadow: var(--shadow-xl);
        transform: scale(0.9);
        transition: transform 0.3s ease;
        border: 1px solid var(--border-color);
    }
    .modal-overlay.active .modal-box {
        transform: scale(1);
    }
    .modal-icon {
        width: 4rem;
        height: 4rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .modal-icon-success {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }
    .modal-icon-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    .modal-icon-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    .modal-icon-info {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }
    .modal-title {
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .modal-text {
        text-align: center;
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    .modal-text strong {
        color: var(--text-primary);
    }
    .modal-text .text-danger {
        color: #ef4444;
    }
    .modal-text .text-warning {
        color: #f59e0b;
    }
    .modal-text .text-success {
        color: #22c55e;
    }
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .modal-actions .btn {
        min-width: 100px;
        justify-content: center;
    }
    
    @media (max-width: 640px) {
        .detail-card { padding: 0.875rem; }
        .detail-value.amount { font-size: 1.25rem; }
        .card { padding: 0.875rem; }
        .btn-sm { font-size: 0.65rem; padding: 0.25rem 0.5rem; }
        .type-badge { font-size: 0.6rem; padding: 0.1rem 0.4rem; }
        .badge-status { font-size: 0.5rem; padding: 0.075rem 0.375rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.65rem; }
        .modal-box { padding: 1.5rem; }
        .modal-actions { flex-direction: column; }
        .modal-actions .btn { width: 100%; }
    }
</style>
@endpush

@section('title', 'Détails de la commission')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
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
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            
            {{-- ✅ APPROUVER (SEULEMENT SI EN ATTENTE) --}}
            @if($commission->status == 'pending')
                <button type="button" 
                        onclick="openApproveModal()" 
                        class="btn btn-info btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approuver
                </button>
                
                {{-- ✅ REJETER (SEULEMENT SI EN ATTENTE) --}}
                <button type="button" 
                        onclick="openRejectModal()" 
                        class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
                
                {{-- ✅ ANNULER (SEULEMENT SI EN ATTENTE) --}}
                <button type="button" 
                        onclick="openCancelModal()" 
                        class="btn btn-warning btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annuler
                </button>
            @endif

            {{-- ✅ PAYER (SEULEMENT SI APPROUVEE) --}}
            @if($commission->status == 'approved')
                <button type="button" 
                        onclick="openPayModal()" 
                        class="btn btn-success btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Payer
                </button>
            @endif
        </div>
    </div>

    <!-- Informations -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">
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
                <span class="type-badge type-badge-{{ $commission->type }}">
                    {{ $commission->type_label ?? ucfirst($commission->type) }}
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
                    <span class="text-blue-500">Prête à être payée</span>
                @elseif($commission->status == 'pending')
                    <span class="text-yellow-500">En attente d'approbation</span>
                @elseif($commission->status == 'rejected')
                    <span class="text-red-500">Rejetée</span>
                @elseif($commission->status == 'cancelled')
                    <span class="text-gray-500">Annulée</span>
                @else
                    Non payée
                @endif
            </p>
        </div>
    </div>

    <!-- Détails -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-2">
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Bénéficiaire</h3>
            <div class="flex items-center gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
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
            <div class="flex items-center gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
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
                <div class="mt-2 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-xs text-[var(--text-secondary)]">Commande associée</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm">#{{ $commission->order->order_number }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">${{ number_format($commission->order->total, 2) }}</p>
                </div>
            @endif
            @if($commission->package)
                <div class="mt-2 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-xs text-[var(--text-secondary)]">Package associé</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm">{{ $commission->package->name }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Description -->
    @if($commission->description)
        <div class="detail-card animate-fadeInUp delay-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">Description</h3>
            <p class="text-[var(--text-secondary)] text-sm">{{ $commission->description }}</p>
        </div>
    @endif

    <!-- Notes -->
    @if($commission->notes)
        <div class="detail-card animate-fadeInUp delay-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">Notes</h3>
            <p class="text-[var(--text-secondary)] text-sm">{{ $commission->notes }}</p>
        </div>
    @endif

    <!-- Info -->
    <div class="detail-card animate-fadeInUp delay-4" style="background: rgba(59, 130, 246, 0.03); border-color: rgba(59, 130, 246, 0.12);">
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

<!-- ============================================================
MODAL APPROUVER
============================================================ -->
<div id="approveModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-info">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
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
            <button type="button" onclick="closeApproveModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="submit" class="btn btn-info btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Approuver
            </button>
        </form>
    </div>
</div>

<!-- ============================================================
MODAL REJETER
============================================================ -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer le rejet</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">rejeter</strong> cette commission ?
            <br>
            Cette action est <strong class="text-danger">irréversible</strong> et la commission sera définitivement rejetée.
        </p>
        <form id="rejectForm" action="{{ route('cashier.commissions.reject', $commission->id) }}" method="POST" class="modal-actions">
            @csrf
            <button type="button" onclick="closeRejectModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="submit" class="btn btn-danger btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Rejeter
            </button>
        </form>
    </div>
</div>

<!-- ============================================================
MODAL ANNULER
============================================================ -->
<div id="cancelModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer l'annulation</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-warning">annuler</strong> cette commission ?
            <br>
            La commission sera marquée comme <strong class="text-warning">annulée</strong> et ne pourra plus être traitée.
        </p>
        <form id="cancelForm" action="{{ route('cashier.commissions.cancel', $commission->id) }}" method="POST" class="modal-actions">
            @csrf
            <button type="button" onclick="closeCancelModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="submit" class="btn btn-warning btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
                </svg>
                Annuler
            </button>
        </form>
    </div>
</div>

<!-- ============================================================
MODAL PAYER
============================================================ -->
<div id="payModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-success">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer le paiement</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-success">payer</strong> cette commission ?
            <br>
            Le montant de <strong class="text-success">${{ number_format($commission->amount, 2) }}</strong> sera crédité sur le portefeuille du bénéficiaire.
        </p>
        <form id="payForm" action="{{ route('cashier.commissions.pay', $commission->id) }}" method="POST" class="modal-actions">
            @csrf
            <button type="button" onclick="closePayModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="submit" class="btn btn-success btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Payer
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
// MODAL APPROUVER
// ============================================================
function openApproveModal() {
    document.getElementById('approveModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// MODAL REJETER
// ============================================================
function openRejectModal() {
    document.getElementById('rejectModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// MODAL ANNULER
// ============================================================
function openCancelModal() {
    document.getElementById('cancelModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// MODAL PAYER
// ============================================================
function openPayModal() {
    document.getElementById('payModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closePayModal() {
    document.getElementById('payModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// FERMER LES MODALS EN CLIQUANT À L'EXTÉRIEUR
// ============================================================
document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// ============================================================
// FERMER LES MODALS AVEC LA TOUCHE ESCAPE
// ============================================================
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