{{-- resources/views/admin/consultations/index.blade.php --}}
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

.consultation-row {
    transition: background 0.15s ease, transform 0.1s ease;
}
.consultation-row:hover {
    background: var(--bg-hover);
}
.consultation-row:active {
    transform: scale(0.99);
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
.badge-secondary { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

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

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
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

/* Alert */
.alert {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    border: 1px solid transparent;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.alert-success {
    background: rgba(28, 126, 74, 0.08);
    border-color: rgba(28, 126, 74, 0.15);
    color: #1C7E4A;
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

@media (max-width: 640px) {
    .card-stats { padding: 0.625rem; }
    .card-stats .text-2xl { font-size: 1.25rem; }
    .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
    .btn-sm svg { width: 0.875rem; height: 0.875rem; }
    .card { padding: 0.875rem; }
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
    .stats-grid {
        grid-template-columns: 1fr 1fr !important;
    }
    .badge {
        font-size: 0.55rem;
        padding: 0.1rem 0.4rem;
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
}
</style>
@endpush

@section('title', 'Consultations')

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header with Search -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Fiches de Consultation
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Gérer les consultations des patients
                <span class="text-xs text-[var(--text-tertiary)] ml-2" id="resultsCount">
                    ({{ $consultations->total() }} trouvés)
                </span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 bg-[#B54708]/20 text-[#B54708] rounded-full text-xs sm:text-sm font-semibold">
                {{ $pendingCount ?? 0 }} en attente
            </span>
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
                           placeholder="Rechercher une consultation..."
                           autocomplete="off">
                    <button type="button" id="clearBtn" class="clear-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success animate-fadeInUp">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-[var(--primary-blue)]">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">{{ $consultations->total() }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#B54708] animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl font-bold text-[#B54708]">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#065F9C] animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</p>
            <p class="text-lg sm:text-xl font-bold text-[#065F9C]">{{ $consultations->where('status', 'processing')->count() }}</p>
        </div>
        <div class="card-stats border-l-4 border-[#1C7E4A] animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Terminées</p>
            <p class="text-lg sm:text-xl font-bold text-[#1C7E4A]">{{ $consultations->where('status', 'completed')->count() }}</p>
        </div>
    </div>

    <!-- List -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">Caissier</th>
                        <th class="text-xs sm:text-sm">Patient</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Motif</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Date</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="consultationsTable">
                    @forelse($consultations as $consultation)
                        <tr class="consultation-row @if($consultation->status == 'pending') bg-[#B54708]/5 @endif"
                            data-search="{{ strtolower($consultation->id . ' ' . $consultation->cashier?->name . ' ' . $consultation->nom_complet . ' ' . $consultation->reason) }}">
                            <td class="font-mono text-xs sm:text-sm text-[var(--primary-blue)]">{{ $consultation->id }}</td>
                            <td class="text-sm">{{ $consultation->cashier?->name ?? 'N/A' }}</td>
                            <td class="text-sm">{{ $consultation->nom_complet }}</td>
                            <td class="text-sm hidden sm:table-cell max-w-[150px] truncate">{{ $consultation->reason ?? 'N/A' }}</td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden md:table-cell">
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
                                        'completed' => 'Terminé',
                                        'cancelled' => 'Annulé',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$consultation->status] ?? 'badge-warning' }}">
                                    {{ $statusLabels[$consultation->status] ?? ucfirst($consultation->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.consultations.show', $consultation) }}" class="btn btn-primary btn-sm">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Traiter
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="noResultsRow">
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune fiche de consultation</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les fiches apparaîtront ici</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($consultations->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $consultations->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    const rows = document.querySelectorAll('#consultationsTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();

        let visibleCount = 0;

        rows.forEach(function(row) {
            if (row.id === 'noResultsRow') return;

            const data = row.dataset.search || '';
            const text = row.textContent.toLowerCase();
            const match = !search || data.includes(search) || text.includes(search);

            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        // Gérer l'affichage "aucun résultat"
        const noResultsRow = document.getElementById('noResultsRow');
        if (noResultsRow) {
            if (visibleCount === 0 && search.length > 0) {
                noResultsRow.style.display = '';
            } else {
                noResultsRow.style.display = 'none';
            }
        }

        clearBtn.classList.toggle('visible', search.length > 0);
    }

    searchInput.addEventListener('input', filterRows);

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

    // Recherche initiale si une valeur est présente
    // (pas de recherche initiale automatique)
});
</script>
@endpush
@endsection