@extends('layouts.app')

@section('title', 'Mon réseau')

@section('content')
@php
    $rankInfo = $controller->getUserRankInfo($user);
    $referralUrl = url('/register?ref=' . $user->sponsor_id);
@endphp
<div class="space-y-4 sm:space-y-6 network-page">

    <div class="member-page-intro network-page-intro-desktop animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Mon réseau</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">Votre généalogie, vos filleuls et votre lien de parrainage</p>
    </div>

    <div class="network-mobile-banner animate-fadeInUp">
        <div class="shop-catalog-banner network-catalog-banner">
            <div class="shop-catalog-banner__text">
                <p class="shop-catalog-banner__eyebrow">Mon équipe</p>
                <p class="shop-catalog-banner__title">Mon réseau</p>
                <p class="shop-catalog-banner__sub">Généalogie · filleuls · parrainage</p>
            </div>
            <div class="shop-catalog-banner__tools">
                <button type="button" class="shop-banner-icon-btn" onclick="copyReferralLink()" aria-label="Copier le lien de parrainage">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="network-stats stats-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total équipe</p>
            <p class="text-lg sm:text-xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 border-l-4 border-blue-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau 1</p>
            <p class="text-lg sm:text-xl font-bold text-blue-500">{{ $stats['level_1'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 border-l-4 border-purple-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau 2</p>
            <p class="text-lg sm:text-xl font-bold text-purple-500">{{ $stats['level_2'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 border-l-4 border-green-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau 3</p>
            <p class="text-lg sm:text-xl font-bold text-green-500">{{ $stats['level_3'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 border-l-4 border-yellow-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Actifs (N1)</p>
            <p class="text-lg sm:text-xl font-bold text-yellow-600">{{ $stats['active'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 border-l-4 border-slate-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">PV équipe</p>
            <p class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">{{ number_format($stats['total_pv'] ?? 0) }}</p>
        </div>
    </div>

    <div class="card network-referral-card animate-fadeInUp delay-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-[var(--text-secondary)]">Votre code parrain</p>
                <p class="text-base font-mono font-semibold text-primary-500">{{ $user->sponsor_id }}</p>
                <p class="text-xs text-[var(--text-tertiary)] mt-1 break-all" id="sponsorLink">{{ $referralUrl }}</p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <button type="button" onclick="copyReferralLink()" class="btn btn-primary btn-sm">Copier le lien</button>
                <a href="{{ route('network.downlines') }}" class="btn btn-outline btn-sm">Tous les filleuls</a>
            </div>
        </div>
    </div>

    @if($parrain)
        <div class="card p-3 sm:p-4 animate-fadeInUp delay-2">
            <p class="text-xs text-[var(--text-secondary)]">Mon parrain</p>
            <p class="text-sm font-medium text-[var(--text-primary)]">{{ $parrain->name }}</p>
            <p class="text-xs font-mono text-[var(--text-tertiary)]">Code {{ $parrain->sponsor_id }}</p>
        </div>
    @endif

    <div class="network-view-tabs animate-fadeInUp delay-3" role="tablist" aria-label="Vue du réseau">
        <button type="button" class="network-view-tab is-active" id="treeViewBtn" onclick="setNetworkView('tree')" aria-selected="true">Arbre</button>
        <button type="button" class="network-view-tab" id="listViewBtn" onclick="setNetworkView('list')" aria-selected="false">Filleuls directs</button>
    </div>

    <div id="treeView" class="card network-genealogy-card">
        <div class="network-tree-panel-head">
            <div>
                <h2>Généalogie de parrainage</h2>
                <p>Cliquez sur une carte pour ouvrir le profil. Utilisez « Afficher filleuls » pour développer une branche.</p>
            </div>
            <div class="network-tree-toolbar">
                <span class="network-tree-legend">
                    <span class="network-tree-legend-dot" aria-hidden="true"></span> Actif
                    <span class="network-tree-legend-dot is-off" aria-hidden="true"></span> Inactif
                </span>
                <button type="button" class="network-tree-toolbar-btn" onclick="centerNetworkTree()">Centrer l’arbre</button>
            </div>
        </div>
        <div class="network-genealogy-scroll" id="genealogyScroll">
            <div class="network-org-chart-wrap" id="networkTreeMount">
                <svg class="network-tree-svg" id="networkTreeSvg" aria-hidden="true"></svg>
                <ul class="network-tree-chart">
                    @if(!empty($tree))
                        {!! $controller->renderGenealogyTree($tree, $controller) !!}
                    @endif
                </ul>
            </div>
            @if(($stats['total'] ?? 0) === 0)
                <p class="network-tree-empty-hint">Aucun filleul pour le moment. Partagez votre lien de parrainage pour agrandir l’organigramme.</p>
            @endif
        </div>
    </div>

    <div id="listView" class="hidden animate-fadeInUp delay-4 space-y-3">
        <div class="relative">
            <label for="searchMember" class="sr-only">Rechercher un filleul</label>
            <input type="search" id="searchMember" placeholder="Rechercher par nom ou e-mail…" class="input pl-9 text-sm" autocomplete="off">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)] pointer-events-none" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
        </div>

        <div class="md:hidden space-y-2" id="memberListMobile">
            @forelse($filleuls as $member)
                @php
                    $mRank = $controller->getUserRankInfo($member);
                    $mAvatar = $controller->getAvatarColor($member);
                @endphp
                <a href="{{ route('network.show', $member->id) }}" class="network-member-card" data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}">
                    <div class="avatar avatar-md {{ $mAvatar }}">{{ strtoupper(substr($member->name, 0, 2)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-sm truncate">{{ $member->name }}</p>
                        <p class="text-xs text-[var(--text-secondary)]">{{ number_format($member->pv_balance ?? 0) }} PV · {{ $member->downline_count ?? 0 }} filleul(s)</p>
                    </div>
                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }} text-[10px]">{{ $member->is_active ? 'Actif' : 'Inactif' }}</span>
                </a>
            @empty
                <div class="card network-empty-state">Aucun filleul direct.</div>
            @endforelse
        </div>

        <div class="card hidden md:block">
            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Rang</th>
                            <th>PV</th>
                            <th>Filleuls</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="memberList">
                        @forelse($filleuls as $member)
                            @php $mRank = $controller->getUserRankInfo($member); @endphp
                            <tr data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}">
                                <td class="font-medium">{{ $member->name }}</td>
                                <td><span class="badge badge-neutral text-xs">{{ $mRank['name'] }}</span></td>
                                <td>{{ number_format($member->pv_balance ?? 0) }}</td>
                                <td>{{ $member->downline_count ?? 0 }}</td>
                                <td>
                                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }} text-xs">
                                        {{ $member->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('network.show', $member->id) }}" class="btn btn-outline btn-sm">Voir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">Aucun filleul direct.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const networkShowUrl = @json(url('/network/show'));
    const childrenUrl = @json(url('/network/children'));
    window.networkShowUrlFor = function (id) {
        return networkShowUrl + '/' + id;
    };

    window.expandNode = function (userId, nodeEl) {
        const branch = nodeEl.closest('.network-branch');
        const level = parseInt(branch?.dataset.level || '0', 10);
        if (level === 0) {
            return;
        }
        window.location.href = networkShowUrlFor(userId);
    };

    function drawNetworkTreeLines() {
        const scroll = document.getElementById('genealogyScroll');
        const mount = document.getElementById('networkTreeMount');
        const svg = document.getElementById('networkTreeSvg');
        if (!scroll || !mount || !svg) {
            return;
        }

        const w = mount.offsetWidth;
        const h = mount.offsetHeight;
        svg.setAttribute('width', String(w));
        svg.setAttribute('height', String(h));
        svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
        svg.innerHTML = '';

        const mountRect = mount.getBoundingClientRect();
        const stem = 22;
        const elbow = 14;

        mount.querySelectorAll('.network-children-row').forEach(function (row) {
            const parentBranch = row.closest('.network-branch');
            if (!parentBranch) {
                return;
            }
            const parentCard = parentBranch.querySelector(':scope > .network-node-card');
            if (!parentCard) {
                return;
            }
            const parentR = parentCard.getBoundingClientRect();
            const px = parentR.left + parentR.width / 2 - mountRect.left;
            const py = parentR.bottom - mountRect.top;

            const childCards = row.querySelectorAll(':scope > .network-branch > .network-node-card');
            if (!childCards.length) {
                return;
            }

            const points = [];
            childCards.forEach(function (card) {
                const r = card.getBoundingClientRect();
                points.push({
                    x: r.left + r.width / 2 - mountRect.left,
                    y: r.top - mountRect.top,
                });
            });

            points.sort(function (a, b) {
                return a.x - b.x;
            });

            const railY = py + stem;
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            let d = 'M ' + px + ' ' + py + ' V ' + railY;

            if (points.length === 1) {
                d += ' V ' + points[0].y;
            } else {
                d += ' M ' + points[0].x + ' ' + railY + ' H ' + points[points.length - 1].x;
                points.forEach(function (p) {
                    d += ' M ' + p.x + ' ' + railY + ' V ' + (p.y - elbow) + ' V ' + p.y;
                });
            }

            path.setAttribute('d', d);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', 'currentColor');
            path.setAttribute('stroke-width', '2');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('class', 'network-tree-path');
            svg.appendChild(path);
        });
    }

    window.loadChildren = async function (userId, btn) {
        if (btn.dataset.loading === '1') {
            return;
        }
        btn.dataset.loading = '1';
        btn.setAttribute('aria-busy', 'true');

        try {
            const res = await fetch(childrenUrl + '/' + userId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await res.json();
            if (!payload.success || !payload.children?.length) {
                btn.textContent = 'Aucun filleul';
                return;
            }

            const slot = btn.closest('.network-tree-expand');
            const ul = document.createElement('ul');
            ul.className = 'network-children-row';
            payload.children.forEach(function (child) {
                ul.appendChild(buildOrgLi(child));
            });
            slot.replaceWith(ul);
            requestAnimationFrame(drawNetworkTreeLines);
        } catch (e) {
            btn.textContent = 'Erreur — réessayer';
            btn.dataset.loading = '0';
        }
    };

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildOrgLi(child) {
        const level = child.level || 1;
        const li = document.createElement('li');
        li.className = 'network-branch';
        li.dataset.userId = child.id;
        li.dataset.level = level;

        const initials = escapeHtml(
            (child.avatar || (child.name ? child.name.substring(0, 2) : '?')).toUpperCase()
        );
        const avatarClass = escapeHtml(child.avatar_color || 'avatar-rank-1');
        const statusClass = child.is_active ? 'is-active' : 'is-inactive';
        const rankName = escapeHtml((child.rank && child.rank.name) ? child.rank.name : 'Distributeur');
        const safeName = escapeHtml(child.name || '');

        let inner =
            '<div class="network-node-card" role="button" tabindex="0" onclick="expandNode(' +
            child.id +
            ', this)">' +
            '<div class="network-node-avatar ' +
            avatarClass +
            '">' +
            initials +
            '<span class="network-node-status ' +
            statusClass +
            '"></span></div>' +
            '<div class="network-node-body">' +
            '<p class="network-node-name" title="' +
            safeName +
            '">' +
            safeName +
            '</p>' +
            '<p class="network-node-sub">Niveau ' +
            level +
            ' · ' +
            rankName +
            '</p></div></div>';

        if (child.has_children) {
            inner +=
                '<div class="network-tree-expand"><button type="button" class="network-tree-expand-btn" onclick="event.stopPropagation(); loadChildren(' +
                child.id +
                ', this)">Développer · ' +
                (child.children_count || 0) +
                ' filleul' +
                ((child.children_count || 0) > 1 ? 's' : '') +
                '</button></div>';
        }

        li.innerHTML = inner;
        return li;
    }

    window.centerNetworkTree = function () {
        const el = document.getElementById('genealogyScroll');
        if (!el) return;
        const root = el.querySelector('.network-node-card.is-root');
        if (root) {
            root.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
        } else {
            el.scrollTo({ left: (el.scrollWidth - el.clientWidth) / 2, top: 0, behavior: 'smooth' });
        }
    };

    window.setNetworkView = function (view) {
        const treeView = document.getElementById('treeView');
        const listView = document.getElementById('listView');
        const treeBtn = document.getElementById('treeViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        const isTree = view === 'tree';
        treeView.classList.toggle('hidden', !isTree);
        listView.classList.toggle('hidden', isTree);
        treeBtn.classList.toggle('is-active', isTree);
        listBtn.classList.toggle('is-active', !isTree);
        treeBtn.setAttribute('aria-selected', isTree ? 'true' : 'false');
        listBtn.setAttribute('aria-selected', !isTree ? 'true' : 'false');
    };

    window.copyReferralLink = function () {
        const link = document.getElementById('sponsorLink')?.textContent?.trim() || '';
        if (!link) return;
        const done = function () {
            if (typeof window.showToast === 'function') {
                window.showToast('Lien copié');
            }
        };
        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(link).then(done).catch(function () {
                fallbackCopy(link, done);
            });
        } else {
            fallbackCopy(link, done);
        }
    };

    function fallbackCopy(text, cb) {
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        cb();
    }

    document.addEventListener('DOMContentLoaded', function () {
        setNetworkView('tree');

        const mount = document.getElementById('networkTreeMount');
        if (mount && typeof ResizeObserver !== 'undefined') {
            var ro = new ResizeObserver(function () {
                drawNetworkTreeLines();
            });
            ro.observe(mount);
        }
        window.addEventListener('resize', function () {
            drawNetworkTreeLines();
        });

        requestAnimationFrame(function () {
            drawNetworkTreeLines();
            centerNetworkTree();
        });

        const searchInput = document.getElementById('searchMember');
        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('#memberList tr[data-name]').forEach(function (row) {
                const name = row.dataset.name || '';
                const email = row.dataset.email || '';
                row.style.display = !q || name.includes(q) || email.includes(q) ? '' : 'none';
            });
            document.querySelectorAll('#memberListMobile .network-member-card').forEach(function (card) {
                const name = card.dataset.name || '';
                const email = card.dataset.email || '';
                card.style.display = !q || name.includes(q) || email.includes(q) ? '' : 'none';
            });
        });
    });
})();
</script>
@endpush
@endsection
