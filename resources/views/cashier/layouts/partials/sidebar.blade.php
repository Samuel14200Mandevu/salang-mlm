        <aside id="sidebar" aria-label="Navigation caisse"
               class="fixed top-0 left-0 z-50 h-full transition-all duration-200 ease-in-out"
               :role="isMobile && sidebarOpen ? 'dialog' : 'complementary'"
               :aria-modal="isMobile && sidebarOpen ? 'true' : null"
               :aria-hidden="isMobile && !sidebarOpen ? 'true' : null"
               :class="{
                  'w-64': sidebarOpen && !isMobile,
                  'w-20 sidebar-is-rail': !sidebarOpen && !isMobile,
                  'admin-sidebar-drawer-open w-64 translate-x-0': sidebarOpen && isMobile,
                  'w-64 -translate-x-full': !sidebarOpen && isMobile
               }">

            <div class="admin-sidebar-panel h-full bg-[var(--bg-navbar)] border-r border-[var(--border-color)] flex flex-col overflow-hidden">
                <div class="cashier-sidebar-accent" aria-hidden="true"></div>

                <!-- Logo -->
                <div class="sidebar-logo-bar flex items-center justify-between h-16 border-b border-[var(--border-color)] flex-shrink-0"
                     :class="sidebarOpen ? 'px-4' : 'px-2'">
                    <a href="{{ route('cashier.dashboard') }}" class="sidebar-brand-link flex items-center justify-center flex-1 min-w-0">
                        <img src="{{ asset('images/salang_logo.png') }}"
                             alt="Salang"
                             width="160"
                             height="56"
                             decoding="async"
                             class="sidebar-logo-img sidebar-logo-full logo-themeable">
                    </a>
                    <button type="button"
                            id="cashierDrawerCloseBtn"
                            @click="closeSidebar()"
                            class="admin-drawer-close lg:hidden p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors"
                            aria-label="Fermer le menu">
                        <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="admin-drawer-mobile-head lg:hidden" aria-hidden="true">
                    Menu caisse
                </div>

                <!-- Menu -->
                <nav class="admin-sidebar-nav flex-1 overflow-y-auto py-4 px-2 custom-scrollbar">
                    <ul class="space-y-0.5">

                        <!-- Dashboard -->
                        <li>
                            <a href="{{ route('cashier.dashboard') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}"
                               data-title="Tableau de bord">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Accueil
                                </span>
                            </a>
                        </li>

                        <!-- Consultations -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200"
                                 :class="!sidebarOpen ? 'hidden' : ''">
                                Consultations
                            </div>
                        </li>
                        <li>
                            <a href="{{ route('cashier.consultations.index') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.consultations*') ? 'active' : '' }}"
                               data-title="Mes Consultations">
                                <div class="relative flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span id="cashierConsultationDot" class="notification-dot workflow-notify-accent" style="display:none;"></span>
                                </div>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Mes Consultations
                                    <span id="cashierConsultationBadge" class="badge-count workflow-notify-accent" style="display:none;">0</span>
                                </span>
                            </a>
                        </li>

                        <!-- Ventes -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200"
                                 :class="!sidebarOpen ? 'hidden' : ''">
                                Ventes
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('cashier.pos') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.pos') ? 'active' : '' }}"
                               data-title="Point de Vente">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Point de Vente
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cashier.orders') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.orders*') ? 'active' : '' }}"
                               data-title="Commandes">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Commandes
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cashier.daily-sales') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.daily-sales') ? 'active' : '' }}"
                               data-title="Ventes du jour">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Ventes du jour
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cashier.commissions') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.commissions') ? 'active' : '' }}"
                               data-title="Commissions">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Commissions
                                </span>
                            </a>
                        </li>
                        <!-- Dépenses -->
                        <li>
                            <a href="{{ route('cashier.expenses.index') }}"
                            class="sidebar-link {{ request()->routeIs('cashier.expenses*') ? 'active' : '' }}"
                            data-title="Dépenses">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                    :class="!sidebarOpen ? 'hidden' : ''">
                                    Dépenses
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cashier.history') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.history') ? 'active' : '' }}"
                               data-title="Historique">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Historique
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('cashier.reports.index') }}"
                            class="sidebar-link {{ request()->routeIs('cashier.reports*') ? 'active' : '' }}"
                            data-title="Rapports journaliers">
                                <div class="relative flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span id="cashierReportDot" class="notification-dot workflow-notify-accent" style="display:none;"></span>
                                </div>
                                <span class="label transition-opacity duration-200"
                                    :class="!sidebarOpen ? 'hidden' : ''">
                                    Rapports
                                    <span id="cashierReportBadge" class="badge-count workflow-notify-accent" style="display:none;">0</span>
                                </span>
                            </a>
                        </li>

                        <!-- Clients -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200"
                                 :class="!sidebarOpen ? 'hidden' : ''">
                                Clients
                            </div>
                        </li>
                        <li>
                            <a href="{{ route('cashier.customers') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.customers') ? 'active' : '' }}"
                               data-title="Clients">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Clients
                                </span>
                            </a>
                        </li>

                        <!-- Membres -->
                        <li>
                            <a href="{{ route('cashier.members') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.members') ? 'active' : '' }}"
                               data-title="Membres">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Membres
                                </span>
                            </a>
                        </li>

                        <!-- Profil -->
                        <li>
                            <a href="{{ route('cashier.profile') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.profile') ? 'active' : '' }}"
                               data-title="Mon Profil">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Mon Profil
                                </span>
                            </a>
                        </li>

                        <!-- Admin -->
                        @auth
                            @if(Auth::user()->hasRole('admin'))
                                <li>
                                    <div class="sidebar-section transition-opacity duration-200"
                                         :class="!sidebarOpen ? 'hidden' : ''">
                                        Administration
                                    </div>
                                </li>
                                <li>
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                                       data-title="Panel Admin">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="label transition-opacity duration-200"
                                              :class="!sidebarOpen ? 'hidden' : ''">
                                            Panel Admin
                                        </span>
                                    </a>
                                </li>
                            @endif
                        @endauth

                        <!-- Déconnexion -->
                        <li class="pt-4 mt-4 border-t border-[var(--border-color)]">
                            <form method="POST" action="{{ route('logout') }}" id="logout-form" class="logout-form">
                                @csrf
                                <button type="submit" class="sidebar-link danger">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="label transition-opacity duration-200"
                                          :class="!sidebarOpen ? 'hidden' : ''">
                                        Déconnexion
                                    </span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-[var(--border-color)] flex-shrink-0">
                    <div class="flex items-center gap-3" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <div class="w-8 h-8 rounded-full bg-[var(--primary)] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            @auth
                                @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                                    <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}"
                                         alt="Avatar"
                                         class="w-8 h-8 rounded-full object-cover">
                                @else
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                @endif
                            @endauth
                        </div>
                        <div class="sidebar-user-text transition-all duration-200 overflow-hidden min-w-0"
                             :class="sidebarOpen ? 'opacity-100 max-w-[200px]' : 'opacity-0 max-w-0 w-0'">
                            <p class="text-sm font-medium text-[var(--text-primary)] truncate whitespace-nowrap">
                                @auth {{ Auth::user()->name }} @endauth
                            </p>
                            <p class="text-xs text-[var(--text-secondary)] truncate whitespace-nowrap">
                                @auth Caissier @endauth
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
