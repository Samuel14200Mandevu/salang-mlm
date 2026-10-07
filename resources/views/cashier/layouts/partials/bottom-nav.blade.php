@include('cashier.layouts.partials.mobile-more-sheet')

        <nav class="mobile-bottom-nav lg:hidden" id="mobileBottomNav" aria-label="Navigation caisse mobile">
            <a href="{{ route('cashier.dashboard') }}"
               class="nav-item {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Accueil</span>
            </a>

            <a href="{{ route('cashier.pos') }}"
               class="nav-item {{ request()->routeIs('cashier.pos') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>POS</span>
            </a>

            <a href="{{ route('cashier.orders') }}"
               class="nav-item {{ request()->routeIs('cashier.orders*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Commandes</span>
            </a>

            <a href="{{ route('cashier.members') }}"
               class="nav-item {{ request()->routeIs('cashier.members*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Membres</span>
            </a>

            <button type="button"
                    class="nav-item nav-item--button {{ request()->routeIs('cashier.consultations*', 'cashier.reports*', 'cashier.commissions', 'cashier.daily-sales', 'cashier.expenses*', 'cashier.history', 'cashier.customers', 'cashier.profile') ? 'active' : '' }}"
                    @click="mobileMoreOpen = !mobileMoreOpen"
                    :class="{ 'active': mobileMoreOpen }"
                    aria-label="Ouvrir le menu caisse"
                    :aria-expanded="mobileMoreOpen">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span>Plus</span>
                <span id="cashierMobileNavBadge" class="badge-count workflow-notify-accent" style="display:none;">0</span>
            </button>
        </nav>
    </div>
