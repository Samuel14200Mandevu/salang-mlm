    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- ============================================ -->
    <!-- PWA SCRIPTS - À GARDER À LA FIN              -->
    <!-- ============================================ -->
    @if(class_exists('PwaKit'))
        {!! PwaKit::scripts() !!}
    @endif
    
    <!-- Consentement aux cookies -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!localStorage.getItem('cookie_consent')) {
            const banner = document.createElement('div');
            banner.id = 'cookie-consent-banner';
            banner.style.cssText = `
                position: fixed;
                bottom: 60px;
                left: 0;
                right: 0;
                background: var(--bg-card);
                border-top: 1px solid var(--border-color);
                padding: 0.75rem 1rem;
                z-index: 9999;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                box-shadow: 0 -4px 24px rgba(0,0,0,0.1);
            `;
            
            banner.innerHTML = `
                <div style="flex: 1; min-width: 150px; text-align: center; font-size: 0.75rem; color: var(--text-secondary);">
                    Nous utilisons des cookies.
                    <a href="{{ route('cookie-policy') }}" style="color: var(--primary-500); text-decoration: underline; white-space: nowrap;">
                        En savoir plus
                    </a>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
                    <button onclick="acceptCookies()" style="
                        padding: 0.375rem 1.25rem;
                        border-radius: var(--radius-md);
                        background: var(--gradient-primary);
                        color: white;
                        border: none;
                        font-weight: 600;
                        font-size: 0.75rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        white-space: nowrap;
                    ">
                        Accepter
                    </button>
                    <button onclick="rejectCookies()" style="
                        padding: 0.375rem 1.25rem;
                        border-radius: var(--radius-md);
                        background: transparent;
                        color: var(--text-secondary);
                        border: 1px solid var(--border-color);
                        font-weight: 600;
                        font-size: 0.75rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        white-space: nowrap;
                    ">
                        Refuser
                    </button>
                </div>
            `;
            document.body.appendChild(banner);
        }
    });

    function acceptCookies() {
        localStorage.setItem('cookie_consent', 'accepted');
        document.getElementById('cookie-consent-banner').remove();
    }

    function rejectCookies() {
        localStorage.setItem('cookie_consent', 'rejected');
        document.getElementById('cookie-consent-banner').remove();
    }
    </script>
    
    @stack('scripts')

    <!-- ===== CONFIRMATION LOGOUT SCRIPT ===== -->
    <script>
    // Variables globales pour le dialogue
    let confirmCallback = null;
    let confirmForm = null;

    /**
     * Afficher le dialogue de confirmation
     */
    function showConfirmDialog(options) {
        const dialog = document.getElementById('confirmDialog');
        const icon = dialog.querySelector('.icon');
        const title = dialog.querySelector('h3');
        const message = dialog.querySelector('p');
        const confirmBtn = document.getElementById('confirmLogoutBtn');
        
        // Configurer le dialogue
        icon.className = 'icon';
        icon.classList.add(options.type || 'danger');
        
        if (options.type === 'success') {
            icon.innerHTML = `
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            `;
        } else if (options.type === 'warning') {
            icon.innerHTML = `
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                </svg>
            `;
        } else {
            icon.innerHTML = `
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            `;
        }
        
        title.textContent = options.title || 'Confirmation';
        message.textContent = options.message || 'Êtes-vous sûr de vouloir continuer ?';
        confirmBtn.textContent = options.confirmText || 'Confirmer';
        confirmBtn.className = 'btn btn-confirm';
        
        if (options.type === 'success') {
            confirmBtn.classList.add('success');
        }
        
        // Sauvegarder les callbacks
        confirmCallback = options.onConfirm || null;
        confirmForm = options.form || null;
        
        // Afficher le dialogue
        dialog.classList.add('active');
    }

    /**
     * Fermer le dialogue de confirmation
     */
    function closeConfirmDialog() {
        document.getElementById('confirmDialog').classList.remove('active');
        confirmCallback = null;
        confirmForm = null;
    }

    /**
     * Confirmer la déconnexion
     */
    function confirmLogout(event) {
        event.preventDefault();
        
        const form = event.target.closest('form');
        
        showConfirmDialog({
            type: 'danger',
            title: 'Confirmation de déconnexion',
            message: 'Êtes-vous sûr de vouloir vous déconnecter ? Vous devrez vous reconnecter pour accéder à votre compte.',
            confirmText: 'Se déconnecter',
            onConfirm: function() {
                if (form) {
                    form.submit();
                }
                closeConfirmDialog();
            },
            form: form
        });
    }

    // Gestionnaire pour le bouton de confirmation
    document.addEventListener('DOMContentLoaded', function() {
        const confirmBtn = document.getElementById('confirmLogoutBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (typeof confirmCallback === 'function') {
                    confirmCallback();
                } else if (confirmForm) {
                    confirmForm.submit();
                }
                closeConfirmDialog();
            });
        }

        // Fermer le dialogue avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeConfirmDialog();
            }
        });

        // Fermer le dialogue en cliquant sur l'overlay
        document.getElementById('confirmDialog').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmDialog();
            }
        });
    });
    </script>

    <!-- ===== CHANGEMENT DE THÈME ===== -->
    <script>
    (function() {
        'use strict';
        
        // Appliquer le thème sauvegardé
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
        
        function initTheme() {
            var toggle = document.getElementById('theme-toggle');
            var iconPath = document.getElementById('theme-icon-path');
            
            if (!toggle || !iconPath) return;
            
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
                if (!iconPath) return;
                if (document.documentElement.classList.contains('dark')) {
                    // Mode sombre : afficher un soleil (pour revenir en mode clair)
                    iconPath.setAttribute('d', 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z');
                } else {
                    // Mode clair : afficher une lune (pour passer en mode sombre)
                    iconPath.setAttribute('d', 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z');
                }
            }
            
            // Remplacer le bouton par une copie pour éviter les doublons d'écouteurs
            var newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                if (document.documentElement.classList.contains('dark')) {
                    setTheme('light');
                } else {
                    setTheme('dark');
                }
            });
            
            // Appliquer le thème initial
            setTheme(localStorage.getItem('theme') === 'dark' ? 'dark' : 'light');
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTheme);
        } else {
            initTheme();
        }
    })();
    </script>
</body>
