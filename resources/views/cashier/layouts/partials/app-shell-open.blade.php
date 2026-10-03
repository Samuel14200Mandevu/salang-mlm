<body class="h-full bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:bg-primary-600 focus:text-white focus:px-4 focus:py-2 focus:rounded-md">
        Aller au contenu principal
    </a>
    <div class="app-container"
         x-data="{
            sidebarOpen: window.innerWidth > 1024,
            isMobile: window.innerWidth < 768
         }"
         x-init="
            sidebarOpen = window.innerWidth > 1024;
            isMobile = window.innerWidth < 768;
            window.addEventListener('resize', () => {
                isMobile = window.innerWidth < 768;
                if (window.innerWidth > 1024) sidebarOpen = true;
                if (window.innerWidth < 768) sidebarOpen = false;
            });
         ">

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

        <!-- Sidebar -->
