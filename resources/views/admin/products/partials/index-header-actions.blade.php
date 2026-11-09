@php
    $statusFilter = request('status');
    $stockFilter = request('stock');
    $categoryFilter = request('category');
    $filterActive = filled($statusFilter) || filled($stockFilter) || filled($categoryFilter);

    if ($statusFilter === 'active') {
        $filterButtonLabel = 'Actifs';
    } elseif ($statusFilter === 'inactive') {
        $filterButtonLabel = 'Inactifs';
    } elseif ($stockFilter === 'low') {
        $filterButtonLabel = 'Stock bas';
    } elseif ($stockFilter === 'out') {
        $filterButtonLabel = 'Rupture';
    } elseif (filled($categoryFilter)) {
        $filterButtonLabel = $categoryFilter;
    } else {
        $filterButtonLabel = 'Filtrer';
    }

    $filterQueryBase = array_filter([
        'search' => request('search'),
    ], fn ($v) => filled($v));

    $filterUrl = function (array $overrides = []) use ($filterQueryBase) {
        return route('admin.products', array_filter(
            array_merge($filterQueryBase, $overrides),
            fn ($v) => $v !== null && $v !== ''
        ));
    };
@endphp

<div class="products-header-tools flex items-center gap-2 order-first">
    <div class="header-search products-header-search">
        <div class="search-wrapper relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   id="searchInputDesktop"
                   class="search-input"
                   placeholder="Rechercher…"
                   autocomplete="off"
                   value="{{ request('search') }}"
                   data-status="{{ $statusFilter }}"
                   data-stock="{{ $stockFilter }}"
                   data-category="{{ $categoryFilter }}">
        </div>
    </div>

    <div class="relative shrink-0" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button"
                @click="open = !open"
                class="btn btn-outline btn-sm sm:btn-md {{ $filterActive ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
                :aria-expanded="open"
                aria-haspopup="true"
                aria-label="Filtrer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <span>{{ $filterButtonLabel }}</span>
        </button>
    <div x-show="open"
         x-cloak
         x-transition
         @click.outside="open = false"
         class="users-filter-menu">
        <a href="{{ $filterUrl(['status' => null, 'stock' => null, 'category' => null]) }}"
           class="{{ ! $filterActive ? 'is-active' : '' }}">
            <span>Tous</span>
        </a>
        <a href="{{ $filterUrl(['status' => 'active', 'stock' => null, 'category' => $categoryFilter]) }}"
           class="{{ $statusFilter === 'active' && ! $stockFilter ? 'is-active' : '' }}">
            <span>Actifs</span>
            <span class="filter-count">{{ $stats['active'] ?? 0 }}</span>
        </a>
        <a href="{{ $filterUrl(['status' => 'inactive', 'stock' => null, 'category' => $categoryFilter]) }}"
           class="{{ $statusFilter === 'inactive' && ! $stockFilter ? 'is-active' : '' }}">
            <span>Inactifs</span>
            <span class="filter-count">{{ $stats['inactive'] ?? 0 }}</span>
        </a>
        <a href="{{ $filterUrl(['status' => null, 'stock' => 'low', 'category' => $categoryFilter]) }}"
           class="{{ $stockFilter === 'low' ? 'is-active' : '' }}">
            <span>Stock bas</span>
            <span class="filter-count">{{ $stats['low_stock'] ?? 0 }}</span>
        </a>
        <a href="{{ $filterUrl(['status' => null, 'stock' => 'out', 'category' => $categoryFilter]) }}"
           class="{{ $stockFilter === 'out' ? 'is-active' : '' }}">
            <span>Rupture</span>
            <span class="filter-count">{{ $stats['out_of_stock'] ?? 0 }}</span>
        </a>
        @if(isset($categories) && $categories->isNotEmpty())
            <div class="products-filter-sep">Catégories</div>
            <a href="{{ $filterUrl(['category' => null, 'status' => $statusFilter, 'stock' => $stockFilter]) }}"
               class="{{ ! $categoryFilter ? 'is-active' : '' }}">
                <span>Toutes</span>
            </a>
            @foreach($categories as $category)
                <a href="{{ $filterUrl(['category' => $category, 'status' => $statusFilter, 'stock' => $stockFilter]) }}"
                   class="{{ $categoryFilter === $category ? 'is-active' : '' }}">
                    <span>{{ $category }}</span>
                </a>
            @endforeach
        @endif
    </div>
    </div>
</div>

<a href="{{ route('admin.products.stock-overview') }}" class="btn btn-outline btn-sm" title="Gérer le stock">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <span class="hidden sm:inline">Stock</span>
</a>
<a href="{{ route('admin.products.barcodes') }}" class="btn btn-outline btn-sm" title="Codes-barres">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h2m4 0h10M4 12h16M4 18h2m4 0h10"/>
    </svg>
    <span class="hidden sm:inline">Codes-barres</span>
</a>
<a href="{{ route('admin.packages') }}" class="btn btn-outline btn-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    Packages
</a>
<a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
    Ajouter
</a>
