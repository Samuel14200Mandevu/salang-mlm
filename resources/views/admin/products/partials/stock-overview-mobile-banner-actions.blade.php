@php
    $searchOpen = filled(request('search'));
    $stockFilter = request('stock');
    $activeOnly = ! request()->exists('active_only') || request()->boolean('active_only');
    $filterActive = filled($stockFilter) || (request()->exists('active_only') && ! $activeOnly);

    $filterQueryBase = array_filter([
        'search' => request('search'),
        'active_only' => request()->exists('active_only') ? (request()->boolean('active_only') ? '1' : '0') : '1',
    ], fn ($v) => $v !== null && $v !== '');

    $filterUrl = function (array $overrides = []) use ($filterQueryBase) {
        return route('admin.products.stock-overview', array_filter(
            array_merge($filterQueryBase, $overrides),
            fn ($v) => $v !== null && $v !== ''
        ));
    };
@endphp
<div class="admin-title-banner__tools">
    <button type="button"
            class="admin-banner-icon-btn{{ $searchOpen ? ' is-active' : '' }}"
            data-admin-search-toggle="1"
            aria-controls="adminSearchPanel"
            aria-expanded="{{ $searchOpen ? 'true' : 'false' }}"
            aria-label="Rechercher">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </button>

    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button"
                @click="open = !open"
                class="admin-banner-icon-btn{{ $filterActive ? ' is-active' : '' }}"
                :aria-expanded="open"
                aria-label="Filtrer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
        </button>
        <div x-show="open"
             x-cloak
             x-transition
             @click.outside="open = false"
             class="users-filter-menu">
            <a href="{{ $filterUrl(['stock' => null, 'active_only' => '1']) }}"
               class="{{ ! $filterActive ? 'is-active' : '' }}">
                <span>Tous (actifs)</span>
            </a>
            <a href="{{ $filterUrl(['stock' => 'low', 'active_only' => '1']) }}"
               class="{{ $stockFilter === 'low' ? 'is-active' : '' }}">
                <span>Stock bas</span>
            </a>
            <a href="{{ $filterUrl(['stock' => 'out', 'active_only' => '1']) }}"
               class="{{ $stockFilter === 'out' ? 'is-active' : '' }}">
                <span>Rupture</span>
            </a>
            <a href="{{ $filterUrl(['stock' => 'ok', 'active_only' => '1']) }}"
               class="{{ $stockFilter === 'ok' ? 'is-active' : '' }}">
                <span>Stock OK</span>
            </a>
            <div class="products-filter-sep">Statut</div>
            <a href="{{ $filterUrl(['active_only' => '1', 'stock' => $stockFilter]) }}"
               class="{{ $activeOnly ? 'is-active' : '' }}">
                <span>Actifs seulement</span>
            </a>
            <a href="{{ $filterUrl(['active_only' => '0', 'stock' => $stockFilter]) }}"
               class="{{ ! $activeOnly ? 'is-active' : '' }}">
                <span>Inclure inactifs</span>
            </a>
        </div>
    </div>

    <a href="{{ route('admin.products.stock-history') }}"
       class="admin-banner-icon-btn"
       title="Historique"
       aria-label="Historique">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </a>

    <a href="{{ route('admin.products') }}" class="btn btn-outline btn-sm btn-round-icon" title="Catalogue" aria-label="Catalogue">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
</div>
