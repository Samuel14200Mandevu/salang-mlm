/**
 * PWA native shell: block iOS pinch-zoom + sync theme-color with light/dark
 * (ou couleur de grade membre si data-member-rank-theme / data-rank-locked).
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

  function rankThemeColor() {
    const fromHtml = document.documentElement.getAttribute('data-member-rank-theme');
    if (fromHtml && /^#[0-9A-Fa-f]{3,8}$/.test(fromHtml)) {
      return fromHtml;
    }
    const meta = document.getElementById('salang-theme-color');
    if (meta && meta.getAttribute('data-rank-locked') === '1') {
      const locked = meta.getAttribute('content');
      if (locked && /^#[0-9A-Fa-f]{3,8}$/.test(locked)) {
        return locked;
      }
    }
    return null;
  }

  function syncThemeColor() {
    const rank = rankThemeColor();
    const isDark = document.documentElement.classList.contains('dark');
    const color = rank || (isDark ? DARK : LIGHT);
    const meta = document.getElementById('salang-theme-color');
    if (meta) {
      meta.setAttribute('content', color);
    }
    document.querySelectorAll('meta[name="theme-color"]').forEach((el) => {
      if (el.id === 'salang-theme-color') {
        return;
      }
      el.setAttribute('content', color);
    });
  }

  syncThemeColor();
  window.addEventListener('theme-changed', syncThemeColor);

  const observer = new MutationObserver(syncThemeColor);
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class', 'data-member-rank-theme'],
  });
})();
