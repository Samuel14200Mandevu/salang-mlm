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
        .avatar-xl { width: 3.5rem; height: 3.5rem; font-size: 1.2rem; }
        .avatar-lg { width: 2.5rem; height: 2.5rem; font-size: 0.8rem; }
        .card { padding: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Reseau</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Visualisez votre arbre genealogique</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <button class="btn btn-primary btn-sm sm:btn-md" onclick="toggleView('tree')" id="treeViewBtn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Arbre
            </button>
            <button class="btn btn-outline btn-sm sm:btn-md" onclick="toggleView('list')" id="listViewBtn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Liste
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-blue-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau 1</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ $stats['level_1'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau 2</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $stats['level_2'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau 3</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $stats['level_3'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Vue Arbre -->
    <div id="treeView" class="card animate-fadeInUp delay-5 p-3 sm:p-4 md:p-6">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Arbre genealogique</h3>
        
        <div class="flex justify-center overflow-x-auto py-3 sm:py-4">
            <div class="tree-node">
                <!-- Moi -->
                <div class="flex flex-col items-center">
                    <div class="avatar avatar-xl avatar-gradient avatar-ring relative">
                        {{ substr(Auth::user()->name, 0, 2) }}
                        <span class="tree-level-badge">★</span>
                    </div>
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mt-1 sm:mt-2">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Moi</p>
                    <span class="badge badge-success text-[10px] sm:text-xs mt-0.5 sm:mt-1">{{ Auth::user()->rank ?? 'Distributor' }}</span>
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
                                    <p class="text-xs sm:text-sm font-medium text-[var(--text-primary)] mt-0.5 sm:mt-1 truncate max-w-[60px] sm:max-w-[80px]">{{ $member->name }}</p>
                                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau 1</p>
                                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs mt-0.5">
                                        {{ $member->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-[var(--text-secondary)] py-6 sm:py-8 mt-3 sm:mt-4 text-sm sm:text-base">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Vous n'avez pas encore de filleuls.<br>
                        Partagez votre lien de parrainage pour developper votre reseau !
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Vue Liste -->
    <div id="listView" class="card animate-fadeInUp delay-6 hidden p-3 sm:p-4 md:p-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Membres de mon reseau</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $recentDownlines->count() ?? 0 }} membres</span>
        </div>

        <!-- Recherche -->
        <div class="relative mb-3 sm:mb-4">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchMember" placeholder="Rechercher un membre..." class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Nom</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Email</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Niveau</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Package</th>
                        <th class="text-xs sm:text-sm">PV</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                    </tr>
                </thead>
                <tbody id="memberList">
                    @forelse($recentDownlines ?? [] as $member)
                        <tr data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}">
                            <td class="font-medium text-sm sm:text-base">{{ $member->name }}</td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">{{ $member->email }}</td>
                            <td class="hidden md:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">Niv. {{ $member->genealogy?->level ?? 1 }}</span>
                            </td>
                            <td class="hidden lg:table-cell text-sm sm:text-base">{{ $member->package?->name ?? 'Starter' }}</td>
                            <td class="text-sm sm:text-base">{{ number_format($member->pv_balance ?? 0) }}</td>
                            <td>
                                <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                    {{ $member->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Aucun membre dans votre reseau
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($recentDownlines) && $recentDownlines instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentDownlines->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $recentDownlines->links() }}
            </div>
        @endif
    </div>

    <!-- Lien de parrainage -->
    <div class="card animate-fadeInUp delay-7 border-l-4 border-primary-500 p-3 sm:p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Votre lien de parrainage</p>
                <p class="text-xs sm:text-sm font-semibold text-primary-500 break-all" id="sponsorLink">
                    {{ url('/register?ref=' . Auth::user()->sponsor_id) }}
                </p>
            </div>
            <button onclick="copyLink()" class="btn btn-primary btn-sm sm:btn-md flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                </svg>
                Copier
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleView(view) {
    var treeView = document.getElementById('treeView');
    var listView = document.getElementById('listView');
    var treeBtn = document.getElementById('treeViewBtn');
    var listBtn = document.getElementById('listViewBtn');

    if (view === 'tree') {
        treeView.classList.remove('hidden');
        listView.classList.add('hidden');
        treeBtn.className = 'btn btn-primary btn-sm sm:btn-md';
        listBtn.className = 'btn btn-outline btn-sm sm:btn-md';
    } else {
        treeView.classList.add('hidden');
        listView.classList.remove('hidden');
        listBtn.className = 'btn btn-primary btn-sm sm:btn-md';
        treeBtn.className = 'btn btn-outline btn-sm sm:btn-md';
    }
}

function copyLink() {
    var link = document.getElementById('sponsorLink').textContent;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(function() {
            showToast('Lien de parrainage copie !');
        }).catch(function() {
            fallbackCopy(link);
        });
    } else {
        fallbackCopy(link);
    }
}

function fallbackCopy(text) {
    var input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    showToast('Lien de parrainage copie !');
}

function showToast(message) {
    var toast = document.createElement('div');
    toast.className = 'fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 px-4 sm:px-6 py-3 rounded-lg bg-green-500 text-white font-medium shadow-lg z-50 transform transition-all duration-500';
    toast.style.animation = 'fadeInUp 0.3s ease forwards';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(function() { toast.remove(); }, 500);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchMember');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var query = this.value.trim().toLowerCase();
            var rows = document.querySelectorAll('#memberList tr');
            
            rows.forEach(function(row) {
                var name = row.dataset.name || '';
                var email = row.dataset.email || '';
                row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
@endsection