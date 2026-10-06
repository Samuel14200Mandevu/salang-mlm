<div id="salangCartRoot" class="salang-cart-root" aria-hidden="true">
    <div id="cartDropdownBackdrop" class="cart-dropdown-backdrop" aria-hidden="true"></div>
    <div id="cartDropdownPanel"
         class="cart-dropdown-panel"
         role="dialog"
         aria-modal="true"
         aria-labelledby="cartDropdownTitle">
        <div class="px-4 py-3 border-b border-[var(--border-color)] flex items-center justify-between gap-2 flex-shrink-0">
            <p id="cartDropdownTitle" class="text-sm font-semibold text-[var(--text-primary)]">Panier</p>
            <div class="flex items-center gap-2">
                <span id="cartHeaderCountMirror" class="workflow-notify-total-pill hidden">0</span>
                <button type="button"
                        id="cartCloseBtn"
                        class="text-[var(--text-tertiary)] hover:text-[var(--text-primary)] p-1 rounded-md hover:bg-[var(--bg-secondary)] transition-colors"
                        aria-label="Fermer le panier">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="cartBody" class="flex-1 overflow-y-auto p-4 min-h-0">
            <div id="cartEmpty" class="text-center py-6 text-[var(--text-secondary)]">
                <svg class="w-10 h-10 mx-auto mb-2 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm font-medium text-[var(--text-primary)]">Votre panier est vide</p>
                <p class="text-xs mt-1">Ajoutez des produits ou packages depuis le POS</p>
            </div>
            <div id="cartItems" class="hidden space-y-0"></div>
        </div>

        <div id="cartFooter" class="p-3 border-t border-[var(--border-color)] bg-[var(--bg-secondary)] flex-shrink-0 hidden">
            <div class="flex justify-between text-base font-bold text-[var(--text-primary)] mb-3">
                <span>Total</span>
                <span id="cartTotal" class="text-[var(--primary)]">$0.00</span>
            </div>
            <a href="#"
               id="checkoutLink"
               class="btn btn-primary btn-block w-full flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Passer la commande
            </a>
            <button type="button" id="clearCartBtn" class="btn btn-outline btn-block w-full mt-2">
                Vider le panier
            </button>
        </div>

        <div id="cartEmptyFooter" class="p-3 border-t border-[var(--border-color)] flex-shrink-0">
            <a href="{{ route('cashier.pos') }}"
               class="workflow-notify-urgent flex items-center justify-center gap-2 w-full px-3 py-2.5 rounded-md text-sm font-semibold text-white bg-[var(--primary)] hover:opacity-95 transition-opacity">
                Aller au point de vente
            </a>
        </div>
    </div>
</div>
