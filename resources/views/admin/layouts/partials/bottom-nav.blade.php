@include('admin.layouts.partials.mobile-more-sheet')

<nav class="mobile-bottom-nav md:hidden" id="adminMobileBottomNav" aria-label="Navigation admin mobile">
    <a href="{{ route('admin.dashboard') }}"
       class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        <span>Accueil</span>
    </a>

    <a href="{{ route('admin.users') }}"
       class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span>Membres</span>
    </a>

    <a href="{{ route('admin.orders.index') }}"
       class="nav-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <span>Commandes</span>
    </a>

    <a href="{{ route('admin.commissions') }}"
       class="nav-item {{ request()->routeIs('admin.commissions*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Commissions</span>
    </a>

    <button type="button"
            class="nav-item nav-item--button {{ request()->routeIs('admin.cashiers*', 'admin.packages*', 'admin.products*', 'admin.kyc*', 'admin.consultations*', 'admin.wallets*', 'admin.withdrawals*', 'admin.ranks*', 'admin.reports*', 'admin.pos-reports.*', 'admin.settings*', 'admin.pv.*') ? 'active' : '' }}"
            @click="mobileMoreOpen = !mobileMoreOpen"
            :class="{ 'active': mobileMoreOpen }"
            aria-label="Ouvrir le menu administration"
            :aria-expanded="mobileMoreOpen">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <span>Plus</span>
        <span id="adminMobileNavBadge" class="badge-count workflow-notify-accent" style="display:none;">0</span>
    </button>
</nav>
