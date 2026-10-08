        <!-- Sidebar -->
        <aside id="sidebar" aria-label="Navigation membre"
               class="fixed top-0 left-0 z-50 h-full transition-all duration-200 ease-in-out"
               :class="{
                  'w-64': sidebarOpen && !isMobile,
                  'w-20 sidebar-is-rail': !sidebarOpen && !isMobile,
                  'w-64 translate-x-0': sidebarOpen && isMobile,
                  'w-64 -translate-x-full': !sidebarOpen && isMobile
               }">

            <div class="h-full bg-[var(--bg-navbar)] border-r border-[var(--border-color)] flex flex-col overflow-hidden sidebar-shell">
                <div class="cashier-sidebar-accent" aria-hidden="true"></div>

                @include('layouts.partials.sidebar-mobile-drawer-head')

                <div class="sidebar-logo-bar hidden lg:flex items-center justify-between h-16 border-b border-[var(--border-color)] flex-shrink-0"
                     :class="sidebarOpen ? 'px-4' : 'px-2'">
                    <a href="{{ route('dashboard') }}" class="sidebar-brand-link flex items-center justify-center flex-1 min-w-0">
                        <img src="{{ asset('images/salang_logo.png') }}"
                             alt="Salang"
                             width="160"
                             height="56"
                             decoding="async"
                             class="sidebar-logo-img sidebar-logo-full logo-themeable">
                    </a>
                </div>

                <nav class="sidebar-mobile-drawer-nav flex-1 overflow-y-auto py-4 px-2 custom-scrollbar"
                     @click="if (isMobile && $event.target.closest('a.sidebar-link, button.sidebar-link')) sidebarOpen = false">
                    @include('layouts.partials.sidebar-nav-menu')
                </nav>

                <p class="sidebar-mobile-drawer-version lg:hidden" aria-hidden="true">Version 1.0.0</p>

                <!-- Pied de page de la sidebar (desktop) -->
                <div class="sidebar-user hidden lg:block flex-shrink-0">
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
