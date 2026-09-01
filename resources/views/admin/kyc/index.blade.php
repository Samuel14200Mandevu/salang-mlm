{{-- resources/views/admin/kyc/index.blade.php --}}
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

.kyc-row {
    transition: background 0.15s ease;
}
.kyc-row:hover {
    background: var(--bg-hover);
}

.kyc-preview-img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.15s ease;
    border: 1px solid var(--border-color);
}
.kyc-preview-img:hover {
    transform: scale(1.05);
    border-color: var(--primary-blue);
}

.card-stats {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.card-stats:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
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
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-info { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }
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

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
    background: var(--primary-blue);
    color: white;
    flex-shrink: 0;
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
.table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }

/* ===== HEADER SEARCH ===== */
.header-search {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-search .search-wrapper {
    position: relative;
}

.header-search .search-wrapper .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

.header-search .search-wrapper .search-input {
    padding: 0.375rem 0.75rem 0.375rem 2.25rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.813rem;
    width: 220px;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, width 0.2s ease;
    outline: none;
}

.header-search .search-wrapper .search-input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
    width: 280px;
}

.header-search .search-wrapper .search-input::placeholder {
    color: var(--text-muted);
}

.header-search .search-wrapper .clear-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 50%;
    display: none;
    transition: background 0.15s ease;
}
.header-search .search-wrapper .clear-btn:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}
.header-search .search-wrapper .clear-btn.visible {
    display: block;
}

.stats-mini {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

/* Modal */
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
.modal-text strong {
    color: var(--text-primary);
}
.modal-text .text-success {
    color: #1C7E4A;
}
.modal-text .text-danger {
    color: #B91C1C;
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
.delay-4 { animation-delay: 0.2s; }
.delay-5 { animation-delay: 0.25s; }
.delay-6 { animation-delay: 0.3s; }

@media (max-width: 640px) {
    .modal-box { padding: 1.25rem; }
    .modal-actions { flex-direction: column; }
    .modal-actions .btn { width: 100%; }
    .kyc-preview-img {
        width: 32px;
        height: 32px;
    }
    .table thead th, .table tbody td {
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.65rem;
    }
    .btn-sm svg {
        width: 0.875rem;
        height: 0.875rem;
    }
    .badge {
        font-size: 0.6rem;
        padding: 0.125rem 0.5rem;
    }
    .avatar {
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.6rem;
    }
    .header-search {
        width: 100%;
    }
    .header-search .search-wrapper {
        flex: 1;
    }
    .header-search .search-wrapper .search-input {
        width: 100%;
    }
    .header-search .search-wrapper .search-input:focus {
        width: 100%;
    }
    .card {
        padding: 0.875rem;
    }
    .grid-cols-4 {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .grid-cols-4 {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header with Search -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Vérification KYC</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Gérer les demandes de vérification d'identité</p>
            <div class="stats-mini mt-1">
                <span class="badge badge-warning text-[10px] sm:text-xs">{{ $pendingCount ?? 0 }} En attente</span>
                <span class="badge badge-success text-[10px] sm:text-xs">{{ $verifiedCount ?? 0 }} Vérifiés</span>
                <span class="badge badge-danger text-[10px] sm:text-xs">{{ $rejectedCount ?? 0 }} Rejetés</span>
                <span class="badge badge-info text-[10px] sm:text-xs">{{ $verifiedUsersCount ?? 0 }} Utilisateurs vérifiés</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="header-search">
                <div class="search-wrapper">
                    <span class="search-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Rechercher un utilisateur..."
                           autocomplete="off">
                    <button type="button" id="clearBtn" class="clear-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <select id="statusFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm" style="padding-left: 0.75rem;">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="verified">Vérifié</option>
                <option value="rejected">Rejeté</option>
            </select>
            <select id="typeFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm" style="padding-left: 0.75rem;">
                <option value="">Tous les types</option>
                <option value="id_card">Carte d'identité</option>
                <option value="passport">Passeport</option>
                <option value="proof_of_address">Justificatif de domicile</option>
                <option value="selfie">Selfie</option>
            </select>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-[#B54708]">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl font-bold text-[#B54708]">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#1C7E4A] animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Vérifiés</p>
            <p class="text-lg sm:text-xl font-bold text-[#1C7E4A]">{{ $verifiedCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#B91C1C] animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Rejetés</p>
            <p class="text-lg sm:text-xl font-bold text-[#B91C1C]">{{ $rejectedCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[var(--primary-blue)] animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Utilisateurs vérifiés</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">{{ $verifiedUsersCount ?? 0 }}</p>
        </div>
    </div>

    <!-- KYC List -->
    <div class="card animate-fadeInUp delay-5 p-3 sm:p-4">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm">Document</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Date</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="kycTable">
                    @forelse($documents ?? [] as $doc)
                        <tr class="kyc-row"
                            data-status="{{ $doc->status }}"
                            data-type="{{ $doc->document_type ?? '' }}"
                            data-user="{{ strtolower($doc->user?->name ?? '') }}">
                            <td>
                                <div class="flex items-center gap-1.5 sm:gap-2">
                                    <div class="avatar">
                                        {{ substr($doc->user?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-[var(--text-primary)] text-xs sm:text-sm truncate max-w-[60px] sm:max-w-[100px] md:max-w-none">
                                            {{ $doc->user?->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate max-w-[60px] sm:max-w-[100px] md:max-w-none">
                                            {{ $doc->user?->email ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">
                                    {{ $doc->document_type_label ?? ucfirst(str_replace('_', ' ', $doc->document_type ?? 'Document')) }}
                                </span>
                            </td>
                            <td>
                                @if($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="inline-block">
                                        @if(str_starts_with($doc->mime_type ?? '', 'image/'))
                                            <img src="{{ asset('storage/' . $doc->file_path) }}"
                                                 alt="{{ $doc->file_name }}"
                                                 class="kyc-preview-img">
                                        @else
                                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-[var(--text-secondary)] text-xs sm:text-sm">N/A</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $doc->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="badge {{ $doc->status == 'pending' ? 'badge-warning' : ($doc->status == 'verified' ? 'badge-success' : 'badge-danger') }} text-[10px] sm:text-xs">
                                    {{ $doc->status == 'pending' ? 'En attente' : ($doc->status == 'verified' ? 'Vérifié' : 'Rejeté') }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if($doc->status == 'pending')
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                                onclick="openVerifyModal('{{ $doc->id }}', '{{ $doc->user?->name ?? 'Utilisateur' }}', '{{ $doc->document_type_label ?? 'Document' }}')"
                                                class="btn btn-success btn-sm btn-icon"
                                                title="Vérifier">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                        <button type="button"
                                                onclick="openRejectModal('{{ $doc->id }}', '{{ $doc->user?->name ?? 'Utilisateur' }}', '{{ $doc->document_type_label ?? 'Document' }}')"
                                                class="btn btn-danger btn-sm btn-icon"
                                                title="Rejeter">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-[var(--text-secondary)]">
                                        {{ $doc->status == 'verified' ? 'Traité' : 'Rejeté' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucun document KYC</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Aucun document n'a été soumis pour le moment</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($documents) && $documents->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $documents->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Verify Modal -->
<div id="verifyModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-success">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer la vérification</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-success">vérifier</strong> le document
            <strong id="verifyDocType"></strong> de <strong id="verifyUserName"></strong> ?
            <br>
            Une fois vérifié, ce document sera validé définitivement.
        </p>
        <form id="verifyForm" action="" method="POST" class="modal-actions">
            @csrf
            <input type="hidden" name="status" value="verified">
            <button type="button" onclick="closeVerifyModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="submit" class="btn btn-success btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Vérifier
            </button>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer le rejet</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">rejeter</strong> le document
            <strong id="rejectDocType"></strong> de <strong id="rejectUserName"></strong> ?
            <br>
            L'utilisateur devra soumettre un nouveau document.
        </p>
        <form id="rejectForm" action="" method="POST" class="modal-actions">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <button type="button" onclick="closeRejectModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="submit" class="btn btn-danger btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Rejeter
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const rows = document.querySelectorAll('#kycTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;
        const type = typeFilter.value;

        let visibleCount = 0;

        rows.forEach(function(row) {
            // Ignorer la ligne "no results"
            if (row.id === 'noResultsRow') return;

            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status || '';
            const rowType = row.dataset.type || '';

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (type && rowType !== type) show = false;

            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        // Gérer l'affichage "aucun résultat"
        const noResultsRow = document.getElementById('noResultsRow');
        if (noResultsRow) {
            if (visibleCount === 0 && (search || status || type)) {
                noResultsRow.style.display = '';
            } else {
                noResultsRow.style.display = 'none';
            }
        }

        clearBtn.classList.toggle('visible', search.length > 0);
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
    typeFilter.addEventListener('change', filterRows);

    // Bouton clear
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.classList.remove('visible');
        filterRows();
        searchInput.focus();
    });

    // Raccourci ESC pour effacer
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            clearBtn.classList.remove('visible');
            filterRows();
            this.blur();
        }
    });

    // Raccourci / pour focus sur la recherche
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            const active = document.activeElement;
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) {
                return;
            }
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });
});

// ============================================================
// MODAL DE VÉRIFICATION
// ============================================================
function openVerifyModal(docId, userName, docType) {
    document.getElementById('verifyModal').classList.add('active');
    document.getElementById('verifyUserName').textContent = userName;
    document.getElementById('verifyDocType').textContent = docType || 'Document';
    document.getElementById('verifyForm').action = '/admin/kyc/' + docId + '/verify';
    document.body.style.overflow = 'hidden';
}

function closeVerifyModal() {
    document.getElementById('verifyModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================================
// MODAL DE REJET
// ============================================================
function openRejectModal(docId, userName, docType) {
    document.getElementById('rejectModal').classList.add('active');
    document.getElementById('rejectUserName').textContent = userName;
    document.getElementById('rejectDocType').textContent = docType || 'Document';
    document.getElementById('rejectForm').action = '/admin/kyc/' + docId + '/reject';
    document.body.style.overflow = 'hidden';
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
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