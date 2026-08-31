@extends('admin.layouts.app')

@push('styles')
<style>
    .search-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 600;
        color: white;
    }
    .result-count {
        font-size: 14px;
        color: var(--text-secondary);
        margin-bottom: 16px;
    }
    .result-count strong {
        color: var(--text-primary);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-[var(--text-primary)]">Rapport réseau d'un membre</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-1">Recherchez un membre pour visualiser son arbre généalogique complet</p>
        </div>
        <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <br>

    <!-- Formulaire de recherche -->
    <div class="card p-6">
        <form method="GET" action="{{ route('admin.reports.user-network.search') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="text-sm text-[var(--text-secondary)]">Rechercher un membre</label>
                <input type="text" 
                       name="search" 
                       id="userSearch" 
                       class="input w-full mt-1" 
                       placeholder="Nom, email ou code de parrainage..."
                       value="{{ request('search') }}"
                       autocomplete="off">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Rechercher
                </button>
            </div>
        </form>

        <br>

        <!-- Résultats de recherche -->
        @if(request('search'))
            <div class="mt-6 border-t border-[var(--border-color)] pt-4">
                <p class="result-count">
                    <strong>{{ $users->count() }}</strong> membre(s) trouvé(s)
                </p>
                @if($users->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($users as $user)
                            <a href="{{ route('admin.reports.user-network.view', $user->id) }}" 
                               class="card p-4 search-card transition-all cursor-pointer hover:border-primary-500 hover:shadow-lg">
                                <div class="flex items-center gap-4">
                                    <div class="user-avatar" style="background: {{ $user->id % 2 ? '#6366f1' : '#8b5cf6' }}">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-[var(--text-primary)] truncate">{{ $user->name }}</p>
                                        <p class="text-sm text-[var(--text-secondary)] truncate">{{ $user->email }}</p>
                                        <p class="text-xs text-[var(--text-tertiary)]">Code: {{ $user->sponsor_id ?? 'N/A' }}</p>
                                    </div>
                                    <svg class="w-5 h-5 text-[var(--text-tertiary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-[var(--text-secondary)]">
                        <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-lg">Aucun membre trouvé</p>
                        <p class="text-sm mt-1">Essayez avec d'autres mots-clés</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <br>

    <!-- Utilisateurs récents (affichés seulement si aucune recherche) -->
    @if(!request('search') && isset($users) && $users->count() > 0)
        <div class="card p-6">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4"> membres récents</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($users->take(12) as $user)
                    <a href="{{ route('admin.reports.user-network.view', $user->id) }}" 
                       class="card p-4 search-card transition-all cursor-pointer hover:border-primary-500 hover:shadow-lg">
                        <div class="flex items-center gap-4">
                            <div class="user-avatar" style="background: {{ $user->id % 2 ? '#6366f1' : '#8b5cf6' }}">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-[var(--text-primary)] truncate">{{ $user->name }}</p>
                                <p class="text-sm text-[var(--text-secondary)] truncate">{{ $user->email }}</p>
                                <p class="text-xs text-[var(--text-tertiary)]">Code: {{ $user->sponsor_id ?? 'N/A' }}</p>
                            </div>
                            <svg class="w-5 h-5 text-[var(--text-tertiary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection