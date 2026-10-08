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

  function wrapAdminTitleText(head) {
    if (!head || head.querySelector(':scope > .admin-title-banner__text')) {
      return;
    }
    const tools = head.querySelector(':scope > .admin-title-banner__tools');
    const textWrap = document.createElement('div');
    textWrap.className = 'admin-title-banner__text';
    const nodes = [...head.childNodes].filter(
      (node) => node !== tools && !(node.nodeType === 1 && node.classList?.contains('admin-title-banner__tools'))
    );
    nodes.forEach((node) => textWrap.appendChild(node));
    if (textWrap.childNodes.length) {
      head.insertBefore(textWrap, tools || null);
    }
  }

  function ensureAdminTitleTools(container) {
    let tools = container.querySelector(':scope > .admin-title-banner__tools');
    if (!tools) {
      tools = document.createElement('div');
      tools.className = 'admin-title-banner__tools';
      container.appendChild(tools);
    }
    return tools;
  }

  function collectHeaderActionNodes(actions) {
    if (!actions) {
      return [];
    }
    const nodes = [];
    actions.childNodes.forEach((child) => {
      if (child.nodeType !== 1) {
        return;
      }
      if (child.matches('.search-wrapper, .header-search, .admin-search-full')) {
        return;
      }
      if (child.querySelector?.('.search-input, #searchInput') && !child.querySelector('.btn')) {
        return;
      }
      if (child.matches('.btn, a[class*="btn-"], button.btn') || child.tagName === 'FORM') {
        nodes.push(child);
      }
    });
    return nodes;
  }

  const ADMIN_SEARCH_ICON =
    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">'
    + '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'
    + '</svg>';

  function findAdminSearchSource(page) {
    const selectors = [
      '.admin-page-header .header-search',
      '.admin-page-header-actions > .header-search',
      '.admin-page-header-actions > .search-wrapper',
      '.admin-sticky-toolbar .header-search',
      '.header-with-search .header-right > .search-wrapper',
      '.header-with-search .header-search',
    ];

    for (let i = 0; i < selectors.length; i += 1) {
      const el = page.querySelector(selectors[i]);
      if (el && !el.closest('.admin-search-panel')) {
        return el;
      }
    }

    const wrap = page.querySelector(
      '.admin-page-header .search-wrapper, .header-with-search .search-wrapper'
    );
    if (wrap && !wrap.closest('.admin-search-panel')) {
      return wrap.closest('.header-search') || wrap;
    }

    return null;
  }

  function placeAdminSearchPanel(page, panel) {
    const greeting = page.querySelector('.admin-mobile-greeting');
    const pageHead = page.querySelector('.admin-mobile-page-head');
    const header = page.querySelector('.admin-page-header');

    if (greeting) {
      greeting.insertAdjacentElement('afterend', panel);
      return;
    }
    if (pageHead) {
      pageHead.insertAdjacentElement('afterend', panel);
      return;
    }
    if (header) {
      header.insertAdjacentElement('afterbegin', panel);
      return;
    }
    page.insertBefore(panel, page.firstChild);
  }

  function createAdminSearchToggle(panelId, expanded) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'admin-banner-icon-btn' + (expanded ? ' is-active' : '');
    btn.dataset.adminSearchToggle = '1';
    btn.setAttribute('aria-controls', panelId);
    btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    btn.setAttribute('aria-label', 'Rechercher');
    btn.innerHTML = ADMIN_SEARCH_ICON;
    return btn;
  }

  function bindAdminSearchToggle(page) {
    const panel = page.querySelector('[data-admin-search-panel]');
    if (!panel) {
      return;
    }

    const input = panel.querySelector('.search-input, #searchInput, input[type="text"]');
    const toggles = page.querySelectorAll('[data-admin-search-toggle]');

    function setSearchPanelOpen(open) {
      panel.classList.toggle('is-open', open);
      panel.setAttribute('aria-hidden', open ? 'false' : 'true');
      toggles.forEach((toggle) => {
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.classList.toggle('is-active', open);
      });
      if (open && input) {
        window.setTimeout(function () {
          input.focus();
        }, 120);
      }
    }

    toggles.forEach((toggle) => {
      if (toggle.dataset.adminSearchBound === '1') {
        return;
      }
      toggle.dataset.adminSearchBound = '1';
      toggle.addEventListener('click', function () {
        setSearchPanelOpen(!panel.classList.contains('is-open'));
      });
    });

    if (input && input.value.trim() !== '' && panel.getAttribute('aria-hidden') !== 'false') {
      setSearchPanelOpen(true);
    }
  }

  function mountAdminSearchToggles(page, panelId, expanded) {
    page.querySelectorAll('.admin-mobile-greeting, .admin-mobile-page-head').forEach((banner) => {
      wrapAdminTitleText(banner);
      const tools = ensureAdminTitleTools(banner);
      if (tools.querySelector('[data-admin-search-toggle]')) {
        return;
      }
      tools.insertBefore(createAdminSearchToggle(panelId, expanded), tools.firstChild);
    });
  }

  function restoreDesktopAdminSearch(page) {
    const panel = page.querySelector('[data-admin-search-panel]');
    if (!panel) {
      return;
    }

    const wrap = panel.querySelector('.search-wrapper, .admin-shop-search');
    const header = page.querySelector('.admin-page-header, .header-with-search');
    const actions =
      header?.querySelector('.admin-page-header-actions, .header-right, .admin-sticky-toolbar')
      || header;

    if (wrap && actions) {
      let host = actions.querySelector('.header-search');
      if (!host) {
        host = document.createElement('div');
        host.className = 'header-search admin-search-full';
        const sticky = actions.querySelector('.admin-sticky-toolbar');
        if (sticky) {
          sticky.insertBefore(host, sticky.firstChild);
        } else {
          actions.insertBefore(host, actions.firstChild);
        }
      }
      host.appendChild(wrap);
    }

    panel.remove();
    page.querySelectorAll('[data-admin-search-toggle]').forEach((btn) => btn.remove());
  }

  function mountAdminSearchTogglesForPvImport(page, panelId) {
    page.querySelectorAll('.admin-mobile-greeting').forEach((banner) => {
      wrapAdminTitleText(banner);
      const tools = ensureAdminTitleTools(banner);
      if (tools.querySelector('[data-admin-search-toggle]')) {
        return;
      }
      const btn = createAdminSearchToggle(panelId, false);
      btn.dataset.adminPvImportSearchToggle = '1';
      tools.insertBefore(btn, tools.firstChild);
    });
  }

  function enhanceAdminPvImportSearch(page) {
    const host = page.querySelector('[data-admin-pv-import-search]');
    if (!host) {
      return;
    }

    const existingPanel = page.querySelector('[data-admin-pv-import-search-panel]');

    if (!MQ.matches) {
      if (existingPanel) {
        const row = existingPanel.querySelector('.pv-import-search-row');
        if (row) {
          host.appendChild(row);
          host.classList.remove('is-search-host-empty');
        }
        existingPanel.remove();
      }
      page.querySelectorAll('[data-admin-pv-import-search-toggle]').forEach((btn) => btn.remove());
      return;
    }

    if (existingPanel) {
      mountAdminSearchTogglesForPvImport(page, existingPanel.id);
      bindAdminSearchToggle(page);
      return;
    }

    const row = host.querySelector('.pv-import-search-row');
    if (!row) {
      return;
    }

    const panel = document.createElement('div');
    panel.className = 'admin-search-panel';
    panel.id = 'adminPvImportSearchPanel';
    panel.dataset.adminSearchPanel = '1';
    panel.dataset.adminPvImportSearchPanel = '1';
    panel.setAttribute('aria-hidden', 'true');
    panel.appendChild(row);

    const greeting = page.querySelector('.admin-mobile-greeting');
    if (greeting) {
      greeting.insertAdjacentElement('afterend', panel);
    } else {
      const head = page.querySelector('.admin-mobile-page-head');
      if (head) {
        head.insertAdjacentElement('afterend', panel);
      } else {
        page.insertBefore(panel, page.firstChild);
      }
    }

    host.classList.add('is-search-host-empty');

    mountAdminSearchTogglesForPvImport(page, panel.id);
    bindAdminSearchToggle(page);
  }

  function restoreDesktopAdminTitleActions(page) {
    page.querySelectorAll('.admin-page-header, .header-with-search').forEach((header) => {
      const actions = header.querySelector('.admin-page-header-actions, .header-right');
      if (!actions) {
        return;
      }

      page.querySelectorAll('.admin-title-banner__tools').forEach((tools) => {
        collectHeaderActionNodes(tools).forEach((node) => {
          if (!actions.contains(node)) {
            actions.appendChild(node);
          }
        });
        tools.querySelectorAll('[data-admin-search-toggle]').forEach((btn) => btn.remove());
      });
    });

    page.querySelectorAll('.admin-action-rail.hidden').forEach((rail) => {
      rail.classList.remove('hidden');
    });
  }

  function enhanceAdminSearchPanels(root) {
    if (!root || !document.body.classList.contains('admin-app')) {
      return;
    }

    const page = root.querySelector('.admin-mobile-page') || root;

    if (!MQ.matches) {
      restoreDesktopAdminSearch(page);
      return;
    }

    if (page.querySelector('[data-admin-search-panel]')) {
      bindAdminSearchToggle(page);
      mountAdminSearchToggles(
        page,
        'adminSearchPanel',
        page.querySelector('#adminSearchPanel')?.classList.contains('is-open') ?? false
      );
      return;
    }

    const source = findAdminSearchSource(page);
    if (!source) {
      return;
    }

    const input = source.querySelector('.search-input, #searchInput, input[type="text"]');
    const hasQuery = Boolean(input && input.value.trim() !== '');

    const panel = document.createElement('div');
    panel.className = 'admin-search-panel' + (hasQuery ? ' is-open' : '');
    panel.id = 'adminSearchPanel';
    panel.dataset.adminSearchPanel = '1';
    panel.setAttribute('aria-hidden', hasQuery ? 'false' : 'true');

    let nodeToMove = source;
    if (source.classList.contains('header-search')) {
      const wrap = source.querySelector('.search-wrapper');
      nodeToMove = wrap || source;
    }

    if (nodeToMove.classList.contains('search-wrapper')) {
      nodeToMove.classList.add('admin-shop-search');
    }

    panel.appendChild(nodeToMove);
    if (source !== nodeToMove && source.parentElement) {
      source.remove();
    }

    placeAdminSearchPanel(page, panel);
    mountAdminSearchToggles(page, panel.id, hasQuery);
    bindAdminSearchToggle(page);
  }

  function normalizeLegacyPageHeaders(page) {
    page.querySelectorAll('.page-header:not(.admin-page-header):not(.admin-desktop-page-head)').forEach((header) => {
      header.classList.add('admin-page-header');
    });

    page.querySelectorAll('.page-header .top-row').forEach((row) => {
      if (row.closest('.admin-desktop-page-head')) {
        return;
      }
      const head = row.querySelector(':scope > div:first-child');
      const actions = row.querySelector('.header-actions');
      if (head && !head.classList.contains('header-actions')) {
        head.classList.add('admin-mobile-page-head');
      }
      if (actions) {
        actions.classList.add('admin-page-header-actions');
      }
    });

    page.querySelectorAll('.header-with-search:not(.admin-page-header)').forEach((header) => {
      header.classList.add('admin-page-header');
      const left = header.querySelector('.header-left');
      if (left) {
        left.classList.add('admin-mobile-page-head');
      }
      const right = header.querySelector('.header-right');
      if (right) {
        right.classList.add('admin-page-header-actions');
      }
    });

    page.querySelectorAll('.admin-page-header.flex, .flex.admin-page-header').forEach((header) => {
      const head = header.querySelector(':scope > .admin-mobile-page-head, :scope > div:first-child');
      if (head && !head.classList.contains('admin-page-header-actions')) {
        head.classList.add('admin-mobile-page-head');
      }
    });
  }

  function restoreDesktopAdminChrome(root) {
    if (!root) {
      return;
    }
    const page = root.querySelector('.admin-mobile-page') || root;
    normalizeLegacyPageHeaders(page);
    restoreDesktopAdminSearch(page);
    restoreDesktopAdminTitleActions(page);
    page.querySelectorAll('.admin-action-rail.hidden').forEach((rail) => {
      rail.classList.remove('hidden');
    });
    page.querySelectorAll('.admin-mobile-greeting[data-auto-greeting="1"]').forEach((el) => {
      el.remove();
    });
    if (page.dataset.adminMobileGreetingAuto === '1') {
      delete page.dataset.adminMobileGreetingAuto;
    }
  }

  function relocateAdminTitleActions(root) {
    if (!root || !document.body.classList.contains('admin-app')) {
      return;
    }

    const page = root.querySelector('.admin-mobile-page') || root;
    normalizeLegacyPageHeaders(page);

    if (!MQ.matches) {
      restoreDesktopAdminTitleActions(page);
      return;
    }

    page.querySelectorAll('.admin-mobile-greeting').forEach((greeting) => {
      wrapAdminTitleText(greeting);
      const tools = ensureAdminTitleTools(greeting);
      const header = page.querySelector('.admin-page-header:not(.admin-desktop-page-head)');
      const actions = header?.querySelector('.admin-page-header-actions');
      collectHeaderActionNodes(actions).forEach((node) => {
        if (!tools.contains(node)) {
          tools.appendChild(node);
        }
      });
    });

    page.querySelectorAll('.admin-page-header').forEach((header) => {
      if (header.classList.contains('admin-desktop-page-head')) {
        return;
      }
      const head = header.querySelector('.admin-mobile-page-head');
      if (!head) {
        return;
      }
      if (head.classList.contains('is-mobile-banner') && head.querySelector('.admin-title-banner__tools')) {
        wrapAdminTitleText(head);
        return;
      }
      wrapAdminTitleText(head);
      const tools = ensureAdminTitleTools(head);
      const actions = header.querySelector('.admin-page-header-actions');
      collectHeaderActionNodes(actions).forEach((node) => {
        if (!tools.contains(node)) {
          tools.appendChild(node);
        }
      });
    });

    page.querySelectorAll('.admin-mobile-page-head').forEach((head) => {
      if (head.closest('.admin-page-header')) {
        return;
      }
      const rail = head.nextElementSibling;
      if (!rail?.classList.contains('admin-action-rail')) {
        return;
      }
      wrapAdminTitleText(head);
      const tools = ensureAdminTitleTools(head);
      rail.querySelectorAll('.btn, a[class*="btn-"], form').forEach((node) => {
        if (!tools.contains(node)) {
          tools.appendChild(node);
        }
      });
      rail.classList.add('hidden');
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

    const hasCustomMobileBanner = page.querySelector('.admin-mobile-page-head.is-mobile-banner');

    if (hasCustomMobileBanner) {
      page.querySelectorAll('.admin-mobile-greeting[data-auto-greeting="1"]').forEach((el) => {
        el.remove();
      });
      if (page.dataset.adminMobileGreetingAuto === '1') {
        delete page.dataset.adminMobileGreetingAuto;
      }
    }

    const hasGreeting =
      page.querySelector('.admin-mobile-greeting') ||
      page.querySelector('.admin-profile-hero') ||
      hasCustomMobileBanner;

    if (!hasGreeting && page.dataset.adminMobileGreetingAuto !== '1') {
      const h1 = Array.from(page.querySelectorAll('h1')).find(function (el) {
        return !el.closest('.admin-desktop-page-head, .admin-mobile-greeting, .admin-profile-hero');
      });
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
    if (!root) {
      return;
    }

    if (!MQ.matches) {
      restoreDesktopAdminChrome(root);
      enhanceAdminTables(root);
      if (typeof window.initSalangPagination === 'function') {
        window.initSalangPagination(root);
      }
      return;
    }

    initMobilePageChrome(root);
    enhanceAdminSearchPanels(root);
    const page = root.querySelector('.admin-mobile-page') || root;
    if (page) {
      enhanceAdminPvImportSearch(page);
    }
    relocateAdminTitleActions(root);
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
