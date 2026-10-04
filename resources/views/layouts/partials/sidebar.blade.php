        <div x-show="sidebarOpen && isMobile" 
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
        </div>

        <!-- Sidebar -->
        <aside id="sidebar" aria-label="Navigation membre" 
               class="fixed top-0 left-0 z-50 h-full transition-all duration-300 ease-in-out"
               :class="{
                  'w-64': sidebarOpen && !isMobile,
                  'w-20': !sidebarOpen && !isMobile,
                  'w-64 translate-x-0': sidebarOpen && isMobile,
                  'w-64 -translate-x-full': !sidebarOpen && isMobile
               }">
            
            <div class="member-sidebar h-full flex flex-col overflow-hidden bg-[var(--bg-navbar)]/95 backdrop-blur-md border-r border-[var(--border-color)] shadow-[4px_0_24px_rgba(0,0,0,0.06)]">
                
                <!-- Logo -->
                <div class="sidebar-brand flex items-center justify-between h-16 px-4 flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center flex-1 min-w-0">
                        <x-ui.image
                            src="images/salang_logo.png"
                            alt="Salang"
                            class="logo-themeable transition-all duration-300 w-auto object-contain"
                            :class="(!sidebarOpen && !isMobile) ? 'h-9 max-w-[2.75rem]' : 'h-10 sm:h-11'"
                            width="200"
                            height="56"
                            :priority="true"
                            :lazy="false"
                        />
                    </a>
                    <button @click="sidebarOpen = false" 
                            class="lg:hidden p-2 rounded-lg hover:bg-[var(--bg-secondary)] transition-colors">
                        <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Menu -->
                <nav class="sidebar-nav flex-1 overflow-y-auto py-3 px-2 custom-scrollbar"
                     :class="{ 'sidebar-nav--icon-only': !sidebarOpen && !isMobile }">
                    <ul class="space-y-0.5">
                        
                        <!-- Accueil -->
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Accueil
                                </span>
                            </a>
                        </li>

                        <!-- Boutique -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Boutique
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('products.index') }}" 
                               class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Produits
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('subscriptions.index') }}" 
                               class="sidebar-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Packages
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('orders.index') }}" 
                               class="sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mes commandes
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('cart.index') }}" 
                               class="sidebar-link {{ request()->routeIs('cart.index') ? 'active' : '' }}">
                                <div class="relative">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <x-ui.cart-badge variant="sidebar" />
                                </div>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Panier
                                </span>
                            </a>
                        </li>

                        <!-- Réseau -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Réseau
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('network.index') }}" 
                               class="sidebar-link {{ request()->routeIs('network.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mon réseau
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('rank.index') }}" 
                               class="sidebar-link {{ request()->routeIs('rank.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mon rang
                                </span>
                            </a>
                        </li>

                        <!-- Finances -->
                        <li>
                            <div class="sidebar-section transition-opacity duration-200" 
                                 :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                Finances
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('wallet.index') }}" 
                               class="sidebar-link {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Portefeuille
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('commissions.index') }}" 
                               class="sidebar-link {{ request()->routeIs('commissions.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Mes commissions
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('kyc.index') }}" 
                               class="sidebar-link {{ request()->routeIs('kyc.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="label transition-opacity duration-200" 
                                      :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                    Vérification KYC
                                </span>
                            </a>
                        </li>

                        <!-- Administration (visible uniquement pour les admins) -->
                        @auth
                            @if(Auth::user()->hasRole('admin'))
                                <li>
                                    <div class="sidebar-section transition-opacity duration-200" 
                                         :class="sidebarOpen ? 'opacity-100 block' : 'opacity-0 hidden'">
                                        Administration
                                    </div>
                                </li>
                                <li>
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="label transition-opacity duration-200" 
                                              :class="sidebarOpen ? 'opacity-100 inline-block' : 'opacity-0 hidden'">
                                            Panneau d'administration
                                        </span>
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>
                </nav>

                <!-- Pied de page de la sidebar -->
                <div class="sidebar-user flex-shrink-0">
                    <div class="flex items-center gap-3" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <div class="avatar-ring w-9 h-9 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            @auth
                                @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                                    <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" 
                                         alt="Avatar" 
                                         class="w-9 h-9 rounded-full object-cover">
                                @else
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                @endif
                            @endauth
                        </div>
                        <div class="transition-all duration-300 overflow-hidden" 
                             :class="sidebarOpen ? 'opacity-100 max-w-[200px]' : 'opacity-0 max-w-0'">
                            <p class="text-sm font-medium text-[var(--text-primary)] truncate whitespace-nowrap">
                                @auth {{ Auth::user()->name }} @endauth
                            </p>
                            <p class="text-xs text-[var(--text-secondary)] truncate whitespace-nowrap">
                                @auth {{ Auth::user()->email }} @endauth
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
