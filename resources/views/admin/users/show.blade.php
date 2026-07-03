@extends('admin.layouts.app')

@push('styles')
<style>
    /* Modal styles */
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
    
    @media (max-width: 640px) {
        .modal-box { padding: 1.5rem; }
        .modal-actions { flex-direction: column; }
        .modal-actions .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6">
    
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Details Utilisateur</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden xs:inline">Retour</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    <!-- Informations -->
    <div class="card p-3 sm:p-4 md:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Nom complet</p>
                <p class="font-semibold text-sm sm:text-base">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Email</p>
                <p class="font-semibold text-sm sm:text-base">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Telephone</p>
                <p class="font-semibold text-sm sm:text-base">{{ $user->phone ?? 'Non renseigne' }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Statut</p>
                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Package</p>
                <p class="font-semibold text-sm sm:text-base">{{ $user->package?->name ?? 'Aucun' }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Parrain</p>
                <p class="font-semibold text-sm sm:text-base">{{ $user->sponsor?->name ?? 'Aucun' }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Inscrit le</p>
                <p class="font-semibold text-sm sm:text-base">{{ $user->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Filleuls</p>
                <p class="font-semibold text-sm sm:text-base">{{ $downlinesCount ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Commissions</p>
                <p class="font-semibold text-sm sm:text-base">{{ $commissionsCount ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Total commissions</p>
                <p class="font-semibold text-sm sm:text-base">${{ number_format($totalCommissions ?? 0, 2) }}</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-[var(--border-color)] flex flex-wrap gap-2 sm:gap-3">
            
            <!-- Activer / Désactiver -->
            @if($user->is_active)
                <button type="button" 
                        onclick="openDeactivateModal()" 
                        class="btn btn-warning btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Désactiver
                </button>
            @else
                <button type="button" 
                        onclick="openActivateModal()" 
                        class="btn btn-success btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Activer
                </button>
            @endif

            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>

            <!-- Bouton Supprimer -->
            <button type="button" 
                    onclick="openDeleteModal()" 
                    class="btn btn-danger btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
MODAL DE CONFIRMATION DE DÉSACTIVATION
============================================================ -->
<div id="deactivateModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer la désactivation</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-warning">désactiver</strong> le compte de <strong>{{ $user->name }}</strong> ?
            <br>
            L'utilisateur ne pourra plus se connecter jusqu'à sa réactivation.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeDeactivateModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <a href="{{ route('admin.users.toggle-status', $user->id) }}" class="btn btn-warning btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Désactiver
            </a>
        </div>
    </div>
</div>

<!-- ============================================================
MODAL DE CONFIRMATION D'ACTIVATION
============================================================ -->
<div id="activateModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-success" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer l'activation</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong style="color: #22c55e;">activer</strong> le compte de <strong>{{ $user->name }}</strong> ?
            <br>
            L'utilisateur pourra à nouveau se connecter à la plateforme.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeActivateModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <a href="{{ route('admin.users.toggle-status', $user->id) }}" class="btn btn-success btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Activer
            </a>
        </div>
    </div>
</div>

<!-- ============================================================
MODAL DE CONFIRMATION DE SUPPRESSION
============================================================ -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer la suppression</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">supprimer</strong> définitivement <strong>{{ $user->name }}</strong> ?
            <br>
            Cette action est <strong class="text-danger">irréversible</strong> et toutes les données liées à cet utilisateur seront perdues.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
// DÉSACTIVATION
// ============================================================
function openDeactivateModal() {
    document.getElementById('deactivateModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeactivateModal() {
    document.getElementById('deactivateModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// ACTIVATION
// ============================================================
function openActivateModal() {
    document.getElementById('activateModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeActivateModal() {
    document.getElementById('activateModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// SUPPRESSION
// ============================================================
function openDeleteModal() {
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// FERMETURE PAR CLIC EXTERNE
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
// FERMETURE PAR TOUCHE ESCAPE
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