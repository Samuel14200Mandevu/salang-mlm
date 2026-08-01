{{-- resources/views/admin/cashiers/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .cashier-row {
        transition: all 0.3s ease;
    }
    .cashier-row:hover {
        background: var(--bg-hover);
        transform: translateX(4px);
    }
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
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
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
    }
    .btn-success {
        background: #22c55e;
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.4);
    }
    .btn-warning {
        background: var(--gradient-warning);
        color: white;
    }
    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
    }
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    .btn-md {
        padding: 0.625rem 1.5rem;
        font-size: 0.875rem;
    }
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .badge-cashier {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }
    .table thead th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }
    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    /* ===== MODAL STYLES ===== */
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
    .modal-icon-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    .modal-icon-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    .modal-icon-success {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
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
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .modal-actions .btn {
        min-width: 100px;
        justify-content: center;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    
    @media (max-width: 640px) {
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.65rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        .card {
            padding: 0.875rem;
        }
        .card-stats {
            padding: 0.75rem;
        }
        .card-stats .text-2xl {
            font-size: 1.25rem;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        .modal-box {
            padding: 1.5rem;
        }
        .modal-actions {
            flex-direction: column;
        }
        .modal-actions .btn {
            width: 100%;
        }
    }
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        .table thead th, .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.6rem;
        }
        .btn-sm {
            padding: 0.125rem 0.375rem;
            font-size: 0.6rem;
        }
        .btn-sm svg {
            width: 0.75rem;
            height: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <span class="text-green-500">
                    <svg class="inline-block w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                Gestion des caissiers
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Liste des caissiers pour les ventes au guichet (POS)
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau caissier
        </a>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $cashiers->total() ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">
                {{ $cashiers->where('is_active', true)->count() }}
            </p>
        </div>
        <div class="card-stats border-l-4 border-red-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Inactifs</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">
                {{ $cashiers->where('is_active', false)->count() }}
            </p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Non vérifiés</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">
                {{ $cashiers->where('kyc_status', 'not_submitted')->count() }}
            </p>
        </div>
    </div>

    <!-- Liste -->
    <div class="card animate-fadeInUp delay-3">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Liste des caissiers</h3>
            <span class="badge badge-info text-[10px] sm:text-xs">
                {{ $cashiers->total() ?? 0 }} caissiers
            </span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">Nom</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Email</th>
                        <th class="text-xs sm:text-sm">Rôle</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">KYC</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashiers as $cashier)
                        <tr class="cashier-row">
                            <td class="font-mono text-xs">#{{ $cashier->id }}</td>
                            <td class="font-medium text-sm sm:text-base">{{ $cashier->name }}</td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $cashier->email }}
                            </td>
                            <td>
                                <span class="badge badge-cashier">Caissier</span>
                            </td>
                            <td>
                                @if($cashier->is_active)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-danger">Inactif</span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell">
                                @if($cashier->kyc_status == 'verified')
                                    <span class="badge badge-success">Vérifié</span>
                                @elseif($cashier->kyc_status == 'pending')
                                    <span class="badge badge-warning">En attente</span>
                                @else
                                    <span class="badge badge-danger">Non soumis</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.cashiers.show', $cashier->id) }}" 
                                       class="btn btn-primary btn-sm" title="Voir">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $cashier->id) }}" 
                                       class="btn btn-primary btn-sm" title="Modifier">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <!-- Bouton désactiver/activer avec modal -->
                                    <button type="button" 
                                            onclick="openToggleModal('{{ $cashier->id }}', '{{ $cashier->name }}', {{ $cashier->is_active ? 'true' : 'false' }})" 
                                            class="btn {{ $cashier->is_active ? 'btn-warning' : 'btn-success' }} btn-sm" 
                                            title="{{ $cashier->is_active ? 'Désactiver' : 'Activer' }}">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($cashier->is_active)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @endif
                                        </svg>
                                    </button>
                                    <!-- Bouton supprimer avec modal -->
                                    <button type="button" 
                                            onclick="openDeleteModal('{{ $cashier->id }}', '{{ $cashier->name }}')" 
                                            class="btn btn-danger btn-sm" 
                                            title="Supprimer">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucun caissier</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Créez votre premier caissier</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cashiers instanceof \Illuminate\Pagination\LengthAwarePaginator && $cashiers->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $cashiers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ============================================================
MODAL TOGGLE (ACTIVER/DÉSACTIVER)
============================================================ -->
<div id="toggleModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
            </svg>
        </div>
        <h3 class="modal-title" id="toggleModalTitle">Confirmer le changement de statut</h3>
        <p class="modal-text" id="toggleModalText">
            Êtes-vous sûr de vouloir <strong id="toggleActionText">désactiver</strong> le caissier 
            <strong id="toggleUserName"></strong> ?
            <br>
            <span id="toggleActionDescription" class="text-warning">
                Le caissier ne pourra plus effectuer de ventes.
            </span>
        </p>
        <form id="toggleForm" action="" method="POST" class="modal-actions">
            @csrf
            @method('POST')
            <button type="button" onclick="closeToggleModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="submit" class="btn btn-warning btn-sm" id="toggleSubmitBtn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
                </svg>
                <span id="toggleSubmitText">Désactiver</span>
            </button>
        </form>
    </div>
</div>

<!-- ============================================================
MODAL SUPPRIMER
============================================================ -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer la suppression</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">supprimer définitivement</strong> 
            le caissier <strong id="deleteUserName"></strong> ?
            <br>
            Cette action est <strong class="text-danger">irréversible</strong> et toutes les données associées seront perdues.
        </p>
        <form id="deleteForm" action="" method="POST" class="modal-actions">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="submit" class="btn btn-danger btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
// MODAL TOGGLE (ACTIVER/DÉSACTIVER)
// ============================================================
function openToggleModal(userId, userName, isActive) {
    const modal = document.getElementById('toggleModal');
    const actionText = document.getElementById('toggleActionText');
    const submitText = document.getElementById('toggleSubmitText');
    const submitBtn = document.getElementById('toggleSubmitBtn');
    const title = document.getElementById('toggleModalTitle');
    const description = document.getElementById('toggleActionDescription');
    const userNameEl = document.getElementById('toggleUserName');
    const form = document.getElementById('toggleForm');
    
    userNameEl.textContent = userName;
    form.action = '/admin/users/toggle-status/' + userId;
    
    if (isActive) {
        // Désactiver
        actionText.textContent = 'désactiver';
        submitText.textContent = 'Désactiver';
        submitBtn.className = 'btn btn-warning btn-sm';
        title.textContent = 'Confirmer la désactivation';
        description.innerHTML = 'Le caissier ne pourra plus effectuer de ventes.';
        description.className = 'text-warning';
        submitBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
            </svg>
            Désactiver
        `;
    } else {
        // Activer
        actionText.textContent = 'activer';
        submitText.textContent = 'Activer';
        submitBtn.className = 'btn btn-success btn-sm';
        title.textContent = 'Confirmer l\'activation';
        description.innerHTML = 'Le caissier pourra à nouveau effectuer des ventes.';
        description.className = 'text-success';
        submitBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Activer
        `;
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeToggleModal() {
    document.getElementById('toggleModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// MODAL SUPPRIMER
// ============================================================
function openDeleteModal(userId, userName) {
    const modal = document.getElementById('deleteModal');
    const userNameEl = document.getElementById('deleteUserName');
    const form = document.getElementById('deleteForm');
    
    userNameEl.textContent = userName;
    form.action = '/admin/users/' + userId;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
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