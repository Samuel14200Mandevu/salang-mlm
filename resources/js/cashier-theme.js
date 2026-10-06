export function initThemeToggle() {
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        updateIcon(theme === 'dark');
        localStorage.setItem('theme', theme);
    }

    function updateIcon(isDark) {
        const icon = document.getElementById('theme-icon');
        const toggleBtn = document.getElementById('theme-toggle');
        if (!icon) return;

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

        if (toggleBtn) {
            toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            toggleBtn.setAttribute('aria-label', isDark ? 'Activer le thème clair' : 'Activer le thème sombre');
        }
    }

    let theme = localStorage.getItem('theme');
    if (!theme) {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    applyTheme(theme);

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#theme-toggle');
        if (!btn) return;
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        applyTheme(isDark ? 'light' : 'dark');
    });
}
