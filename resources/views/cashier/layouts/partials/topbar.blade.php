        <!-- Main Content -->
        <div class="main-wrapper"
             :style="{
                'margin-left': (!isMobile && sidebarOpen) ? '16rem' : (!isMobile && !sidebarOpen) ? '5rem' : '0',
                'width': (!isMobile && sidebarOpen) ? 'calc(100% - 16rem)' : (!isMobile && !sidebarOpen) ? 'calc(100% - 5rem)' : '100%'
             }">

            @php
                $cashierTopTabs = [
                    ['label' => 'Accueil', 'route' => 'cashier.dashboard', 'match' => 'cashier.dashboard', 'params' => []],
                    ['label' => 'Consultations', 'route' => 'cashier.consultations.index', 'match' => 'cashier.consultations*', 'params' => []],
                    ['label' => 'Commandes', 'route' => 'cashier.orders', 'match' => 'cashier.orders*', 'params' => []],
                    ['label' => 'Membres', 'route' => 'cashier.members', 'match' => 'cashier.members*', 'params' => []],
                    ['label' => 'Rapports', 'route' => 'cashier.reports.index', 'match' => 'cashier.reports*', 'params' => []],
                ];
            @endphp

            <!-- Top Navigation Quixotic -->
            <nav class="cashier-topbar bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-40 flex-shrink-0">
                <div class="px-3 sm:px-4 lg:px-6">
                    <div class="flex justify-between items-center h-14 sm:h-16 gap-2">

                        <a href="{{ route('cashier.dashboard') }}"
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

                        <div class="flex items-center gap-2 sm:gap-3 min-w-0 md:hidden">
                            <button type="button"
                                    @click="sidebarOpen = !sidebarOpen; window.salangPersistSidebarOpen(sidebarOpen)"
                                    class="p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors flex-shrink-0"
                                    aria-label="Menu">
                                <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <div class="min-w-0 flex-1">
                                @if(isset($header) && $header)
                                    <div class="truncate">{{ $header }}</div>
                                @else
                                    <h1 id="pageTitle" class="text-base sm:text-lg font-semibold text-[var(--text-primary)] truncate">
                                        @yield('title', 'Caisse')
                                    </h1>
                                @endif
                            </div>
                        </div>

                        {{-- Onglets Quixotic (desktop) --}}
                        <div class="hidden md:flex flex-1 items-center justify-center min-w-0 px-2">
                            <div class="admin-quixotic-tabs flex items-center gap-1">
                                @foreach($cashierTopTabs as $tab)
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

                        <div class="flex items-center gap-1 sm:gap-2 lg:gap-3 flex-shrink-0">

                            @include('partials.shell.workflow-bell-dropdown', [
                                'workflowBell' => [
                                    'idPrefix' => 'cashierWorkflow',
                                    'panelTitle' => 'Notifications',
                                    'consultationsUrl' => route('cashier.consultations.index'),
                                    'reportsUrl' => route('cashier.reports.index'),
                                    'consultationsLabel' => 'Consultations',
                                    'reportsLabel' => 'Rapports',
                                    'consultationsHint' => 'Réponses de l\'administration',
                                    'reportsHint' => 'Approbations et rejets',
                                    'emptyText' => 'Aucune nouvelle notification.',
                                    'urgentLabel' => 'Voir en priorité',
                                ],
                            ])

                            <!-- Theme Toggle -->
                            <button type="button" id="theme-toggle"
                                    class="p-1.5 sm:p-2 rounded-xl hover:bg-[var(--bg-secondary)] transition-colors"
                                    aria-label="Changer le thème">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                                </svg>
                            </button>

                            @include('partials.shell.cart-dropdown')

                            <!-- Profile -->
                            @auth
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                            class="flex items-center gap-1 sm:gap-2 p-1.5 sm:p-2 rounded-xl hover:bg-[var(--bg-secondary)] transition-colors"
                                            aria-label="Profil">
                                        <span class="hidden sm:inline text-xs sm:text-sm text-[var(--text-primary)] truncate max-w-[80px] md:max-w-[120px]">
                                            {{ Auth::user()?->name }}
                                        </span>
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[var(--primary)] flex items-center justify-center text-white font-bold text-xs sm:text-sm flex-shrink-0 overflow-hidden">
                                            @if(Auth::user()?->avatar && file_exists(public_path('storage/avatars/' . Auth::user()?->avatar)))
                                                <img src="{{ asset('storage/avatars/' . Auth::user()?->avatar) }}"
                                                     alt="Avatar"
                                                     class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover">
                                            @else
                                                {{ strtoupper(substr(Auth::user()?->name, 0, 1)) }}
                                            @endif
                                        </div>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                         class="absolute right-0 mt-2 w-48 sm:w-56 bg-[var(--bg-card)] rounded-xl shadow-[var(--shadow-md)] py-1 border border-[var(--border-color)] z-50"
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         style="display: none;">

                                        <div class="px-4 py-2 border-b border-[var(--border-color)] sm:hidden">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">{{ Auth::user()?->name }}</p>
                                            <p class="text-xs text-[var(--text-secondary)] truncate">{{ Auth::user()?->email }}</p>
                                        </div>

                                        <a href="{{ route('cashier.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--text-primary)] transition-colors">
                                            Accueil
                                        </a>

                                        <a href="{{ route('cashier.profile') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--text-primary)] transition-colors">
                                            Mon Profil
                                        </a>

                                        @if(Auth::user()?->hasRole('admin'))
                                            <hr class="border-[var(--border-color)]">
                                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm font-semibold text-[var(--primary)] transition-colors">
                                                Administration
                                            </a>
                                        @endif

                                        <hr class="border-[var(--border-color)]">

                                        <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile" class="logout-form">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--danger)] transition-colors">
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
