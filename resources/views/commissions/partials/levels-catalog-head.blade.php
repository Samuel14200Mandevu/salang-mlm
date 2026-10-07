@php
    $levelItems = collect($levels ?? [])->except('total');
    $totalPaid = (float) ($levels['total'] ?? 0);
    $typesCount = $levelItems->count();
    $linesCount = (int) $levelItems->sum('count');
@endphp
<div class="commissions-levels-catalog-head shop-catalog-head member-shop-animate member-shop-animate--1">
    <div class="shop-catalog-banner commissions-levels-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Salang MLM · Niveaux</p>
            <p class="shop-catalog-banner__title">Commissions par niveau</p>
            <p class="shop-catalog-banner__sub">
                ${{ number_format($totalPaid, 2) }} payé · {{ $typesCount }} type(s) · {{ $linesCount }} commission(s)
            </p>
        </div>
        <div class="shop-catalog-banner__tools">
            <a href="{{ route('commissions.index') }}" class="shop-banner-icon-btn" aria-label="Mes commissions">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </a>
            <a href="{{ route('commissions.dashboard') }}" class="shop-banner-icon-btn" aria-label="Tableau de bord">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </a>
            <a href="{{ route('rank.index') }}" class="shop-banner-icon-btn" aria-label="Mon rang">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </a>
        </div>
    </div>
</div>
