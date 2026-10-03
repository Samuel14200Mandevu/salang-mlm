        <!-- Main Content -->
        <div class="main-wrapper"
             :style="{
                'margin-left': (!isMobile && sidebarOpen) ? '16rem' : (!isMobile && !sidebarOpen) ? '4.5rem' : '0',
                'width': (!isMobile && sidebarOpen) ? 'calc(100% - 16rem)' : (!isMobile && !sidebarOpen) ? 'calc(100% - 4.5rem)' : '100%'
             }">

            <!-- Top Navigation -->
            <nav class="bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-40 flex-shrink-0">
                <div class="px-3 sm:px-4 lg:px-6">
                    <div class="flex justify-between items-center h-14 sm:h-16">

                        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                            <button @click="sidebarOpen = !sidebarOpen"
                                    class="p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors flex-shrink-0">
                                <svg class="w-5 h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <div class="min-w-0 flex-1">
                                @if(isset($header) && $header)
                                    <div class="truncate">{{ $header }}</div>
                                @else
                                    <h1 id="pageTitle" class="text-base sm:text-lg lg:text-xl font-semibold text-[var(--text-primary)] truncate">
                                        @yield('title', 'Dashboard Caissier')
                                    </h1>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1 sm:gap-2 lg:gap-4 flex-shrink-0">

                            <!-- Theme Toggle -->
                            <button id="theme-toggle"
                                    class="p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon"/>
                                </svg>
                            </button>

                            <!-- Panier -->
                            <button id="cartToggleBtn"
                                    class="p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors relative">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span id="headerCartCount" class="absolute -top-1 -right-1 bg-[var(--danger)] text-white text-[8px] sm:text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 hidden">0</span>
                            </button>

                            <!-- Profile -->
                            @auth
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                            class="flex items-center gap-1 sm:gap-2 p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors">
                                        <span class="hidden sm:inline text-xs sm:text-sm text-[var(--text-primary)] truncate max-w-[80px] md:max-w-[120px]">
                                            {{ Auth::user()->name }}
                                        </span>
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[var(--primary)] flex items-center justify-center text-white font-bold text-xs sm:text-sm flex-shrink-0">
                                            @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                                                <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}"
                                                     alt="Avatar"
                                                     class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover">
                                            @else
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            @endif
                                        </div>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                         class="absolute right-0 mt-2 w-48 sm:w-56 bg-[var(--bg-card)] rounded-lg shadow-sm py-1 border border-[var(--border-color)] z-50"
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         style="display: none;">

                                        <div class="px-4 py-2 border-b border-[var(--border-color)] sm:hidden">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-[var(--text-secondary)] truncate">{{ Auth::user()->email }}</p>
                                        </div>

                                        <a href="{{ route('cashier.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                                </svg>
                                                Dashboard
                                            </span>
                                        </a>

                                        <a href="{{ route('cashier.profile') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Mon Profil
                                            </span>
                                        </a>

                                        @if(Auth::user()->hasRole('admin'))
                                            <hr class="border-[var(--border-color)]">
                                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm font-semibold text-[var(--primary)] transition-colors">
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    Administration
                                                </span>
                                            </a>
                                        @endif

                                        <hr class="border-[var(--border-color)]">

                                        <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile" class="logout-form">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2.5 hover:bg-[var(--bg-secondary)] text-sm text-[var(--danger)] transition-colors">
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                    </svg>
                                                    Déconnexion
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </nav>
