<div class="commissions-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner commissions-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · Gains</p>
            <p class="shop-catalog-banner__title">Mes commissions</p>
            <p class="shop-catalog-banner__sub">
                ${{ number_format($stats['paid'] ?? 0, 2) }} déjà retiré · ${{ number_format($stats['wallet_balance'] ?? 0, 2) }} portefeuille · <span id="resultCountMobile">{{ $commissions->total() ?? 0 }} commissions</span>
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn"
                    id="commissionsSearchToggle"
                    aria-expanded="false"
                    aria-controls="commissionsSearchPanel"
                    aria-label="Rechercher">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <a href="{{ route('commissions.export-pdf', request()->all()) }}"
               class="shop-banner-icon-btn"
               title="Exporter PDF"
               aria-label="Exporter PDF"
               target="_blank"
               rel="noopener">
                <x-pdf-icon class="w-5 h-5" />
            </a>
        </div>
    </div>

    <div class="shop-catalog-search-panel commissions-search-panel" id="commissionsSearchPanel" aria-hidden="true">
        <div class="member-shop-search search-wrapper">
            <span class="member-shop-search__icon">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="search"
                   id="searchInput"
                   placeholder="Rechercher une commission…"
                   class="input search-input text-sm"
                   autocomplete="off"
                   aria-label="Rechercher une commission">
        </div>
    </div>
</div>
