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

.commission-row {
    transition: background 0.15s ease;
}
.commission-row:hover {
    background: var(--bg-hover);
}

.amount-positive {
    color: #1C7E4A;
    font-weight: 700;
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
.badge-purple { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }

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

.input {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
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

/* Type badges */
.type-badge-cash_pos {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.type-badge-commission {
    background: rgba(6, 95, 156, 0.12);
    color: #065F9C;
    border-color: rgba(6, 95, 156, 0.15);
}
.type-badge-bonus {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}
.type-badge-leadership {
    background: rgba(181, 71, 8, 0.12);
    color: #B54708;
    border-color: rgba(181, 71, 8, 0.15);
}
.type-badge-direct {
    background: var(--primary-blue-bg);
    color: var(--primary-blue);
    border-color: var(--primary-blue-border);
}
.type-badge-indirect {
    background: rgba(10, 42, 108, 0.12);
    color: var(--primary-blue);
    border-color: rgba(10, 42, 108, 0.15);
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
    .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
    .btn-sm svg { width: 0.875rem; height: 0.875rem; }
    .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
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
    .card-stats { padding: 0.625rem; }
    .card-stats .text-2xl { font-size: 1.25rem; }
    .card { padding: 0.875rem; }
    .stats-grid { grid-template-columns: 1fr 1fr !important; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr !important; }
    .card { padding: 0.75rem; }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header with Search -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Commissions</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Gérer toutes les commissions générées</p>
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
            <select id="typeFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm" style="padding-left: 0.75rem;">
                <option value="">Tous les types</option>
                @php
                    $allowedTypes = ['cash_pos', 'commission', 'bonus', 'leadership', 'direct', 'indirect'];
                @endphp
                @foreach($types ?? [] as $type)
                    @if(in_array($type, $allowedTypes))
                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endif
                @endforeach
            </select>
            <select id="statusFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm" style="padding-left: 0.75rem;">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="paid">Payé</option>
                <option value="cancelled">Annulé</option>
            </select>
            <select id="periodFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm" style="padding-left: 0.75rem;">
                <option value="">Toutes les périodes</option>
                @foreach($periods ?? [] as $period)
                    <option value="{{ $period }}">{{ $period }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.commissions.export', request()->all()) }}" class="btn btn-outline btn-sm" title="Exporter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="hidden sm:inline">Exporter</span>
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-[#1C7E4A] p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total payé</p>
            <p class="text-lg sm:text-xl font-bold text-[#1C7E4A]">
                ${{ number_format($stats['total_paid'] ?? 0, 2) }}
            </p>
        </div>
        <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-[#B54708] p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-lg sm:text-xl font-bold text-[#B54708]">
                ${{ number_format($stats['total_pending'] ?? 0, 2) }}
            </p>
        </div>
        <div class="card-stats animate-fadeInUp delay-3 border-l-4 border-[#B91C1C] p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Annulé</p>
            <p class="text-lg sm:text-xl font-bold text-[#B91C1C]">
                ${{ number_format($stats['total_cancelled'] ?? 0, 2) }}
            </p>
        </div>
        <div class="card-stats animate-fadeInUp delay-4 border-l-4 border-[var(--primary-blue)] p-3 sm:p-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-blue)]">
                {{ $stats['total_count'] ?? 0 }}
            </p>
        </div>
    </div>

    <!-- Commission List -->
    <div class="card animate-fadeInUp delay-5 p-3 sm:p-4">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">De</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm">Montant</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Statut</th>
                        <th class="text-xs sm:text-sm hidden xl:table-cell">Date</th>
                        <th class="text-xs sm:text-sm text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="commissionsTable">
                    @forelse($commissions as $commission)
                        @php
                            // Exclure les types non souhaités
                            $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];
                            if (in_array($commission->type, $excludedTypes)) {
                                continue;
                            }

                            $typeClass = 'type-badge-' . $commission->type;
                            $typeLabel = $commission->type_label ?? ucfirst(str_replace('_', ' ', $commission->type));
                        @endphp
                        <tr class="commission-row"
                            data-search="{{ strtolower($commission->user?->name ?? '') }}"
                            data-type="{{ $commission->type }}"
                            data-status="{{ $commission->status }}">
                            <td class="font-medium text-sm">
                                {{ $commission->user?->name ?? 'N/A' }}
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden md:table-cell">
                                {{ $commission->fromUser?->name ?? 'Système' }}
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="badge {{ $typeClass }} text-[10px] sm:text-xs">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="amount-positive text-sm">
                                +${{ number_format($commission->amount, 2) }}
                            </td>
                            <td class="hidden lg:table-cell">
                                <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : ($commission->status == 'pending' ? 'badge-warning' : 'badge-danger') }} text-[10px] sm:text-xs">
                                    {{ $commission->status == 'paid' ? 'Payé' : ($commission->status == 'pending' ? 'En attente' : 'Annulé') }}
                                </span>
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden xl:table-cell">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.commissions.show', $commission->id) }}"
                                   class="btn btn-outline btn-sm btn-icon" title="Voir">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les commissions apparaîtront ici une fois générées</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const periodFilter = document.getElementById('periodFilter');
    const rows = document.querySelectorAll('#commissionsTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const type = typeFilter.value;
        const status = statusFilter.value;
        const period = periodFilter.value;

        let visibleCount = 0;

        rows.forEach(function(row) {
            if (row.id === 'noResultsRow') return;

            const data = row.dataset.search || '';
            const text = row.textContent.toLowerCase();
            const rowType = row.dataset.type || '';
            const rowStatus = row.dataset.status || '';

            let show = true;

            if (search && !data.includes(search) && !text.includes(search)) show = false;
            if (type && rowType !== type) show = false;
            if (status && rowStatus !== status) show = false;

            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        // Gérer l'affichage "aucun résultat"
        const noResultsRow = document.getElementById('noResultsRow');
        if (noResultsRow) {
            if (visibleCount === 0 && (search || type || status || period)) {
                noResultsRow.style.display = '';
            } else {
                noResultsRow.style.display = 'none';
            }
        }

        clearBtn.classList.toggle('visible', search.length > 0);
    }

    searchInput.addEventListener('input', filterRows);
    typeFilter.addEventListener('change', filterRows);
    statusFilter.addEventListener('change', filterRows);
    if (periodFilter) {
        periodFilter.addEventListener('change', filterRows);
    }

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
</script>
@endpush
@endsection