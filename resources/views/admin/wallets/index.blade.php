{{-- resources/views/admin/wallets/index.blade.php --}}
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
.wallet-row {
    transition: background 0.1s ease;
}
.wallet-row:hover {
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
.badge-neutral {
    background: #EEF0F3;
    color: var(--text-secondary);
    border-color: #DCDEE3;
}
.badge-warning {
    background: #FEF1E6;
    color: #A65A0E;
    border-color: #FADCB8;
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
    .card {
        padding: 0.875rem;
    }
    .card-stats {
        padding: 0.625rem 0.75rem;
    }
    .card-stats .text-xl {
        font-size: 1.125rem;
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
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Portefeuilles</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $wallets->total() }} portefeuilles
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
                @if(request('status'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Statut: {{ request('status') == 'active' ? 'Actifs' : 'Inactifs' }}
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
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>

            <button id="resetFilters" class="btn btn-outline btn-sm text-xs" title="Réinitialiser les filtres">
                Réinitialiser
            </button>

            <a href="{{ route('admin.wallets.export', request()->all()) }}" class="btn btn-outline btn-sm" title="Exporter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="hidden sm:inline">Exporter</span>
            </a>
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
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--primary-navy)]">{{ $totalWallets ?? 0 }}</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Solde total</p>
            <p class="text-xl sm:text-2xl font-bold text-[#1F7B4D]">{{ number_format($totalBalance ?? 0, 2) }} $</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-xl sm:text-2xl font-bold text-[#A65A0E]">{{ number_format($pendingBalance ?? 0, 2) }} $</p>
        </div>
        <div class="card-stats">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--primary-navy)]">{{ $activeWallets ?? 0 }}</p>
        </div>
    </div>

    <!-- Liste des portefeuilles -->
    <div class="card p-3 sm:p-4 animate-fadeInUp" style="animation-delay: 0.1s;">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Solde</th>
                        <th class="hidden sm:table-cell">En attente</th>
                        <th class="hidden md:table-cell">Retiré</th>
                        <th class="hidden md:table-cell">Déposé</th>
                        <th class="hidden lg:table-cell">Devise</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="walletsTable">
                    @forelse($wallets as $wallet)
                        <tr class="wallet-row">
                            <td class="font-medium text-sm">
                                {{ $wallet->user?->name ?? 'N/A' }}
                                <span class="text-[10px] text-[var(--text-secondary)] block">
                                    {{ $wallet->user?->email ?? '' }}
                                </span>
                            </td>
                            <td class="font-bold text-[#1F7B4D] text-sm">
                                {{ number_format($wallet->balance, 2) }} $
                            </td>
                            <td class="hidden sm:table-cell text-[#A65A0E] text-sm">
                                {{ number_format($wallet->pending_balance, 2) }} $
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-sm">
                                {{ number_format($wallet->total_withdrawn, 2) }} $
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-sm">
                                {{ number_format($wallet->total_deposited, 2) }} $
                            </td>
                            <td class="hidden lg:table-cell">
                                <span class="badge badge-neutral text-[10px] sm:text-xs">
                                    {{ $wallet->currency ?? 'USD' }}
                                </span>
                                @if(!$wallet->is_active)
                                    <span class="badge badge-warning text-[10px] sm:text-xs ml-1">
                                        Bloquer
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.wallets.show', $wallet->id) }}"
                                       class="btn btn-outline btn-sm btn-icon" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucun portefeuille</p>
                                <p class="text-sm text-[var(--text-tertiary)]">
                                    @if(request('search') || request('status'))
                                        Aucun résultat pour ces critères
                                    @else
                                        Les portefeuilles apparaîtront lors de l'inscription des utilisateurs
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wallets->hasPages())
            <div class="mt-3 sm:mt-4" id="paginationContainer">
                {{ $wallets->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    const statusFilter = document.getElementById('statusFilter');
    const resetBtn = document.getElementById('resetFilters');
    let searchTimeout;

    // Recherche avec debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            clearBtn.classList.toggle('visible', query.length > 0);
            
            searchTimeout = setTimeout(() => {
                fetchWallets(query, statusFilter.value);
            }, 300);
        });

        // Raccourci ESC
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                clearBtn.classList.remove('visible');
                fetchWallets('', statusFilter.value);
                this.blur();
            }
        });
    }

    // Filtre par statut
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            fetchWallets(searchInput.value.trim(), this.value);
        });
    }

    // Réinitialisation
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (clearBtn) clearBtn.classList.remove('visible');
            fetchWallets('', '');
        });
    }

    // Bouton clear
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.classList.remove('visible');
            fetchWallets('', statusFilter.value);
            searchInput.focus();
        });
    }

    function fetchWallets(search, status) {
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
        
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('walletsTable');
        if (!tableBody) return;

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
            
            const newTableBody = doc.getElementById('walletsTable');
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
                const totalMatch = html.match(/(\d+)\s+portefeuilles?/);
                if (totalMatch) {
                    let text = totalMatch[0];
                    if (search) {
                        text += ' <span class="text-xs text-[var(--text-tertiary)] ml-2">· Résultats pour "' + search + '"</span>';
                    }
                    if (status) {
                        text += ' <span class="text-xs text-[var(--text-tertiary)] ml-2">· Statut: ' + (status === 'active' ? 'Actifs' : 'Inactifs') + '</span>';
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
        })
        .catch(error => {
            console.error('Erreur de recherche:', error);
            tableBody.innerHTML = `
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