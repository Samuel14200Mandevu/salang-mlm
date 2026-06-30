import './bootstrap';
import 'flowbite';

// ============================================================
// GESTION DU THÈME
// ============================================================
(function() {
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        updateIcon(theme === 'dark');
        localStorage.setItem('theme', theme);
        
        // Dispatch un événement pour les composants Livewire/Alpine
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
    }

    function updateIcon(isDark) {
        const icon = document.getElementById('theme-icon');
        if (!icon) return;
        
        if (isDark) {
            // Icône Lune -> Soleil (mode sombre)
            icon.setAttribute('d', 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z');
            icon.parentElement?.setAttribute('aria-label', 'Passer au thème clair');
        } else {
            // Icône Soleil -> Lune (mode clair)
            icon.setAttribute('d', 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z');
            icon.parentElement?.setAttribute('aria-label', 'Passer au thème sombre');
        }
    }

    // Récupérer le thème sauvegardé ou la préférence système
    let theme = localStorage.getItem('theme');
    if (!theme) {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    applyTheme(theme);

    // Écouter le clic sur le bouton de toggle
    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    }

    // Écouter les changements de préférence système
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });
})();

// ============================================================
// TOAST / NOTIFICATIONS
// ============================================================
window.showToast = function(message, type = 'success', duration = 3000) {
    const colors = {
        success: 'toast-success',
        error: 'toast-error',
        warning: 'toast-warning',
        info: 'toast-info'
    };
    
    const icons = {
        success: '✓',
        error: '✗',
        warning: '⚠',
        info: 'ℹ'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast ${colors[type] || 'toast-info'}`;
    toast.innerHTML = `<span class="mr-2 font-bold">${icons[type] || 'ℹ'}</span> ${message}`;
    document.body.appendChild(toast);
    
    // Afficher avec animation
    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    });
    
    // Masquer après la durée
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 500);
    }, duration);
};

// ============================================================
// DÉTECTION MOBILE
// ============================================================
window.isMobile = function() {
    return window.innerWidth <= 768;
};

window.isTablet = function() {
    return window.innerWidth > 768 && window.innerWidth <= 1024;
};

window.isDesktop = function() {
    return window.innerWidth > 1024;
};

// ============================================================
// GESTION DU SIDEBAR SUR MOBILE
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (window.isMobile()) {
                sidebar?.classList.toggle('open');
                overlay?.classList.toggle('active');
                document.body.style.overflow = sidebar?.classList.contains('open') ? 'hidden' : '';
            }
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar?.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Fermer la sidebar sur mobile lors d'un clic sur un lien
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.isMobile()) {
                sidebar?.classList.remove('open');
                overlay?.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
});

// ============================================================
// EXPORT POUR MODULES
// ============================================================
export default {
    showToast: window.showToast,
    isMobile: window.isMobile,
    isTablet: window.isTablet,
    isDesktop: window.isDesktop
};