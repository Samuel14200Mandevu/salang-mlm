import './bootstrap';
import Alpine from 'alpinejs';
import api from './api';

window.Alpine = Alpine;
window.api = api;

Alpine.start();

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = String(str ?? '');
    return div.innerHTML;
}

(function () {
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        updateIcon(theme === 'dark');
        localStorage.setItem('theme', theme);
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
    }

    function updateIcon(isDark) {
        const icon = document.getElementById('theme-icon');
        if (!icon) return;

        if (isDark) {
            icon.setAttribute(
                'd',
                'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'
            );
            icon.parentElement?.setAttribute('aria-label', 'Passer au thème clair');
        } else {
            icon.setAttribute(
                'd',
                'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z'
            );
            icon.parentElement?.setAttribute('aria-label', 'Passer au thème sombre');
        }
    }

    let theme = localStorage.getItem('theme');
    if (!theme) {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    applyTheme(theme);

    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    }

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
        if (!localStorage.getItem('theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });
})();

window.showToast = function (message, type = 'success', duration = 3500) {
    const safeMessage = escapeHtml(message);
    const container = document.getElementById('toastContainer');

    if (container) {
        const toast = document.createElement('div');
        toast.className = `toast-item ${type}`;
        toast.innerHTML = `
            <svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>${safeMessage}</span>
            <button type="button" class="toast-close" aria-label="Fermer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        toast.querySelector('.toast-close')?.addEventListener('click', () => toast.remove());
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'toastOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 400);
            }
        }, duration);
        return;
    }

    const colors = {
        success: 'toast-success',
        error: 'toast-error',
        warning: 'toast-warning',
        info: 'toast-info',
    };

    const icons = {
        success: '✓',
        error: '✗',
        warning: '!',
        info: 'i',
    };

    const toast = document.createElement('div');
    toast.className = `toast ${colors[type] || 'toast-info'}`;
    toast.setAttribute('role', 'status');
    toast.innerHTML = `<span class="mr-2 font-bold">${icons[type] || 'i'}</span><span>${safeMessage}</span>`;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    });

    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 500);
    }, duration);
};

window.isMobile = function () {
    return window.innerWidth <= 768;
};

window.isTablet = function () {
    return window.innerWidth > 768 && window.innerWidth <= 1024;
};

window.isDesktop = function () {
    return window.innerWidth > 1024;
};

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const overlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (window.isMobile()) {
                sidebar?.classList.toggle('open');
                overlay?.classList.toggle('active');
                document.body.style.overflow = sidebar?.classList.contains('open') ? 'hidden' : '';
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar?.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    document.querySelectorAll('.sidebar-link').forEach((link) => {
        link.addEventListener('click', function () {
            if (window.isMobile()) {
                sidebar?.classList.remove('open');
                overlay?.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
});

export default {
    showToast: window.showToast,
    isMobile: window.isMobile,
    isTablet: window.isTablet,
    isDesktop: window.isDesktop,
    api,
};
