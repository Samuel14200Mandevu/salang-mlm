<div class="commissions-dashboard-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner commissions-dashboard-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · Gains</p>
            <p class="shop-catalog-banner__title">Tableau des commissions</p>
            <p class="shop-catalog-banner__sub">
                ${{ number_format($stats['total_amount'] ?? 0, 2) }} gagné · ${{ number_format($stats['paid_amount'] ?? 0, 2) }} payé · ${{ number_format($stats['pending_amount'] ?? 0, 2) }} en attente
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            <a href="{{ route('commissions.index') }}" class="shop-banner-icon-btn" aria-label="Toutes les commissions">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </a>
            <a href="{{ route('commissions.levels') }}" class="shop-banner-icon-btn" aria-label="Par niveau">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </a>
            <a href="{{ route('wallet.index') }}" class="shop-banner-icon-btn" aria-label="Portefeuille">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </a>
        </div>
    </div>
</div>
