// Configuration simple sans dépendances externes
// Ce fichier est requis par Laravel pour le chargement des assets

// Configuration pour les requêtes AJAX
if (typeof window !== 'undefined') {
    window.axios = {
        defaults: {
            headers: {
                common: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            }
        }
    };
}

// Export pour compatibilité
export default {};
