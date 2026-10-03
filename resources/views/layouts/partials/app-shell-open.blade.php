    <div class="min-h-screen flex" 
         x-data="{ 
            sidebarOpen: window.innerWidth > 1024, 
            notificationOpen: false,
            profileOpen: false,
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
