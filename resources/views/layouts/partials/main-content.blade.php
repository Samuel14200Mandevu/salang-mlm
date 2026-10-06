        <div class="main-wrapper"
             :style="{
                'margin-left': (!isMobile && sidebarOpen) ? '16rem' : (!isMobile && !sidebarOpen) ? '5rem' : '0',
                'width': (!isMobile && sidebarOpen) ? 'calc(100% - 16rem)' : (!isMobile && !sidebarOpen) ? 'calc(100% - 5rem)' : '100%'
             }">

            <nav class="cashier-topbar bg-[var(--bg-navbar)] border-b border-[var(--border-color)] sticky top-0 z-40 flex-shrink-0" aria-label="Barre supérieure">
                <div class="px-3 sm:px-4 lg:px-6">
                    <div class="flex justify-between items-center h-14 sm:h-16">
                        
                        <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                            <button type="button"
                                    @click="sidebarOpen = !sidebarOpen; if (!isMobile && typeof window.salangPersistSidebarOpen === 'function') window.salangPersistSidebarOpen(sidebarOpen)"
                                    class="p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors flex-shrink-0"
                                    aria-label="Ouvrir ou fermer le menu latéral">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            
                            <div class="min-w-0 flex-1">
                                @if(isset($header) && $header)
                                    <div class="truncate text-base sm:text-lg font-semibold text-[var(--text-primary)]">{{ $header }}</div>
                                @else
                                    <h1 id="pageTitle" class="text-base sm:text-lg lg:text-xl font-semibold text-[var(--text-primary)] truncate">
                                        @yield('title', 'Espace membre')
                                    </h1>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1 sm:gap-2 lg:gap-4 flex-shrink-0">

                            <a href="{{ route('cart.index') }}"
                               class="member-topbar-hide-mobile p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors relative hidden md:inline-flex"
                               aria-label="Panier">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <x-ui.cart-badge variant="topbar" />
                            </a>

                            <!-- Notifications -->
                            <div class="relative" x-data="{ open: false, unreadCount: {{ auth()->user()->unreadNotifications()->count() ?? 0 }} }">
                                <button type="button"
                                        @click="open = !open" 
                                        class="p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors relative"
                                        aria-label="Notifications"
                                        :aria-expanded="open">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <span x-cloak
                                          x-show="unreadCount > 0"
                                          x-text="unreadCount > 99 ? '99+' : unreadCount"
                                          class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-primary-500 rounded-full"></span>
                                </button>

                                <div x-show="open" @click.away="open = false" 
                                     class="absolute right-0 mt-2 w-[calc(100vw-2rem)] sm:w-80 md:w-96 py-2 max-h-[80vh] overflow-y-auto z-50 bg-[var(--bg-card)] rounded-lg shadow-sm border border-[var(--border-color)]"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     style="display: none;">
                                    
                                    <div class="px-3 sm:px-4 py-2 border-b border-[var(--border-color)] flex justify-between items-center">
                                        <h4 class="font-semibold text-sm sm:text-base text-[var(--text-primary)]">Notifications</h4>
                                        <a href="{{ route('notifications.index') }}" class="text-xs text-primary-500 hover:text-primary-600 transition font-medium">Voir tout</a>
                                    </div>

                                    <div class="divide-y divide-[var(--border-color)]" id="notificationList">
                                        @forelse(auth()->user()->notifications()->limit(5)->get() as $notification)
                                            <div class="px-3 sm:px-4 py-3 hover:bg-[var(--bg-secondary)] transition cursor-pointer notification-item" data-id="{{ $notification->id }}">
                                                <p class="text-sm font-medium text-[var(--text-primary)]">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                                <p class="text-xs text-[var(--text-secondary)]">{{ $notification->data['message'] ?? '' }}</p>
                                                <p class="text-xs text-[var(--text-tertiary)] mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        @empty
                                            <div class="px-3 sm:px-4 py-4 text-center text-[var(--text-secondary)] text-sm">
                                                Aucune notification
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="px-3 sm:px-4 py-2 border-t border-[var(--border-color)] text-center">
                                        <button type="button"
                                                @click="window.markAllAsRead(() => { unreadCount = 0 })"
                                                class="text-xs text-primary-500 hover:text-primary-600 transition font-medium hover:underline cursor-pointer">
                                            Tout marquer comme lu
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Changement de thème -->
                            <button type="button"
                                    id="theme-toggle"
                                    class="p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors"
                                    aria-label="Changer de thème">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="theme-icon" aria-hidden="true">
                                    <!-- Mode clair : affiche une lune (mode sombre) -->
                                    <!-- Mode sombre : affiche un soleil (mode clair) -->
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" id="theme-icon-path"/>
                                </svg>
                            </button>

                            <!-- Profil -->
                            @auth
                                <div class="relative" x-data="{ open: false }">
                                    <button type="button"
                                            @click="open = !open" 
                                            class="member-topbar-compact flex items-center gap-1 sm:gap-2 p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors"
                                            aria-label="Menu profil"
                                            :aria-expanded="open">
                                        <span class="hidden md:inline text-xs sm:text-sm text-[var(--text-primary)] truncate max-w-[80px] md:max-w-[120px]">
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
                                         class="absolute right-0 mt-2 w-48 sm:w-56 py-1 z-50 bg-[var(--bg-card)] rounded-lg shadow-sm border border-[var(--border-color)]"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         style="display: none;">
                                        
                                        <div class="px-4 py-2 border-b border-[var(--border-color)] sm:hidden">
                                            <p class="text-sm font-medium text-[var(--text-primary)]">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-[var(--text-secondary)] truncate">{{ Auth::user()->email }}</p>
                                        </div>
                                        
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                                </svg>
                                                Accueil
                                            </span>
                                        </a>
                                        
                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Profil
                                            </span>
                                        </a>
                                        
                                        <a href="{{ route('subscriptions.index') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-[var(--text-primary)] transition-colors">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                                </svg>
                                                Packages
                                            </span>
                                        </a>
                                        
                                        @if(Auth::user()->hasRole('admin'))
                                            <hr class="border-[var(--border-color)]">
                                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-primary-600 font-semibold transition-colors">
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
                                        
                                        <!-- FORMULAIRE DE DÉCONNEXION AVEC CONFIRMATION -->
                                        <form method="POST" action="{{ route('logout') }}" id="logout-form" class="logout-form">
                                            @csrf
                                            <button type="button" 
                                                    onclick="confirmLogout(event)"
                                                    class="block w-full text-left px-4 py-2.5 hover:bg-[var(--bg-primary)] text-sm text-red-500 transition-colors">
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

