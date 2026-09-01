@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.search-card {
    transition: box-shadow 0.15s ease, transform 0.1s ease;
}
.search-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.search-card:active {
    transform: scale(0.98);
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
    flex-shrink: 0;
    background: var(--primary-blue);
}

.avatar-1 { background: #0A2A6C; }
.avatar-2 { background: #065F9C; }
.avatar-3 { background: #1C7E4A; }
.avatar-4 { background: #B54708; }
.avatar-5 { background: #B91C1C; }

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    outline: none;
}
.input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}
.input::placeholder {
    color: var(--text-muted);
}

.result-count {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}
.result-count strong {
    color: var(--text-primary);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }

@media (max-width: 640px) {
    .card { padding: 0.875rem; }
    .user-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    .grid-cols-3 { grid-template-columns: 1fr !important; }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Rapport réseau d'un membre</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Recherchez un membre pour visualiser son arbre généalogique complet</p>
        </div>
        <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <!-- Search Form -->
    <div class="card p-3 sm:p-4 animate-fadeInUp delay-1">
        <form method="GET" action="{{ route('admin.reports.user-network.search') }}" class="flex flex-col md:flex-row gap-3">
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Rechercher
                </button>
            </div>
        </form>

        <!-- Search Results -->
        @if(request('search'))
            <div class="mt-4 border-t border-[var(--border-color)] pt-4">
                <p class="result-count">
                    <strong>{{ $users->count() }}</strong> membre(s) trouvé(s)
                </p>
                @if($users->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($users as $index => $user)
                            @php
                                $avatarClass = 'avatar-' . (($index % 5) + 1);
                            @endphp
                            <a href="{{ route('admin.reports.user-network.view', $user->id) }}"
                               class="card p-3 search-card transition-all cursor-pointer hover:border-[var(--primary-blue)]">
                                <div class="flex items-center gap-3">
                                    <div class="user-avatar {{ $avatarClass }}">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-[var(--text-primary)] truncate">{{ $user->name }}</p>
                                        <p class="text-sm text-[var(--text-secondary)] truncate">{{ $user->email }}</p>
                                        <p class="text-xs text-[var(--text-tertiary)]">Code: {{ $user->sponsor_id ?? 'N/A' }}</p>
                                    </div>
                                    <svg class="w-5 h-5 text-[var(--text-tertiary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-[var(--text-secondary)]">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-base sm:text-lg">Aucun membre trouvé</p>
                        <p class="text-sm mt-1">Essayez avec d'autres mots-clés</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Recent Users (shown only if no search) -->
    @if(!request('search') && isset($users) && $users->count() > 0)
        <div class="card p-3 sm:p-4 animate-fadeInUp delay-1">
            <h3 class="font-semibold text-[var(--text-primary)] mb-3">Derniers membres</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($users->take(12) as $index => $user)
                    @php
                        $avatarClass = 'avatar-' . (($index % 5) + 1);
                    @endphp
                    <a href="{{ route('admin.reports.user-network.view', $user->id) }}"
                       class="card p-3 search-card transition-all cursor-pointer hover:border-[var(--primary-blue)]">
                        <div class="flex items-center gap-3">
                            <div class="user-avatar {{ $avatarClass }}">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-[var(--text-primary)] truncate">{{ $user->name }}</p>
                                <p class="text-sm text-[var(--text-secondary)] truncate">{{ $user->email }}</p>
                                <p class="text-xs text-[var(--text-tertiary)]">Code: {{ $user->sponsor_id ?? 'N/A' }}</p>
                            </div>
                            <svg class="w-5 h-5 text-[var(--text-tertiary)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection