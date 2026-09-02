{{-- resources/views/admin/withdrawals/index.blade.php --}}
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

.withdrawal-row {
    transition: background 0.1s ease;
}
.withdrawal-row:hover {
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
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Retraits</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5" id="subtitleCount">
                {{ $withdrawals->total() }} retraits
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
                @if(request('status'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Statut: {{ ucfirst(request('status')) }}
                    </span>
                @endif
                @if(request('method'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Méthode: {{ ucfirst(request('method')) }}
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
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En traitement</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminé</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
            </select>

            @if(isset($methods) && $methods->isNotEmpty())
                <select id="methodFilter" class="filter-select text-sm">
                    <option value="" {{ !request('method') ? 'selected' : '' }}>Toutes méthodes</option>
                    @foreach($methods as $method)
                        <option value="{{ $method }}" {{ request('method') == $method ? 'selected' : '' }}>
                            {{ ucfirst($method) }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button id="resetFilters" class="btn btn-outline btn-sm text-xs" title="Réinitialiser les filtres">
                Réinitialiser
            </button>

            <a href="{{ route('admin.withdrawals.export', request()->all()) }}" class="btn btn-outline btn-sm" title="Exporter">
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
        <div class="card-stats" id="stat-pending">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En attente</p>
            <p class="text-xl sm:text-2xl font-bold text-[#A65A0E]">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="card-stats" id="stat-processing">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">En traitement</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--primary-navy)]">{{ $stats['processing'] ?? 0 }}</p>
        </div>
        <div class="card-stats" id="stat-total-amount">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Montant total</p>
            <p class="text-xl sm:text-2xl font-bold text-[#1F7B4D]">{{ number_format($stats['total_amount'] ?? 0, 2) }} $</p>
        </div>
        <div class="card-stats" id="stat-total-fees">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Frais totaux</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--text-secondary)]">{{ number_format($stats['total_fees'] ?? 0, 2) }} $</p>
        </div>
    </div>

    <!-- Liste des retraits -->
    <div class="card p-3 sm:p-4 animate-fadeInUp" style="animation-delay: 0.1s;">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th class="hidden sm:table-cell">Email</th>
                        <th>Montant</th>
                        <th class="hidden md:table-cell">Méthode</th>
                        <th>Statut</th>
                        <th class="hidden lg:table-cell">Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="withdrawalsTableBody">
                    @include('admin.withdrawals.partials.table_rows', ['withdrawals' => $withdrawals])
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="mt-3 sm:mt-4">
            {{ $withdrawals->appends(request()->query())->links() }}
        </div>
    </div>

</div>

<!-- Modal Rejet -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title">Rejeter le retrait</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">rejeter</strong> ce retrait ?
            <br>
            Veuillez indiquer la raison du rejet.
        </p>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                    Motif du rejet *
                </label>
                <textarea name="reason" rows="3" class="input w-full text-sm" placeholder="Raison du rejet..." required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeRejectModal()" class="btn btn-outline btn-sm flex-1">
                    Annuler
                </button>
                <button type="submit" class="btn btn-danger btn-sm flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    const statusFilter = document.getElementById('statusFilter');
    const methodFilter = document.getElementById('methodFilter');
    const resetBtn = document.getElementById('resetFilters');
    const tableBody = document.getElementById('withdrawalsTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    let searchTimeout;
    let currentPage = 1;

    // Fonction pour mettre à jour le tableau et la pagination
    function updateTable(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Mettre à jour le tableau
        const newTableBody = doc.getElementById('withdrawalsTableBody');
        if (newTableBody) {
            tableBody.innerHTML = newTableBody.innerHTML;
        }

        // Mettre à jour la pagination
        const newPagination = doc.getElementById('paginationContainer');
        if (newPagination && paginationContainer) {
            paginationContainer.innerHTML = newPagination.innerHTML;
        }

        // Mettre à jour le compteur
        const newSubtitle = doc.querySelector('#subtitleCount');
        const subtitle = document.getElementById('subtitleCount');
        if (newSubtitle && subtitle) {
            subtitle.innerHTML = newSubtitle.innerHTML;
        }

        // Mettre à jour les statistiques
        updateStats(doc);

        // Réinitialiser les événements des boutons d'action
        initActionButtons();
    }

    // Fonction pour mettre à jour les statistiques
    function updateStats(doc) {
        const statIds = ['stat-pending', 'stat-processing', 'stat-total-amount', 'stat-total-fees'];
        statIds.forEach(function(id) {
            const newStat = doc.getElementById(id);
            const currentStat = document.getElementById(id);
            if (newStat && currentStat) {
                const newValue = newStat.querySelector('.text-xl');
                const currentValue = currentStat.querySelector('.text-xl');
                if (newValue && currentValue) {
                    currentValue.textContent = newValue.textContent;
                }
            }
        });
    }

    // Fonction pour réinitialiser les événements des boutons d'action
    function initActionButtons() {
        // Les boutons d'action sont maintenant dans le DOM frais
        // Les événements onclick sont déjà définis dans les attributs HTML
    }

    // Fonction pour récupérer les données
    function fetchData(url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.text();
        })
        .then(html => {
            updateTable(html);
        })
        .catch(error => {
            console.error('Erreur:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-8 text-[#B32A2A]">
                        Une erreur est survenue lors de la recherche
                    </td>
                </tr>
            `;
        });
    }

    // Fonction pour construire l'URL avec les paramètres
    function buildUrl(page) {
        const params = new URLSearchParams();
        const search = searchInput.value.trim();
        const status = statusFilter.value;
        const method = methodFilter.value;

        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (method) params.set('method', method);
        if (page && page > 1) params.set('page', page);

        const queryString = params.toString();
        return window.location.pathname + (queryString ? '?' + queryString : '');
    }

    // Recherche avec debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            clearBtn.classList.toggle('visible', query.length > 0);
            
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                const url = buildUrl(currentPage);
                fetchData(url);
            }, 300);
        });

        // Raccourci ESC
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                clearBtn.classList.remove('visible');
                currentPage = 1;
                const url = buildUrl(currentPage);
                fetchData(url);
                this.blur();
            }
        });

        // Raccourci / pour focus
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
    }

    // Filtres
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            currentPage = 1;
            const url = buildUrl(currentPage);
            fetchData(url);
        });
    }

    if (methodFilter) {
        methodFilter.addEventListener('change', function() {
            currentPage = 1;
            const url = buildUrl(currentPage);
            fetchData(url);
        });
    }

    // Réinitialisation
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (methodFilter) methodFilter.value = '';
            if (clearBtn) clearBtn.classList.remove('visible');
            currentPage = 1;
            const url = buildUrl(currentPage);
            fetchData(url);
        });
    }

    // Bouton clear
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.classList.remove('visible');
            currentPage = 1;
            const url = buildUrl(currentPage);
            fetchData(url);
            searchInput.focus();
        });
    }

    // Gestion de la pagination (délégation d'événements)
    if (paginationContainer) {
        paginationContainer.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            
            const url = new URL(link.href);
            const page = url.searchParams.get('page');
            if (page) {
                e.preventDefault();
                currentPage = parseInt(page) || 1;
                const fetchUrl = buildUrl(currentPage);
                fetchData(fetchUrl);
            }
        });
    }
});

// ============================================================
// MODAL DE REJET
// ============================================================
function openRejectModal(withdrawalId) {
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

document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

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