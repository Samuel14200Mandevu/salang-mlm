{{-- resources/views/admin/consultations/index.blade.php --}}
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

/* ===== STATS ===== */
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
.consultation-row {
    transition: background 0.1s ease;
}
.consultation-row:hover {
    background: var(--bg-hover);
}
.consultation-row-pending {
    background: rgba(166, 90, 14, 0.04);
}
.consultation-row-pending:hover {
    background: rgba(166, 90, 14, 0.08);
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

.btn-primary {
    background: var(--primary-navy);
    color: white;
    border-color: var(--primary-navy);
}
.btn-primary:hover {
    background: var(--primary-navy-dark);
    border-color: var(--primary-navy-dark);
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
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
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

/* ===== FILTRES ===== */
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
}
.filter-select:focus {
    border-color: var(--primary-navy);
}
.filter-select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ===== HEADER AVEC RECHERCHE À DROITE ===== */
.header-with-search {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
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
    align-items: center;
    gap: 0.75rem;
}

.search-wrapper {
    position: relative;
    min-width: 200px;
    max-width: 300px;
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
        min-width: 120px;
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
    .card {
        padding: 0.875rem;
    }
    .card-stats {
        padding: 0.625rem 0.75rem;
    }
    .card-stats .text-xl {
        font-size: 1.125rem;
    }
    .header-right {
        flex-direction: column;
        align-items: stretch;
    }
    .search-wrapper {
        max-width: none;
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
}
</style>
@endpush

@section('title', 'Consultations')

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- En-tête avec recherche à droite -->
    <div class="header-with-search">
        <div class="header-left">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Fiches de consultation</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $consultations->total() }} consultations
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
            </p>
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
                       placeholder="Rechercher patient, motif ou ID"
                       class="search-input"
                       value="{{ request('search') }}">
            </div>
            <select id="statusFilter" class="filter-select text-sm">
                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Tous</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En traitement</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminées</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulées</option>
            </select>
            <button id="resetFilters" class="btn btn-outline btn-sm text-xs" title="Réinitialiser les filtres">
                Réinitialiser
            </button>
            <span class="px-3 py-1 bg-[#FEF1E6] border border-[#FADCB8] rounded-lg text-[#A65A0E] text-sm font-medium whitespace-nowrap">
                {{ $pendingCount ?? 0 }} en attente
            </span>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="p-3 sm:p-4 bg-[#E6F4EC] border border-[#B8DFCC] rounded-lg text-[#1F7B4D] text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistiques -->
    <div class="stats-grid grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">{{ $statusCounts['all'] ?? 0 }}</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-xl sm:text-2xl font-bold text-[#A65A0E]">{{ $statusCounts['pending'] ?? 0 }}</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--primary-navy)]">{{ $statusCounts['processing'] ?? 0 }}</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-xl sm:text-2xl font-bold text-[#1F7B4D]">{{ $statusCounts['completed'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Liste des consultations -->
    <div class="card p-3 sm:p-4">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Caissier</th>
                        <th>Patient</th>
                        <th class="hidden sm:table-cell">Motif</th>
                        <th class="hidden md:table-cell">Date</th>
                        <th>Statut</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="consultationsTableBody">
                    @forelse($consultations as $consultation)
                        <tr class="consultation-row {{ $consultation->status == 'pending' ? 'consultation-row-pending' : '' }}">
                            <td class="font-mono text-xs text-[var(--primary-navy)]">{{ $consultation->id }}</td>
                            <td class="text-sm">{{ $consultation->cashier?->name ?? 'N/A' }}</td>
                            <td class="text-sm font-medium">{{ $consultation->nom_complet }}</td>
                            <td class="hidden sm:table-cell text-sm text-[var(--text-secondary)] max-w-[150px] truncate">
                                {{ $consultation->reason ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $consultation->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending' => 'badge-warning',
                                        'processing' => 'badge-info',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-danger',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'En attente',
                                        'processing' => 'En traitement',
                                        'completed' => 'Terminée',
                                        'cancelled' => 'Annulée',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$consultation->status] ?? 'badge-warning' }}">
                                    {{ $statusLabels[$consultation->status] ?? ucfirst($consultation->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.consultations.show', $consultation) }}" class="btn btn-primary btn-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Traiter
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune consultation</p>
                                <p class="text-sm text-[var(--text-tertiary)]">
                                    @if(request('search') || request('status'))
                                        Aucun résultat pour ces critères
                                    @else
                                        Les consultations apparaitront ici
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($consultations->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $consultations->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const resetBtn = document.getElementById('resetFilters');
    let searchTimeout;

    // Recherche avec debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            searchTimeout = setTimeout(() => {
                fetchConsultations(query, statusFilter.value);
            }, 300);
        });
    }

    // Filtre par statut
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const query = searchInput ? searchInput.value.trim() : '';
            fetchConsultations(query, this.value);
        });
    }

    // Réinitialisation
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = 'all';
            fetchConsultations('', 'all');
        });
    }

    function fetchConsultations(query, status) {
        const url = new URL(window.location.href);
        
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        
        if (status && status !== 'all') {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('consultationsTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
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
            
            const newTableBody = doc.getElementById('consultationsTableBody');
            const newPagination = doc.querySelector('.mt-3.sm\\:mt-4');
            
            if (newTableBody) {
                document.getElementById('consultationsTableBody').innerHTML = newTableBody.innerHTML;
            }
            
            if (newPagination) {
                const paginationContainer = document.querySelector('.mt-3.sm\\:mt-4');
                if (paginationContainer) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                }
            }

            // Mise à jour du titre et du compte
            const subtitle = document.querySelector('.text-sm.text-\\[var\\(--text-secondary\\)\\]');
            if (subtitle) {
                const totalMatch = html.match(/(\d+)\s+consultations?/);
                if (totalMatch) {
                    subtitle.textContent = totalMatch[0];
                }
            }

            // Mise à jour des statistiques
            const stats = doc.querySelectorAll('.card-stats .text-xl');
            if (stats.length >= 4) {
                document.querySelectorAll('.card-stats .text-xl').forEach((el, index) => {
                    if (stats[index]) {
                        el.textContent = stats[index].textContent;
                    }
                });
            }

            // Mise à jour du badge "en attente"
            const pendingBadge = document.querySelector('.px-3.py-1.bg-\\[\\#FEF1E6\\]');
            if (pendingBadge) {
                const newPendingBadge = doc.querySelector('.px-3.py-1.bg-\\[\\#FEF1E6\\]');
                if (newPendingBadge) {
                    pendingBadge.textContent = newPendingBadge.textContent;
                }
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('consultationsTableBody').innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-8 text-[#B32A2A]">
                        Une erreur est survenue lors de la recherche
                    </td>
                </tr>
            `;
        });
    }
});
</script>
@endpush
@endsection