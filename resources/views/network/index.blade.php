@extends('layouts.app')

@push('styles')
<style>
    .tree-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.5rem;
        position: relative;
    }
    .tree-node::before {
        content: '';
        position: absolute;
        top: -1rem;
        left: 50%;
        width: 2px;
        height: 1rem;
        background: var(--border-color);
    }
    .tree-node:first-child::before { display: none; }
    .tree-children {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid var(--border-color);
        position: relative;
    }
    .tree-children::before {
        content: '';
        position: absolute;
        top: -2px;
        left: 50%;
        width: 2px;
        height: 1rem;
        background: var(--border-color);
    }
    .tree-level-badge {
        position: absolute;
        top: -0.5rem;
        right: -0.5rem;
        font-size: 0.5rem;
        padding: 0.1rem 0.4rem;
        border-radius: var(--radius-full);
        background: var(--primary-500);
        color: white;
    }
    @media (max-width: 640px) {
        .tree-children { flex-direction: column; align-items: center; gap: 0.5rem; }
        .tree-children::before { display: none; }
        .tree-node::before { display: none; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🌳 Mon Réseau</h1>
            <p class="text-[var(--text-secondary)] mt-1">Visualisez votre arbre généalogique</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-outline btn-sm" onclick="toggleView('tree')" id="treeViewBtn">
                🌳 Arbre
            </button>
            <button class="btn btn-outline btn-sm" onclick="toggleView('list')" id="listViewBtn">
                📋 Liste
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total</p>
            <p class="text-2xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Niveau 1</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['level_1'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Niveau 2</p>
            <p class="text-2xl font-bold text-purple-500">{{ $stats['level_2'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Niveau 3</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['level_3'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Vue Arbre -->
    <div id="treeView" class="card animate-fadeInUp delay-5">
        <h3 class="font-semibold text-[var(--text-primary)] mb-4">🌳 Arbre généalogique</h3>
        
        <div class="flex justify-center overflow-x-auto py-4">
            <div class="tree-node">
                <!-- Moi -->
                <div class="flex flex-col items-center">
                    <div class="avatar avatar-xl avatar-gradient avatar-ring relative">
                        {{ substr(Auth::user()->name, 0, 2) }}
                        <span class="tree-level-badge">★</span>
                    </div>
                    <p class="font-semibold text-[var(--text-primary)] mt-2">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">Moi</p>
                    <span class="badge badge-success text-xs mt-1">{{ Auth::user()->rank ?? 'Distributor' }}</span>
                </div>

                <!-- Enfants -->
                @if(isset($recentDownlines) && $recentDownlines->count() > 0)
                    <div class="tree-children">
                        @foreach($recentDownlines->take(6) as $member)
                            <div class="tree-node">
                                <div class="flex flex-col items-center">
                                    <div class="avatar avatar-lg {{ $member->is_active ? 'avatar-success' : 'avatar-danger' }} relative">
                                        {{ substr($member->name, 0, 1) }}
                                        <span class="tree-level-badge">1</span>
                                    </div>
                                    <p class="text-sm font-medium text-[var(--text-primary)] mt-1 truncate max-w-[80px]">{{ $member->name }}</p>
                                    <p class="text-xs text-[var(--text-secondary)]">Niveau 1</p>
                                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }} text-xs mt-0.5">
                                        {{ $member->is_active ? '✅' : '❌' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-[var(--text-secondary)] py-8 mt-4">
                        <span class="text-4xl block mb-2">🌱</span>
                        Vous n'avez pas encore de filleuls.<br>
                        Partagez votre lien de parrainage pour développer votre réseau !
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Vue Liste -->
    <div id="listView" class="card animate-fadeInUp delay-6 hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[var(--text-primary)]">📋 Membres de mon réseau</h3>
            <span class="badge badge-neutral text-xs">{{ $recentDownlines->count() ?? 0 }} membres</span>
        </div>

        <!-- Recherche -->
        <div class="relative mb-4">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchMember" placeholder="Rechercher un membre..." class="input pl-9">
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th class="hidden sm:table-cell">Email</th>
                        <th class="hidden md:table-cell">Niveau</th>
                        <th class="hidden lg:table-cell">Package</th>
                        <th>PV</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="memberList">
                    @forelse($recentDownlines ?? [] as $member)
                        <tr data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}">
                            <td class="font-medium">{{ $member->name }}</td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)]">{{ $member->email }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge badge-info">Niv. {{ $member->genealogy?->level ?? 1 }}</span>
                            </td>
                            <td class="hidden lg:table-cell">{{ $member->package?->name ?? 'Starter' }}</td>
                            <td>{{ number_format($member->pv_balance ?? 0) }}</td>
                            <td>
                                <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $member->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Aucun membre dans votre réseau
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($recentDownlines) && $recentDownlines instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentDownlines->hasPages())
    <div class="mt-4">
        {{ $recentDownlines->links() }}
    </div>
@endif
    </div>

    <!-- Lien de parrainage -->
    <div class="card animate-fadeInUp delay-7 border-l-4 border-primary-500">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-[var(--text-secondary)]">🔗 Votre lien de parrainage</p>
                <p class="text-sm font-semibold text-primary-500 break-all" id="sponsorLink">
                    {{ url('/register?ref=' . Auth::user()->sponsor_id) }}
                </p>
            </div>
            <button onclick="copyLink()" class="btn btn-primary btn-sm">
                📋 Copier
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleView(view) {
    const treeView = document.getElementById('treeView');
    const listView = document.getElementById('listView');
    const treeBtn = document.getElementById('treeViewBtn');
    const listBtn = document.getElementById('listViewBtn');

    if (view === 'tree') {
        treeView.classList.remove('hidden');
        listView.classList.add('hidden');
        treeBtn.classList.add('btn-primary');
        treeBtn.classList.remove('btn-outline');
        listBtn.classList.remove('btn-primary');
        listBtn.classList.add('btn-outline');
    } else {
        treeView.classList.add('hidden');
        listView.classList.remove('hidden');
        listBtn.classList.add('btn-primary');
        listBtn.classList.remove('btn-outline');
        treeBtn.classList.remove('btn-primary');
        treeBtn.classList.add('btn-outline');
    }
}

function copyLink() {
    const link = document.getElementById('sponsorLink').textContent;
    navigator.clipboard.writeText(link).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copié !',
            text: 'Lien de parrainage copié dans le presse-papier',
            timer: 2000,
            showConfirmButton: false
        });
    }).catch(() => {
        // Fallback
        const input = document.createElement('input');
        input.value = link;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        Swal.fire({
            icon: 'success',
            title: 'Copié !',
            timer: 2000,
            showConfirmButton: false
        });
    });
}

// Recherche dans la liste
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchMember');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('#memberList tr');
            
            rows.forEach(row => {
                const name = row.dataset.name || '';
                const email = row.dataset.email || '';
                row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
@endsection