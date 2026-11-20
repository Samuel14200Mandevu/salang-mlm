/**
 * PWA native shell: block iOS pinch-zoom + sync theme-color with light/dark.
 */
(function pwaNativeShell() {
  const prevent = (event) => {
    event.preventDefault();
  };

  document.addEventListener('gesturestart', prevent, { passive: false });
  document.addEventListener('gesturechange', prevent, { passive: false });
  document.addEventListener('gestureend', prevent, { passive: false });

  const LIGHT = '#F4F5F7';
  const DARK = '#0c1017';

  function syncThemeColor() {
    const isDark = document.documentElement.classList.contains('dark');
    const color = isDark ? DARK : LIGHT;
    const meta = document.getElementById('salang-theme-color');
    if (meta) {
      meta.setAttribute('content', color);
    }
    document.querySelectorAll('meta[name="theme-color"]').forEach((el) => {
      if (el.id === 'salang-theme-color') {
        return;
      }
      if (!el.hasAttribute('media')) {
        el.setAttribute('content', color);
      }
    });
  }

  syncThemeColor();
  window.addEventListener('theme-changed', syncThemeColor);

  const observer = new MutationObserver(syncThemeColor);
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
  });
})();
