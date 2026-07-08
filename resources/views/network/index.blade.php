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
        flex-wrap: wrap;
        justify-content: center;
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
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
        position: relative;
        color: white;
    }
    .avatar-xl { width: 4.5rem; height: 4.5rem; font-size: 1.5rem; }
    .avatar-lg { width: 3.5rem; height: 3.5rem; font-size: 1.25rem; }
    .avatar-md { width: 2.5rem; height: 2.5rem; font-size: 0.875rem; }
    .avatar-sm { width: 2rem; height: 2rem; font-size: 0.75rem; }
    .avatar-gradient { background: var(--gradient-primary); }
    .avatar-success { background: #22c55e; }
    .avatar-danger { background: #ef4444; }
    .avatar-ring { border: 3px solid var(--primary-500); box-shadow: 0 0 0 4px rgba(90, 182, 56, 0.15); }
    .avatar img { width: 100%; height: 100%; object-fit: cover; }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    .card-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-sm { padding: 0.375rem 1rem; font-size: 0.75rem; }
    .btn-md { padding: 0.625rem 1.5rem; font-size: 0.875rem; }
    
    .input {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    
    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; }
    .table thead th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }
    
    .hidden { display: none; }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    
    .custom-toast {
        animation: slideUp 0.3s ease forwards;
        position: fixed;
        bottom: 1rem;
        left: 1rem;
        right: 1rem;
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        background: #22c55e;
        color: white;
        font-weight: 500;
        font-size: 0.875rem;
        box-shadow: var(--shadow-lg);
        z-index: 9999;
    }
    @media (min-width: 640px) {
        .custom-toast { left: auto; right: 1rem; max-width: 400px; }
    }
    
    @media (max-width: 640px) {
        .tree-children { flex-direction: column; align-items: center; gap: 0.5rem; }
        .tree-children::before { display: none; }
        .tree-node::before { display: none; }
        .avatar-xl { width: 3.5rem; height: 3.5rem; font-size: 1.2rem; }
        .avatar-lg { width: 2.5rem; height: 2.5rem; font-size: 0.8rem; }
        .card { padding: 0.875rem; }
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .btn-sm svg { width: 0.875rem; height: 0.875rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
        .stats-grid { grid-template-columns: 1fr 1fr !important; }
        .tree-header { flex-direction: column; align-items: flex-start !important; }
        .tree-header .btn-group { margin-left: 0 !important; margin-top: 0.5rem; }
    }
    
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr !important; }
        .card { padding: 0.75rem; }
        .tree-view { padding: 0.5rem; }
        .avatar-xl { width: 3rem; height: 3rem; font-size: 1rem; }
        .avatar-lg { width: 2rem; height: 2rem; font-size: 0.7rem; }
        .tree-node { padding: 0.25rem; }
        .tree-children { gap: 0.25rem; }
        .tree-level-badge { font-size: 0.4rem; padding: 0.05rem 0.3rem; top: -0.3rem; right: -0.3rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="tree-header flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Réseau</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Visualisez votre arbre généalogique</p>
        </div>
        <div class="btn-group flex gap-1.5 sm:gap-2">
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
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Filleuls</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">
                {{ $filleuls->count() }}
            </p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Niveau 1</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ $stats['level_1'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Niveau 2</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $stats['level_2'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Niveau 3</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $stats['level_3'] ?? 0 }}</p>
        </div>
    </div>

    <!--  Carte du Parrain -->
    <div class="card animate-fadeInUp delay-2 p-3 sm:p-4 border-l-4 border-primary-500">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Mon Parrain</p>
                @if($parrain)
                    <div class="flex items-center gap-3 mt-1">
                        <div class="avatar avatar-md avatar-gradient">
                            {{ strtoupper(substr($parrain->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                                {{ $parrain->name }}
                            </p>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                                {{ $parrain->email }}
                            </p>
                        </div>
                    </div>
                @else
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                        Aucun parrain
                    </p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                        Vous êtes le premier de votre réseau
                    </p>
                @endif
            </div>
            @if($parrain)
                <span class="badge badge-success text-[10px] sm:text-xs">Parrainé</span>
            @endif
        </div>
    </div>

    <!--  Carte des Filleuls -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Mes Filleuls</p>
                <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    {{ $filleuls->count() }} personne(s) invitée(s)
                </p>
            </div>
            <span class="badge badge-info text-[10px] sm:text-xs">
                {{ $filleuls->count() }} filleuls
            </span>
        </div>

        @if($filleuls->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3">
                @foreach($filleuls as $filleul)
                    <div class="flex items-center gap-2 p-2 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                        <div class="avatar avatar-sm {{ $filleul->is_active ? 'avatar-success' : 'avatar-danger' }}">
                            {{ strtoupper(substr($filleul->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-[var(--text-primary)] text-xs sm:text-sm truncate">
                                {{ $filleul->name }}
                            </p>
                            <p class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] truncate">
                                {{ $filleul->email }}
                            </p>
                            <p class="text-[8px] sm:text-[10px] text-[var(--text-tertiary)] font-mono">
                                Code: {{ $filleul->sponsor_id }}
                            </p>
                        </div>
                        <span class="badge {{ $filleul->is_active ? 'badge-success' : 'badge-danger' }} text-[8px] sm:text-[10px]">
                            {{ $filleul->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-[var(--text-secondary)] text-sm">
                <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p>Vous n'avez pas encore de filleuls</p>
                <p class="text-[var(--text-tertiary)] text-xs mt-1">
                    Partagez votre lien de parrainage pour inviter des amis
                </p>
            </div>
        @endif
    </div>

    <!-- Vue Arbre -->
    <div id="treeView" class="tree-view card animate-fadeInUp delay-4 p-3 sm:p-4 md:p-6">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Arbre Généalogique</h3>
        
        <div class="flex justify-center overflow-x-auto py-3 sm:py-4">
            <div class="tree-node">
                <!-- Moi -->
                <div class="flex flex-col items-center">
                    <div class="avatar avatar-xl avatar-gradient avatar-ring relative">
                        @if(Auth::user()->avatar && file_exists(public_path('storage/avatars/' . Auth::user()->avatar)))
                            <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" alt="Avatar">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        @endif
                        <span class="tree-level-badge">★</span>
                    </div>
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mt-1 sm:mt-2">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Moi</p>
                    <span class="badge badge-success text-[10px] sm:text-xs mt-0.5 sm:mt-1">{{ Auth::user()->rank?->name ?? 'Distributor' }}</span>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5">
                        Code: <span class="font-mono text-primary-500 font-semibold">{{ Auth::user()->sponsor_id }}</span>
                    </p>
                    @if($parrain)
                        <p class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] mt-0.5">
                            Parrain: <span class="font-medium text-primary-500">{{ $parrain->name }}</span>
                        </p>
                    @endif
                </div>

                <!-- Enfants -->
                @if($filleuls->count() > 0)
                    <div class="tree-children">
                        @foreach($filleuls->take(6) as $member)
                            <div class="tree-node">
                                <div class="flex flex-col items-center">
                                    <div class="avatar avatar-lg {{ $member->is_active ? 'avatar-success' : 'avatar-danger' }} relative">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                        <span class="tree-level-badge">1</span>
                                    </div>
                                    <p class="text-xs sm:text-sm font-medium text-[var(--text-primary)] mt-0.5 sm:mt-1 truncate max-w-[60px] sm:max-w-[80px]">{{ $member->name }}</p>
                                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Niveau 1</p>
                                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs mt-0.5">
                                        {{ $member->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                    <p class="text-[8px] sm:text-[10px] text-[var(--text-tertiary)] mt-0.5 font-mono truncate max-w-[60px]">
                                        {{ $member->sponsor_id }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                        @if($filleuls->count() > 6)
                            <div class="tree-node">
                                <div class="flex flex-col items-center justify-center h-full min-h-[80px]">
                                    <div class="avatar avatar-lg bg-primary-500/20 text-primary-500">
                                        +{{ $filleuls->count() - 6 }}
                                    </div>
                                    <p class="text-xs text-[var(--text-secondary)] mt-1">Voir plus</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-center text-[var(--text-secondary)] py-6 sm:py-8 mt-3 sm:mt-4 text-sm sm:text-base">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Vous n'avez pas encore de filleuls.<br>
                        Partagez votre lien de parrainage pour développer votre réseau !
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Vue Liste -->
    <div id="listView" class="card animate-fadeInUp delay-5 hidden p-3 sm:p-4 md:p-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Membres de mon réseau</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $filleuls->count() }} membres</span>
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
                    @forelse($filleuls as $member)
                        <tr data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}">
                            <td class="font-medium text-sm sm:text-base">
                                {{ $member->name }}
                                <span class="text-[8px] sm:text-[10px] text-[var(--text-tertiary)] block font-mono">
                                    Code: {{ $member->sponsor_id }}
                                </span>
                            </td>
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
                                Aucun membre dans votre réseau
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lien de parrainage -->
    <div class="card animate-fadeInUp delay-6 border-l-4 border-primary-500 p-3 sm:p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Votre lien de parrainage</p>
                <p class="text-xs sm:text-sm font-semibold text-primary-500 break-all" id="sponsorLink">
                    {{ url('/register?ref=' . Auth::user()->sponsor_id) }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">
                    Code de parrain: <span class="font-mono text-primary-500 font-semibold">{{ Auth::user()->sponsor_id }}</span>
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
            showToast('Lien de parrainage copié !');
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
    showToast('Lien de parrainage copié !');
}

function showToast(message) {
    document.querySelectorAll('.custom-toast').forEach(function(el) { el.remove(); });
    
    var toast = document.createElement('div');
    toast.className = 'custom-toast';
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