/**
 * Pagination mobile — barre + balayage horizontal (liste et barre)
 */
(function () {
  'use strict';

  const MQ = window.matchMedia('(max-width: 767px)');
  const SWIPE_MIN = 56;
  const SWIPE_INTENT_PX = 12;

  function positionThumb(nav, thumb) {
    if (!nav || !thumb) {
      return;
    }
    const bar = thumb.parentElement;
    const travel = Math.max(0, (bar?.clientWidth || 0) - thumb.offsetWidth);

    const last = Number(nav.dataset.lastPage || 0);
    const current = Number(nav.dataset.currentPage || 0);
    let ratio;

    if (last > 1 && current >= 1) {
      ratio = (current - 1) / (last - 1);
    } else {
      const hasPrev = Boolean(nav.dataset.prevUrl);
      const hasNext = Boolean(nav.dataset.nextUrl);
      if (hasPrev && hasNext) {
        ratio = 0.5;
      } else if (hasPrev && !hasNext) {
        ratio = 1;
      } else if (!hasPrev && hasNext) {
        ratio = 0;
      } else {
        ratio = 0.5;
      }
    }

    thumb.style.transform = `translateX(${travel * ratio}px)`;
  }

  function initBars(root) {
    root.querySelectorAll('[data-salang-pagination]').forEach((nav) => {
      const thumb = nav.querySelector('[data-salang-pagination-thumb]');
      if (thumb) {
        positionThumb(nav, thumb);
      }
    });
  }

  function resolveNav(fromEl) {
    let node = fromEl;
    while (node && node !== document.body) {
      let sib = node.nextElementSibling;
      while (sib) {
        if (sib.matches?.('[data-salang-pagination]')) {
          return sib;
        }
        const nested = sib.querySelector?.('[data-salang-pagination]');
        if (nested) {
          return nested;
        }
        sib = sib.nextElementSibling;
      }
      node = node.parentElement;
    }
    return document.querySelector('[data-salang-pagination]');
  }

  function navigateFromSwipe(nav, dx) {
    if (!nav) {
      return false;
    }
    const prev = nav.dataset.prevUrl;
    const next = nav.dataset.nextUrl;
    if (dx > SWIPE_MIN && prev) {
      window.location.assign(prev);
      return true;
    }
    if (dx < -SWIPE_MIN && next) {
      window.location.assign(next);
      return true;
    }
    return false;
  }

  /** Balayage avec détection d’intention (fonctionne sur cartes / liens) */
  function bindHorizontalPageSwipe(container, resolveNavFn) {
    if (!container || container.dataset.salangHorizSwipe === '1') {
      return;
    }
    container.dataset.salangHorizSwipe = '1';

    let startX = 0;
    let startY = 0;
    let intent = null;
    let tracking = false;
    let blockClickUntil = 0;

    container.addEventListener(
      'click',
      (e) => {
        if (Date.now() < blockClickUntil) {
          e.preventDefault();
          e.stopPropagation();
        }
      },
      true
    );

    container.addEventListener(
      'touchstart',
      (e) => {
        if (!MQ.matches) {
          return;
        }
        if (e.target.closest('input, textarea, select, button, [data-no-page-swipe]')) {
          tracking = false;
          return;
        }
        if (e.target.closest('.admin-kpi-rail, .admin-quick-rail, .admin-action-rail')) {
          tracking = false;
          return;
        }
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        intent = null;
        tracking = true;
      },
      { passive: true }
    );

    container.addEventListener(
      'touchmove',
      (e) => {
        if (!tracking || !MQ.matches || intent === 'vertical') {
          return;
        }
        const dx = e.touches[0].clientX - startX;
        const dy = e.touches[0].clientY - startY;
        const adx = Math.abs(dx);
        const ady = Math.abs(dy);

        if (intent === null && adx > SWIPE_INTENT_PX && adx > ady * 1.15) {
          intent = 'horizontal';
        } else if (intent === null && ady > SWIPE_INTENT_PX && ady > adx * 1.15) {
          intent = 'vertical';
          tracking = false;
        }
      },
      { passive: true }
    );

    container.addEventListener(
      'touchend',
      (e) => {
        if (!tracking || !MQ.matches || intent !== 'horizontal') {
          tracking = false;
          return;
        }
        tracking = false;
        const dx = e.changedTouches[0].clientX - startX;
        const nav = resolveNavFn(e.target);
        if (navigateFromSwipe(nav, dx)) {
          blockClickUntil = Date.now() + 400;
        }
      },
      { passive: true }
    );
  }

  function initPageSwipes(root) {
    if (!MQ.matches) {
      return;
    }

    root.querySelectorAll('.salang-pagination--mobile').forEach((barZone) => {
      bindHorizontalPageSwipe(barZone, (target) =>
        target.closest('[data-salang-pagination]') || resolveNav(barZone)
      );
    });

    const zone =
      root.querySelector('.main-content')
      || root.querySelector('.main-wrapper')
      || root.querySelector('.member-main');

    if (zone) {
      bindHorizontalPageSwipe(zone, (target) => resolveNav(target));
    }
  }

  function run(root = document) {
    initBars(root);
    initPageSwipes(root);
  }

  window.initSalangPagination = run;

  function watchPaginationContainers() {
    const observer = new MutationObserver(() => {
      window.requestAnimationFrame(() => run(document));
    });

    const observe = (el) => {
      if (el && el.dataset.salangPaginationObserved !== '1') {
        el.dataset.salangPaginationObserved = '1';
        observer.observe(el, { childList: true, subtree: true });
      }
    };

    ['paginationContainerMobile', 'paginationContainer', 'paginationWrapper'].forEach((id) => {
      observe(document.getElementById(id));
    });

    document.querySelectorAll('.salang-pagination-wrap, .salang-pagination-nav').forEach(observe);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      run(document);
      watchPaginationContainers();
    });
  } else {
    run(document);
    watchPaginationContainers();
  }

  document.addEventListener('livewire:navigated', () => run(document));
  document.addEventListener('livewire:update', () => {
    window.requestAnimationFrame(() => run(document));
  });

  MQ.addEventListener('change', () => run(document));
})();
