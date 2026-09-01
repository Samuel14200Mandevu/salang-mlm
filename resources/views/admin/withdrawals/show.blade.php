{{-- resources/views/admin/withdrawals/show.blade.php --}}
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

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-light);
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-row .label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-tertiary);
}
.detail-row .value {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
    text-align: right;
    word-break: break-word;
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

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
.badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

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

.btn-info {
    background: #065F9C;
    color: white;
    border-color: #065F9C;
}
.btn-info:hover {
    background: #054B7A;
    border-color: #054B7A;
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

.input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    outline: none;
}
.input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}
.input::placeholder {
    color: var(--text-muted);
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active {
    display: flex;
}
.modal-box {
    background: var(--bg-card);
    border-radius: 12px;
    padding: 1.75rem;
    max-width: 440px;
    width: 90%;
    border: 1px solid var(--border-color);
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}
.modal-title {
    text-align: center;
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.375rem;
}
.modal-text {
    text-align: center;
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 1.25rem;
    line-height: 1.6;
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

@media (max-width: 640px) {
    .card {
        padding: 0.875rem;
    }
    .badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
    }
    .btn {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    .detail-row {
        flex-direction: column;
        gap: 0.125rem;
        padding: 0.375rem 0;
    }
    .detail-row .value {
        text-align: left;
    }
    .action-buttons {
        flex-direction: column;
    }
    .action-buttons .btn {
        width: 100%;
    }
    .modal-box {
        padding: 1.25rem;
    }
    .modal-actions {
        flex-direction: column;
    }
    .modal-actions .btn {
        width: 100%;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .detail-grid {
        grid-template-columns: 1fr !important;
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
                Retrait #{{ $withdrawal->id }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Détails complets de la demande de retrait
            </p>
        </div>
        <a href="{{ route('admin.withdrawals') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-1">

        <!-- General Information -->
        <div class="card p-3 sm:p-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 border-b border-[var(--border-color)] pb-2">
                Informations générales
            </h3>

            <div class="space-y-1">
                <div class="detail-row">
                    <span class="label">ID</span>
                    <span class="value font-mono">#{{ $withdrawal->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Utilisateur</span>
                    <span class="value font-medium">{{ $withdrawal->user?->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Email</span>
                    <span class="value text-sm">{{ $withdrawal->user?->email ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Montant</span>
                    <span class="value font-bold text-[#1C7E4A]">${{ number_format($withdrawal->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Frais (2.5%)</span>
                    <span class="value text-[#B91C1C]">${{ number_format($withdrawal->fee, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Montant net</span>
                    <span class="value font-bold text-[var(--primary-blue)]">${{ number_format($withdrawal->net_amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Méthode</span>
                    <span class="value">
                        <span class="badge badge-info text-[10px] sm:text-xs">{{ ucfirst($withdrawal->method) }}</span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">Statut</span>
                    <span class="value">
                        <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : ($withdrawal->status == 'processing' ? 'badge-info' : 'badge-danger')) }} text-[10px] sm:text-xs">
                            {{ ucfirst($withdrawal->status) }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">Créé le</span>
                    <span class="value text-sm">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($withdrawal->completed_at)
                <div class="detail-row">
                    <span class="label">Terminé le</span>
                    <span class="value text-sm">{{ $withdrawal->completed_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Payment Details -->
        <div class="card p-3 sm:p-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 border-b border-[var(--border-color)] pb-2">
                Détails du paiement
            </h3>

            <div class="space-y-1">
                @if($withdrawal->payment_address)
                <div class="detail-row">
                    <span class="label">Adresse de paiement</span>
                    <span class="value font-mono text-xs break-all">{{ $withdrawal->payment_address }}</span>
                </div>
                @endif

                @if($withdrawal->phone_number)
                <div class="detail-row">
                    <span class="label">Numéro de téléphone</span>
                    <span class="value">{{ $withdrawal->phone_number }}</span>
                </div>
                @endif

                @if($withdrawal->bank_details)
                <div class="detail-row">
                    <span class="label">Détails bancaires</span>
                    <span class="value text-sm whitespace-pre-line">{{ $withdrawal->bank_details }}</span>
                </div>
                @endif

                @if($withdrawal->notes)
                <div class="detail-row">
                    <span class="label">Notes</span>
                    <span class="value text-sm">{{ $withdrawal->notes }}</span>
                </div>
                @endif

                @if($withdrawal->status == 'pending' || $withdrawal->status == 'processing')
                <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
                    <div class="action-buttons flex flex-col sm:flex-row gap-2 sm:gap-3">
                        @if($withdrawal->status == 'pending')
                        <form action="{{ route('admin.withdrawals.process', $withdrawal->id) }}" method="POST" class="w-full sm:flex-1">
                            @csrf
                            <button type="submit" class="btn btn-info w-full text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                Mettre en traitement
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" class="w-full sm:flex-1">
                            @csrf
                            <button type="submit" class="btn btn-success w-full text-sm"
                                    onclick="return confirm('Approuver ce retrait ?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approuver
                            </button>
                        </form>
                        <button onclick="showRejectModal('{{ $withdrawal->id }}')"
                                class="btn btn-danger w-full sm:flex-1 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rejeter
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <div class="text-center">
            <svg class="w-12 h-12 mx-auto text-[#B91C1C] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="modal-title">Rejeter le retrait</h3>
            <p class="modal-text">
                Veuillez indiquer la raison du rejet.
            </p>
        </div>

        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                    Motif du rejet *
                </label>
                <textarea name="reason" rows="3" class="input w-full text-sm" placeholder="Raison du rejet..." required></textarea>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-danger flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
                <button type="button" onclick="closeRejectModal()" class="btn btn-outline flex-1">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal(withdrawalId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '{{ route("admin.withdrawals.reject", ["id" => ":id"]) }}'.replace(':id', withdrawalId);
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRejectModal();
});
</script>
@endpush
@endsection