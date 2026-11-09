@extends('admin.layouts.app')

@section('title', 'Stock produits')

@push('styles')
<style>
[x-cloak] { display: none !important; }

.users-filter-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 0.35rem);
    min-width: 12.5rem;
    max-height: min(70vh, 28rem);
    overflow-y: auto;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 0.65rem;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
    padding: 0.35rem;
    z-index: 40;
}
.users-filter-menu a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.5rem 0.65rem;
    border-radius: 0.45rem;
    font-size: 0.8125rem;
    color: var(--text-primary);
    text-decoration: none;
}
.users-filter-menu a:hover { background: var(--bg-secondary); }
.users-filter-menu a.is-active {
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    font-weight: 600;
}
.users-filter-menu .filter-count {
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--text-tertiary);
}
.users-filter-menu a.is-active .filter-count { color: var(--primary); }
.products-filter-sep {
    margin: 0.35rem 0.35rem 0.15rem;
    padding-top: 0.4rem;
    border-top: 1px solid var(--border-light);
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-tertiary);
}

.admin-app .header-actions .products-header-tools {
    flex: 0 0 auto;
    order: -1;
}
.admin-app .header-actions .products-header-search.header-search {
    width: 11.5rem;
    max-width: 11.5rem;
    flex: 0 0 11.5rem;
}
.admin-app .header-actions .products-header-search .search-wrapper,
.admin-app .header-actions .products-header-search .search-input {
    width: 100%;
    max-width: 100%;
}
@media (min-width: 1024px) {
    .admin-app .header-actions .products-header-search.header-search {
        width: 14rem;
        max-width: 14rem;
        flex-basis: 14rem;
    }
}

.stock-op-card {
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    background: var(--bg-card);
    overflow: hidden;
}
.stock-op-card__head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.9rem 1rem 0.75rem;
    border-bottom: 1px solid var(--border-light);
    background: color-mix(in srgb, var(--bg-secondary) 65%, transparent);
}
.stock-op-card__head h3 {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}
.stock-op-card__head p {
    margin: 0.2rem 0 0;
    font-size: 0.72rem;
    color: var(--text-tertiary);
}
.stock-op-card__body {
    display: grid;
    gap: 1rem;
    padding: 1rem;
}
@media (min-width: 900px) {
    .stock-op-card__body {
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr) auto;
        align-items: start;
    }
}
.stock-op-seg {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.45rem;
}
.stock-op-seg input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.stock-op-seg label {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.15rem;
    padding: 0.65rem 0.7rem;
    border: 1px solid var(--border-color);
    border-radius: 0.65rem;
    background: var(--bg-secondary);
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    min-height: 3.6rem;
}
.stock-op-seg label:hover {
    border-color: color-mix(in srgb, var(--primary) 40%, var(--border-color));
}
.stock-op-seg input:checked + label {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--primary) 35%, transparent);
}
.stock-op-seg .op-icon {
    width: 1.05rem;
    height: 1.05rem;
    color: var(--text-tertiary);
}
.stock-op-seg input:checked + label .op-icon,
.stock-op-seg input:checked + label .op-title {
    color: var(--primary);
}
.stock-op-seg .op-title {
    font-size: 0.8rem;
    font-weight: 700;
    line-height: 1.15;
    color: var(--text-primary);
}
.stock-op-seg .op-sub {
    font-size: 0.65rem;
    font-weight: 500;
    color: var(--text-tertiary);
    line-height: 1.2;
}
.stock-op-seg input:checked + label .op-sub {
    color: color-mix(in srgb, var(--primary) 70%, var(--text-tertiary));
}
.stock-reason-field label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 0.4rem;
}
.stock-reason-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.5rem;
}
.stock-reason-chips button {
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-radius: 0.45rem;
    padding: 0.28rem 0.55rem;
    font-size: 0.68rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color 0.15s ease, color 0.15s ease, background 0.15s ease;
}
.stock-reason-chips button:hover,
.stock-reason-chips button.is-active {
    border-color: var(--primary);
    color: var(--primary);
    background: color-mix(in srgb, var(--primary) 8%, transparent);
}
.stock-op-apply {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    min-width: 10.75rem;
    padding-top: 1.15rem;
}
@media (max-width: 899px) {
    .stock-op-apply { padding-top: 0; }
}
.stock-op-apply .btn {
    min-height: 2.5rem;
}
.stock-op-help {
    margin: 0.55rem 0 0;
    font-size: 0.7rem;
    color: var(--text-tertiary);
}
</style>
@endpush

@section('content')
@php
    $action = old('action', 'adjust');
    $searchOpen = filled(request('search'));
    $stockFilter = request('stock');

    $stockSubtitle = ($stats['total'] ?? 0).' produits référencés';
    if ($stockFilter === 'low') {
        $stockSubtitle = 'Stock bas · '.($stats['listed'] ?? 0).' produit(s)';
    } elseif ($stockFilter === 'out') {
        $stockSubtitle = 'Rupture · '.($stats['listed'] ?? 0).' produit(s)';
    } elseif ($stockFilter === 'ok') {
        $stockSubtitle = 'Stock OK · '.($stats['listed'] ?? 0).' produit(s)';
    }
    if (request('search')) {
        $stockSubtitle .= ' · « '.e(request('search')).' »';
    }
@endphp
<div class="space-y-4 sm:space-y-6">

    @include('admin.layouts.partials.desktop-page-header', [
        'title' => 'Gestion du stock',
        'subtitle' => $stockSubtitle,
        'actions' => view('admin.products.partials.stock-overview-header-actions', [
            'stats' => $stats,
        ])->render(),
    ])

    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Stock</h1>
                <p class="page-subtitle">{{ $stockSubtitle }}</p>
            </div>
            @include('admin.products.partials.stock-overview-mobile-banner-actions')
        </div>

        <div id="adminSearchPanel"
             class="admin-search-panel{{ $searchOpen ? ' is-open' : '' }}"
             data-admin-search-panel="1"
             aria-hidden="{{ $searchOpen ? 'false' : 'true' }}">
            <div class="search-wrapper admin-shop-search">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="stockSearchMobile"
                       class="search-input"
                       placeholder="Rechercher un produit"
                       autocomplete="off"
                       value="{{ request('search') }}"
                       data-stock="{{ request('stock') }}"
                       data-active-only="{{ (! request()->exists('active_only') || request()->boolean('active_only')) ? '1' : '0' }}">
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 salang-flash-success border rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 salang-flash-error border rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <x-ui.stat-grid class="grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp">
        <x-ui.stat title="Total" :value="$stats['total'] ?? 0" id="kpi-stock-total" padding="p-3" />
        <x-ui.stat title="Actifs" :value="$stats['active'] ?? 0" id="kpi-stock-active" padding="p-3" />
        <x-ui.stat title="Stock bas" :value="$stats['low_stock'] ?? 0" id="kpi-stock-low" padding="p-3" />
        <x-ui.stat title="Rupture" :value="$stats['out_of_stock'] ?? 0" id="kpi-stock-out" padding="p-3" />
    </x-ui.stat-grid>

    <form method="POST" action="{{ route('admin.products.stock-bulk') }}" class="space-y-4" id="bulkStockForm">
        @csrf
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="stock" value="{{ request('stock') }}">
        <input type="hidden" name="active_only" value="{{ request()->exists('active_only') ? (request()->boolean('active_only') ? '1' : '0') : '1' }}">

        <div class="stock-op-card animate-fadeInUp">
            <div class="stock-op-card__head">
                <div>
                    <h3>Mise à jour groupée</h3>
                    <p id="bulkHelp">Saisissez le stock final souhaité sur chaque ligne concernée.</p>
                </div>
                <span class="badge badge-muted">{{ $stats['listed'] ?? 0 }} ligne(s)</span>
            </div>

            <div class="stock-op-card__body">
                <div>
                    <p class="block text-xs font-semibold text-[var(--text-secondary)] mb-1.5">Opération</p>
                    <div class="stock-op-seg" role="radiogroup" aria-label="Type d’opération">
                        <div class="relative">
                            <input type="radio" name="action" id="action_adjust" value="adjust" @checked($action === 'adjust')>
                            <label for="action_adjust">
                                <svg class="op-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <span class="op-title">Inventaire</span>
                                <span class="op-sub">Fixer le stock</span>
                            </label>
                        </div>
                        <div class="relative">
                            <input type="radio" name="action" id="action_entry" value="entry" @checked($action === 'entry')>
                            <label for="action_entry">
                                <svg class="op-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="op-title">Entrée</span>
                                <span class="op-sub">Ajouter au stock</span>
                            </label>
                        </div>
                        <div class="relative">
                            <input type="radio" name="action" id="action_exit" value="exit" @checked($action === 'exit')>
                            <label for="action_exit">
                                <svg class="op-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                                </svg>
                                <span class="op-title">Sortie</span>
                                <span class="op-sub">Retirer du stock</span>
                            </label>
                        </div>
                    </div>
                    <p class="stock-op-help" id="bulkHelpDetail">Les lignes vides sont ignorées.</p>
                </div>

                <div class="stock-reason-field">
                    <label for="reason">Motif</label>
                    <input type="text"
                           name="reason"
                           id="reason"
                           maxlength="255"
                           value="{{ old('reason') }}"
                           class="form-control"
                           list="stockReasonSuggestions"
                           placeholder="Ex. inventaire mensuel"
                           autocomplete="off">
                    <datalist id="stockReasonSuggestions">
                        <option value="Inventaire mensuel"></option>
                        <option value="Réception fournisseur"></option>
                        <option value="Réapprovisionnement"></option>
                        <option value="Casse / perte"></option>
                        <option value="Retour client"></option>
                        <option value="Correction d’erreur"></option>
                    </datalist>
                    <div class="stock-reason-chips" id="reasonChips" aria-label="Motifs rapides">
                        <button type="button" data-reason="Inventaire mensuel">Inventaire</button>
                        <button type="button" data-reason="Réception fournisseur">Réception</button>
                        <button type="button" data-reason="Réapprovisionnement">Réappro</button>
                        <button type="button" data-reason="Casse / perte">Casse</button>
                        <button type="button" data-reason="Correction d’erreur">Correction</button>
                    </div>
                </div>

                <div class="stock-op-apply">
                    <button type="submit" class="btn btn-primary w-full">
                        Appliquer
                    </button>
                    <p class="text-[0.65rem] text-[var(--text-tertiary)] text-center leading-snug">
                        Uniquement les quantités remplies
                    </p>
                </div>
            </div>
        </div>

        <div class="card p-0 overflow-hidden animate-fadeInUp">
            <div class="flex items-center justify-between gap-2 px-3 sm:px-4 pt-3 sm:pt-4 pb-2">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Produits</h3>
                <span class="badge badge-muted">{{ $stats['listed'] ?? 0 }} produit(s)</span>
            </div>
            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="hidden sm:table-cell">SKU</th>
                            <th>Stock actuel</th>
                            <th style="min-width: 7rem;" id="qtyColumnLabel">Quantité</th>
                            <th class="hidden md:table-cell text-right">Fiche</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $product)
                            @php
                                $stockTone = match (true) {
                                    $product->stock <= 0 => 'badge-danger',
                                    $product->stock <= 5 => 'badge-warning',
                                    default => 'badge-success',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-medium text-sm text-[var(--text-primary)]">{{ $product->name }}</div>
                                    <div class="text-[11px] text-[var(--text-tertiary)] sm:hidden font-mono">{{ $product->sku ?: '—' }}</div>
                                </td>
                                <td class="hidden sm:table-cell font-mono text-xs text-[var(--text-tertiary)]">
                                    {{ $product->sku ?: '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $stockTone }}">{{ $product->stock }}</span>
                                </td>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                    <input type="number"
                                           name="items[{{ $index }}][quantity]"
                                           min="0"
                                           class="form-control form-control-sm stock-qty-input"
                                           placeholder="—"
                                           value="{{ old('items.'.$index.'.quantity') }}"
                                           inputmode="numeric">
                                </td>
                                <td class="hidden md:table-cell text-right">
                                    <a href="{{ route('admin.products.stock', $product->id) }}" class="btn btn-outline btn-sm">Détail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-[var(--text-secondary)]">
                                    Aucun produit pour ce filtre.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($products->isNotEmpty())
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    Appliquer les quantités
                </button>
            </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const help = document.getElementById('bulkHelp');
    const reasonInput = document.getElementById('reason');
    const actionRadios = document.querySelectorAll('input[name="action"]');
    const qtyInputs = document.querySelectorAll('.stock-qty-input');
    const reasonChips = document.querySelectorAll('#reasonChips [data-reason]');

    const helpText = {
        adjust: 'Saisissez le stock final souhaité sur chaque ligne concernée.',
        entry: 'Saisissez la quantité à ajouter sur chaque ligne concernée.',
        exit: 'Saisissez la quantité à retirer sur chaque ligne concernée.',
    };
    const qtyPlaceholder = {
        adjust: 'Stock final',
        entry: 'À ajouter',
        exit: 'À retirer',
    };
    const qtyColumn = {
        adjust: 'Stock final',
        entry: 'À ajouter',
        exit: 'À retirer',
    };
    const reasonPlaceholder = {
        adjust: 'Ex. inventaire mensuel',
        entry: 'Ex. réception fournisseur',
        exit: 'Ex. casse / perte',
    };
    const qtyColumnLabel = document.getElementById('qtyColumnLabel');

    function syncOperationUi() {
        const selected = document.querySelector('input[name="action"]:checked')?.value || 'adjust';
        if (help) help.textContent = helpText[selected] || helpText.adjust;
        if (qtyColumnLabel) qtyColumnLabel.textContent = qtyColumn[selected] || 'Quantité';
        qtyInputs.forEach((input) => {
            input.placeholder = qtyPlaceholder[selected] || '—';
        });
        if (reasonInput && !reasonInput.value.trim()) {
            reasonInput.placeholder = reasonPlaceholder[selected] || reasonPlaceholder.adjust;
        }
    }

    actionRadios.forEach((radio) => {
        radio.addEventListener('change', syncOperationUi);
    });
    syncOperationUi();

    reasonChips.forEach((chip) => {
        chip.addEventListener('click', function () {
            if (!reasonInput) return;
            reasonInput.value = this.dataset.reason || '';
            reasonChips.forEach((c) => c.classList.toggle('is-active', c === this));
            reasonInput.focus();
        });
    });

    const searchInputs = [
        document.getElementById('stockSearchDesktop'),
        document.getElementById('stockSearchMobile'),
    ].filter(Boolean);
    let searchTimeout;

    function syncSearchInputs(value, source) {
        searchInputs.forEach((input) => {
            if (input !== source) input.value = value;
        });
    }

    function navigateSearch(query, source) {
        const url = new URL(window.location.href);
        if (query) url.searchParams.set('search', query);
        else url.searchParams.delete('search');

        const meta = source || searchInputs[0];
        const stock = meta?.dataset.stock || '';
        const activeOnly = meta?.dataset.activeOnly ?? '1';

        if (stock) url.searchParams.set('stock', stock);
        else url.searchParams.delete('stock');
        url.searchParams.set('active_only', activeOnly);

        window.location.href = url.toString();
    }

    searchInputs.forEach((input) => {
        input.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            syncSearchInputs(this.value, this);
            const query = this.value.trim();
            searchTimeout = setTimeout(() => navigateSearch(query, this), 400);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                navigateSearch(this.value.trim(), this);
            }
        });
    });
});
</script>
@endpush
