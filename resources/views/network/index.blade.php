@extends('layouts.app')

@push('styles')
{{-- Inclure le CSS de la librairie OrgChart --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.1/css/jquery.orgchart.min.css">
<style>
    /* ============================================================
       VARIABLES & THEME BASE (Basé sur ton style)
    ============================================================ */
    :root {
        --node-bg: var(--bg-card);
        --node-border: var(--border-color);
        --node-text: var(--text-primary);
        --radius-node: 12px;
    }

    /* Conteneur principal de l'arbre */
    #tree-container {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        position: relative;
        height: 600px; /* Hauteur fixe pour le scroll interne */
        background-image: radial-gradient(var(--border-light) 1px, transparent 1px);
        background-size: 20px 20px;
    }

    /* Style personnalisé pour les noeuds OrgChart */
    .orgchart .node {
        width: 150px; /* Largeur fixe pour l'uniformité */
        border: 2px solid transparent;
        background-color: var(--node-bg);
        border-radius: var(--radius-node);
        padding: 10px;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
    }

    .orgchart .node:hover, .orgchart .node.focused {
        background-color: var(--bg-hover);
        border-color: var(--primary-500);
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    /* Masquer les éléments par défaut d'OrgChart qu'on ne veut pas */
    .orgchart .node .title, .orgchart .node .content {
        display: none;
    }

    /* ============================================================
       STYLE PERSONNALISÉ INTÉRIEUR DU NOEUD
    ============================================================ */
    .node-custom-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .node-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        font-size: 1.2rem;
        border: 3px solid var(--bg-card);
        box-shadow: var(--shadow-sm);
        margin-bottom: 8px;
        position: relative;
    }

    .status-dot {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid var(--bg-card);
    }
    .status-dot.online { background-color: #22c55e; box-shadow: 0 0 8px #22c55e; }
    .status-dot.offline { background-color: #6b7280; }

    .node-name {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--node-text);
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .node-rank {
        font-size: 0.65rem;
        padding: 2px 8px;
        border-radius: 9999px;
        background-color: var(--bg-secondary);
        color: var(--text-secondary);
        margin-top: 4px;
    }

    .node-info {
        font-size: 0.6rem;
        color: var(--text-tertiary);
        margin-top: 4px;
    }

    /* Couleurs des avatars par grade (copié de ton code) */
    .avatar-rank-1 { background: linear-gradient(135deg, #6b7280, #4b5563); }
    .avatar-rank-2 { background: linear-gradient(135deg, #60a5fa, #3b82f6); }
    .avatar-rank-3 { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }
    .avatar-rank-4 { background: linear-gradient(135deg, #34d399, #22c55e); }
    /* ... ajoute les autres grades ici ... */

    /* Style des lignes de connexion */
    .orgchart .line {
        background-color: var(--border-color);
    }
</style>
@endpush

@section('content')
<div class="space-y-4">

    {{-- HEADER (Garder ton header actuel, il est bien) --}}
    <div class="card p-6 animate-fadeInUp">
        {{-- ... Ton code de header actuel (titre, stats) ... --}}
    </div>

    {{-- TOOLBAR (Simplifié, OrgChart gère le zoom/pan) --}}
    <div class="card p-3 flex items-center justify-between gap-3 animate-fadeInUp delay-1">
        <h3 class="font-semibold text-sm">Vue Arborescente</h3>
        <div class="flex gap-2">
            <button onclick="initTree()" class="btn btn-outline btn-sm">Réinitialiser l'arbre</button>
        </div>
    </div>

    {{-- CONTENEUR DE L'ARBRE --}}
    <div id="tree-container" class="animate-fadeInUp delay-2">
        {{-- OrgChart chargera l'arbre ici --}}
        <div id="chart-container"></div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Inclure jQuery et la librairie OrgChart --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.1/js/jquery.orgchart.min.js"></script>

<script>
    const BASE_URL = '{{ url("/") }}';
    let oc; // Variable pour stocker l'instance OrgChart

    // Template personnalisé pour le rendu d'un noeud
    const nodeTemplate = function(data) {
        return `
            <div class="node-custom-content">
                <div class="node-avatar ${data.avatar_color}">
                    ${data.avatar_text}
                    <span class="status-dot ${data.is_active ? 'online' : 'offline'}"></span>
                </div>
                <div class="node-name">${data.name}</div>
                <div class="node-rank">${data.rank_name}</div>
                <div class="node-info">PV: ${data.pv_balance} | ID: #${data.id}</div>
            </div>
        `;
    };

    // Fonction d'initialisation de l'arbre
    function initTree() {
        // Détruire l'instance existante si nécessaire
        if (oc) {
            oc.destroy();
        }

        // Configuration d'OrgChart
        $('#chart-container').orgchart({
            'data' : BASE_URL + '/network/tree-data', // URL qui renvoie le JSON initial (racine + niveau 1)
            'nodeTemplate': nodeTemplate, // Utiliser notre template personnalisé
            'nodeId': 'id', // Clé unique pour le noeud
            'toggleSiblingsResp': true, // Masquer les frères/soeurs lors de l'expansion
            'depth': 2, // Profondeur initiale chargée
            'pan': true, // Activer le déplacement (Drag)
            'zoom': true, // Activer le zoom
            'initCompleted': function() {
                // Centrer l'arbre après le chargement initial
                const $chart = $('#chart-container');
                $chart.scrollLeft(($chart[0].scrollWidth - $chart.width()) / 2);
            }
        });

        oc = $('#chart-container').data('orgchart');
    }

    // Charger l'arbre au chargement de la page
    $(function() {
        initTree();

        // Gestion du clic sur un noeud pour rediriger vers le profil
        $('#chart-container').on('click', '.node', function() {
            const userId = $(this).attr('id');
            window.location.href = BASE_URL + '/network/show/' + userId;
        });
    });
</script>
@endpush