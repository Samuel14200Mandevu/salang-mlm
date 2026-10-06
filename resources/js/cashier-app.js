/**
 * Caisse Salang — panier, confirmations (globals immédiats pour onclick / POS)
 */
import { initThemeToggle } from './cashier-theme';

let confirmCallback = null;
let confirmForm = null;
let cashierGlobalsRegistered = false;

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = String(str ?? '');
    return div.innerHTML;
}

export function registerCashierGlobals() {
    if (cashierGlobalsRegistered) return;
    cashierGlobalsRegistered = true;

    window.cart = window.cart || [];

    window.showToast = function (message, type = 'success', duration = 3500) {
        const safeMessage = escapeHtml(message);
        const container = document.getElementById('toastContainer');
        if (!container) return;

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
    };

    window.buildCheckoutUrl = function () {
        if (window.cart.length === 0) return null;
        const items = window.cart.map((item) => item.type + ':' + item.id);
        const base = document.body.dataset.checkoutUrl;
        if (!base) return null;
        return base + '?items=' + items.join(',');
    };

    window.loadCart = function () {
        try {
            const saved = localStorage.getItem('pos_cart');
            if (saved) {
                window.cart = JSON.parse(saved);
                window.renderCart();
            }
        } catch {
            window.cart = [];
        }
    };

    window.saveCart = function () {
        localStorage.setItem('pos_cart', JSON.stringify(window.cart));
        window.updateCartCount();
    };

    window.addToCart = function (itemId, type) {
        const existing = window.cart.find((item) => item.id === itemId && item.type === type);
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
        const pvValue = pvBadge ? parseFloat(String(pvBadge.textContent).replace(/ PV| BV/g, '')) || 0 : 0;

        window.cart.push({
            id: itemId,
            type,
            name,
            price,
            image,
            source,
            sourceLabel,
            pv_value: pvValue,
            bv_value: 0,
            quantity: 1,
        });

        window.saveCart();
        window.renderCart();
        window.showToast('Article ajouté au panier', 'success');
    };

    /** Compatibilité POS (pos.blade.php) */
    window.addToCartGlobal = function (productId, type, name, price, pv) {
        const existing = window.cart.find((item) => item.id === productId && item.type === type);
        if (existing) {
            existing.quantity += 1;
        } else {
            window.cart.push({
                id: productId,
                type,
                name,
                price,
                pv_value: pv || 0,
                bv_value: 0,
                quantity: 1,
                source: type === 'product' ? 'pos' : 'mlm',
                sourceLabel: type === 'product' ? 'POS' : 'MLM',
                image: null,
            });
        }
        window.saveCart();
        window.renderCart();
    };

    window.removeFromCart = function (itemId, type) {
        window.cart = window.cart.filter((item) => !(item.id === itemId && item.type === type));
        window.saveCart();
        window.renderCart();
    };

    window.updateQuantity = function (itemId, type, delta) {
        const item = window.cart.find((i) => i.id === itemId && i.type === type);
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
        const cartItemsContainer = document.getElementById('cartItems');
        const cartEmpty = document.getElementById('cartEmpty');
        const cartFooter = document.getElementById('cartFooter');
        const cartTotal = document.getElementById('cartTotal');
        const checkoutLink = document.getElementById('checkoutLink');

        if (!cartItemsContainer) return;

        cartItemsContainer.innerHTML = '';

        if (window.cart.length === 0) {
            cartEmpty?.classList.remove('hidden');
            cartItemsContainer.classList.add('hidden');
            cartFooter?.classList.add('hidden');
            window.updateCartCount();
            return;
        }

        cartEmpty?.classList.add('hidden');
        cartItemsContainer.classList.remove('hidden');
        cartFooter?.classList.remove('hidden');

        let total = 0;
        window.cart.forEach((item) => {
            total += item.price * item.quantity;
            const div = document.createElement('div');
            div.className =
                'flex gap-3 items-center py-3 border-b border-[var(--border-color)] last:border-b-0';
            div.innerHTML = `
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-[var(--text-primary)] truncate">${escapeHtml(item.name)}</h4>
                    <div class="text-xs text-[var(--text-secondary)]">$${item.price.toFixed(2)} x ${item.quantity}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-qty-minus="${item.id}" data-qty-type="${item.type}" class="btn btn-outline btn-sm btn-icon">-</button>
                    <span class="w-5 text-center font-semibold text-sm">${item.quantity}</span>
                    <button type="button" data-qty-plus="${item.id}" data-qty-type="${item.type}" class="btn btn-outline btn-sm btn-icon">+</button>
                </div>
                <button type="button" data-remove-id="${item.id}" data-remove-type="${item.type}" class="text-[var(--danger)] text-lg leading-none p-1">×</button>
            `;
            cartItemsContainer.appendChild(div);
        });

        cartItemsContainer.querySelectorAll('[data-qty-minus]').forEach((btn) => {
            btn.addEventListener('click', () => {
                window.updateQuantity(Number(btn.dataset.qtyMinus), btn.dataset.qtyType, -1);
            });
        });
        cartItemsContainer.querySelectorAll('[data-qty-plus]').forEach((btn) => {
            btn.addEventListener('click', () => {
                window.updateQuantity(Number(btn.dataset.qtyPlus), btn.dataset.qtyType, 1);
            });
        });
        cartItemsContainer.querySelectorAll('[data-remove-id]').forEach((btn) => {
            btn.addEventListener('click', () => {
                window.removeFromCart(Number(btn.dataset.removeId), btn.dataset.removeType);
            });
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

    window.updateCartCount = function () {
        const count = window.cart.reduce((sum, item) => sum + item.quantity, 0);
        const headerCartCount = document.getElementById('headerCartCount');
        const mobileCartCount = document.getElementById('mobileCartCount');

        if (count > 0) {
            headerCartCount?.classList.remove('hidden');
            mobileCartCount?.classList.remove('hidden');
            if (headerCartCount) headerCartCount.textContent = count;
            if (mobileCartCount) mobileCartCount.textContent = count;
        } else {
            headerCartCount?.classList.add('hidden');
            mobileCartCount?.classList.add('hidden');
        }
    };

    window.toggleCart = function () {
        document.getElementById('cartSidebar')?.classList.toggle('translate-x-full');
        document.getElementById('cartOverlay')?.classList.toggle('hidden');
    };

    window.openClearCartModal = function () {
        if (window.cart.length === 0) {
            window.showToast('Le panier est déjà vide', 'info');
            return;
        }
        const modal = document.getElementById('clearCartModal');
        if (!modal) return;
        modal.classList.remove('opacity-0', 'invisible');
        document.body.style.overflow = 'hidden';
    };

    window.closeClearCartModal = function () {
        const modal = document.getElementById('clearCartModal');
        if (!modal) return;
        modal.classList.add('opacity-0', 'invisible');
        document.body.style.overflow = '';
    };

    window.confirmClearCart = function () {
        window.cart = [];
        window.saveCart();
        window.renderCart();
        window.closeClearCartModal();
        window.showToast('Panier vidé avec succès', 'info');
    };

    window.showConfirmDialog = function (options) {
        const dialog = document.getElementById('confirmDialog');
        if (!dialog) return;
        const icon = dialog.querySelector('.icon');
        const title = document.getElementById('confirmTitle');
        const message = document.getElementById('confirmMessage');
        const confirmBtn = document.getElementById('confirmDialogBtn');
        if (!icon || !title || !message || !confirmBtn) return;

        icon.className = 'icon';
        icon.classList.add(options.type || 'danger');
        title.textContent = options.title || 'Confirmation';
        message.textContent = options.message || 'Êtes-vous sûr de vouloir continuer ?';
        confirmBtn.textContent = options.confirmText || 'Confirmer';
        confirmBtn.className = 'btn';
        confirmBtn.classList.add(
            options.type === 'success' ? 'btn-success' : options.type === 'danger' ? 'btn-danger' : 'btn-primary'
        );

        confirmCallback = options.onConfirm || null;
        confirmForm = options.form || null;
        dialog.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closeConfirmDialog = function () {
        document.getElementById('confirmDialog')?.classList.remove('active');
        document.body.style.overflow = '';
        confirmCallback = null;
        confirmForm = null;
    };

    window.confirmLogout = function (event, form) {
        event.preventDefault();
        window.showConfirmDialog({
            type: 'danger',
            title: 'Confirmation de déconnexion',
            message:
                'Êtes-vous sûr de vouloir vous déconnecter ? Vous devrez vous reconnecter pour accéder à votre compte.',
            confirmText: 'Se déconnecter',
            onConfirm: function () {
                form?.submit();
                window.closeConfirmDialog();
            },
            form,
        });
    };
}

function initCashierPageTitle() {
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
            const text = label?.textContent?.trim();
            if (text) {
                pageTitle.textContent = text;
                return;
            }
        }
        const fullTitle = document.querySelector('title')?.textContent || '';
        const cleanTitle = fullTitle.replace(/\s*[-|]\s*Salang\s*MLM\s*$/, '').trim();
        if (cleanTitle && cleanTitle !== 'Reception' && cleanTitle !== 'Dashboard Caissier') {
            pageTitle.textContent = cleanTitle;
        }
    }

    setTimeout(updatePageTitle, 50);
    document.querySelectorAll('.sidebar-link').forEach((link) => {
        new MutationObserver(updatePageTitle).observe(link, { attributes: true, attributeFilter: ['class'] });
    });
}

function bindCashierEvents() {
    document.getElementById('cartToggleBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.toggleCart();
    });
    document.getElementById('mobileCartToggleBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.toggleCart();
    });
    document.getElementById('cartCloseBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.toggleCart();
    });
    document.getElementById('cartOverlay')?.addEventListener('click', () => window.toggleCart());

    document.getElementById('checkoutLink')?.addEventListener('click', (e) => {
        if (window.cart.length === 0) {
            e.preventDefault();
            window.showToast('Le panier est vide', 'error');
        }
    });

    document.getElementById('clearCartBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.openClearCartModal();
    });
    document.getElementById('clearCartCancelBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.closeClearCartModal();
    });
    document.getElementById('clearCartConfirmBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.confirmClearCart();
    });
    document.getElementById('clearCartModal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) window.closeClearCartModal();
    });

    document.getElementById('confirmCancelBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.closeConfirmDialog();
    });
    document.getElementById('confirmDialogBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        if (typeof confirmCallback === 'function') confirmCallback();
        else if (confirmForm) confirmForm.submit();
        window.closeConfirmDialog();
    });
    document.getElementById('confirmDialog')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) window.closeConfirmDialog();
    });

    document.querySelectorAll('.logout-form').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            window.confirmLogout(e, form);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        window.closeConfirmDialog();
        window.closeClearCartModal();
        const sidebar = document.getElementById('cartSidebar');
        if (sidebar && !sidebar.classList.contains('translate-x-full')) window.toggleCart();
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('#cartToggleBtn') || e.target.closest('#mobileCartToggleBtn')) {
            e.preventDefault();
            window.toggleCart();
        }
    });
}

export function initCashierShell() {
    if (!document.body?.classList.contains('cashier-app')) return;

    registerCashierGlobals();
    initThemeToggle();

    const boot = () => {
        initCashierPageTitle();
        bindCashierEvents();
        window.loadCart();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}
