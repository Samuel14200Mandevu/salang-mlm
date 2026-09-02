{{-- resources/views/admin/kyc/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-navy: #0F2B4F;
    --primary-navy-dark: #091E3B;
    --bg-base: #F5F6F8;
    --bg-card: #FFFFFF;
    --bg-secondary: #EEF0F3;
    --bg-hover: #E8EAEE;
    --text-primary: #1A1A1E;
    --text-secondary: #4A4A52;
    --text-tertiary: #7A7A82;
    --border-color: #DCDEE3;
    --border-light: #E8EAEE;
    --success: #1F7B4D;
    --danger: #B32A2A;
    --warning: #A65A0E;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-base);
    color: var(--text-primary);
}

/* ===== CARTES ===== */
.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1.25rem;
    transition: border-color 0.15s ease;
}

.card-stats {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 0.875rem 1.125rem;
    transition: border-color 0.15s ease;
}
.card-stats:hover {
    border-color: var(--primary-navy);
}

/* ===== TABLE ===== */
.kyc-row {
    transition: background 0.1s ease;
}
.kyc-row:hover {
    background: var(--bg-hover);
}

.table-wrap { overflow-x: auto; }
.table { 
    width: 100%; 
    border-collapse: collapse; 
    font-size: 0.875rem; 
}
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
.table-striped tbody tr:nth-child(even) { 
    background: var(--bg-secondary); 
}

/* ===== BADGES ===== */
.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success {
    background: #E6F4EC;
    color: #1F7B4D;
    border-color: #B8DFCC;
}
.badge-danger {
    background: #FDE8E8;
    color: #B32A2A;
    border-color: #F5C8C8;
}
.badge-warning {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
}
.badge-info {
    background: #E8EDF5;
    color: var(--primary-navy);
    border-color: #C8D4E3;
}
.badge-secondary {
    background: #EEF0F3;
    color: var(--text-secondary);
    border-color: #DCDEE3;
}

/* ===== BOUTONS ===== */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-success {
    background: #1F7B4D;
    color: white;
    border-color: #1F7B4D;
}
.btn-success:hover {
    background: #16633D;
    border-color: #16633D;
}

.btn-danger {
    background: #B32A2A;
    color: white;
    border-color: #B32A2A;
}
.btn-danger:hover {
    background: #8F2121;
    border-color: #8F2121;
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

/* ===== INPUTS ===== */
.input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease;
    outline: none;
}
.input:focus {
    border-color: var(--primary-navy);
}
.input::placeholder {
    color: var(--text-tertiary);
}

/* ===== AVATAR ===== */
.avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
    background: var(--primary-navy);
    color: white;
    flex-shrink: 0;
}

/* ===== PREVIEW IMAGE ===== */
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
    border-color: var(--primary-navy);
}

/* ===== HEADER AVEC RECHERCHE À DROITE ===== */
.header-with-search {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.header-left {
    flex: 1 1 auto;
    min-width: 0;
}

.header-right {
    flex: 0 0 auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}

.search-wrapper {
    position: relative;
    min-width: 200px;
    max-width: 280px;
}

.search-wrapper .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-tertiary);
    pointer-events: none;
}

.search-wrapper .search-input {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease;
    outline: none;
}
.search-wrapper .search-input:focus {
    border-color: var(--primary-navy);
}
.search-wrapper .search-input::placeholder {
    color: var(--text-tertiary);
}

.search-wrapper .clear-btn {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-tertiary);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 50%;
    display: none;
    transition: background 0.15s ease;
}
.search-wrapper .clear-btn:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}
.search-wrapper .clear-btn.visible {
    display: block;
}

.filter-select {
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.875rem;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234A4A52' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    outline: none;
    transition: border-color 0.15s ease;
    min-width: 120px;
}
.filter-select:focus {
    border-color: var(--primary-navy);
}

.stats-mini {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    margin-top: 0.25rem;
}

/* ===== MODAL ===== */
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
    border-radius: 10px;
    padding: 1.75rem;
    max-width: 440px;
    width: 90%;
    border: 1px solid var(--border-color);
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
    background: #E6F4EC;
    color: #1F7B4D;
}
.modal-icon-danger {
    background: #FDE8E8;
    color: #B32A2A;
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
    color: #1F7B4D;
}
.modal-text .text-danger {
    color: #B32A2A;
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

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .header-with-search {
        flex-direction: column;
        align-items: stretch;
    }
    .header-right {
        flex-wrap: wrap;
    }
    .search-wrapper {
        flex: 1;
        min-width: 0;
        max-width: none;
    }
    .filter-select {
        flex: 1;
        min-width: 100px;
    }
    .stats-grid {
        grid-template-columns: 1fr 1fr !important;
    }
}

@media (max-width: 640px) {
    .table thead th, .table tbody td {
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.65rem;
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
    .card {
        padding: 0.875rem;
    }
    .card-stats {
        padding: 0.625rem 0.75rem;
    }
    .card-stats .text-lg {
        font-size: 1.125rem;
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
    .kyc-preview-img {
        width: 32px;
        height: 32px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
    .header-right {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-select {
        width: 100%;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- En-tête avec recherche à droite -->
    <div class="header-with-search animate-fadeInUp">
        <div class="header-left">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Vérification KYC</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $documents->total() ?? 0 }} documents
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
                @if(request('status') && request('status') !== 'all')
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Filtre: {{ ucfirst(request('status')) }}
                    </span>
                @endif
                @if(request('type'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Type: {{ str_replace('_', ' ', ucfirst(request('type'))) }}
                    </span>
                @endif
            </p>
            <div class="stats-mini">
                <span class="badge badge-warning">{{ $pendingCount ?? 0 }} En attente</span>
                <span class="badge badge-success">{{ $verifiedCount ?? 0 }} Vérifiés</span>
                <span class="badge badge-danger">{{ $rejectedCount ?? 0 }} Rejetés</span>
                <span class="badge badge-info">{{ $verifiedUsersCount ?? 0 }} Utilisateurs vérifiés</span>
            </div>
        </div>
        <div class="header-right">
            <div class="search-wrapper">
                <span class="search-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text"
                       id="searchInput"
                       placeholder="Rechercher un utilisateur"
                       class="search-input"
                       autocomplete="off"
                       value="{{ request('search') }}">
                <button type="button" id="clearBtn" class="clear-btn {{ request('search') ? 'visible' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <select id="statusFilter" class="filter-select text-sm">
                <option value="" {{ !request('status') ? 'selected' : '' }}>Tous statuts</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Vérifié</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
            </select>
            <select id="typeFilter" class="filter-select text-sm">
                <option value="" {{ !request('type') ? 'selected' : '' }}>Tous types</option>
                <option value="id_card" {{ request('type') == 'id_card' ? 'selected' : '' }}>Carte d'identité</option>
                <option value="passport" {{ request('type') == 'passport' ? 'selected' : '' }}>Passeport</option>
                <option value="proof_of_address" {{ request('type') == 'proof_of_address' ? 'selected' : '' }}>Justificatif domicile</option>
                <option value="selfie" {{ request('type') == 'selfie' ? 'selected' : '' }}>Selfie</option>
            </select>
            <button id="resetFilters" class="btn btn-outline btn-sm text-xs" title="Réinitialiser les filtres">
                Réinitialiser
            </button>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="p-3 sm:p-4 bg-[#E6F4EC] border border-[#B8DFCC] rounded-lg text-[#1F7B4D] text-sm flex items-center gap-2 animate-fadeInUp">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-[#FDE8E8] border border-[#F5C8C8] rounded-lg text-[#B32A2A] text-sm flex items-center gap-2 animate-fadeInUp">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistiques -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp" style="animation-delay: 0.05s;">
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-xl sm:text-2xl font-bold text-[#A65A0E]">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Vérifiés</p>
            <p class="text-xl sm:text-2xl font-bold text-[#1F7B4D]">{{ $verifiedCount ?? 0 }}</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Rejetés</p>
            <p class="text-xl sm:text-2xl font-bold text-[#B32A2A]">{{ $rejectedCount ?? 0 }}</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Utilisateurs vérifiés</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--primary-navy)]">{{ $verifiedUsersCount ?? 0 }}</p>
        </div>
    </div>

    <!-- Liste des documents KYC -->
    <div class="card p-3 sm:p-4 animate-fadeInUp" style="animation-delay: 0.1s;">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th class="hidden sm:table-cell">Type</th>
                        <th>Document</th>
                        <th class="hidden md:table-cell">Date</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="kycTable">
                    @forelse($documents as $doc)
                        <tr class="kyc-row">
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
                                @php
                                    $statusLabels = [
                                        'pending' => 'En attente',
                                        'verified' => 'Vérifié',
                                        'rejected' => 'Rejeté',
                                    ];
                                    $statusClasses = [
                                        'pending' => 'badge-warning',
                                        'verified' => 'badge-success',
                                        'rejected' => 'badge-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$doc->status] ?? 'badge-warning' }}">
                                    {{ $statusLabels[$doc->status] ?? ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if($doc->status == 'pending')
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                                onclick="openVerifyModal('{{ $doc->id }}', '{{ $doc->user?->name ?? 'Utilisateur' }}', '{{ $doc->document_type_label ?? ucfirst(str_replace('_', ' ', $doc->document_type ?? 'Document')) }}')"
                                                class="btn btn-success btn-sm btn-icon"
                                                title="Vérifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                        <button type="button"
                                                onclick="openRejectModal('{{ $doc->id }}', '{{ $doc->user?->name ?? 'Utilisateur' }}', '{{ $doc->document_type_label ?? ucfirst(str_replace('_', ' ', $doc->document_type ?? 'Document')) }}')"
                                                class="btn btn-danger btn-sm btn-icon"
                                                title="Rejeter">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucun document KYC</p>
                                <p class="text-sm text-[var(--text-tertiary)]">
                                    @if(request('search') || request('status') || request('type'))
                                        Aucun résultat pour ces critères
                                    @else
                                        Aucun document n'a été soumis pour le moment
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="mt-3 sm:mt-4" id="paginationContainer">
                {{ $documents->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Vérification -->
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

<!-- Modal Rejet -->
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
    const resetBtn = document.getElementById('resetFilters');
    let searchTimeout;

    // Recherche avec debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            clearBtn.classList.toggle('visible', query.length > 0);
            
            searchTimeout = setTimeout(() => {
                fetchKycData(query, statusFilter.value, typeFilter.value);
            }, 300);
        });

        // Raccourci ESC
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                clearBtn.classList.remove('visible');
                fetchKycData('', statusFilter.value, typeFilter.value);
                this.blur();
            }
        });
    }

    // Filtres
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            fetchKycData(searchInput.value.trim(), this.value, typeFilter.value);
        });
    }

    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            fetchKycData(searchInput.value.trim(), statusFilter.value, this.value);
        });
    }

    // Réinitialisation
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (typeFilter) typeFilter.value = '';
            if (clearBtn) clearBtn.classList.remove('visible');
            fetchKycData('', '', '');
        });
    }

    // Bouton clear
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.classList.remove('visible');
            fetchKycData('', statusFilter.value, typeFilter.value);
            searchInput.focus();
        });
    }

    function fetchKycData(search, status, type) {
        const url = new URL(window.location.href);
        
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        
        if (type) {
            url.searchParams.set('type', type);
        } else {
            url.searchParams.delete('type');
        }
        
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('kycTable');
        if (!tableBody) return;

        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-[var(--primary-navy)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Recherche en cours…</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newTableBody = doc.getElementById('kycTable');
            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            const newPagination = doc.getElementById('paginationContainer');
            const paginationContainer = document.getElementById('paginationContainer');
            if (newPagination && paginationContainer) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            }

            // Mise à jour du titre et du compte
            const subtitle = document.querySelector('.text-sm.text-\\[var\\(--text-secondary\\)\\]');
            if (subtitle) {
                const totalMatch = html.match(/(\d+)\s+documents?/);
                if (totalMatch) {
                    let text = totalMatch[0];
                    if (search) {
                        text += ' <span class="text-xs text-[var(--text-tertiary)] ml-2">· Résultats pour "' + search + '"</span>';
                    }
                    if (status) {
                        text += ' <span class="text-xs text-[var(--text-tertiary)] ml-2">· Filtre: ' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
                    }
                    if (type) {
                        text += ' <span class="text-xs text-[var(--text-tertiary)] ml-2">· Type: ' + type.replace('_', ' ') + '</span>';
                    }
                    subtitle.innerHTML = text;
                }
            }

            // Mise à jour des statistiques
            const statValues = doc.querySelectorAll('.card-stats .text-xl');
            const currentStats = document.querySelectorAll('.card-stats .text-xl');
            if (statValues.length === currentStats.length && statValues.length > 0) {
                statValues.forEach((stat, index) => {
                    if (currentStats[index]) {
                        currentStats[index].textContent = stat.textContent;
                    }
                });
            }

            // Mise à jour des mini-badges
            const miniBadges = doc.querySelectorAll('.stats-mini .badge');
            const currentMiniBadges = document.querySelectorAll('.stats-mini .badge');
            if (miniBadges.length === currentMiniBadges.length && miniBadges.length > 0) {
                miniBadges.forEach((badge, index) => {
                    if (currentMiniBadges[index]) {
                        currentMiniBadges[index].textContent = badge.textContent;
                    }
                });
            }
        })
        .catch(error => {
            console.error('Erreur de recherche:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-8 text-[#B32A2A]">
                        Une erreur est survenue lors de la recherche
                    </td>
                </tr>
            `;
        });
    }
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