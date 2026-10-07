@extends('cashier.layouts.app')

@section('title', 'Dépenses')

@push('styles')
<style>
    /* ============================================================
       STAT CARDS
       ============================================================ */
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem 1.25rem;
        transition: background 0.2s ease;
    }

    .stat-card .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-today { background: rgba(179, 42, 42, 0.10); color: #b32a2a; }
    .stat-icon-month { background: rgba(245, 158, 11, 0.10); color: #f59e0b; }
    .stat-icon-year { background: rgba(30, 93, 173, 0.10); color: var(--primary); }
    .stat-icon-count { background: rgba(34, 197, 94, 0.10); color: #22c55e; }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
    }

    /* ============================================================
       BOUTONS
       ============================================================ */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
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

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.15rem 0.5rem;
        font-size: 0.65rem;
    }

    /* ============================================================
       TABLE
       ============================================================ */
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
        text-align: left;
        padding: 0.6rem 0.75rem;
        font-weight: 600;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody td {
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover td {
        background: var(--bg-secondary);
    }

    /* ============================================================
       BADGES
       ============================================================ */
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
    .badge-info { background: rgba(30, 93, 173, 0.10); color: var(--primary); }

    /* ============================================================
       FORMULAIRES
       ============================================================ */
    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md, 6px);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.813rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(30, 93, 173, 0.1);
    }

    /* ============================================================
       TABS (style POS)
       ============================================================ */
    .tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .tab-btn {
        padding: 0.4rem 1.25rem;
        border: none;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.813rem;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
        background: transparent;
        color: var(--text-secondary);
    }
    .tab-btn:hover {
        color: var(--text-primary);
        background: var(--bg-secondary);
    }
    .tab-btn.active {
        background: var(--primary);
        color: #FFFFFF;
    }

    @media (max-width: 640px) {
        .stat-card { padding: 0.75rem 1rem; }
        .stat-value { font-size: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.4rem 0.5rem; font-size: 0.7rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .tab-btn { padding: 0.3rem 0.75rem; font-size: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Dépenses
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Gestion des sorties de caisse et remboursements
            </p>
        </div>
        <a href="{{ route('cashier.expenses.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle dépense
        </a>
    </div>

    {{-- STATISTIQUES --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Aujourd'hui</p>
                    <p class="stat-value text-[var(--ui-stat-danger)]">${{ number_format($stats['today'], 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-today">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Ce mois</p>
                    <p class="stat-value text-[var(--ui-stat-warning)]">${{ number_format($stats['month'], 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-month">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Cette année</p>
                    <p class="stat-value text-[var(--primary)]">${{ number_format($stats['year'], 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-year">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Nb. ce mois</p>
                    <p class="stat-value text-[var(--ui-stat-success)]">{{ $stats['count_month'] }}</p>
                </div>
                <div class="stat-icon stat-icon-count">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- BARRE DE RECHERCHE LIVE (style POS) --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)] pointer-events-none"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   id="expenseSearch"
                   placeholder="Rechercher une dépense (titre, catégorie, référence...)"
                   class="form-control pl-9">
        </div>
        <button type="button" id="clearSearchBtn"
                class="btn btn-outline hidden">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Effacer
        </button>
    </div>

    <div id="searchResult" class="text-xs sm:text-sm text-[var(--text-secondary)] hidden">
        Résultats: <span id="resultCount" class="font-semibold text-[var(--primary)]">0</span> dépense(s)
    </div>

    {{-- FILTRES AVANCÉS (recherche serveur via GET) --}}
    <div class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-[var(--radius-md)] p-3 sm:p-4">
        <form method="GET" action="{{ route('cashier.expenses.index') }}"
              class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">

            <select name="category" class="form-control">
                <option value="">Catégorie</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="expense_type" class="form-control">
                <option value="">Type</option>
                <option value="caisse" {{ request('expense_type') === 'caisse' ? 'selected' : '' }}>Caisse</option>
                <option value="membre" {{ request('expense_type') === 'membre' ? 'selected' : '' }}>Membre</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="form-control" placeholder="Du">

            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="form-control" placeholder="Au">

            <button type="submit" class="btn btn-primary">Filtrer</button>
            <a href="{{ route('cashier.expenses.index') }}" class="btn btn-outline">Réinitialiser</a>
        </form>
    </div>

    {{-- LISTE --}}
    <div class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-[var(--radius-md)] p-3 sm:p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Historique des dépenses</h3>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('cashier.expenses.export', request()->query()) }}"
                   class="text-[var(--primary)] hover:underline">Exporter CSV</a>
                <span class="text-[var(--text-tertiary)]">•</span>
                <a href="{{ route('cashier.expenses.stats') }}"
                   class="text-[var(--primary)] hover:underline">Statistiques</a>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table" id="expensesTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Catégorie</th>
                        <th>Titre</th>
                        <th class="text-right">Montant</th>
                        <th>Paiement</th>
                        <th class="hidden sm:table-cell">Par</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        @php
                            // Prépare les données pour la recherche live
                            $searchText = strtolower(implode(' ', [
                                $expense->title,
                                $expense->description ?? '',
                                $expense->category_label,
                                $expense->reference ?? '',
                                $expense->user->name ?? '',
                                $expense->relatedUser->name ?? '',
                                $expense->expense_type_label,
                                $expense->payment_method_label,
                                $expense->formatted_amount,
                            ]));
                        @endphp
                        <tr class="expense-row" data-search="{{ $searchText }}">
                            <td class="text-xs">{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $expense->expense_type === 'caisse' ? 'badge-info' : 'badge-warning' }}">
                                    {{ $expense->expense_type_label }}
                                </span>
                            </td>
                            <td class="text-xs">{{ $expense->category_label }}</td>
                            <td class="text-sm">{{ $expense->title }}</td>
                            <td class="text-right font-semibold text-[var(--danger)]">
                                {{ $expense->formatted_amount }}
                            </td>
                            <td class="text-xs">{{ $expense->payment_method_label }}</td>
                            <td class="hidden sm:table-cell text-xs">{{ $expense->user->name ?? 'N/A' }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('cashier.expenses.show', $expense->id) }}"
                                       class="btn btn-outline btn-xs">Voir</a>
                                    <a href="{{ route('cashier.expenses.edit', $expense->id) }}"
                                       class="btn btn-outline btn-xs">Modifier</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
                            <td colspan="8" class="text-center py-6 text-[var(--text-tertiary)]">
                                Aucune dépense enregistrée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Message "aucun résultat" pour la recherche live --}}
            <div id="noResultMessage" class="hidden text-center py-8">
                <svg class="w-12 h-12 mx-auto mb-3 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="text-[var(--text-tertiary)]">Aucune dépense ne correspond à votre recherche</p>
            </div>
        </div>

        <x-salang-pagination :paginator="$expenses" id="paginationWrapper" class="mt-3" />
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('expenseSearch');
    const clearBtn = document.getElementById('clearSearchBtn');
    const searchResult = document.getElementById('searchResult');
    const resultCount = document.getElementById('resultCount');
    const noResultMessage = document.getElementById('noResultMessage');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const rows = document.querySelectorAll('.expense-row');
    let timeout;

    function performSearch(query) {
        const q = query.trim().toLowerCase();
        let count = 0;

        rows.forEach(function (row) {
            const text = row.dataset.search || '';
            const match = q === '' || text.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) count++;
        });

        // Afficher / masquer les infos
        if (q === '') {
            searchResult.classList.add('hidden');
            clearBtn.classList.add('hidden');
            noResultMessage.classList.add('hidden');
            paginationWrapper.classList.remove('hidden');
        } else {
            searchResult.classList.remove('hidden');
            clearBtn.classList.remove('hidden');
            resultCount.textContent = count;

            if (count === 0) {
                noResultMessage.classList.remove('hidden');
                paginationWrapper.classList.add('hidden');
            } else {
                noResultMessage.classList.add('hidden');
                paginationWrapper.classList.add('hidden'); // cache la pagination pendant la recherche
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            const value = this.value;
            timeout = setTimeout(function () {
                performSearch(value);
            }, 200); // debounce 200ms
        });

        // Raccourci clavier : Ctrl+K ou Cmd+K
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            performSearch('');
            searchInput.focus();
        });
    }
});
</script>
@endpush
@endsection