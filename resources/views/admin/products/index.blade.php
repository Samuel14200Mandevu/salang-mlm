{{-- resources/views/admin/products/index.blade.php --}}
@extends('admin.layouts.app')

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
.users-filter-menu a:hover {
    background: var(--bg-secondary);
}
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
.users-filter-menu a.is-active .filter-count {
    color: var(--primary);
}
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

.table-wrap { overflow-x: auto; }

.products-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.875rem;
}
.products-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    padding: 0.55rem 0.85rem;
    text-align: left;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-tertiary);
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
}
.products-table thead th.is-num,
.products-table tbody td.is-num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.products-table thead th.is-center,
.products-table tbody td.is-center {
    text-align: center;
}
.products-table tbody td {
    padding: 0.75rem 0.85rem;
    color: var(--text-primary);
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
    background: var(--bg-card);
}
.products-table tbody tr:last-child td {
    border-bottom: none;
}
.product-row {
    transition: background 0.12s ease;
}
.product-row:hover td {
    background: color-mix(in srgb, var(--primary) 4%, var(--bg-card));
}
.product-row.is-inactive {
    opacity: 0.7;
}

.product-thumb {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.65rem;
    object-fit: cover;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    flex-shrink: 0;
}
.product-thumb-fallback {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--text-tertiary);
}
.product-name {
    font-size: 0.875rem;
    font-weight: 650;
    color: var(--text-primary);
    line-height: 1.25;
}
.product-meta {
    margin-top: 0.15rem;
    font-size: 0.68rem;
    color: var(--text-tertiary);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}
.category-chip {
    display: inline-flex;
    max-width: 10rem;
    align-items: center;
    padding: 0.22rem 0.55rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pv-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 3.25rem;
    padding: 0.22rem 0.5rem;
    border-radius: 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    background: color-mix(in srgb, var(--primary) 8%, transparent);
    color: var(--primary);
    border: 1px solid color-mix(in srgb, var(--primary) 18%, transparent);
}
.price-cell {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.05rem;
    line-height: 1.15;
}
.price-main {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--primary);
}
.price-half {
    font-size: 0.9rem;
    font-weight: 650;
    color: var(--text-primary);
}
.price-empty {
    color: var(--text-tertiary);
    font-weight: 500;
}
.stock-cell {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.15rem;
}
.stock-qty {
    font-size: 0.9rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: var(--text-primary);
}
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.18rem 0.5rem;
    border-radius: 999px;
    font-size: 0.62rem;
    font-weight: 700;
    border: 1px solid transparent;
    text-decoration: none;
    white-space: nowrap;
}
.badge-success {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.18);
}
.badge-danger {
    background: rgba(185, 28, 28, 0.1);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.18);
}
.badge-warning {
    background: rgba(181, 71, 8, 0.1);
    color: #B54708;
    border-color: rgba(181, 71, 8, 0.18);
}
.badge-muted {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}

.products-mobile-card {
    display: block;
    border: 1px solid var(--border-color);
    border-radius: 0.85rem;
    background: var(--bg-card);
    padding: 0.85rem;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.12s ease, background 0.12s ease;
}
.products-mobile-card:hover {
    border-color: color-mix(in srgb, var(--primary) 35%, var(--border-color));
    background: color-mix(in srgb, var(--primary) 3%, var(--bg-card));
}
.products-mobile-top {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}
.products-mobile-copy {
    min-width: 0;
    flex: 1;
}
.products-mobile-copy h3 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.25;
}
.products-mobile-copy p {
    margin: 0.2rem 0 0;
    font-size: 0.72rem;
    color: var(--text-tertiary);
}
.products-mobile-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.5rem;
    margin-top: 0.75rem;
    padding-top: 0.7rem;
    border-top: 1px solid var(--border-light);
}
.products-mobile-grid .metric {
    min-width: 0;
}
.products-mobile-grid .metric span {
    display: block;
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-tertiary);
}
.products-mobile-grid .metric strong {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-primary);
    font-variant-numeric: tabular-nums;
}
.products-mobile-grid .metric strong.is-primary {
    color: var(--primary);
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}
.btn-danger-icon {
    color: var(--danger);
}
.btn-danger-icon:hover {
    background: rgba(185, 28, 28, 0.08);
    color: #8F2121;
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
.modal-text .text-danger {
    color: var(--danger);
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.modal-actions .btn {
    min-width: 90px;
}

@media (max-width: 640px) {
    .modal-box { padding: 1.25rem; }
    .modal-actions { flex-direction: column; }
    .modal-actions .btn { width: 100%; }
}
</style>
@endpush

@section('content')
@php
    $statusFilter = request('status');
    $stockFilter = request('stock');
    $categoryFilter = request('category');
    $searchOpen = filled(request('search'));

    $productsSubtitle = ($stats['total'] ?? $products->total()).' produits référencés';
    if ($stockFilter === 'low') {
        $productsSubtitle = 'Stock bas · '.$products->total().' produit(s)';
    } elseif ($stockFilter === 'out') {
        $productsSubtitle = 'Rupture · '.$products->total().' produit(s)';
    } elseif ($statusFilter === 'active') {
        $productsSubtitle = 'Actifs · '.$products->total().' produit(s)';
    } elseif ($statusFilter === 'inactive') {
        $productsSubtitle = 'Inactifs · '.$products->total().' produit(s)';
    } elseif (filled($categoryFilter)) {
        $productsSubtitle = e($categoryFilter).' · '.$products->total().' produit(s)';
    }
    if (request('search')) {
        $productsSubtitle .= ' · « '.e(request('search')).' »';
    }
@endphp

@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Catalogue produits',
    'subtitle' => $productsSubtitle,
    'actions' => view('admin.products.partials.index-header-actions', [
        'stats' => $stats,
        'categories' => $categories,
    ])->render(),
])

<div class="admin-mobile-page space-y-4 sm:space-y-6">

    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Catalogue produits</h1>
                <p class="page-subtitle" id="productsSubtitleMobile">{{ $productsSubtitle }}</p>
            </div>
            @include('admin.products.partials.mobile-banner-actions')
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
                       id="searchInput"
                       class="search-input"
                       placeholder="Rechercher un produit"
                       autocomplete="off"
                       value="{{ request('search') }}"
                       data-status="{{ $statusFilter }}"
                       data-stock="{{ $stockFilter }}"
                       data-category="{{ $categoryFilter }}">
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 salang-flash-success border rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 salang-flash-error border rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <x-ui.stat-grid class="grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp">
        <x-ui.stat title="Total" :value="$stats['total'] ?? $products->total()" id="kpi-products-total" padding="p-3" />
        <x-ui.stat title="Actifs" :value="$stats['active'] ?? 0" id="kpi-products-active" padding="p-3" />
        <x-ui.stat title="Stock bas" :value="$stats['low_stock'] ?? 0" id="kpi-products-low" padding="p-3" />
        <x-ui.stat title="Rupture" :value="$stats['out_of_stock'] ?? 0" id="kpi-products-out" padding="p-3" />
    </x-ui.stat-grid>

    <div class="card p-3 sm:p-4 animate-fadeInUp" id="productsListPanel">
        <div class="flex items-center justify-between gap-2 mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Liste des produits</h3>
            <span class="badge badge-muted" id="productsListCount">{{ $products->total() }} produit(s)</span>
        </div>

        <div class="hidden md:block">
            <div class="table-wrap -mx-3 sm:-mx-4 overflow-x-auto">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Catégorie</th>
                            <th class="is-center">PV</th>
                            <th class="is-num">Prix flacon</th>
                            <th class="is-num">Prix ½</th>
                            <th class="is-num">Stock</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        @include('admin.products._table_rows', ['products' => $products])
                    </tbody>
                </table>
            </div>
        </div>

        @include('admin.products._mobile_list', ['products' => $products])

        <div class="mt-3 sm:mt-4" id="productsPagination">
            @if($products->hasPages())
                <x-salang-pagination :paginator="$products" />
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = [
        document.getElementById('searchInput'),
        document.getElementById('searchInputDesktop'),
    ].filter(Boolean);
    let searchTimeout;

    function syncSearchInputs(value, source) {
        searchInputs.forEach((input) => {
            if (input !== source) input.value = value;
        });
    }

    function buildUrl(query, source) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');

        const meta = source || searchInputs[0];
        const status = meta?.dataset.status || '';
        const stock = meta?.dataset.stock || '';
        const category = meta?.dataset.category || '';

        if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');
        if (stock) url.searchParams.set('stock', stock); else url.searchParams.delete('stock');
        if (category) url.searchParams.set('category', category); else url.searchParams.delete('category');

        return url;
    }

    function fetchProducts(query, source) {
        const url = buildUrl(query, source);
        const tableBody = document.getElementById('productsTableBody');
        const mobileEl = document.getElementById('productsMobileList');

        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-[var(--primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Recherche en cours…</span>
                        </div>
                    </td>
                </tr>
            `;
        }

        if (mobileEl) {
            mobileEl.innerHTML = `
                <div class="flex items-center justify-center gap-3 py-8 text-sm text-[var(--text-secondary)]">
                    <svg class="animate-spin h-5 w-5 text-[var(--primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Recherche en cours…</span>
                </div>
            `;
        }

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTableBody = doc.getElementById('productsTableBody');
            const newMobileList = doc.getElementById('productsMobileList');
            const newPagination = doc.getElementById('productsPagination');
            const newCount = doc.getElementById('productsListCount');
            const newSubtitleMobile = doc.getElementById('productsSubtitleMobile');

            if (newTableBody && tableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }
            if (mobileEl && newMobileList) {
                mobileEl.innerHTML = newMobileList.innerHTML;
            }
            if (newPagination) {
                const paginationContainer = document.getElementById('productsPagination');
                if (paginationContainer) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                }
            }
            if (newCount) {
                const countEl = document.getElementById('productsListCount');
                if (countEl) countEl.textContent = newCount.textContent;
            }
            if (newSubtitleMobile) {
                const sub = document.getElementById('productsSubtitleMobile');
                if (sub) sub.textContent = newSubtitleMobile.textContent;
            }

            window.history.replaceState({}, '', url.toString());
        })
        .catch(function() {
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-8 text-[var(--danger)]">
                            Une erreur est survenue lors de la recherche
                        </td>
                    </tr>
                `;
            }
            if (mobileEl) {
                mobileEl.innerHTML = `
                    <div class="py-8 text-center text-sm text-[var(--danger)]">
                        Une erreur est survenue lors de la recherche
                    </div>
                `;
            }
        });
    }

    searchInputs.forEach((input) => {
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            syncSearchInputs(this.value, this);
            const query = this.value.trim();
            searchTimeout = setTimeout(() => fetchProducts(query, this), 300);
        });
    });
});
</script>
@endpush
@endsection
