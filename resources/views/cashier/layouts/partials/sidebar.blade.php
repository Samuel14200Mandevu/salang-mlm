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

            <div class="admin-sidebar-panel h-full bg-[var(--bg-navbar)] border-r border-[var(--border-color)] flex flex-col overflow-hidden sidebar-shell">
                <div class="cashier-sidebar-accent" aria-hidden="true"></div>

                @include('layouts.partials.sidebar-mobile-drawer-head', ['drawerVariant' => 'cashier'])

                <!-- Logo (desktop) -->
                <div class="sidebar-logo-bar hidden md:flex items-center justify-between h-16 border-b border-[var(--border-color)] flex-shrink-0"
                     :class="sidebarOpen ? 'px-4' : 'px-2'">
                    <a href="{{ route('cashier.dashboard') }}" class="sidebar-brand-link flex items-center justify-center flex-1 min-w-0">
                        <img src="{{ asset('images/salang_logo.png') }}"
                             alt="Salang"
                             width="160"
                             height="56"
                             decoding="async"
                             class="sidebar-logo-img sidebar-logo-full logo-themeable">
                    </a>
                </div>

                <!-- Menu -->
                <nav class="admin-sidebar-nav sidebar-mobile-drawer-nav flex-1 overflow-y-auto py-4 px-2 custom-scrollbar"
                     @click="if (isMobile && $event.target.closest('a.sidebar-link, button.sidebar-link')) closeSidebar()">
                    <ul class="space-y-0.5">

                        <!-- Accueil + chevron <> desktop -->
                        <li class="admin-sidebar-home-item">
                            <div class="admin-sidebar-home-row">
                                <a href="{{ route('cashier.dashboard') }}"
                                   class="sidebar-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}"
                                   data-title="Accueil">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    <span class="label transition-opacity duration-200"
                                          :class="!sidebarOpen ? 'hidden' : ''">
                                        Accueil
                                    </span>
                                </a>
                                <button type="button"
                                        class="admin-rail-chevron"
                                        x-show="!isMobile"
                                        x-cloak
                                        @click.stop="sidebarOpen = !sidebarOpen; window.salangPersistSidebarOpen(sidebarOpen)"
                                        :aria-label="sidebarOpen ? 'Réduire le menu' : 'Agrandir le menu'"
                                        :title="sidebarOpen ? 'Réduire le menu' : 'Agrandir le menu'">
                                    <span class="admin-rail-chevron__icon" aria-hidden="true" x-text="sidebarOpen ? '‹' : '›'"></span>
                                </button>
                            </div>
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
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
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
                               class="sidebar-link {{ request()->routeIs('cashier.commissions*', 'cashier.offline-bonus.calculate*') ? 'active' : '' }}"
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
                        <li>
                            <a href="{{ route('cashier.guichet.index') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.guichet.*', 'cashier.offline-bonus.pay*') ? 'active' : '' }}"
                               data-title="Retraits guichet">
                                <div class="relative flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 0118 0z"/>
                                    </svg>
                                    <span id="cashierGuichetDot" class="notification-dot workflow-notify-accent" style="display:none;"></span>
                                </div>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Retraits guichet
                                    <span id="cashierGuichetBadge" class="badge-count workflow-notify-accent" style="display:none;">0</span>
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
                        <li>
                            <a href="{{ route('cashier.pv.transactions') }}"
                               class="sidebar-link {{ request()->routeIs('cashier.pv.*') ? 'active' : '' }}"
                               data-title="Gestion des PV">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Gestion des PV
                                </span>
                            </a>
                        </li>

                        <!-- Admin -->
                        @auth
                            @if(Auth::user()?->hasRole('admin'))
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

                        <li class="pt-3 mt-3 border-t border-[var(--border-color)]">
                            <a href="{{ url('/?site=1') }}"
                               class="sidebar-link"
                               data-title="Voir le site">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span class="label transition-opacity duration-200"
                                      :class="!sidebarOpen ? 'hidden' : ''">
                                    Voir le site
                                </span>
                            </a>
                        </li>

                        <!-- Déconnexion (même style rouge que IT manager) -->
                        <li class="admin-sidebar-logout-item pt-4 mt-4 border-t border-[var(--border-color)]">
                            <form method="POST" action="{{ route('logout') }}" id="logout-form" class="admin-sidebar-logout logout-form sidebar-logout-form">
                                @csrf
                                <button type="submit"
                                        class="sidebar-link sidebar-link--button sidebar-link--logout danger w-full"
                                        data-title="Déconnexion">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
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

                <p class="sidebar-mobile-drawer-version md:hidden" aria-hidden="true">Version 1.0.0</p>
            </div>
        </aside>
