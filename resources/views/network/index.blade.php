@extends('layouts.app')

@push('styles')
<style>
    /* ===== CONTENEUR GLOBAL ===== */
    .tree-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem 1rem;
        min-height: 600px;
        background: radial-gradient(ellipse at center, var(--bg-card) 0%, var(--bg-secondary) 100%);
        border-radius: var(--radius-lg);
        position: relative;
        overflow-x: auto;
        width: 100%;
    }

    /* ===== STRUCTURE DE L'ARBRE GÉNÉALOGIQUE ===== */
    .tree-branch {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0 15px;
        position: relative;
    }

    /* Conteneur de l'avatar */
    .tree-node-container {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .tree-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.5rem;
        transition: all 0.2s ease;
        cursor: pointer;
        animation: fadeInUp 0.5s ease forwards;
    }

    .tree-node:hover {
        transform: translateY(-3px);
    }

    .tree-node .avatar {
        border: 3px solid var(--bg-card);
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .tree-node:hover .avatar {
        border-color: var(--primary-500);
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.2);
    }

    /* ===== AVATARS ===== */
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        color: white;
        position: relative;
        text-transform: uppercase;
    }
    .avatar-md { width: 2.8rem; height: 2.8rem; font-size: 0.8rem; }
    .avatar-lg { width: 3.5rem; height: 3.5rem; font-size: 1rem; }
    .avatar-xl { width: 4.2rem; height: 4.2rem; font-size: 1.2rem; }

    .avatar .status-dot {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid var(--bg-card);
    }
    .avatar .status-dot.online { background: #22c55e; }
    .avatar .status-dot.offline { background: #6b7280; }

    .level-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        font-size: 0.5rem;
        padding: 0.05rem 0.35rem;
        border-radius: 9999px;
        font-weight: 700;
        color: white;
        border: 2px solid var(--bg-card);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .level-badge.level-1 { background: #22c55e; }
    .level-badge.level-2 { background: #3b82f6; }
    .level-badge.level-3 { background: #8b5cf6; }
    .level-badge.level-4 { background: #f59e0b; }
    .level-badge.level-5 { background: #ec4899; }

    .name {
        font-weight: 600;
        color: var(--text-primary);
        margin-top: 0.3rem;
        text-align: center;
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .badge {
        display: inline-block;
        padding: 0.1rem 0.4rem;
        border-radius: 9999px;
        font-weight: 600;
        margin-top: 0.05rem;
        opacity: 0.9;
    }
    .badge-success { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .badge-danger { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

    /* ===== LES CONNECTEURS (LIGNES ENTRE PARENTS ET ENFANTS) ===== */
    .tree-children-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        position: relative;
        padding-top: 20px;
    }

    /* La ligne VERTICALE qui part du parent vers le bas */
    .tree-children-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 2px;
        height: 20px;
        background: #94a3b8; /* Gris doux pour la ligne */
        transform: translateX(-50%);
        z-index: 1;
    }

    /* La ligne HORIZONTALE qui relie les enfants entre eux (apparaît seulement si > 1 enfant) */
    .horizontal-branch-line {
        position: absolute;
        top: 20px; /* Rejoint la fin de la ligne verticale */
        left: 10%;
        right: 10%;
        height: 2px;
        background: #94a3b8;
        z-index: 1;
    }

    .tree-children {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        width: 100%;
        padding-top: 0;
        position: relative;
        z-index: 2;
        gap: 20px;
    }

    /* La petite ligne VERTICALE pour chaque enfant (remonte vers l'horizontale) */
    .tree-children > .tree-branch::before {
        content: '';
        position: absolute;
        top: -20px; /* Distance entre le haut de l'enfant et la ligne horizontale */
        left: 50%;
        width: 2px;
        height: 20px;
        background: #94a3b8;
        transform: translateX(-50%);
        z-index: 1;
    }

    /* Cas spécial pour un seul enfant : pas de ligne horizontale, juste la verticale continue */
    .tree-children-container:has(.horizontal-branch-line) + .tree-children > .tree-branch::before {
        top: -20px;
        height: 20px;
    }

    /* ===== COULEURS AVATARS ===== */
    .avatar-success { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .avatar-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .avatar-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .avatar-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .avatar-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .avatar-gold { background: linear-gradient(135deg, #eab308, #ca8a04); }
    .avatar-neutral { background: linear-gradient(135deg, #6b7280, #4b5563); }

    /* ===== STATS & UI ===== */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 0.5rem; }
    .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 0.4rem 0.5rem; text-align: center; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); border-color: var(--primary-500); }
    .stat-card .number { font-size: 1.1rem; font-weight: 700; }
    .stat-card .label { font-size: 0.5rem; color: var(--text-secondary); text-transform: uppercase; }

    .card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 0.75rem; }
    .btn { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.8rem; border-radius: var(--radius-md); font-weight: 600; font-size: 0.7rem; transition: all 0.3s ease; cursor: pointer; border: none; }
    .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4); }
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }
    .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.6rem; }
    .hidden { display: none; }

    /* ===== ZOOM & SCROLL ===== */
    .tree-container-wrapper { position: relative; width: 100%; overflow: auto; padding: 0.5rem; background: var(--bg-secondary); border-radius: var(--radius-lg); border: 1px solid var(--border-color); min-height: 400px; max-height: 600px; }
    .tree-container-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
    .tree-container-wrapper::-webkit-scrollbar-thumb { background: var(--primary-500); border-radius: 3px; }
    .tree-scroll-content { min-width: max-content; padding: 0.5rem; display: flex; justify-content: center; transform-origin: top left; transition: transform 0.2s ease; }
    .zoom-controls { display: flex; align-items: center; gap: 0.15rem; background: var(--bg-card); padding: 0.1rem 0.3rem; border-radius: var(--radius-full); border: 1px solid var(--border-color); }
    .zoom-controls button { width: 20px; height: 20px; border-radius: 50%; border: none; background: var(--bg-secondary); color: var(--text-primary); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; font-weight: 700; }
    .zoom-controls button:hover { background: var(--primary-500); color: white; }
    .zoom-controls .zoom-level { font-size: 0.55rem; font-weight: 600; color: var(--text-secondary); min-width: 30px; text-align: center; }
    .fullscreen-btn { display: flex; align-items: center; gap: 0.15rem; padding: 0.15rem 0.5rem; border-radius: var(--radius-full); border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-primary); cursor: pointer; transition: all 0.2s ease; font-size: 0.6rem; }
    .fullscreen-btn:hover { background: var(--primary-500); color: white; }
    .tree-container-wrapper.fullscreen { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; max-height: 100vh; border-radius: 0; background: var(--bg-primary); }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fadeInUp { animation: fadeInUp 0.4s ease forwards; }
    .delay-1 { animation-delay: 0.1s; } .delay-2 { animation-delay: 0.2s; } .delay-3 { animation-delay: 0.3s; } .delay-4 { animation-delay: 0.4s; }

    .custom-toast { position: fixed; bottom: 1rem; right: 1rem; padding: 0.4rem 0.8rem; border-radius: var(--radius-md); background: #22c55e; color: white; font-weight: 500; font-size: 0.7rem; box-shadow: 0 8px 32px rgba(0,0,0,0.2); z-index: 9999; animation: fadeInUp 0.3s ease forwards; max-width: 300px; }

    /* ===== RESPONSIVE (ADAPTATION DES LIGNES) ===== */
    @media (max-width: 768px) {
        .tree-children { gap: 15px; }
        .tree-children-container { padding-top: 15px; }
        .tree-children-container::before { height: 15px; }
        .horizontal-branch-line { top: 15px; }
        .tree-children > .tree-branch::before { top: -15px; height: 15px; }
        .avatar-xl { width: 3.5rem; height: 3.5rem; font-size: 1rem; }
        .avatar-lg { width: 3rem; height: 3rem; font-size: 0.9rem; }
        .avatar-md { width: 2.2rem; height: 2.2rem; font-size: 0.7rem; }
    }

    @media (max-width: 480px) {
        .tree-children { gap: 10px; }
        .tree-children-container { padding-top: 10px; }
        .tree-children-container::before { height: 10px; }
        .horizontal-branch-line { top: 10px; }
        .tree-children > .tree-branch::before { top: -10px; height: 10px; }
        .tree-node { padding: 0.2rem; }
        .name { font-size: 0.6rem; max-width: 50px; }
    }
</style>
@endpush

@section('content')
<div class="space-y-2 sm:space-y-3">
    
    <div class="flex flex-wrap items-center justify-between gap-2 animate-fadeInUp">
        <div>
            <h1 class="text-lg sm:text-2xl font-bold text-[var(--text-primary)]"> Mon Réseau</h1>
            <p class="text-xs text-[var(--text-secondary)]">Arbre généalogique MLM</p>
        </div>
        <div class="flex gap-1">
            <button class="btn btn-primary btn-sm" onclick="toggleView('tree')" id="treeViewBtn"> Arbre</button>
            <button class="btn btn-outline btn-sm" onclick="toggleView('list')" id="listViewBtn"> Liste</button>
        </div>
    </div>

    <div id="treeView" class="card animate-fadeInUp delay-2 p-2 sm:p-3">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <h3 class="font-semibold text-sm text-[var(--text-primary)]">Arbre MLM</h3>
            <div class="flex flex-wrap items-center gap-1">
                <span class="level-indicator"><span class="font-semibold text-primary-500">{{ $controller->countNodes($tree) ?? 0 }}</span> membres</span>
                <div class="zoom-controls">
                    <button onclick="zoomTree('out')">−</button>
                    <span class="zoom-level" id="zoomLevel">100%</span>
                    <button onclick="zoomTree('in')">+</button>
                    <button onclick="zoomTree('reset')">⟲</button>
                </div>
                <button class="fullscreen-btn" onclick="toggleFullscreen()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/></svg>
                </button>
            </div>
        </div>

        <div class="tree-container-wrapper" id="treeContainerWrapper">
            <div class="tree-scroll-content" id="treeScrollContent" style="transform: scale(1);">
                <div class="tree-wrapper">
                    @if($tree && isset($tree['children']) && count($tree['children']) > 0)
                        {!! $controller->renderGenealogyTree($tree, $controller) !!}
                    @else
                        <div class="text-center py-6 text-[var(--text-secondary)]">
                            <div class="text-4xl mb-2">🌱</div>
                            <p class="font-medium">Vous n'avez pas encore de filleuls</p>
                            <p class="text-xs text-[var(--text-tertiary)]">Partagez votre lien de parrainage !</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="listView" class="card animate-fadeInUp delay-3 hidden p-2 sm:p-3">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold text-sm text-[var(--text-primary)]"> Liste des membres</h3>
            <span class="badge badge-neutral text-[8px]">{{ $filleuls->count() }} membres</span>
        </div>
        <div class="relative mb-2">
            <input type="text" id="searchMember" placeholder="Rechercher..." class="w-full pl-6 pr-2 py-1 text-xs border-2 border-[var(--border-color)] rounded-lg bg-[var(--bg-input)] text-[var(--text-primary)] focus:border-primary-500 focus:outline-none">
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-xs">
                <thead>
                    <tr class="border-b-2 border-[var(--border-color)]">
                        <th class="text-left py-1 px-2 font-semibold text-[var(--text-secondary)]">#</th>
                        <th class="text-left py-1 px-2 font-semibold text-[var(--text-secondary)]">Membre</th>
                        <th class="text-left py-1 px-2 font-semibold text-[var(--text-secondary)] hidden sm:table-cell">Grade</th>
                        <th class="text-left py-1 px-2 font-semibold text-[var(--text-secondary)] hidden md:table-cell">Niv.</th>
                        <th class="text-left py-1 px-2 font-semibold text-[var(--text-secondary)]">PV</th>
                        <th class="text-left py-1 px-2 font-semibold text-[var(--text-secondary)]">Statut</th>
                        <th class="text-center py-1 px-2 font-semibold text-[var(--text-secondary)]">Action</th>
                    </tr>
                </thead>
                <tbody id="memberList">
                    @forelse($filleuls as $member)
                        @php
                            $rankInfo = $controller->getUserRankInfo($member);
                            $rankName = $rankInfo['name'];
                            $rankColor = $controller->getRankColor($rankInfo['level']);
                            $avatarColor = $controller->getAvatarColor($member);
                        @endphp
                        <tr data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}" class="border-b border-[var(--border-light)] hover:bg-[var(--bg-hover)] transition-colors cursor-pointer" onclick="navigateToUser({{ $member->id }})">
                            <td class="py-1 px-2 text-[var(--text-tertiary)] font-mono">#{{ $member->id }}</td>
                            <td class="py-1 px-2"><div class="flex items-center gap-1.5"><div class="avatar avatar-sm {{ $avatarColor }}">{{ strtoupper(substr($member->name, 0, 1)) }}</div><span class="font-medium">{{ $member->name }}</span></div></td>
                            <td class="hidden sm:table-cell py-1 px-2"><span class="badge {{ $rankColor }}">{{ $rankName }}</span></td>
                            <td class="hidden md:table-cell py-1 px-2"><span class="badge badge-info">Niv.{{ $member->level ?? 1 }}</span></td>
                            <td class="py-1 px-2 font-semibold text-primary-500">{{ number_format($member->pv_balance ?? 0) }}</td>
                            <td class="py-1 px-2"><span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">{{ $member->is_active ? 'Actif' : 'Inactif' }}</span></td>
                            <td class="text-center py-1 px-2"><button onclick="event.stopPropagation(); navigateToUser({{ $member->id }})" class="btn btn-primary btn-sm text-[8px]">👁</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-[var(--text-secondary)]"><div class="text-2xl mb-1">📭</div>Aucun membre dans votre réseau</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card animate-fadeInUp delay-4 border-l-4 border-primary-500 p-2 sm:p-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="text-[8px] uppercase tracking-wider text-[var(--text-secondary)]">🔗 Lien de parrainage</p>
                <p class="text-xs font-semibold text-primary-500 break-all" id="sponsorLink">{{ url('/register?ref=' . Auth::user()->sponsor_id) }}</p>
                <p class="text-[8px] text-[var(--text-secondary)]">Code: <span class="font-mono text-primary-500 font-semibold">{{ Auth::user()->sponsor_id }}</span></p>
            </div>
            <button onclick="copyLink()" class="btn btn-primary btn-sm flex-shrink-0">📋 Copier</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const BASE_URL = '{{ url("/") }}';
function navigateToUser(userId) { window.location.href = BASE_URL + '/network/show/' + userId; }
function toggleView(view) {
    document.getElementById('treeView').classList.toggle('hidden', view !== 'tree');
    document.getElementById('listView').classList.toggle('hidden', view !== 'list');
    document.getElementById('treeViewBtn').className = view === 'tree' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
    document.getElementById('listViewBtn').className = view === 'list' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
}
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchMember');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var query = this.value.trim().toLowerCase();
            document.querySelectorAll('#memberList tr').forEach(function(row) {
                row.style.display = (row.dataset.name.includes(query) || row.dataset.email.includes(query)) ? '' : 'none';
            });
        });
    }
    initDragScroll();
});

let currentZoom = 1;
const zoomStep = 0.1, minZoom = 0.3, maxZoom = 2;
function zoomTree(action) {
    const content = document.getElementById('treeScrollContent');
    if (!content) return;
    if (action === 'in') currentZoom = Math.min(currentZoom + zoomStep, maxZoom);
    else if (action === 'out') currentZoom = Math.max(currentZoom - zoomStep, minZoom);
    else if (action === 'reset') currentZoom = 1;
    content.style.transform = 'scale(' + currentZoom + ')';
    content.style.transformOrigin = 'top left';
    document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
}

function toggleFullscreen() {
    const wrapper = document.getElementById('treeContainerWrapper');
    if (!wrapper) return;
    wrapper.classList.toggle('fullscreen');
    document.body.style.overflow = wrapper.classList.contains('fullscreen') ? 'hidden' : '';
    if(!wrapper.classList.contains('fullscreen')) { wrapper.scrollTop = 0; wrapper.scrollLeft = 0; }
}

function initDragScroll() {
    const wrapper = document.getElementById('treeContainerWrapper');
    if (!wrapper) return;
    let isDragging = false, startX = 0, startY = 0, scrollLeft = 0, scrollTop = 0;
    wrapper.addEventListener('mousedown', function(e) {
        isDragging = true; wrapper.classList.add('dragging');
        startX = e.pageX - wrapper.offsetLeft; startY = e.pageY - wrapper.offsetTop;
        scrollLeft = wrapper.scrollLeft; scrollTop = wrapper.scrollTop;
        wrapper.style.cursor = 'grabbing';
    });
    ['mouseleave', 'mouseup'].forEach(evt => {
        wrapper.addEventListener(evt, function() {
            isDragging = false; wrapper.classList.remove('dragging'); wrapper.style.cursor = 'grab';
        });
    });
    wrapper.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - wrapper.offsetLeft, y = e.pageY - wrapper.offsetTop;
        wrapper.scrollLeft = scrollLeft - (x - startX) * 1.5;
        wrapper.scrollTop = scrollTop - (y - startY) * 1.5;
    });
    wrapper.addEventListener('wheel', function(e) {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            e.deltaY > 0 ? zoomTree('out') : zoomTree('in');
        }
    }, { passive: false });
}

function copyLink() {
    var link = document.getElementById('sponsorLink').textContent;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(() => showToast('✅ Lien copié !')).catch(() => fallbackCopy(link));
    } else { fallbackCopy(link); }
}
function fallbackCopy(text) {
    var input = document.createElement('input'); input.value = text; document.body.appendChild(input); input.select(); document.execCommand('copy'); document.body.removeChild(input); showToast('✅ Lien copié !');
}
function showToast(message) {
    document.querySelectorAll('.custom-toast').forEach(el => el.remove());
    var toast = document.createElement('div'); toast.className = 'custom-toast'; toast.textContent = message; document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(20px)'; setTimeout(() => toast.remove(), 500); }, 2500);
}
</script>
@endpush
@endsection