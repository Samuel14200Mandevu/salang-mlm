        @php
            $isItManagerShell = auth()->user()?->usesItManagerShell() ?? false;
            $isMemberHomeTopbar = $isItManagerShell
                ? request()->routeIs('it-manager.dashboard')
                : request()->routeIs('dashboard');
            $memberTopTabs = $isItManagerShell
                ? [
                    ['label' => 'Accueil', 'route' => 'it-manager.dashboard', 'match' => 'it-manager.dashboard', 'params' => []],
                    ['label' => 'Registre', 'route' => 'it-manager.activity-register.show', 'match' => 'it-manager.activity-register.*', 'params' => []],
                    ['label' => 'Suivi', 'route' => 'it-manager.follow-up.index', 'match' => 'it-manager.follow-up.*', 'params' => []],
                    ['label' => 'Messages', 'route' => 'site-management.contacts.index', 'match' => 'site-management.contacts*', 'params' => []],
                    ['label' => 'Annonces', 'route' => 'it-manager.announcements.create', 'match' => 'it-manager.announcements.*', 'params' => []],
                ]
                : [
                    ['label' => 'Accueil', 'route' => 'dashboard', 'match' => 'dashboard', 'params' => []],
                    ['label' => 'Services', 'route' => 'services.index', 'match' => 'services.*', 'params' => []],
                    ['label' => 'Boutique', 'route' => 'products.index', 'match' => 'products.*', 'params' => []],
                    ['label' => 'Réseau', 'route' => 'network.index', 'match' => 'network.*', 'params' => []],
                    ['label' => 'Portefeuille', 'route' => 'wallet.index', 'match' => 'wallet.*', 'params' => []],
                ];
            $topbarBrandRoute = $isItManagerShell ? 'it-manager.dashboard' : 'dashboard';
        @endphp
        <div class="main-wrapper {{ $isMemberHomeTopbar ? 'main-wrapper--member-home' : '' }}"
             :style="{
                'margin-left': (!isMobile && sidebarOpen) ? '16rem' : (!isMobile && !sidebarOpen) ? '5rem' : '0',
                'width': (!isMobile && sidebarOpen) ? 'calc(100% - 16rem)' : (!isMobile && !sidebarOpen) ? 'calc(100% - 5rem)' : '100%'
             }">

            <nav class="cashier-topbar member-topbar member-mobile-topbar bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-40 flex-shrink-0 {{ $isMemberHomeTopbar ? 'member-mobile-topbar--home' : '' }}" aria-label="Barre supérieure">
                <div class="px-3 sm:px-4 lg:px-6">
                    <div class="flex justify-between items-center h-14 sm:h-16 gap-2">

                        <a href="{{ route($topbarBrandRoute) }}"
                           class="admin-topbar-brand items-center gap-2.5 shrink-0 no-underline"
                           aria-label="Salang Group — Health Care International">
                            <img src="{{ asset('images/salang_logo.png') }}"
                                 alt="Salang"
                                 width="120"
                                 height="40"
                                 decoding="async"
                                 class="admin-topbar-brand__logo logo-themeable">
                            <span class="admin-topbar-brand__text">
                                <span class="admin-topbar-brand__title">Salang Group</span>
                                <span class="admin-topbar-brand__subtitle">Health Care International</span>
                            </span>
                        </a>

                        <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 member-mobile-topbar__start md:hidden">
                            <button type="button"
                                    @click="sidebarOpen = !sidebarOpen; if (!isMobile && typeof window.salangPersistSidebarOpen === 'function') window.salangPersistSidebarOpen(sidebarOpen)"
                                    class="member-mobile-topbar__menu p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors flex-shrink-0"
                                    aria-label="Ouvrir ou fermer le menu latéral">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <div class="min-w-0 flex-1 member-mobile-topbar__title">
                                @if(isset($header) && $header)
                                    <div class="truncate text-base sm:text-lg font-semibold text-[var(--text-primary)]">{{ $header }}</div>
                                @else
                                    <h1 id="pageTitle" class="text-base sm:text-lg lg:text-xl font-semibold text-[var(--text-primary)] truncate">
                                        @yield('title', 'Espace membre')
                                    </h1>
                                @endif
                            </div>
                        </div>

                        {{-- Onglets Quixotic (desktop) — pas de classe "hidden" :
                             .member-app .hidden { display:none } casse md:flex --}}
                        <div class="admin-quixotic-tabs-host flex flex-1 items-center justify-center min-w-0 px-2">
                            <div class="admin-quixotic-tabs flex items-center gap-1">
                                @foreach($memberTopTabs as $tab)
                                    @php
                                        $tabActive = request()->routeIs($tab['match']);
                                        try {
                                            $tabHref = route($tab['route'], $tab['params']);
                                        } catch (\Throwable $e) {
                                            $tabHref = '#';
                                        }
                                    @endphp
                                    <a href="{{ $tabHref }}"
                                       @class([
                                           'admin-quixotic-tab px-3 py-1.5 text-sm font-medium rounded-xl transition-colors whitespace-nowrap',
                                           'is-active' => $tabActive,
                                       ])>
                                        {{ $tab['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-1 sm:gap-2 lg:gap-3 flex-shrink-0 member-mobile-topbar__actions">

                            @unless ($isItManagerShell)
                            <a href="{{ route('cart.index') }}"
                               class="member-topbar-hide-mobile member-mobile-topbar__cart p-1.5 sm:p-2 rounded-xl hover:bg-[var(--bg-secondary)] transition-colors relative inline-flex max-md:hidden"
                               aria-label="Panier">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <x-ui.cart-badge variant="topbar" />
                            </a>
                            @endunless

                            @auth
                                @include('layouts.partials.member-notifications-topbar')
                            @endauth

                            <button type="button"
                                    id="theme-toggle"
                                    class="member-mobile-topbar__theme p-1.5 sm:p-2 rounded-xl hover:bg-[var(--bg-secondary)] transition-colors"
                                    aria-label="Changer de thème">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="theme-icon" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon-path"/>
                                </svg>
                            </button>

                            @auth
                                <div class="relative member-mobile-topbar__profile" x-data="{ open: false }">
                                    <button type="button"
                                            @click="open = !open"
                                            class="member-topbar-compact flex items-center gap-1 sm:gap-2 p-1.5 sm:p-2 rounded-xl hover:bg-[var(--bg-secondary)] transition-colors"
                                            aria-label="Menu profil"
                                            :aria-expanded="open">
                                        <span class="inline max-md:hidden text-xs sm:text-sm text-[var(--text-primary)] truncate max-w-[80px] md:max-w-[120px]">
                                            {{ Auth::user()?->name }}
                                        </span>
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[var(--primary)] flex items-center justify-center text-white font-bold text-xs sm:text-sm flex-shrink-0 overflow-hidden">
                                            @if(Auth::user()?->avatar && file_exists(public_path('storage/avatars/' . Auth::user()?->avatar)))
                                                <img src="{{ asset('storage/avatars/' . Auth::user()?->avatar) }}"
                                                     alt="Avatar"
                                                     class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover">
                                            @else
                                                {{ strtoupper(substr((string) Auth::user()?->name, 0, 1)) }}
                                            @endif
                                        </div>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                         class="absolute right-0 mt-2 w-48 sm:w-56 py-1 z-50 bg-[var(--bg-card)] rounded-xl shadow-[var(--shadow-md)] border border-[var(--border-color)]"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         style="display: none;">

                                        <div class="px-4 py-2 border-b border-[var(--border-color)] sm:hidden">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">{{ Auth::user()?->name }}</p>
                                            <p class="text-xs text-[var(--text-secondary)] truncate">{{ Auth::user()?->email }}</p>
                                        </div>

                                        <a href="{{ route($isItManagerShell ? 'it-manager.dashboard' : 'dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--text-primary)] transition-colors">
                                            Accueil
                                        </a>

                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--text-primary)] transition-colors">
                                            Profil
                                        </a>

                                        @unless($isItManagerShell)
                                            <a href="{{ route('subscriptions.index') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--text-primary)] transition-colors">
                                                Packages
                                            </a>
                                        @endunless

                                        @if($isItManagerShell)
                                            <a href="{{ url('/?site=1') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--text-primary)] transition-colors">
                                                Voir le site
                                            </a>
                                        @endif

                                        @if(Auth::user()?->hasRole('admin'))
                                            <hr class="border-[var(--border-color)]">
                                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm font-semibold text-[var(--primary)] transition-colors">
                                                Administration
                                            </a>
                                        @endif

                                        <hr class="border-[var(--border-color)]">

                                        <form method="POST" action="{{ route('logout') }}" id="logout-form" class="logout-form">
                                            @csrf
                                            <button type="button"
                                                    onclick="confirmLogout(event)"
                                                    class="block w-full text-left px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--danger)] transition-colors">
                                                Déconnexion
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <main class="main-content" id="main-content">
                @include('partials.member.flash-alerts')
                @yield('content')
            </main>
        </div>

