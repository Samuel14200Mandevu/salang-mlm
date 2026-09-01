{{-- resources/views/admin/commissions/show.blade.php --}}

@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.detail-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.detail-card:hover {
    border-color: var(--primary-blue);
}

.detail-label {
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    font-weight: 600;
}
.detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-top: 0.25rem;
}
.detail-value.amount {
    font-size: 1.5rem;
    font-weight: 700;
}
.detail-value.amount-positive { color: #1C7E4A; }
.detail-value.amount-negative { color: #B91C1C; }

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-info { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }
.badge-secondary { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.type-badge-direct { background: rgba(10, 42, 108, 0.08); color: var(--primary-blue); border-color: rgba(10, 42, 108, 0.12); }
.type-badge-indirect { background: rgba(10, 42, 108, 0.08); color: var(--primary-blue); border-color: rgba(10, 42, 108, 0.12); }
.type-badge-leadership { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.type-badge-retail { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.type-badge-bonus { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 600;
    flex-shrink: 0;
    overflow: hidden;
}
.avatar-lg { width: 3rem; height: 3rem; font-size: 1rem; }
.avatar-gradient {
    background: var(--primary-blue);
    color: white;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
}

.btn-danger {
    background: #B91C1C;
    color: white;
    border-color: #B91C1C;
}
.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
}

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-secondary:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
}
.table tbody tr:hover {
    background: var(--bg-hover);
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
.timeline-dot-success { background: #1C7E4A; }
.timeline-dot-pending { background: #B54708; }
.timeline-dot-info { background: #065F9C; }
.timeline-dot-danger { background: #B91C1C; }

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
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
    border-radius: 12px;
    padding: 1.75rem;
    max-width: 440px;
    width: 90%;
    border: 1px solid var(--border-color);
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    transform: scale(0.95);
    transition: transform 0.25s ease;
}
.modal-overlay.active .modal-box {
    transform: scale(1);
}
.modal-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
}
.modal-icon-success {
    background: rgba(28, 126, 74, 0.1);
    color: #1C7E4A;
}
.modal-icon-danger {
    background: rgba(185, 28, 28, 0.1);
    color: #B91C1C;
}
.modal-icon svg {
    width: 1.75rem;
    height: 1.75rem;
}
.modal-title {
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--text-primary);
    text-align: center;
    margin-bottom: 0.375rem;
}
.modal-message {
    font-size: 0.875rem;
    color: var(--text-secondary);
    text-align: center;
    margin-bottom: 1.25rem;
    line-height: 1.6;
}
.modal-message strong {
    color: var(--text-primary);
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.modal-actions .btn {
    min-width: 90px;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }

@media (max-width: 640px) {
    .detail-card {
        padding: 0.875rem;
    }
    .detail-value {
        font-size: 0.875rem;
    }
    .detail-value.amount {
        font-size: 1.25rem;
    }
    .avatar-lg {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 0.75rem;
    }
    .btn {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    .btn-sm {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
    }
    .card {
        padding: 0.875rem;
    }
    .table thead th,
    .table tbody td {
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
    }
    .badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
    }
    .type-badge {
        font-size: 0.6rem;
        padding: 0.1rem 0.4rem;
    }
    .header-actions {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .header-actions .btn {
        width: 100%;
        justify-content: center;
    }
    .detail-grid {
        grid-template-columns: 1fr !important;
    }
    .timeline-item {
        padding: 0.375rem 0;
    }
    .modal-box {
        padding: 1.25rem;
        max-width: 95%;
    }
    .modal-actions {
        flex-direction: column;
    }
    .modal-actions .btn {
        width: 100%;
        min-width: unset;
    }
}

@media (max-width: 480px) {
    .detail-card {
        padding: 0.75rem;
    }
    .detail-value.amount {
        font-size: 1.1rem;
    }
    .card {
        padding: 0.75rem;
    }
    .table thead th,
    .table tbody td {
        padding: 0.25rem 0.375rem;
        font-size: 0.6rem;
    }
    .badge {
        font-size: 0.55rem;
        padding: 0.1rem 0.375rem;
    }
    .type-badge {
        font-size: 0.55rem;
        padding: 0.075rem 0.3rem;
    }
    .modal-box {
        padding: 1rem;
    }
    .modal-title {
        font-size: 1rem;
    }
    .modal-message {
        font-size: 0.813rem;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .detail-card {
        padding: 1rem;
    }
    .detail-value.amount {
        font-size: 1.3rem;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Commission #{{ $commission->id }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Détails de la commission
            </p>
        </div>
        <div class="header-actions flex flex-wrap gap-2">
            <a href="{{ route('admin.commissions') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            @if($commission->status == 'pending')
                <button onclick="openConfirmModal('approve')" class="btn btn-success btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approuver
                </button>
                <button onclick="openConfirmModal('reject')" class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
            @endif
        </div>
    </div>

    <!-- Main Information -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">

        <!-- Amount -->
        <div class="detail-card text-center">
            <p class="detail-label">Montant</p>
            <p class="detail-value amount amount-positive">
                +${{ number_format($commission->amount, 2) }}
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                Taux: {{ $commission->percentage }}%
            </p>
        </div>

        <!-- Type -->
        <div class="detail-card text-center">
            <p class="detail-label">Type</p>
            <p class="detail-value">
                <span class="type-badge type-badge-{{ $commission->type }}">
                    {{ $commission->type_label ?? ucfirst($commission->type) }}
                </span>
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                Commission {{ $commission->type }}
            </p>
        </div>

        <!-- Status -->
        <div class="detail-card text-center">
            <p class="detail-label">Statut</p>
            <p class="detail-value">
                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : ($commission->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                    {{ $commission->status == 'paid' ? 'Payée' : ($commission->status == 'pending' ? 'En attente' : 'Annulée') }}
                </span>
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                @if($commission->paid_at)
                    Payée le {{ $commission->paid_at->format('d/m/Y H:i') }}
                @else
                    Non payée
                @endif
            </p>
        </div>
    </div>

    <!-- User and Source Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-2">

        <!-- User -->
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                Utilisateur
            </h3>

            <div class="flex items-center gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <div class="avatar avatar-lg avatar-gradient">
                    {{ $commission->user?->name ? substr($commission->user->name, 0, 2) : 'N/A' }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                        {{ $commission->user?->name ?? 'N/A' }}
                    </p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)] truncate">
                        {{ $commission->user?->email ?? 'Email non disponible' }}
                    </p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                        ID: #{{ $commission->user_id }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Source -->
        <div class="detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                Source
            </h3>

            <div class="flex items-center gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <div class="avatar avatar-lg avatar-gradient">
                    {{ $commission->fromUser?->name ? substr($commission->fromUser->name, 0, 2) : 'S' }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                        {{ $commission->fromUser?->name ?? 'Système' }}
                    </p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)] truncate">
                        {{ $commission->fromUser?->email ?? 'Généré automatiquement' }}
                    </p>
                    @if($commission->fromUser)
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                            Parrain: {{ $commission->fromUser->parrain?->name ?? 'Aucun' }}
                        </p>
                    @endif
                </div>
            </div>

            @if($commission->package)
            <div class="mt-2 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Package associé</p>
                <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    {{ $commission->package->name }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                    ${{ number_format($commission->package->price, 2) }}
                </p>
            </div>
            @endif
        </div>
    </div>

    <!-- Description -->
    @if($commission->description)
    <div class="detail-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">
            Description
        </h3>
        <p class="text-[var(--text-secondary)] text-sm">{{ $commission->description }}</p>
    </div>
    @endif

    <!-- Timeline -->
    <div class="detail-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
            Chronologie
        </h3>

        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-info"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Commission créée</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ $commission->created_at->format('d/m/Y H:i:s') }}
                    <span class="text-[10px]">({{ $commission->created_at->diffForHumans() }})</span>
                </p>
            </div>
        </div>

        @if($commission->status == 'paid')
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-success"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Commission payée</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ $commission->paid_at?->format('d/m/Y H:i:s') ?? 'N/A' }}
                </p>
            </div>
        </div>
        @elseif($commission->status == 'pending')
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-pending"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">En attente de paiement</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    Cette commission sera payée automatiquement lors du prochain cycle
                </p>
            </div>
        </div>
        @elseif($commission->status == 'cancelled')
        <div class="timeline-item">
            <div class="timeline-dot timeline-dot-danger"></div>
            <div>
                <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">Commission annulée</p>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ $commission->updated_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- Similar Commissions -->
    @if(isset($similarCommissions) && $similarCommissions->count() > 0)
    <div class="detail-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
            Commissions similaires
        </h3>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th class="hidden sm:table-cell">Type</th>
                        <th>Montant</th>
                        <th class="hidden md:table-cell">Statut</th>
                        <th class="hidden lg:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($similarCommissions as $similar)
                        <tr>
                            <td class="font-medium text-sm">
                                {{ $similar->user?->name ?? 'N/A' }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="type-badge type-badge-{{ $similar->type }}">
                                    {{ $similar->type_label ?? ucfirst($similar->type) }}
                                </span>
                            </td>
                            <td class="amount-positive">+${{ number_format($similar->amount, 2) }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge {{ $similar->status == 'paid' ? 'badge-success' : ($similar->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $similar->status == 'paid' ? 'Payé' : ($similar->status == 'pending' ? 'En attente' : 'Annulé') }}
                                </span>
                            </td>
                            <td class="hidden lg:table-cell text-[var(--text-secondary)] text-xs">
                                {{ $similar->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<!-- Modal -->
<div id="confirmModal" class="modal-overlay">
    <div class="modal-box">
        <div id="modalIcon" class="modal-icon modal-icon-success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 id="modalTitle" class="modal-title">Confirmer l'approbation</h3>
        <p id="modalMessage" class="modal-message">
            Êtes-vous sûr de vouloir <strong>approuver</strong> cette commission ?
            <br>
            <span class="text-xs text-[var(--text-secondary)]">
                Montant: <strong id="modalAmount" class="text-[var(--primary-blue)]">$0.00</strong>
            </span>
        </p>
        <div class="modal-actions">
            <button onclick="closeConfirmModal()" class="btn btn-secondary">
                Annuler
            </button>
            <button id="confirmActionBtn" class="btn btn-success">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Confirmer
            </button>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
@if($commission->status == 'pending')
    <form id="approveForm" action="{{ route('admin.commissions.approve', $commission->id) }}" method="POST" style="display:none;">
        @csrf
    </form>
    <form id="rejectForm" action="{{ route('admin.commissions.reject', $commission->id) }}" method="POST" style="display:none;">
        @csrf
    </form>
@endif

@push('scripts')
<script>
    let confirmAction = null;
    let confirmForm = null;

    function openConfirmModal(action) {
        const modal = document.getElementById('confirmModal');
        const icon = document.getElementById('modalIcon');
        const title = document.getElementById('modalTitle');
        const message = document.getElementById('modalMessage');
        const amount = document.getElementById('modalAmount');
        const confirmBtn = document.getElementById('confirmActionBtn');

        icon.className = 'modal-icon';

        if (action === 'approve') {
            icon.classList.add('modal-icon-success');
            icon.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            `;
            title.textContent = 'Confirmer l\'approbation';
            message.innerHTML = `
                Êtes-vous sûr de vouloir <strong>approuver</strong> cette commission ?
                <br>
                <span class="text-xs text-[var(--text-secondary)]">
                    Montant: <strong id="modalAmount" class="text-[var(--primary-blue)]">${{ number_format($commission->amount, 2) }}</strong>
                </span>
            `;
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Approuver
            `;
            confirmAction = 'approve';
            confirmForm = document.getElementById('approveForm');
        } else if (action === 'reject') {
            icon.classList.add('modal-icon-danger');
            icon.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            `;
            title.textContent = 'Confirmer le rejet';
            message.innerHTML = `
                Êtes-vous sûr de vouloir <strong>rejeter</strong> cette commission ?
                <br>
                <span class="text-xs text-[var(--text-secondary)]">
                    Montant: <strong id="modalAmount" class="text-[#B91C1C]">${{ number_format($commission->amount, 2) }}</strong>
                </span>
            `;
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Rejeter
            `;
            confirmAction = 'reject';
            confirmForm = document.getElementById('rejectForm');
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        confirmAction = null;
        confirmForm = null;
    }

    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        if (confirmForm) {
            confirmForm.submit();
        }
        closeConfirmModal();
    });

    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeConfirmModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
        }
    });
</script>
@endpush
@endsection