@extends('admin.layouts.app')

@section('title', 'Activations en attente')

@section('content')
@php
    $searchOpen = filled(request('search'));
    $activationsSubtitle = ($stats['total_inactive'] ?? 0).' comptes inactifs'
        .(request('search') ? ' · « '.request('search').' »' : '');
@endphp

    {{-- Mobile : bandeau Activations + outils --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1>Activations</h1>
                <p>{{ $activationsSubtitle }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <button type="button"
                        class="admin-banner-icon-btn{{ $searchOpen ? ' is-active' : '' }}"
                        data-admin-search-toggle="1"
                        aria-controls="adminActivationsSearchPanel"
                        aria-expanded="{{ $searchOpen ? 'true' : 'false' }}"
                        aria-label="Rechercher">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <a href="{{ route('admin.users', ['filter' => 'inactive']) }}"
                   class="btn btn-outline btn-sm btn-round-icon"
                   title="Voir tous les inactifs"
                   aria-label="Voir tous les inactifs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
            </div>
        </div>

        <div id="adminActivationsSearchPanel"
             class="admin-search-panel{{ $searchOpen ? ' is-open' : '' }}"
             data-admin-search-panel="1"
             aria-hidden="{{ $searchOpen ? 'false' : 'true' }}">
            <form method="GET" action="{{ route('admin.activations.index') }}" class="search-wrapper admin-shop-search">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search"
                       name="search"
                       class="search-input"
                       placeholder="Nom, e-mail ou code parrain…"
                       autocomplete="off"
                       value="{{ request('search') }}"
                       aria-label="Rechercher une activation">
            </form>
        </div>
    </div>

    {{-- Desktop : titre + recherche + action --}}
    <div class="admin-page-header page-header header-with-search hidden md:flex animate-fadeInUp">
        <div class="header-left admin-mobile-page-head">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Activations en attente</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Comptes inactifs — générer un code, renvoyer un e-mail ou activer manuellement.
            </p>
        </div>
        <div class="header-right admin-page-header-actions flex items-center gap-2">
            <form method="GET" action="{{ route('admin.activations.index') }}" class="header-search admin-search-full">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search"
                           name="search"
                           class="search-input"
                           placeholder="Nom, e-mail ou code parrain…"
                           autocomplete="off"
                           value="{{ request('search') }}"
                           aria-label="Rechercher une activation">
                </div>
            </form>
            <a href="{{ route('admin.users', ['filter' => 'inactive']) }}" class="btn btn-outline btn-sm">
                Voir tous les inactifs (membres)
            </a>
        </div>
    </div>

@if(session('success'))
    <div class="mb-4 p-3 rounded-lg border border-green-200 bg-green-50 text-green-800 text-sm whitespace-pre-line">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-3 rounded-lg border border-red-200 bg-red-50 text-red-800 text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-lg p-4">
        <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)] font-semibold">Inactifs</p>
        <p class="text-2xl font-bold text-[var(--text-primary)] mt-1">{{ number_format($stats['total_inactive'] ?? 0) }}</p>
    </div>
    <div class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-lg p-4">
        <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)] font-semibold">Avec code</p>
        <p class="text-2xl font-bold text-[var(--primary-blue)] mt-1">{{ number_format($stats['with_code'] ?? 0) }}</p>
    </div>
    <div class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-lg p-4">
        <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)] font-semibold">Sans code</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['without_code'] ?? 0) }}</p>
    </div>
</div>

<div class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[var(--bg-secondary)] border-b border-[var(--border-color)]">
                <tr class="text-left text-xs uppercase tracking-wide text-[var(--text-tertiary)]">
                    <th class="px-3 py-2.5 font-semibold">Membre</th>
                    <th class="px-3 py-2.5 font-semibold hidden md:table-cell">Parrain</th>
                    <th class="px-3 py-2.5 font-semibold">Code</th>
                    <th class="px-3 py-2.5 font-semibold hidden sm:table-cell">Package</th>
                    <th class="px-3 py-2.5 font-semibold hidden lg:table-cell">Inscrit le</th>
                    <th class="px-3 py-2.5 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border-color)]">
                @forelse($inactiveUsers as $user)
                    <tr class="hover:bg-[var(--bg-secondary)]/60">
                        <td class="px-3 py-3">
                            <a href="{{ route('admin.activations.show', $user->id) }}" class="font-medium text-[var(--text-primary)] hover:text-[var(--primary-blue)]">
                                {{ $user->name }}
                            </a>
                            <p class="text-xs text-[var(--text-secondary)] truncate max-w-[14rem]">{{ $user->email }}</p>
                            <p class="text-[10px] text-[var(--text-tertiary)] font-mono mt-0.5">{{ $user->sponsor_id }}</p>
                        </td>
                        <td class="px-3 py-3 hidden md:table-cell text-[var(--text-secondary)]">
                            #{{ $user->parrain_id ?? '—' }}
                        </td>
                        <td class="px-3 py-3">
                            @if($user->activation_code)
                                <span class="font-mono text-xs text-[var(--primary-blue)]">{{ $user->activation_code }}</span>
                                @if($user->activation_code_expires_at)
                                    <p class="text-[10px] text-[var(--text-tertiary)] mt-0.5">
                                        exp. {{ \Carbon\Carbon::parse($user->activation_code_expires_at)->format('d/m/Y') }}
                                    </p>
                                @endif
                            @else
                                <span class="text-xs text-amber-600 font-medium">Aucun</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 hidden sm:table-cell text-[var(--text-secondary)]">
                            {{ $user->activationPackage?->name ?? $user->package?->name ?? '—' }}
                        </td>
                        <td class="px-3 py-3 hidden lg:table-cell text-[var(--text-secondary)] text-xs">
                            {{ optional($user->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.activations.show', $user->id) }}" class="btn btn-outline btn-sm">Détail</a>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-primary btn-sm">Fiche</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-10 text-center text-[var(--text-secondary)]">
                            Aucun compte inactif
                            @if(request('search'))
                                pour « {{ request('search') }} »
                            @endif
                            .
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($inactiveUsers->hasPages())
        <div class="px-3 py-3 border-t border-[var(--border-color)]">
            {{ $inactiveUsers->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
