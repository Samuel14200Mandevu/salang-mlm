/**
 * Caisse — Alpine + panier (toggle fiable, indépendant du script inline)
 */
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

function setCartOpen(open) {
    const root = document.getElementById('salangCartRoot');
    if (!root) {
        return;
    }

    const isOpen = Boolean(open);
    root.classList.toggle('salang-cart-root--open', isOpen);
    root.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

    const btn = document.getElementById('cartToggleBtn');
    if (btn) {
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    if (isOpen) {
        if (typeof window.renderCart === 'function') {
            window.renderCart();
        }
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

window.setCartOpen = setCartOpen;

window.toggleCart = function toggleCart() {
    const root = document.getElementById('salangCartRoot');
    if (!root) {
        return;
    }
    setCartOpen(!root.classList.contains('salang-cart-root--open'));
};

window.closeCart = function closeCart() {
    setCartOpen(false);
};

function bindCartUi() {
    if (!document.body.classList.contains('cashier-app')) {
        return;
    }

    document.addEventListener('click', (e) => {
        if (e.target.closest('#cartToggleBtn') || e.target.closest('#mobileCartToggleBtn')) {
            e.preventDefault();
            window.toggleCart();
            return;
        }

        if (e.target.closest('#cartCloseBtn')) {
            e.preventDefault();
            window.closeCart();
            return;
        }

        if (e.target.closest('#cartDropdownBackdrop')) {
            window.closeCart();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindCartUi);
} else {
    bindCartUi();
}
