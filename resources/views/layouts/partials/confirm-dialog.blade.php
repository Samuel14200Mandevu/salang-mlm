    <!-- ===== DIALOGUE DE CONFIRMATION ===== -->
    <div id="confirmDialog" class="confirm-overlay">
        <div class="confirm-dialog">
            <div class="icon danger">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                </svg>
            </div>
            <h3>Confirmation de déconnexion</h3>
            <p>Êtes-vous sûr de vouloir vous déconnecter ? Vous devrez vous reconnecter pour accéder à votre compte.</p>
            <div class="actions">
                <button type="button" class="btn btn-cancel" onclick="closeConfirmDialog()">Annuler</button>
                <button type="button" class="btn btn-confirm" id="confirmLogoutBtn">Se déconnecter</button>
            </div>
        </div>
    </div>
