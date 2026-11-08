        <!-- Sidebar -->
        <aside id="sidebar" aria-label="Navigation membre"
               class="member-sidebar-drawer fixed top-0 left-0 h-full transition-all duration-200 ease-in-out"
               :class="{
                  'w-64': sidebarOpen && !isMobile,
                  'w-20 sidebar-is-rail': !sidebarOpen && !isMobile,
                  'w-64 translate-x-0': sidebarOpen && isMobile,
                  'w-64 -translate-x-full': !sidebarOpen && isMobile
               }">

            <div class="admin-sidebar-panel h-full bg-[var(--bg-navbar)] border-r border-[var(--border-color)] flex flex-col overflow-hidden sidebar-shell">
                <div class="cashier-sidebar-accent" aria-hidden="true"></div>

                @include('layouts.partials.sidebar-mobile-drawer-head')

                <div class="sidebar-logo-bar hidden md:flex items-center justify-between h-16 border-b border-[var(--border-color)] flex-shrink-0"
                     :class="sidebarOpen ? 'px-4' : 'px-2'">
                    <a href="{{ route(auth()->user()?->usesItManagerShell() ? 'it-manager.dashboard' : 'dashboard') }}" class="sidebar-brand-link flex items-center justify-center flex-1 min-w-0">
                        <img src="{{ asset('images/salang_logo.png') }}"
                             alt="Salang"
                             width="160"
                             height="56"
                             decoding="async"
                             class="sidebar-logo-img sidebar-logo-full logo-themeable">
                    </a>
                </div>

                <nav class="admin-sidebar-nav sidebar-mobile-drawer-nav flex-1 overflow-y-auto py-4 px-2 custom-scrollbar"
                     @click="if (isMobile && $event.target.closest('a.sidebar-link, button.sidebar-link')) sidebarOpen = false">
                    @if(auth()->user()?->usesItManagerShell())
                        @include('layouts.partials.sidebar-nav-it-manager')
                    @else
                        @include('layouts.partials.sidebar-nav-menu')
                    @endif
                </nav>

                <p class="sidebar-mobile-drawer-version lg:hidden" aria-hidden="true">Version 1.0.0</p>
            </div>
        </aside>
