    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:bg-primary-600 focus:text-white focus:px-4 focus:py-2 focus:rounded-md">
        Aller au contenu principal
    </a>
    <div class="app-container"
         x-data="{
            sidebarOpen: (typeof window.salangReadSidebarOpen === 'function' ? window.salangReadSidebarOpen() : window.innerWidth > 1024),
            isMobile: window.innerWidth < 768,
            mobileMoreOpen: false
         }"
         x-init="
            sidebarOpen = (typeof window.salangReadSidebarOpen === 'function' ? window.salangReadSidebarOpen() : window.innerWidth > 1024);
            isMobile = window.innerWidth < 768;
            if (window.innerWidth < 768) sidebarOpen = false;
            if (typeof window.salangSyncSidebarShell === 'function') {
                window.salangSyncSidebarShell(sidebarOpen);
            }
            window.addEventListener('resize', () => {
                isMobile = window.innerWidth < 768;
                if (window.innerWidth < 768) {
                    sidebarOpen = false;
                } else {
                    sidebarOpen = (typeof window.salangReadSidebarOpen === 'function' ? window.salangReadSidebarOpen() : true);
                }
                if (typeof window.salangSyncSidebarShell === 'function') {
                    window.salangSyncSidebarShell(sidebarOpen);
                }
            });
            $watch('mobileMoreOpen', function (open) {
                document.body.classList.toggle('member-mobile-scroll-lock', !!open);
            });
            $watch('sidebarOpen', function (open) {
                if (isMobile) {
                    document.body.classList.toggle('member-mobile-scroll-lock', !!open);
                }
            });
         "
         @sidebar-toggle.window="sidebarOpen = !sidebarOpen; if (!isMobile && typeof window.salangPersistSidebarOpen === 'function') window.salangPersistSidebarOpen(sidebarOpen)">

        <!-- Overlay mobile -->
        <div x-show="sidebarOpen && isMobile"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/40 z-40 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
        </div>
