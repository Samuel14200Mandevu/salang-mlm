<body
    class="cashier-app public-body h-full bg-[var(--bg-page)] text-[var(--text-primary)] antialiased"
    data-checkout-url="{{ route('cashier.checkout') }}"
>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:bg-primary-600 focus:text-white focus:px-4 focus:py-2 focus:rounded-md">
        Aller au contenu principal
    </a>
    <div class="app-container"
         :class="{ 'admin-drawer-active': isMobile && sidebarOpen }"
         x-data="{
            sidebarOpen: window.innerWidth < 768 ? false : window.salangReadSidebarOpen(),
            isMobile: window.innerWidth < 768,
            mobileMoreOpen: false,
            toggleSidebar() {
                if (this.isMobile && this.mobileMoreOpen) {
                    this.mobileMoreOpen = false;
                }
                this.sidebarOpen = !this.sidebarOpen;
                if (!this.isMobile) {
                    window.salangPersistSidebarOpen(this.sidebarOpen);
                }
            },
            closeSidebar() {
                this.sidebarOpen = false;
                if (!this.isMobile) {
                    window.salangPersistSidebarOpen(false);
                }
            }
         }"
         x-init="
            isMobile = window.innerWidth < 768;
            sidebarOpen = isMobile ? false : window.salangReadSidebarOpen();
            window.salangSyncSidebarShell(sidebarOpen);
            window.addEventListener('resize', () => {
                const wasMobile = isMobile;
                isMobile = window.innerWidth < 768;
                if (isMobile) {
                    sidebarOpen = false;
                    mobileMoreOpen = false;
                } else if (wasMobile) {
                    sidebarOpen = window.salangReadSidebarOpen();
                }
                window.salangSyncSidebarShell(sidebarOpen);
            });
            const syncCashierScrollLock = () => {
                const lock = mobileMoreOpen || (isMobile && sidebarOpen);
                document.body.classList.toggle('admin-mobile-scroll-lock', !!lock);
            };
            $watch('mobileMoreOpen', syncCashierScrollLock);
            $watch('sidebarOpen', (open) => {
                syncCashierScrollLock();
                if (open && isMobile) {
                    mobileMoreOpen = false;
                    $nextTick(() => document.getElementById('cashierDrawerCloseBtn')?.focus({ preventScroll: true }));
                }
            });
         "
         @sidebar-toggle.window="toggleSidebar()"
         @shell-close-drawer.window="if (isMobile) closeSidebar()"
         @admin-close-drawer.window="if (isMobile) closeSidebar()"
         @keydown.escape.window="if (isMobile && sidebarOpen) closeSidebar()">

        <!-- Overlay mobile -->
        <div x-show="sidebarOpen && isMobile"
             x-cloak
             @click="closeSidebar()"
             class="admin-mobile-drawer-backdrop fixed inset-0 bg-black/45 z-40 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
        </div>

        <!-- Sidebar -->
