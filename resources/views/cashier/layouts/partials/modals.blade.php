    <!-- ===== MODAL VIDER LE PANIER ===== -->
    <div id="clearCartModal" class="fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center opacity-0 invisible transition-all duration-200">
        <div class="bg-[var(--bg-card)] rounded-lg p-6 max-w-[420px] w-[90%] border border-[var(--border-color)] transform scale-90 transition-all duration-200">
            <div class="w-16 h-16 rounded-full bg-amber-500/10 flex items-center justify-center mx-auto mb-4 text-amber-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
                </svg>
            </div>
            <h3 class="text-center text-xl font-bold text-[var(--text-primary)] mb-2">Vider le panier ?</h3>
            <p class="text-center text-[var(--text-secondary)] text-sm mb-6">
                Êtes-vous sûr de vouloir <strong>vider votre panier</strong> ?
                <br>
                Cette action est <strong>irréversible</strong> et tous les articles seront supprimés.
            </p>
            <div class="flex gap-3 justify-center">
                <button id="clearCartCancelBtn" class="btn btn-outline">Annuler</button>
                <button id="clearCartConfirmBtn" class="btn btn-danger flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Vider
                </button>
            </div>
        </div>
    </div>

    <!-- ===== CONFIRMATION DIALOG ===== -->
    <div id="confirmDialog" class="confirm-overlay">
        <div class="confirm-dialog">
            <div class="icon danger">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                </svg>
            </div>
            <h3 id="confirmTitle">Confirmation</h3>
            <p id="confirmMessage">Êtes-vous sûr de vouloir continuer ?</p>
            <div class="actions">
                <button type="button" id="confirmCancelBtn" class="btn btn-outline">Annuler</button>
                <button type="button" id="confirmDialogBtn" class="btn btn-primary">Confirmer</button>
            </div>
        </div>
    </div>
