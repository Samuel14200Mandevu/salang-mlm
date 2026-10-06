<button type="button"
        id="cartToggleBtn"
        class="relative p-1.5 sm:p-2 rounded-md hover:bg-[var(--bg-secondary)] transition-colors"
        aria-expanded="false"
        aria-haspopup="dialog"
        aria-controls="cartDropdownPanel"
        title="Panier"
        aria-label="Panier">
    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <span id="headerCartCount"
          class="absolute -top-0.5 -right-0.5 bg-[var(--danger)] text-white text-[8px] sm:text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 hidden">0</span>
</button>
