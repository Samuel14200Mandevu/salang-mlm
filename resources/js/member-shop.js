/**
 * Boutique membre — recherche, filtres rapides, partage, favoris.
 */
(function () {
  function shopToast(message, type) {
    if (typeof window.showToast === 'function') {
      window.showToast(message, type || 'success');
    }
  }

  function shopShare(url, title) {
    if (!url) {
      return;
    }
    if (navigator.share) {
      navigator.share({ title: title || document.title, url: url }).catch(function () {
        copyFallback(url);
      });
      return;
    }
    copyFallback(url);
  }

  function copyFallback(url) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(function () {
        shopToast('Lien copié', 'success');
      });
      return;
    }
    var input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    shopToast('Lien copié', 'success');
  }

  window.shopShareProduct = shopShare;

  function bindShareButtons(root) {
    root.querySelectorAll('[data-shop-share-url]').forEach(function (btn) {
      if (btn.dataset.shareBound === '1') {
        return;
      }
      btn.dataset.shareBound = '1';
      btn.addEventListener('click', function () {
        shopShare(btn.dataset.shopShareUrl, btn.dataset.shopShareTitle);
      });
    });
  }

  function bindWishlistButtons(root) {
    root.querySelectorAll('[data-shop-wishlist]').forEach(function (btn) {
      if (btn.dataset.wishlistBound === '1') {
        return;
      }
      btn.dataset.wishlistBound = '1';
      btn.addEventListener('click', function () {
        var productId = btn.dataset.shopWishlist;
        var glyph = btn.querySelector('.shop-action-btn__glyph');
        if (glyph) {
          glyph.classList.add('shop-like-pop');
          setTimeout(function () { glyph.classList.remove('shop-like-pop'); }, 450);
        }
        var icon = btn.querySelector('.shop-action-btn__icon');
        fetch('/wishlist/toggle/' + productId, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          },
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) {
              btn.classList.toggle('is-active', !!data.added);
              if (icon) {
                icon.setAttribute('fill', data.added ? 'currentColor' : 'none');
              }
              var label = btn.querySelector('.shop-action-btn__label');
              if (label) {
                label.textContent = data.added ? 'Aimé' : 'J\'aime';
              }
              shopToast(data.message, 'success');
            } else {
              shopToast(data.message || 'Erreur', 'error');
            }
          })
          .catch(function () {
            shopToast('Erreur réseau', 'error');
          });
      });
    });
  }

  function initMemberShopCatalog() {
    const root = document.querySelector('.member-shop--catalog');
    if (!root || root.dataset.shopInit === '1') {
      return;
    }
    root.dataset.shopInit = '1';

    bindShareButtons(root);
    bindWishlistButtons(root);

    const searchToggle = document.getElementById('shopSearchToggle');
    const searchPanel = document.getElementById('shopSearchPanel');
    const searchInput = document.getElementById('searchInput');

    function setSearchPanelOpen(open) {
      if (!searchPanel || !searchToggle) {
        return;
      }
      searchPanel.classList.toggle('is-open', open);
      searchPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
      searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      searchToggle.classList.toggle('is-active', open);
      if (open && searchInput) {
        window.setTimeout(function () {
          searchInput.focus();
        }, 120);
      }
    }

    if (searchToggle && searchPanel) {
      searchToggle.addEventListener('click', function () {
        setSearchPanelOpen(!searchPanel.classList.contains('is-open'));
      });

      if (searchInput && searchInput.value.trim() !== '') {
        setSearchPanelOpen(true);
      }
    }

    const productsContainer = document.getElementById('productsContainer');
    const searchResult = document.getElementById('searchResult');
    const resultCount = document.getElementById('resultCount');
    const visibleCountEl = document.getElementById('shopVisibleCount');
    const catalogTotalEl = document.getElementById('shopCatalogTotal');
    let quickFilter = null;
    let searchTimeout;
    let searchAbort = null;
    let lastServerQuery = (searchInput?.value || '').trim();

    function getProductCards() {
      return root.querySelectorAll('.member-shop-card');
    }

    function cardMatchesQuickFilter(card) {
      if (quickFilter === 'featured') {
        return card.dataset.featured === '1';
      }
      if (quickFilter === 'instock') {
        return card.dataset.inStock === '1';
      }
      return true;
    }

    function applyQuickFilterOnly() {
      const cards = getProductCards();
      let count = 0;

      cards.forEach(function (card) {
        if (cardMatchesQuickFilter(card)) {
          card.style.display = '';
          count++;
        } else {
          card.style.display = 'none';
        }
      });

      if (visibleCountEl) {
        visibleCountEl.textContent = String(count);
      }

      updateResultsUi((searchInput?.value || '').trim(), count);
    }

    function updateResultsUi(query, matchCount) {
      const paginationContainer = document.getElementById('paginationContainer');

      if (query.length > 0) {
        searchResult?.classList.remove('hidden');
        searchResult?.classList.add('is-visible');
        if (resultCount) {
          resultCount.textContent = String(matchCount);
        }
        if (paginationContainer) {
          paginationContainer.style.display = '';
        }
      } else if (quickFilter) {
        searchResult?.classList.remove('hidden');
        searchResult?.classList.add('is-visible');
        if (resultCount) {
          resultCount.textContent = String(matchCount);
        }
        if (paginationContainer) {
          paginationContainer.style.display = 'none';
        }
      } else {
        searchResult?.classList.add('hidden');
        searchResult?.classList.remove('is-visible');
        if (paginationContainer) {
          paginationContainer.style.display = '';
        }
      }
    }

    function buildCatalogFetchUrl(query) {
      const base = productsContainer?.dataset.catalogUrl || window.location.pathname;
      const url = new URL(base, window.location.origin);
      const current = new URL(window.location.href);

      current.searchParams.forEach(function (value, key) {
        if (key !== 'page' && key !== 'search') {
          url.searchParams.set(key, value);
        }
      });

      if (query) {
        url.searchParams.set('search', query);
      }

      return url;
    }

    function syncBrowserUrl(query) {
      const url = new URL(window.location.href);
      if (query) {
        url.searchParams.set('search', query);
      } else {
        url.searchParams.delete('search');
      }
      url.searchParams.delete('page');
      window.history.replaceState({}, '', url.toString());
    }

    function afterCatalogHtmlSwap(total, visible) {
      bindShareButtons(root);
      bindWishlistButtons(root);

      if (catalogTotalEl && typeof total === 'number') {
        catalogTotalEl.textContent = String(total);
      }

      if (quickFilter) {
        applyQuickFilterOnly();
        return;
      }

      if (visibleCountEl && typeof visible === 'number') {
        visibleCountEl.textContent = String(visible);
      }
    }

    function fetchCatalogSearch(query) {
      if (searchAbort) {
        searchAbort.abort();
      }
      searchAbort = new AbortController();

      productsContainer?.classList.add('is-loading');

      fetch(buildCatalogFetchUrl(query).toString(), {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        signal: searchAbort.signal,
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('catalog_fetch_failed');
          }
          return response.json();
        })
        .then(function (payload) {
          if (!payload.success || !productsContainer) {
            return;
          }
          productsContainer.innerHTML = payload.html;
          lastServerQuery = query;
          syncBrowserUrl(query);
          afterCatalogHtmlSwap(payload.total, payload.visible);
          updateResultsUi(query, payload.total);
        })
        .catch(function (err) {
          if (err.name === 'AbortError') {
            return;
          }
          shopToast('Impossible de charger la recherche', 'error');
        })
        .finally(function () {
          productsContainer?.classList.remove('is-loading');
          searchAbort = null;
        });
    }

    function onSearchInput() {
      const query = (searchInput?.value || '').trim();

      if (query === lastServerQuery) {
        if (!query && quickFilter) {
          applyQuickFilterOnly();
        }
        return;
      }

      clearTimeout(searchTimeout);
      searchTimeout = window.setTimeout(function () {
        if (!query) {
          fetchCatalogSearch('');
          return;
        }
        fetchCatalogSearch(query);
      }, 380);
    }

    root.querySelectorAll('[data-shop-quick]').forEach(function (chip) {
      chip.addEventListener('click', function () {
        const key = chip.dataset.shopQuick;
        const isActive = chip.classList.contains('is-active');
        root.querySelectorAll('[data-shop-quick]').forEach(function (c) {
          c.classList.remove('is-active');
        });
        if (isActive) {
          quickFilter = null;
        } else {
          chip.classList.add('is-active');
          quickFilter = key;
        }
        applyQuickFilterOnly();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', onSearchInput);
    }

    if ((searchInput?.value || '').trim()) {
      updateResultsUi(
        lastServerQuery,
        parseInt(catalogTotalEl?.textContent || '0', 10) || getProductCards().length
      );
    }
  }

  function initMemberShopDetail() {
    const root = document.querySelector('.member-shop--detail');
    if (!root) {
      return;
    }
    bindShareButtons(root);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initMemberShopCatalog();
    initMemberShopDetail();
  });
})();
