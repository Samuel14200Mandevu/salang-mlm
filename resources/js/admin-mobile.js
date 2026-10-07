/**
 * Admin & caisse mobile — tables en cartes, bandeau titre, drawer
 */
(function () {
  'use strict';

  const MQ = window.matchMedia('(max-width: 767px)');

  function isBackofficeShell() {
    if (document.body.classList.contains('member-app')) {
      return false;
    }
    return (
      document.body.classList.contains('admin-app')
      || document.body.classList.contains('cashier-app')
    );
  }

  function enhanceAdminTables(root) {
    if (!root) return;

    const mobile = MQ.matches;

    root.querySelectorAll('table.table').forEach((table) => {
      if (
        table.classList.contains('admin-table-desktop-only')
        || table.closest('.hidden.md\\:block')
        || table.closest('[class*="md:block"]')
      ) {
        return;
      }

      if (!mobile) {
        table.classList.remove('admin-mobile-table');
        return;
      }

      table.classList.add('admin-mobile-table');

      const headers = Array.from(table.querySelectorAll('thead th')).map((th) =>
        (th.textContent || '').trim()
      );

      if (!headers.length) {
        return;
      }

      table.querySelectorAll('tbody tr').forEach((row) => {
        row.querySelectorAll('td').forEach((cell, index) => {
          if (cell.hasAttribute('data-label')) {
            return;
          }
          const label = headers[index] || '';
          if (label) {
            cell.setAttribute('data-label', label);
          }
        });
      });
    });
  }

  function initMobilePageChrome(root) {
    if (!root || !MQ.matches) {
      return;
    }

    if (root.querySelector('.member-shop--catalog, .member-dashboard--fintech')) {
      return;
    }

    const page = root.querySelector('.admin-mobile-page') || root;
    if (!page) {
      return;
    }

    const hasGreeting =
      page.querySelector('.admin-mobile-greeting') ||
      page.querySelector('.admin-profile-hero');

    if (!hasGreeting && page.dataset.adminMobileGreetingAuto !== '1') {
      const h1 = page.querySelector('h1');
      if (h1 && !h1.closest('.admin-mobile-greeting, .admin-profile-hero')) {
        const titleBlock = h1.parentElement;
        const subtitle = titleBlock?.querySelector(':scope > p');

        const greeting = document.createElement('div');
        greeting.className = 'admin-mobile-greeting md:hidden';
        greeting.dataset.autoGreeting = '1';

        const gh1 = document.createElement('h1');
        gh1.textContent = (h1.textContent || '').trim();
        greeting.appendChild(gh1);

        if (subtitle && (subtitle.textContent || '').trim()) {
          const gp = document.createElement('p');
          gp.textContent = subtitle.textContent.trim();
          greeting.appendChild(gp);
        }

        page.insertBefore(greeting, page.firstChild);
        page.dataset.adminMobileGreetingAuto = '1';

        if (titleBlock) {
          titleBlock.classList.add('admin-mobile-page-head');
        }

        const headerRow =
          h1.closest('.admin-page-header') ||
          h1.closest('.flex.flex-wrap') ||
          h1.closest('.flex.items-center');

        if (headerRow && !headerRow.classList.contains('admin-page-header')) {
          headerRow.classList.add('admin-page-header');
        }
      }
    }

    page.querySelectorAll('.admin-mobile-page-head').forEach((el) => {
      if (!el.classList.contains('admin-mobile-page-head')) {
        el.classList.add('admin-mobile-page-head');
      }
    });

    page.querySelectorAll('.grid').forEach((grid) => {
      if (grid.classList.contains('admin-kpi-rail') || grid.classList.contains('admin-quick-rail')) {
        return;
      }
      if (grid.querySelector(':scope > .card-stats, :scope > .stat-card')) {
        grid.classList.add('admin-kpi-rail');
      }
    });

    page.querySelectorAll('.admin-page-header').forEach((header) => {
      if (header.querySelector('.admin-sticky-toolbar')) {
        return;
      }
      const actions = header.querySelector('.admin-page-header-actions, .flex.gap-2, .flex.flex-wrap.gap-2');
      if (actions && !actions.classList.contains('admin-page-header-actions')) {
        actions.classList.add('admin-page-header-actions');
      }
    });

    page.querySelectorAll('#searchInput, .search-input').forEach((input) => {
      if (input.closest('.admin-sticky-toolbar, .admin-page-header')) {
        return;
      }
      const wrap =
        input.closest('.header-search') ||
        input.closest('.search-wrapper') ||
        input.parentElement;
      const toolbar = document.createElement('div');
      toolbar.className = 'admin-sticky-toolbar md:contents';
      const parent = wrap?.parentElement;
      if (wrap && parent && !parent.classList.contains('admin-sticky-toolbar')) {
        parent.insertBefore(toolbar, wrap);
        toolbar.appendChild(wrap);
        if (wrap.querySelector('.search-input, #searchInput')) {
          wrap.classList.add('admin-search-full');
        }
      }
    });

    page.querySelectorAll('.card').forEach((card) => {
      if (card.closest('.admin-section')) {
        return;
      }
      if (card.querySelector('form') && card.querySelector('.info-row, label, .form-group')) {
        card.classList.add('admin-section');
      }
    });
  }

  function run() {
    if (!isBackofficeShell()) {
      return;
    }
    const root = document.querySelector('.main-content');
    initMobilePageChrome(root);
    enhanceAdminTables(root);
    if (typeof window.initSalangPagination === 'function') {
      window.initSalangPagination(root);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }

  document.addEventListener('livewire:navigated', run);
  document.addEventListener('livewire:update', () => {
    window.requestAnimationFrame(run);
  });

  MQ.addEventListener('change', run);

  /** Drawer — swipe vers la gauche pour fermer (mobile admin / caisse) */
  function initAdminDrawerSwipe() {
    if (!isBackofficeShell()) {
      return;
    }

    const sidebar = document.getElementById('sidebar');
    if (!sidebar) {
      return;
    }

    let startX = 0;
    let startY = 0;
    let tracking = false;

    sidebar.addEventListener(
      'touchstart',
      (e) => {
        if (!MQ.matches) {
          return;
        }
        if (!sidebar.classList.contains('admin-sidebar-drawer-open')) {
          return;
        }
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        tracking = true;
      },
      { passive: true }
    );

    sidebar.addEventListener(
      'touchmove',
      (e) => {
        if (!tracking || !MQ.matches) {
          return;
        }
        const dx = e.touches[0].clientX - startX;
        const dy = Math.abs(e.touches[0].clientY - startY);
        if (dx < -12 && dy < 40) {
          sidebar.classList.add('admin-sidebar-drawer-dragging');
        }
      },
      { passive: true }
    );

    sidebar.addEventListener(
      'touchend',
      (e) => {
        if (!tracking || !MQ.matches) {
          return;
        }
        tracking = false;
        sidebar.classList.remove('admin-sidebar-drawer-dragging');
        const dx = e.changedTouches[0].clientX - startX;
        const dy = Math.abs(e.changedTouches[0].clientY - startY);
        if (dx < -72 && dy < 48) {
          window.dispatchEvent(new CustomEvent('shell-close-drawer'));
          window.dispatchEvent(new CustomEvent('admin-close-drawer'));
        }
      },
      { passive: true }
    );
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDrawerSwipe);
  } else {
    initAdminDrawerSwipe();
  }
})();
