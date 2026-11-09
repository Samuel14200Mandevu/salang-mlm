<div class="products-header-tools flex items-center gap-2 order-first">
    <div class="header-search products-header-search">
        <div class="search-wrapper relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   id="historySearchDesktop"
                   class="search-input"
                   placeholder="Rechercher…"
                   autocomplete="off"
                   value="{{ request('search') }}">
        </div>
    </div>

    <div class="relative shrink-0" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button"
                @click="open = !open"
                class="btn btn-outline btn-sm sm:btn-md {{ !empty($filterActive) ? 'border-[var(--primary)] text-[var(--primary)]' : '' }}"
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
            <a href="{{ $filterUrl(['type' => null]) }}"
               class="{{ empty($typeFilter) ? 'is-active' : '' }}">
                <span>Tous les types</span>
            </a>
            @foreach($typeLabels as $type => $label)
                <a href="{{ $filterUrl(['type' => $type]) }}"
                   class="{{ ($typeFilter ?? null) === $type ? 'is-active' : '' }}">
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

<a href="{{ route('admin.products.stock-overview') }}" class="btn btn-outline btn-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    Stock
</a>
