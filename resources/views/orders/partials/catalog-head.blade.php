<div class="orders-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner orders-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · Commandes</p>
            <p class="shop-catalog-banner__title">Mes commandes</p>
            <p class="shop-catalog-banner__sub">
                {{ $orders->total() ?? 0 }} au total · {{ $pendingCount ?? 0 }} en attente · ${{ number_format($totalSpent ?? 0, 2) }} dépensé
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn {{ request()->filled('q') ? 'is-active' : '' }}"
                    id="ordersSearchToggle"
                    aria-expanded="false"
                    aria-controls="ordersSearchPanel"
                    aria-label="Rechercher">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <a href="{{ route('products.index') }}" class="shop-banner-icon-btn" aria-label="Nouvelle commande">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="shop-catalog-search-panel orders-search-panel" id="ordersSearchPanel" aria-hidden="true">
        <div class="orders-filter-bar filters-wrapper">
            <div class="relative flex-1 min-w-0">
                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" id="searchInput" placeholder="Rechercher une commande…" class="input pl-9 text-sm" autocomplete="off">
            </div>
            <select id="statusFilter" class="input orders-filter-select text-sm">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="processing">En traitement</option>
                <option value="completed">Terminée</option>
                <option value="cancelled">Annulée</option>
            </select>
            <input type="date" id="dateFrom" class="input orders-filter-date text-sm" aria-label="Du">
            <input type="date" id="dateTo" class="input orders-filter-date text-sm" aria-label="Au">
        </div>
    </div>
</div>
