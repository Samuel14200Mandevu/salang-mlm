    <!-- ===== CART SIDEBAR ===== -->
    <div id="cartOverlay" class="fixed inset-0 bg-black/40 z-[998] hidden"></div>

    <div id="cartSidebar" class="fixed right-0 top-0 h-full w-[380px] bg-[var(--bg-card)] border-l border-[var(--border-color)] transform translate-x-full transition-transform duration-200 ease-in-out z-[999] flex flex-col">
        <div class="p-4 border-b border-[var(--border-color)] flex justify-between items-center">
            <h3 class="font-bold text-[var(--text-primary)]">Panier</h3>
            <button id="cartCloseBtn" class="text-[var(--text-tertiary)] hover:text-[var(--text-primary)] transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="cartBody" class="flex-1 overflow-y-auto p-4">
            <div id="cartEmpty" class="text-center py-8 text-[var(--text-tertiary)]">
                <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p>Votre panier est vide</p>
                <p class="text-xs mt-1">Ajoutez des produits ou packages MLM</p>
            </div>
            <div id="cartItems" class="hidden"></div>
        </div>
        <div id="cartFooter" class="p-4 border-t border-[var(--border-color)] bg-[var(--bg-secondary)] hidden">
            <div class="flex justify-between text-lg font-bold text-[var(--text-primary)]">
                <span>Total</span>
                <span id="cartTotal" class="text-[var(--primary)]">$0.00</span>
            </div>
            <a href="#" id="checkoutLink" class="btn btn-primary btn-block mt-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Passer la commande
            </a>
            <button id="clearCartBtn" class="btn btn-outline btn-block mt-2">
                Vider le panier
            </button>
        </div>
    </div>
