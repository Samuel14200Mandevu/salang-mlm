    <!-- ===== TOAST CONTAINER ===== -->
    <div id="toastContainer" class="toast-container" role="status" aria-live="polite" aria-atomic="true"></div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('scripts')

    <script>
    // ================================================================
    //  MISE À JOUR DU TITRE DYNAMIQUE
    // ================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const pageTitle = document.getElementById('pageTitle');
        if (!pageTitle) return;

        function updatePageTitle() {
            const activeLink = document.querySelector('.sidebar-link.active');

            if (activeLink) {
                const dataTitle = activeLink.getAttribute('data-title');
                if (dataTitle) {
                    pageTitle.textContent = dataTitle;
                    return;
                }

                const label = activeLink.querySelector('.label');
                if (label) {
                    const text = label.textContent.trim();
                    if (text) {
                        pageTitle.textContent = text;
                        return;
                    }
                }
            }

            const titleElement = document.querySelector('title');
            if (titleElement) {
                const fullTitle = titleElement.textContent;
                const cleanTitle = fullTitle.replace(/\s*[-|]\s*Salang\s*MLM\s*$/, '').trim();
                if (cleanTitle && cleanTitle !== 'Reception' && cleanTitle !== 'Dashboard Caissier') {
                    pageTitle.textContent = cleanTitle;
                }
            }
        }

        setTimeout(updatePageTitle, 50);

        const observer = new MutationObserver(function() {
            updatePageTitle();
        });

        document.querySelectorAll('.sidebar-link').forEach(function(link) {
            observer.observe(link, {
                attributes: true,
                attributeFilter: ['class']
            });
        });

        document.addEventListener('livewire:update', function() {
            setTimeout(updatePageTitle, 100);
        });

        document.addEventListener('livewire:load', function() {
            setTimeout(updatePageTitle, 100);
        });

        let lastUrl = window.location.href;
        setInterval(function() {
            if (window.location.href !== lastUrl) {
                lastUrl = window.location.href;
                setTimeout(updatePageTitle, 150);
            }
        }, 500);
    });

    // ================================================================
    //  DÉFINITION DES FONCTIONS GLOBALES
    // ================================================================

    window.cart = [];

    window.buildCheckoutUrl = function() {
        if (window.cart.length === 0) return null;
        const items = window.cart.map(item => item.type + ':' + item.id);
        return '{{ route('cashier.checkout') }}?items=' + items.join(',');
    };

    window.loadCart = function() {
        try {
            const saved = localStorage.getItem('pos_cart');
            if (saved) {
                window.cart = JSON.parse(saved);
                window.renderCart();
            }
        } catch (e) {
            window.cart = [];
        }
    };

    window.saveCart = function() {
        localStorage.setItem('pos_cart', JSON.stringify(window.cart));
        window.updateCartCount();
    };

    window.addToCart = function(itemId, type) {
        const existing = window.cart.find(item => item.id === itemId && item.type === type);
        if (existing) {
            existing.quantity += 1;
            window.saveCart();
            window.renderCart();
            window.showToast('Quantité augmentée', 'success');
            return;
        }

        let card;
        if (type === 'product') {
            card = document.querySelector(`.product-card[data-product-id="${itemId}"][data-type="product"]`);
        } else {
            card = document.querySelector(`.product-card[data-product-id="${itemId}"][data-type="package"]`);
        }

        if (!card) {
            window.showToast('Erreur: article non trouvé', 'error');
            return;
        }

        const name = card.querySelector('.product-name')?.textContent || 'Article';
        const priceText = card.querySelector('.product-price')?.textContent || '$0.00';
        const price = parseFloat(priceText.replace('$', '').replace(',', ''));
        const image = card.querySelector('.image-container img')?.getAttribute('src') || null;
        const source = type === 'product' ? 'pos' : 'mlm';
        const sourceLabel = type === 'product' ? 'POS' : 'MLM';
        const pvBadge = card.querySelector('.pv-badge');
        const pvValue = pvBadge ? parseFloat(pvBadge.textContent.replace(' PV', '')) || 0 : 0;
        const bvBadge = card.querySelector('.pv-badge[style*="color:#3d8a2a"]');
        const bvValue = bvBadge ? parseFloat(bvBadge.textContent.replace(' BV', '')) || 0 : 0;

        window.cart.push({
            id: itemId,
            type: type,
            name: name,
            price: price,
            image: image,
            source: source,
            sourceLabel: sourceLabel,
            pv_value: pvValue,
            bv_value: bvValue,
            quantity: 1
        });

        window.saveCart();
        window.renderCart();
        window.showToast('Article ajouté au panier', 'success');
    };

    window.removeFromCart = function(itemId, type) {
        window.cart = window.cart.filter(item => !(item.id === itemId && item.type === type));
        window.saveCart();
        window.renderCart();
    };

    window.updateQuantity = function(itemId, type, delta) {
        const item = window.cart.find(item => item.id === itemId && item.type === type);
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                window.removeFromCart(itemId, type);
            } else {
                window.saveCart();
                window.renderCart();
            }
        }
    };

    window.renderCart = function() {
        const cartItemsContainer = document.getElementById('cartItems');
        const cartEmpty = document.getElementById('cartEmpty');
        const cartFooter = document.getElementById('cartFooter');
        const cartTotal = document.getElementById('cartTotal');
        const checkoutLink = document.getElementById('checkoutLink');

        if (!cartItemsContainer) return;

        cartItemsContainer.innerHTML = '';

        if (window.cart.length === 0) {
            if (cartEmpty) cartEmpty.classList.remove('hidden');
            if (cartItemsContainer) cartItemsContainer.classList.add('hidden');
            if (cartFooter) cartFooter.classList.add('hidden');
            window.updateCartCount();
            return;
        }

        if (cartEmpty) cartEmpty.classList.add('hidden');
        if (cartItemsContainer) cartItemsContainer.classList.remove('hidden');
        if (cartFooter) cartFooter.classList.remove('hidden');

        let total = 0;
        window.cart.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;

            const div = document.createElement('div');
            div.className = 'flex gap-3 items-center py-3 border-b border-[var(--border-color)] last:border-b-0';
            div.innerHTML = `
                <div class="w-12 h-12 rounded-md overflow-hidden flex-shrink-0 bg-[var(--bg-secondary)]">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover" loading="lazy">` : `
                        <svg class="w-full h-full p-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                    `}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-[var(--text-primary)] truncate">${item.name}</h4>
                    <div class="text-xs text-[var(--text-secondary)]">$${item.price.toFixed(2)} x ${item.quantity}</div>
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full ${item.source === 'pos' ? 'bg-green-500/10 text-green-500' : 'bg-blue-500/10 text-blue-500'}">${item.sourceLabel}</span>
                    ${item.pv_value ? `<span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-blue-500/10 text-blue-500 ml-1">${item.pv_value} PV</span>` : ''}
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.updateQuantity(${item.id}, '${item.type}', -1)" class="btn btn-outline btn-sm btn-icon">-</button>
                    <span class="w-5 text-center font-semibold text-sm">${item.quantity}</span>
                    <button onclick="window.updateQuantity(${item.id}, '${item.type}', 1)" class="btn btn-outline btn-sm btn-icon">+</button>
                </div>
                <button onclick="window.removeFromCart(${item.id}, '${item.type}')" class="text-[var(--danger)] hover:text-[var(--danger-hover)] text-lg leading-none transition-colors p-1">×</button>
            `;
            cartItemsContainer.appendChild(div);
        });

        if (cartTotal) cartTotal.textContent = `$${total.toFixed(2)}`;
        window.updateCartCount();

        if (checkoutLink) {
            const url = window.buildCheckoutUrl();
            if (url) {
                checkoutLink.href = url;
                checkoutLink.style.display = 'flex';
            } else {
                checkoutLink.href = '#';
                checkoutLink.style.display = 'none';
            }
        }
    };

    window.updateCartCount = function() {
        const count = window.cart.reduce((sum, item) => sum + item.quantity, 0);
        const headerCartCount = document.getElementById('headerCartCount');
        const mobileCartCount = document.getElementById('mobileCartCount');

        if (count > 0) {
            if (headerCartCount) {
                headerCartCount.textContent = count;
                headerCartCount.classList.remove('hidden');
            }
            if (mobileCartCount) {
                mobileCartCount.textContent = count;
                mobileCartCount.classList.remove('hidden');
            }
        } else {
            if (headerCartCount) headerCartCount.classList.add('hidden');
            if (mobileCartCount) mobileCartCount.classList.add('hidden');
        }
    };

    window.toggleCart = function() {
        const sidebar = document.getElementById('cartSidebar');
        const overlay = document.getElementById('cartOverlay');
        if (sidebar) {
            sidebar.classList.toggle('translate-x-full');
        }
        if (overlay) {
            overlay.classList.toggle('hidden');
        }
    };

    window.openClearCartModal = function() {
        if (window.cart.length === 0) {
            window.showToast('Le panier est déjà vide', 'info');
            return;
        }
        const modal = document.getElementById('clearCartModal');
        if (modal) {
            modal.classList.remove('opacity-0', 'invisible');
            const box = modal.querySelector('.bg-\\[var\\(--bg-card\\)\\]');
            if (box) box.classList.remove('scale-90');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeClearCartModal = function() {
        const modal = document.getElementById('clearCartModal');
        if (modal) {
            modal.classList.add('opacity-0', 'invisible');
            const box = modal.querySelector('.bg-\\[var\\(--bg-card\\)\\]');
            if (box) box.classList.add('scale-90');
            document.body.style.overflow = '';
        }
    };

    window.confirmClearCart = function() {
        window.cart = [];
        window.saveCart();
        window.renderCart();
        window.closeClearCartModal();
        window.showToast('Panier vidé avec succès', 'info');
    };

    // ================================================================
    //  CONFIRMATION DIALOG
    // ================================================================
    let confirmCallback = null;
    let confirmForm = null;

    window.showConfirmDialog = function(options) {
        const dialog = document.getElementById('confirmDialog');
        const icon = dialog.querySelector('.icon');
        const title = document.getElementById('confirmTitle');
        const message = document.getElementById('confirmMessage');
        const confirmBtn = document.getElementById('confirmDialogBtn');

        icon.className = 'icon';
        icon.classList.add(options.type || 'danger');

        if (options.type === 'success') {
            icon.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            `;
        } else if (options.type === 'warning') {
            icon.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                </svg>
            `;
        } else {
            icon.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            `;
        }

        title.textContent = options.title || 'Confirmation';
        message.textContent = options.message || 'Êtes-vous sûr de vouloir continuer ?';
        confirmBtn.textContent = options.confirmText || 'Confirmer';
        confirmBtn.className = 'btn';

        if (options.type === 'success') {
            confirmBtn.classList.add('btn-success');
        } else if (options.type === 'danger') {
            confirmBtn.classList.add('btn-danger');
        } else {
            confirmBtn.classList.add('btn-primary');
        }

        confirmCallback = options.onConfirm || null;
        confirmForm = options.form || null;

        dialog.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closeConfirmDialog = function() {
        document.getElementById('confirmDialog').classList.remove('active');
        document.body.style.overflow = '';
        confirmCallback = null;
        confirmForm = null;
    };

    window.confirmLogout = function(event, form) {
        event.preventDefault();
        window.showConfirmDialog({
            type: 'danger',
            title: 'Confirmation de déconnexion',
            message: 'Êtes-vous sûr de vouloir vous déconnecter ? Vous devrez vous reconnecter pour accéder à votre compte.',
            confirmText: 'Se déconnecter',
            onConfirm: function() {
                if (form) form.submit();
                window.closeConfirmDialog();
            },
            form: form
        });
    };

    // ================================================================
    //  INITIALISATION AU CHARGEMENT
    // ================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // ================================================================
        //  THEME TOGGLE
        // ================================================================
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }

        const toggle = document.getElementById('theme-toggle');
        const icon = document.getElementById('theme-icon');
        if (toggle && icon) {
            function setTheme(theme) {
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
                updateIcon();
            }

            function updateIcon() {
                if (!icon) return;
                if (document.documentElement.classList.contains('dark')) {
                    icon.setAttribute('d', 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z');
                } else {
                    icon.setAttribute('d', 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z');
                }
            }

            setTheme(localStorage.getItem('theme') === 'dark' ? 'dark' : 'light');

            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                if (document.documentElement.classList.contains('dark')) {
                    setTheme('light');
                } else {
                    setTheme('dark');
                }
            });
        }

        // ================================================================
        //  CART - EVENT LISTENERS
        // ================================================================

        document.getElementById('cartToggleBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.toggleCart();
        });

        document.getElementById('mobileCartToggleBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.toggleCart();
        });

        document.getElementById('cartCloseBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.toggleCart();
        });

        document.getElementById('cartOverlay')?.addEventListener('click', function(e) {
            window.toggleCart();
        });

        document.getElementById('checkoutLink')?.addEventListener('click', function(e) {
            if (window.cart.length === 0) {
                e.preventDefault();
                window.showToast('Le panier est vide', 'error');
            }
        });

        document.getElementById('clearCartBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.openClearCartModal();
        });

        document.getElementById('clearCartCancelBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.closeClearCartModal();
        });

        document.getElementById('clearCartConfirmBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.confirmClearCart();
        });

        document.getElementById('clearCartModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeClearCartModal();
            }
        });

        // ================================================================
        //  CONFIRMATION DIALOG - EVENT LISTENERS
        // ================================================================

        document.getElementById('confirmCancelBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.closeConfirmDialog();
        });

        document.getElementById('confirmDialogBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof confirmCallback === 'function') {
                confirmCallback();
            } else if (confirmForm) {
                confirmForm.submit();
            }
            window.closeConfirmDialog();
        });

        document.getElementById('confirmDialog')?.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeConfirmDialog();
            }
        });

        // ================================================================
        //  LOGOUT FORMS - EVENT LISTENERS
        // ================================================================

        document.querySelectorAll('.logout-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                window.confirmLogout(e, this);
            });
        });

        // ================================================================
        //  KEYBOARD SHORTCUTS
        // ================================================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeConfirmDialog();
                window.closeClearCartModal();
                const sidebar = document.getElementById('cartSidebar');
                if (sidebar && !sidebar.classList.contains('translate-x-full')) {
                    window.toggleCart();
                }
            }
        });

        // ================================================================
        //  CHARGER LE PANIER
        // ================================================================
        window.loadCart();
    });
    </script>
</body>
