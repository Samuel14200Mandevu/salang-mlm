    </div><!-- /.app-container -->

    @include('partials.shell.cart-root')

    <div id="toastContainer" class="cashier-toast-container" role="status" aria-live="polite" aria-atomic="true"></div>

    <script>
    (function () {
        if (!document.body.classList.contains('cashier-app')) return;

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = String(str ?? '');
            return div.innerHTML;
        }

        window.showToast = window.showToast || function (message, type, duration) {
            type = type || 'success';
            duration = duration || 3500;
            var container = document.getElementById('toastContainer');
            if (!container) return;
            var toast = document.createElement('div');
            toast.className = 'toast-item ' + type;
            toast.innerHTML =
                '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">' +
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
                '<span>' + escapeHtml(message) + '</span>' +
                '<button type="button" class="toast-close" aria-label="Fermer">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>';
            toast.querySelector('.toast-close').addEventListener('click', function () { toast.remove(); });
            container.appendChild(toast);
            requestAnimationFrame(function () { toast.classList.add('show'); });
            setTimeout(function () {
                if (toast.parentNode) {
                    toast.style.animation = 'toastOut 0.3s ease forwards';
                    setTimeout(function () { toast.remove(); }, 400);
                }
            }, duration);
        };

        window.cart = window.cart || [];

        window.buildCheckoutUrl = function () {
            if (window.cart.length === 0) return null;
            var items = window.cart.map(function (item) { return item.type + ':' + item.id; });
            var base = document.body.dataset.checkoutUrl;
            if (!base) return null;
            return base + '?items=' + items.join(',');
        };

        window.loadCart = function () {
            try {
                var saved = localStorage.getItem('pos_cart');
                if (saved) {
                    window.cart = JSON.parse(saved);
                    window.renderCart();
                }
            } catch (e) {
                window.cart = [];
            }
        };

        window.saveCart = function () {
            localStorage.setItem('pos_cart', JSON.stringify(window.cart));
            window.updateCartCount();
        };

        window.addToCart = function (itemId, type) {
            var existing = window.cart.find(function (item) { return item.id === itemId && item.type === type; });
            if (existing) {
                existing.quantity += 1;
                window.saveCart();
                window.renderCart();
                window.showToast('Quantité augmentée', 'success');
                return;
            }
            var card = type === 'product'
                ? document.querySelector('.product-card[data-product-id="' + itemId + '"][data-type="product"]')
                : document.querySelector('.product-card[data-product-id="' + itemId + '"][data-type="package"]');
            if (!card) {
                window.showToast('Erreur: article non trouvé', 'error');
                return;
            }
            var name = (card.querySelector('.product-name') && card.querySelector('.product-name').textContent) || 'Article';
            var priceText = (card.querySelector('.product-price') && card.querySelector('.product-price').textContent) || '$0.00';
            var price = parseFloat(priceText.replace('$', '').replace(',', ''));
            var imageEl = card.querySelector('.image-container img');
            var image = imageEl ? imageEl.getAttribute('src') : null;
            var pvBadge = card.querySelector('.pv-badge');
            var pvValue = pvBadge ? parseFloat(String(pvBadge.textContent).replace(/ PV| BV/g, '')) || 0 : 0;

            window.cart.push({
                id: itemId,
                type: type,
                name: name,
                price: price,
                image: image,
                source: type === 'product' ? 'pos' : 'mlm',
                sourceLabel: type === 'product' ? 'POS' : 'MLM',
                pv_value: pvValue,
                bv_value: 0,
                quantity: 1
            });
            window.saveCart();
            window.renderCart();
            window.showToast('Article ajouté au panier', 'success');
        };

        window.addToCartGlobal = function (productId, type, name, price, pv) {
            var existing = window.cart.find(function (item) { return item.id === productId && item.type === type; });
            if (existing) {
                existing.quantity += 1;
            } else {
                window.cart.push({
                    id: productId,
                    type: type,
                    name: name,
                    price: price,
                    pv_value: pv || 0,
                    bv_value: 0,
                    quantity: 1,
                    source: type === 'product' ? 'pos' : 'mlm',
                    sourceLabel: type === 'product' ? 'POS' : 'MLM',
                    image: null
                });
            }
            window.saveCart();
            window.renderCart();
        };

        window.removeFromCart = function (itemId, type) {
            window.cart = window.cart.filter(function (item) { return !(item.id === itemId && item.type === type); });
            window.saveCart();
            window.renderCart();
        };

        window.updateQuantity = function (itemId, type, delta) {
            var item = window.cart.find(function (i) { return i.id === itemId && i.type === type; });
            if (!item) return;
            item.quantity += delta;
            if (item.quantity <= 0) {
                window.removeFromCart(itemId, type);
            } else {
                window.saveCart();
                window.renderCart();
            }
        };

        window.renderCart = function () {
            var cartItemsContainer = document.getElementById('cartItems');
            var cartEmpty = document.getElementById('cartEmpty');
            var cartFooter = document.getElementById('cartFooter');
            var cartTotal = document.getElementById('cartTotal');
            var checkoutLink = document.getElementById('checkoutLink');
            if (!cartItemsContainer) return;

            cartItemsContainer.innerHTML = '';

            if (window.cart.length === 0) {
                if (cartEmpty) cartEmpty.classList.remove('hidden');
                cartItemsContainer.classList.add('hidden');
                if (cartFooter) cartFooter.classList.add('hidden');
                window.updateCartCount();
                return;
            }

            if (cartEmpty) cartEmpty.classList.add('hidden');
            cartItemsContainer.classList.remove('hidden');
            if (cartFooter) cartFooter.classList.remove('hidden');

            var total = 0;
            window.cart.forEach(function (item) {
                total += item.price * item.quantity;
                var div = document.createElement('div');
                div.className = 'flex gap-2 items-center py-3 border-b border-[var(--border-color)] last:border-b-0 min-w-0';
                div.innerHTML =
                    '<div class="flex-1 min-w-0">' +
                    '<h4 class="text-sm font-semibold text-[var(--text-primary)] truncate">' + escapeHtml(item.name) + '</h4>' +
                    '<div class="text-xs text-[var(--text-secondary)]">$' + item.price.toFixed(2) + ' × ' + item.quantity + '</div>' +
                    '</div>' +
                    '<div class="flex items-center gap-1 flex-shrink-0">' +
                    '<button type="button" class="btn btn-outline btn-sm btn-icon cart-qty-minus" data-id="' + item.id + '" data-type="' + item.type + '">-</button>' +
                    '<span class="w-5 text-center font-semibold text-sm tabular-nums">' + item.quantity + '</span>' +
                    '<button type="button" class="btn btn-outline btn-sm btn-icon cart-qty-plus" data-id="' + item.id + '" data-type="' + item.type + '">+</button>' +
                    '<button type="button" class="cart-remove text-[var(--danger)] text-lg leading-none p-1 flex-shrink-0" aria-label="Retirer" data-id="' + item.id + '" data-type="' + item.type + '">×</button>' +
                    '</div>';
                cartItemsContainer.appendChild(div);
            });

            cartItemsContainer.querySelectorAll('.cart-qty-minus').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    window.updateQuantity(Number(btn.getAttribute('data-id')), btn.getAttribute('data-type'), -1);
                });
            });
            cartItemsContainer.querySelectorAll('.cart-qty-plus').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    window.updateQuantity(Number(btn.getAttribute('data-id')), btn.getAttribute('data-type'), 1);
                });
            });
            cartItemsContainer.querySelectorAll('.cart-remove').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    window.removeFromCart(Number(btn.getAttribute('data-id')), btn.getAttribute('data-type'));
                });
            });

            if (cartTotal) cartTotal.textContent = '$' + total.toFixed(2);
            window.updateCartCount();

            if (checkoutLink) {
                var url = window.buildCheckoutUrl();
                if (url) {
                    checkoutLink.href = url;
                    checkoutLink.style.display = 'flex';
                } else {
                    checkoutLink.href = '#';
                    checkoutLink.style.display = 'none';
                }
            }
        };

        window.updateCartCount = function () {
            var count = window.cart.reduce(function (sum, item) { return sum + item.quantity; }, 0);
            var headerCartCount = document.getElementById('headerCartCount');
            var mobileCartCount = document.getElementById('mobileCartCount');
            var mirror = document.getElementById('cartHeaderCountMirror');
            var emptyFooter = document.getElementById('cartEmptyFooter');

            if (count > 0) {
                if (headerCartCount) {
                    headerCartCount.textContent = count > 99 ? '99+' : String(count);
                    headerCartCount.classList.remove('hidden');
                }
                if (mobileCartCount) {
                    mobileCartCount.textContent = count > 99 ? '99+' : String(count);
                    mobileCartCount.classList.remove('hidden');
                }
                if (mirror) {
                    mirror.textContent = count > 99 ? '99+' : String(count);
                    mirror.classList.remove('hidden');
                }
                if (emptyFooter) emptyFooter.classList.add('hidden');
            } else {
                if (headerCartCount) headerCartCount.classList.add('hidden');
                if (mobileCartCount) mobileCartCount.classList.add('hidden');
                if (mirror) mirror.classList.add('hidden');
                if (emptyFooter) emptyFooter.classList.remove('hidden');
            }
        };

        window.openClearCartModal = function () {
            if (window.cart.length === 0) {
                window.showToast('Le panier est déjà vide', 'info');
                return;
            }
            var modal = document.getElementById('clearCartModal');
            if (modal) {
                modal.classList.remove('opacity-0', 'invisible');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeClearCartModal = function () {
            var modal = document.getElementById('clearCartModal');
            if (modal) {
                modal.classList.add('opacity-0', 'invisible');
                document.body.style.overflow = '';
            }
        };

        window.confirmClearCart = function () {
            window.cart = [];
            window.saveCart();
            window.renderCart();
            window.closeClearCartModal();
            window.showToast('Panier vidé avec succès', 'info');
        };

        var confirmCallback = null;
        var confirmForm = null;

        window.showConfirmDialog = function (options) {
            var dialog = document.getElementById('confirmDialog');
            if (!dialog) return;
            var icon = dialog.querySelector('.icon');
            var title = document.getElementById('confirmTitle');
            var message = document.getElementById('confirmMessage');
            var confirmBtn = document.getElementById('confirmDialogBtn');
            if (!icon || !title || !message || !confirmBtn) return;

            icon.className = 'icon';
            icon.classList.add(options.type || 'danger');
            title.textContent = options.title || 'Confirmation';
            message.textContent = options.message || 'Êtes-vous sûr de vouloir continuer ?';
            confirmBtn.textContent = options.confirmText || 'Confirmer';
            confirmBtn.className = 'btn btn-' + (options.type === 'success' ? 'success' : options.type === 'danger' ? 'danger' : 'primary');

            confirmCallback = options.onConfirm || null;
            confirmForm = options.form || null;
            dialog.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        window.closeConfirmDialog = function () {
            var dialog = document.getElementById('confirmDialog');
            if (dialog) dialog.classList.remove('active');
            document.body.style.overflow = '';
            confirmCallback = null;
            confirmForm = null;
        };

        window.confirmLogout = function (event, form) {
            event.preventDefault();
            window.showConfirmDialog({
                type: 'danger',
                title: 'Confirmation de déconnexion',
                message: 'Êtes-vous sûr de vouloir vous déconnecter ? Vous devrez vous reconnecter pour accéder à votre compte.',
                confirmText: 'Se déconnecter',
                onConfirm: function () {
                    if (form) form.submit();
                    window.closeConfirmDialog();
                },
                form: form
            });
        };

        function applyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('theme', theme);
            var icon = document.getElementById('theme-icon');
            var toggleBtn = document.getElementById('theme-toggle');
            if (icon) {
                icon.setAttribute('d', theme === 'dark'
                    ? 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'
                    : 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z');
            }
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
                toggleBtn.setAttribute('aria-label', theme === 'dark' ? 'Activer le thème clair' : 'Activer le thème sombre');
            }
        }

        function bindCashierUi() {
            var theme = localStorage.getItem('theme');
            if (!theme) {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            applyTheme(theme);

            document.getElementById('theme-toggle')?.addEventListener('click', function (e) {
                e.preventDefault();
                applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
            });

            document.getElementById('checkoutLink')?.addEventListener('click', function (e) {
                if (window.cart.length === 0) {
                    e.preventDefault();
                    window.showToast('Le panier est vide', 'error');
                    return;
                }
                if (typeof window.closeCart === 'function') {
                    window.closeCart();
                }
            });

            document.querySelector('#cartEmptyFooter a')?.addEventListener('click', function () {
                if (typeof window.closeCart === 'function') {
                    window.closeCart();
                }
            });

            document.getElementById('clearCartBtn')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.openClearCartModal();
            });
            document.getElementById('clearCartCancelBtn')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.closeClearCartModal();
            });
            document.getElementById('clearCartConfirmBtn')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.confirmClearCart();
            });
            document.getElementById('clearCartModal')?.addEventListener('click', function (e) {
                if (e.target === this) window.closeClearCartModal();
            });

            document.getElementById('confirmCancelBtn')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.closeConfirmDialog();
            });
            document.getElementById('confirmDialogBtn')?.addEventListener('click', function (e) {
                e.preventDefault();
                if (typeof confirmCallback === 'function') confirmCallback();
                else if (confirmForm) confirmForm.submit();
                window.closeConfirmDialog();
            });
            document.getElementById('confirmDialog')?.addEventListener('click', function (e) {
                if (e.target === this) window.closeConfirmDialog();
            });

            document.querySelectorAll('.logout-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    window.confirmLogout(e, form);
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                var cartRoot = document.getElementById('salangCartRoot');
                if (cartRoot && cartRoot.classList.contains('salang-cart-root--open') && typeof window.closeCart === 'function') {
                    window.closeCart();
                    return;
                }
                window.closeConfirmDialog();
                window.closeClearCartModal();
            });

            var pageTitle = document.getElementById('pageTitle');
            if (pageTitle) {
                function updatePageTitle() {
                    var activeLink = document.querySelector('.sidebar-link.active');
                    if (activeLink) {
                        var dataTitle = activeLink.getAttribute('data-title');
                        if (dataTitle) {
                            pageTitle.textContent = dataTitle;
                            return;
                        }
                        var label = activeLink.querySelector('.label');
                        if (label && label.textContent.trim()) {
                            pageTitle.textContent = label.textContent.trim();
                            return;
                        }
                    }
                }
                setTimeout(updatePageTitle, 50);
            }

            window.loadCart();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindCashierUi);
        } else {
            bindCashierUi();
        }
    })();
    </script>

    @php
        $workflowPollConfig = [
            'url' => route('cashier.workflow-pending-counts'),
            'links' => [
                'consultations' => route('cashier.consultations.index'),
                'reports' => route('cashier.reports.index'),
            ],
            'consultations' => ['badge' => 'cashierConsultationBadge', 'dot' => 'cashierConsultationDot'],
            'reports' => ['badge' => 'cashierReportBadge', 'dot' => 'cashierReportDot'],
            'header' => ['badge' => 'cashierWorkflowHeaderBadge', 'dot' => 'cashierWorkflowHeaderDot'],
            'mobileNav' => ['badge' => 'cashierMobileNavBadge'],
            'dropdown' => [
                'consultationCount' => 'cashierWorkflowDropdownConsultationCount',
                'reportCount' => 'cashierWorkflowDropdownReportCount',
                'totalMirror' => 'cashierWorkflowHeaderBadgeMirror',
                'empty' => 'cashierWorkflowDropdownEmpty',
                'items' => 'cashierWorkflowDropdownItems',
                'urgentWrap' => 'cashierWorkflowUrgentWrap',
                'urgentLink' => 'cashierWorkflowUrgentLink',
                'consultationRow' => 'cashierWorkflowConsultationRow',
                'reportRow' => 'cashierWorkflowReportRow',
            ],
        ];
    @endphp
    @include('partials.shell.workflow-notification-poll')

    @stack('scripts')
</body>
