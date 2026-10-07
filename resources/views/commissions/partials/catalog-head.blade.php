<div class="commissions-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner commissions-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · Gains</p>
            <p class="shop-catalog-banner__title">Mes commissions</p>
            <p class="shop-catalog-banner__sub">
                ${{ number_format($stats['total'] ?? 0, 2) }} gagné · ${{ number_format($stats['pending'] ?? 0, 2) }} en attente · {{ $stats['total_count'] ?? 0 }} ligne(s)
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            <button type="button"
                    class="shop-banner-icon-btn"
                    id="commissionsSearchToggle"
                    aria-expanded="false"
                    aria-controls="commissionsSearchPanel"
                    aria-label="Rechercher">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <a href="{{ route('commissions.dashboard') }}" class="shop-banner-icon-btn" aria-label="Tableau de bord">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </a>
            <a href="{{ route('commissions.levels') }}" class="shop-banner-icon-btn" aria-label="Par niveau">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="shop-catalog-search-panel commissions-search-panel" id="commissionsSearchPanel" aria-hidden="true">
        <div class="relative w-full">
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher une commission…" class="input pl-9 text-sm w-full" autocomplete="off">
        </div>
        <div class="commissions-search-meta">
            <span id="resultCountMobile">{{ $commissions->total() ?? 0 }} commissions</span>
            <a href="{{ route('commissions.export-pdf', request()->all()) }}" class="commissions-search-pdf" target="_blank" rel="noopener">Exporter PDF</a>
        </div>
    </div>
</div>
