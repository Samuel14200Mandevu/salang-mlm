import './bootstrap';
import './public-animations';
import './public-splash';
import './public-onboarding';
import './auth-forms';
import './auth-password';
import './auth-login';
import './auth-register';
import './admin-mobile';
import './salang-pagination';
import './member-shop';
import './member-withdrawal';
import './member-assistant';
import './member-services-featured';
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

    function syncSettingsThemeToggle(isDark) {
        const input = document.getElementById('settings-theme-toggle');
        if (input) {
            input.checked = isDark;
        }
    }

    function applyReduceMotion(enabled) {
        document.documentElement.classList.toggle('salang-reduce-motion', enabled);
        localStorage.setItem('salang_reduce_motion', enabled ? '1' : '0');
        const motion = document.getElementById('settings-reduce-motion-toggle');
        if (motion) {
            motion.checked = enabled;
        }
    }

    function applySidebarDefault(open) {
        localStorage.setItem('sidebar_open', open ? 'true' : 'false');
        if (typeof window.salangSyncSidebarShell === 'function' && window.innerWidth >= 768) {
            window.salangSyncSidebarShell(open);
        }
        const sidebar = document.getElementById('settings-sidebar-toggle');
        if (sidebar) {
            sidebar.checked = open;
        }
    }

    function applyWorkflowPoll(enabled) {
        localStorage.setItem('admin_workflow_poll_enabled', enabled ? '1' : '0');
        const poll = document.getElementById('settings-workflow-poll-toggle');
        if (poll) {
            poll.checked = enabled;
        }
        if (enabled && typeof window.salangWorkflowPollRefresh === 'function') {
            window.salangWorkflowPollRefresh();
        }
    }

    function syncLocalPreferenceToggles() {
        const sidebar = document.getElementById('settings-sidebar-toggle');
        if (sidebar) {
            const open =
                typeof window.salangReadSidebarOpen === 'function'
                    ? window.salangReadSidebarOpen()
                    : localStorage.getItem('sidebar_open') !== 'false';
            sidebar.checked = open;
        }

        const motionOn = localStorage.getItem('salang_reduce_motion') === '1';
        applyReduceMotion(motionOn);

        const poll = document.getElementById('settings-workflow-poll-toggle');
        const pollOn = localStorage.getItem('admin_workflow_poll_enabled') !== '0';
        if (poll) {
            poll.checked = pollOn;
        }
    }

    if (localStorage.getItem('salang_reduce_motion') === '1') {
        document.documentElement.classList.add('salang-reduce-motion');
    }

    function updateIcon(isDark) {
        const icon = document.getElementById('theme-icon');
        const toggleBtn = document.getElementById('theme-toggle');

        if (icon) {
            if (isDark) {
                icon.setAttribute(
                    'd',
                    'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'
                );
            } else {
                icon.setAttribute(
                    'd',
                    'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z'
                );
            }
        }

        if (toggleBtn) {
            toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            toggleBtn.setAttribute('aria-label', isDark ? 'Activer le thème clair' : 'Activer le thème sombre');
        }

        syncSettingsThemeToggle(isDark);
    }

    let theme = localStorage.getItem('theme');
    if (!theme) {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    applyTheme(theme);

    window.salangApplyTheme = applyTheme;
    window.salangGetTheme = function () {
        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    };

    const settingsThemeToggle = document.getElementById('settings-theme-toggle');
    if (settingsThemeToggle && !settingsThemeToggle.dataset.themeBound) {
        settingsThemeToggle.dataset.themeBound = '1';
        settingsThemeToggle.addEventListener('change', function () {
            applyTheme(this.checked ? 'dark' : 'light');
        });
    }

    const sidebarToggle = document.getElementById('settings-sidebar-toggle');
    if (sidebarToggle && !sidebarToggle.dataset.prefBound) {
        sidebarToggle.dataset.prefBound = '1';
        sidebarToggle.addEventListener('change', function () {
            applySidebarDefault(this.checked);
        });
    }

    const motionToggle = document.getElementById('settings-reduce-motion-toggle');
    if (motionToggle && !motionToggle.dataset.prefBound) {
        motionToggle.dataset.prefBound = '1';
        motionToggle.addEventListener('change', function () {
            applyReduceMotion(this.checked);
        });
    }

    const pollToggle = document.getElementById('settings-workflow-poll-toggle');
    if (pollToggle && !pollToggle.dataset.prefBound) {
        pollToggle.dataset.prefBound = '1';
        pollToggle.addEventListener('change', function () {
            applyWorkflowPoll(this.checked);
        });
    }

    syncLocalPreferenceToggles();

    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn && !toggleBtn.dataset.themeBound) {
        toggleBtn.dataset.themeBound = '1';
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

/**
 * Marque toutes les notifications comme lues (dropdown shell membre).
 * @param {(() => void)|undefined} onSuccess — ex. remise à zéro du badge Alpine
 */
window.markAllAsRead = async function (onSuccess) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        return;
    }

    try {
        const response = await fetch('/notifications/mark-all-read', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok && !response.redirected) {
            throw new Error(`HTTP ${response.status}`);
        }

        if (typeof onSuccess === 'function') {
            onSuccess();
        }

        window.showToast?.('Toutes les notifications ont été marquées comme lues', 'success');
    } catch {
        window.showToast?.('Impossible de marquer les notifications', 'error');
    }
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
    markAllAsRead: window.markAllAsRead,
    isMobile: window.isMobile,
    isTablet: window.isTablet,
    isDesktop: window.isDesktop,
    api,
};
