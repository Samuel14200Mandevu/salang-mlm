{{-- resources/views/cashier/consultations/index.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem 1.25rem;
        transition: background 0.2s ease;
    }

    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-card .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
    }

    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-total { background: rgba(30, 93, 173, 0.10); color: var(--primary); }
    .stat-icon-pending { background: rgba(245, 158, 11, 0.10); color: #f59e0b; }
    .stat-icon-processing { background: rgba(59, 130, 246, 0.10); color: #3b82f6; }
    .stat-icon-completed { background: rgba(34, 197, 94, 0.10); color: #22c55e; }

    .consultation-row {
        transition: background 0.2s ease;
        cursor: pointer;
    }
    .consultation-row:hover {
        background: var(--bg-secondary);
    }
    .consultation-row .row-actions {
        cursor: default;
    }

    .header-search .search-wrapper {
        position: relative;
    }
    .header-search .search-wrapper .search-input {
        padding: 0.375rem 0.75rem 0.375rem 2.25rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.813rem;
        width: 220px;
        max-width: 100%;
        transition: border-color 0.15s ease, width 0.2s ease;
        outline: none;
    }
    .header-search .search-wrapper .search-input:focus {
        border-color: var(--primary);
        width: 280px;
    }
    .header-search .search-wrapper .search-input::placeholder {
        color: var(--text-tertiary);
    }
    @media (max-width: 767px) {
        .header-search .search-wrapper .search-input,
        .header-search .search-wrapper .search-input:focus {
            width: 100%;
        }
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.813rem;
    }

    .table thead th {
        padding: 0.6rem 0.75rem;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody td {
        padding: 0.6rem 0.75rem;
        color: var(--text-secondary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover td {
        background: var(--bg-secondary);
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

    /* ===== BOUTONS AMÉLIORÉS ===== */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease, transform 0.15s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: var(--primary-hover, #134178);
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

    .btn-sm {
        padding: 0.3rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
    }

    .btn-block {
        display: flex;
        width: 100%;
    }

    .alert {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.20);
        color: #16a34a;
    }

    .alert-warning {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.20);
        color: #d97706;
    }

    @media (max-width: 767px) {
        .consultations-index .admin-page-header-actions .btn-primary {
            flex: 1 1 100%;
            min-height: 2.75rem;
            border-radius: 9999px;
        }

        .consultations-index .admin-mobile-table tbody tr:has(td[colspan]) {
            display: block;
            padding: 0;
            border: none;
            background: transparent;
            box-shadow: none;
        }

        .consultations-index .admin-mobile-table tbody tr:has(td[colspan]) td {
            display: none !important;
        }
    }

    @media (max-width: 640px) {
        .table thead th, .table tbody td { padding: 0.4rem 0.5rem; font-size: 0.7rem; }
        .card { padding: 0.875rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
    }
</style>
@endpush

@section('title', 'Mes Consultations')

@section('content')
@php
    $consultationsSubtitle = ($stats['total'] ?? 0) . ' fiches · créez ou suivez vos dossiers';
@endphp
<div class="consultations-index space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE : titre + Nouvelle consultation dans le div --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Consultations</h1>
                <p class="page-subtitle">{{ $consultationsSubtitle }}</p>
            </div>
            <div class="admin-title-banner__tools admin-title-banner__tools--consultations-index flex items-center gap-2">
                <a href="{{ route('cashier.consultations.create') }}"
                   class="btn btn-primary btn-sm btn-round-icon"
                   title="Nouvelle consultation"
                   aria-label="Nouvelle consultation">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            </div>
        </div>
        <div class="mt-3 header-search">
            <div class="search-wrapper">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       class="search-input consultation-search-input"
                       placeholder="Code ID ou N° dossier"
                       autocomplete="off"
                       value="{{ request('search') }}">
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Mes Consultations
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5" id="consultationsSubtitle">
                Gérez vos fiches de consultation
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">· Résultats pour "{{ request('search') }}"</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end">
            <div class="header-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInput"
                           class="search-input consultation-search-input"
                           placeholder="Code ID ou N° dossier"
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </div>
            <a href="{{ route('cashier.consultations.create') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nouvelle consultation</span>
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('warning') }}
        </div>
    @endif

    {{-- STATISTIQUES --}}
    <div class="admin-kpi-rail grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary)]">{{ $stats['total'] ?? 0 }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--color-accent-600)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--color-accent-600)]">{{ $stats['pending'] ?? 0 }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-info)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-info)]">{{ $stats['processing'] ?? 0 }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-success)]">{{ $stats['completed'] ?? 0 }}</p>
        </div>
    </div>

    @if($consultations->isEmpty())
        <div class="admin-empty-state md:hidden">
            <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="font-medium text-[var(--text-primary)]">Aucune consultation</p>
            <p class="text-sm mt-1">Créez votre première fiche de consultation.</p>
        </div>
    @endif

    {{-- LISTE --}}
    <div class="card {{ $consultations->isEmpty() && ! request('search') ? 'hidden md:block' : '' }}" id="consultationsListCard">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° dossier</th>
                        <th>Code ID</th>
                        <th>Patient</th>
                        <th>Date de l'examen</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="consultationsTableBody">
                    @forelse($consultations as $consultation)
                        <tr class="consultation-row"
                            data-href="{{ route('cashier.consultations.show', $consultation) }}"
                            tabindex="0"
                            role="link"
                            aria-label="Voir la consultation {{ $consultation->id }}">
                            <td class="text-xs sm:text-sm font-mono text-[var(--primary)]">{{ $consultation->numero ?? 'N/A' }}</td>
                            <td class="text-xs sm:text-sm font-mono">{{ $consultation->code_id ?? 'N/A' }}</td>
                            <td class="text-sm">{{ $consultation->nom_complet }}</td>
                            <td class="text-[var(--text-tertiary)] text-xs sm:text-sm">
                                {{ $consultation->date_examen ? $consultation->date_examen->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending' => ['label' => 'En attente', 'class' => 'badge-warning'],
                                        'processing' => ['label' => 'En traitement', 'class' => 'badge-info'],
                                        'completed' => ['label' => 'Terminé', 'class' => 'badge-success'],
                                        'cancelled' => ['label' => 'Annulé', 'class' => 'badge-danger'],
                                    ];
                                    $status = $statusMap[$consultation->status] ?? ['label' => ucfirst($consultation->status), 'class' => 'badge-secondary'];
                                @endphp
                                <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 sm:py-8 text-[var(--text-tertiary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-base font-medium">Aucune consultation</p>
                                <p class="text-sm text-[var(--text-tertiary)]">
                                    @if(request('search'))
                                        Aucun résultat pour « {{ request('search') }} »
                                    @else
                                        Commencez par créer une nouvelle fiche
                                    @endif
                                </p>
                                @unless(request('search'))
                                <a href="{{ route('cashier.consultations.create') }}" class="btn btn-primary mt-3 hidden md:inline-flex">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Nouvelle consultation
                                </a>
                                @endunless
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 sm:mt-4" id="consultationsPagination">
            @if($consultations->hasPages())
                <x-salang-pagination :paginator="$consultations" />
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('consultationsTableBody');
    const searchInputs = document.querySelectorAll('.consultation-search-input');
    let searchTimeout;

    if (tbody) {
        tbody.addEventListener('click', function (e) {
            const row = e.target.closest('tr.consultation-row[data-href]');
            if (!row || e.target.closest('.row-actions, a, button, form')) return;
            window.location.href = row.dataset.href;
        });

        tbody.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            const row = e.target.closest('tr.consultation-row[data-href]');
            if (!row) return;
            e.preventDefault();
            window.location.href = row.dataset.href;
        });
    }

    function syncSearchInputs(value) {
        searchInputs.forEach(function (input) {
            if (input.value !== value) input.value = value;
        });
    }

    function fetchConsultations(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');

        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-8 text-[var(--text-secondary)]">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-[var(--primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Recherche en cours…</span>
                        </div>
                    </td>
                </tr>
            `;
        }

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (response) { return response.text(); })
        .then(function (html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newBody = doc.getElementById('consultationsTableBody');
            if (tbody && newBody) {
                tbody.innerHTML = newBody.innerHTML;
            }

            const pagination = document.getElementById('consultationsPagination');
            const newPagination = doc.getElementById('consultationsPagination');
            if (pagination) {
                pagination.innerHTML = newPagination ? newPagination.innerHTML : '';
            }

            const subtitle = document.getElementById('consultationsSubtitle');
            const newSubtitle = doc.getElementById('consultationsSubtitle');
            if (subtitle && newSubtitle) {
                subtitle.innerHTML = newSubtitle.innerHTML;
            }

            const listCard = document.getElementById('consultationsListCard');
            if (listCard) {
                listCard.classList.remove('hidden');
            }

            if (typeof window.initSalangPagination === 'function') {
                window.initSalangPagination(document);
            }

            history.replaceState({}, '', url.toString());
        })
        .catch(function (error) {
            console.error('Erreur:', error);
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-8 text-[var(--ui-stat-danger)]">
                            Une erreur est survenue lors de la recherche
                        </td>
                    </tr>
                `;
            }
        });
    }

    searchInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            syncSearchInputs(this.value);
            searchTimeout = setTimeout(function () {
                fetchConsultations(query);
            }, 300);
        });
    });
});
</script>
@endpush