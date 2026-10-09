@extends('layouts.app')

@section('title', $member->name)

@section('content')
@php
    $rankInfo = $controller->getUserRankInfo($member);
    $rankName = $rankInfo['name'];
    $rankLevel = $rankInfo['level'];
    $rankColor = $controller->getRankColor($rankLevel);
    $memberSubtitle = $member->name . ' · Code ' . ($member->sponsor_id ?? '—');
@endphp
<div class="space-y-4 sm:space-y-6 network-page network-show-page">

    {{-- Desktop — même structure que admin/users/show --}}
    <div class="page-header network-show-desktop-page-head hidden md:block animate-fadeInUp">
        <div class="top-row">
            <div class="min-w-0">
                <h1 class="page-title">Profil du membre</h1>
                <p class="page-subtitle">{{ $memberSubtitle }}</p>
            </div>
            <div class="header-actions flex flex-wrap gap-2 items-center">
                <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>

    {{-- Mobile — bandeau --}}
    <div class="network-mobile-banner animate-fadeInUp md:hidden">
        <div class="shop-catalog-banner network-catalog-banner">
            <div class="shop-catalog-banner__text">
                <p class="shop-catalog-banner__eyebrow">Mon réseau</p>
                <p class="shop-catalog-banner__title">Profil du membre</p>
                <p class="shop-catalog-banner__sub">Détails et réseau de {{ $member->name }}</p>
            </div>
            <div class="shop-catalog-banner__tools">
                <a href="{{ route('network.index') }}" class="shop-banner-icon-btn" aria-label="Retour au réseau">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Mobile — résumé profil --}}
    <div class="profile-header animate-fadeInUp delay-1 md:hidden">
        <div class="flex flex-wrap items-start gap-4">
            <div class="avatar avatar-xxl avatar-gradient">
                {{ strtoupper(substr($member->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-[var(--text-primary)]">{{ $member->name }}</h2>
                <div class="flex flex-wrap gap-2 mt-1">
                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $member->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span class="badge badge-info">Niveau {{ $memberStats['level'] ?? 1 }}</span>
                    <span class="badge {{ $rankColor }}">{{ $rankName }}</span>
                </div>
                <div class="network-show-profile-meta grid grid-cols-1 gap-3 mt-3">
                    <div class="min-w-0">
                        <p class="text-xs text-[var(--text-secondary)]">Email</p>
                        <p class="text-sm font-medium break-all">{{ $member->email }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-[var(--text-secondary)]">Code de parrain</p>
                        <p class="text-sm font-mono text-primary-500 font-bold break-all">{{ $member->sponsor_id }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-[var(--text-secondary)]">Inscrit le</p>
                        <p class="text-sm">{{ $member->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="network-show-stats network-show-stats--mobile grid gap-2 animate-fadeInUp delay-2 md:hidden">
        <div class="card-stats p-3 text-center border-l-4 border-primary-500">
            <p class="text-[10px] text-[var(--text-secondary)] uppercase tracking-wider">Filleuls</p>
            <p class="text-lg font-bold text-primary-500">{{ $memberStats['total_downlines'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 text-center border-l-4 border-green-500">
            <p class="text-[10px] text-[var(--text-secondary)] uppercase tracking-wider">Actifs</p>
            <p class="text-lg font-bold text-green-500">{{ $memberStats['active_downlines'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 text-center border-l-4 border-blue-500">
            <p class="text-[10px] text-[var(--text-secondary)] uppercase tracking-wider">PV Total</p>
            <p class="text-lg font-bold text-blue-500">{{ number_format($memberStats['total_pv'] ?? 0) }}</p>
        </div>
        <div class="card-stats p-3 text-center border-l-4 border-purple-500">
            <p class="text-[10px] text-[var(--text-secondary)] uppercase tracking-wider">PV Personnel</p>
            <p class="text-lg font-bold text-purple-500">{{ number_format($member->pv_balance ?? 0) }}</p>
        </div>
    </div>

    {{-- Desktop — carte unique (informations + stats + filleuls) --}}
    <div class="network-show-desktop-panel hidden md:block animate-fadeInUp delay-1">
        <div class="card p-3 sm:p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-[var(--border-light)]">
                <div class="px-0 sm:px-4 py-2 sm:py-0">
                    <div class="network-show-info-row">
                        <span class="label">Nom complet</span>
                        <span class="value">{{ $member->name }}</span>
                    </div>
                    <div class="network-show-info-row">
                        <span class="label">Email</span>
                        <span class="value text-sm break-all">{{ $member->email }}</span>
                    </div>
                    <div class="network-show-info-row">
                        <span class="label">Niveau dans le réseau</span>
                        <span class="value">Niveau {{ $memberStats['level'] ?? 1 }}</span>
                    </div>
                </div>
                <div class="px-0 sm:px-4 py-2 sm:py-0">
                    <div class="network-show-info-row">
                        <span class="label">Statut</span>
                        <span class="value">
                            <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $member->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </span>
                    </div>
                    <div class="network-show-info-row">
                        <span class="label">Grade</span>
                        <span class="value">
                            <span class="badge {{ $rankColor }}">{{ $rankName }}</span>
                            <span class="text-xs text-[var(--text-tertiary)] font-normal ml-1">(Niv. {{ $rankLevel }})</span>
                        </span>
                    </div>
                    <div class="network-show-info-row">
                        <span class="label">Inscrit le</span>
                        <span class="value">{{ $member->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                <div class="px-0 sm:px-4 py-2 sm:py-0">
                    <div class="network-show-info-row">
                        <span class="label">Code de parrainage</span>
                        <span class="value font-mono text-primary-500 font-bold">{{ $member->sponsor_id ?? '—' }}</span>
                    </div>
                    <div class="network-show-info-row">
                        <span class="label">PV personnel</span>
                        <span class="value font-semibold font-mono text-green-600">{{ number_format($member->pv_balance ?? 0) }}</span>
                    </div>
                    <div class="network-show-info-row">
                        <span class="label">PV total équipe</span>
                        <span class="value font-semibold font-mono text-primary-500">{{ number_format($memberStats['total_pv'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="network-show-desktop-kpis mt-4 pt-4 border-t border-[var(--border-color)] grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-primary-500">{{ $memberStats['total_downlines'] ?? 0 }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">Filleuls</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-500">{{ $memberStats['active_downlines'] ?? 0 }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">Actifs</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-500">{{ number_format($memberStats['total_pv'] ?? 0) }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">PV Total</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-purple-500">{{ number_format($member->pv_balance ?? 0) }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">PV Personnel</p>
                </div>
            </div>

            @if($downlines->count() > 0)
                <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
                    <h4 class="network-show-section-title">
                        Filleuls ({{ $downlines->count() }})
                    </h4>
                    <div class="table-wrap mt-3">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="text-xs">Nom</th>
                                    <th class="text-xs">Code</th>
                                    <th class="text-xs">Grade</th>
                                    <th class="text-xs hidden sm:table-cell">Niveau</th>
                                    <th class="text-xs hidden sm:table-cell">PV</th>
                                    <th class="text-xs">Statut</th>
                                    <th class="text-xs">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($downlines as $downline)
                                    @php
                                        $dlRank = $controller->getUserRankInfo($downline);
                                        $dlRankName = $dlRank['name'];
                                        $dlRankLevel = $dlRank['level'];
                                        $dlRankColor = $controller->getRankColor($dlRankLevel);
                                        $level = $downline->level ?? 1;
                                    @endphp
                                    <tr class="cursor-pointer hover:bg-[var(--bg-hover)]" onclick="navigateToUser({{ $downline->id }})">
                                        <td class="text-sm font-medium">{{ $downline->name }}</td>
                                        <td class="text-xs font-mono text-primary-500">{{ $downline->sponsor_id }}</td>
                                        <td>
                                            <span class="badge {{ $dlRankColor }} text-[10px]">{{ $dlRankName }}</span>
                                        </td>
                                        <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)]">Niv. {{ $level }}</td>
                                        <td class="hidden sm:table-cell text-sm font-medium font-mono text-green-600">{{ number_format($downline->pv_balance ?? 0) }}</td>
                                        <td>
                                            <span class="badge {{ $downline->is_active ? 'badge-success' : 'badge-danger' }} text-[10px]">
                                                {{ $downline->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('network.show', $downline->id) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">
                                                Voir
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="mt-4 pt-4 border-t border-[var(--border-color)] text-center py-6 text-[var(--text-secondary)]">
                    <p class="font-medium">Aucun filleul</p>
                    <p class="text-sm text-[var(--text-tertiary)] mt-1">Ce membre n'a pas encore de filleuls dans son réseau</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Mobile — liste filleuls --}}
    <div class="card animate-fadeInUp delay-3 md:hidden">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm">
                Filleuls de {{ $member->name }}
                <span class="text-xs text-[var(--text-secondary)] font-normal">({{ $downlines->count() }})</span>
            </h3>
        </div>

        @if($downlines->count() > 0)
            <div class="space-y-2">
                @foreach($downlines as $downline)
                    @php
                        $rankInfo = $controller->getUserRankInfo($downline);
                        $rankName = $rankInfo['name'];
                        $rankLevel = $rankInfo['level'];
                        $rankColor = $controller->getRankColor($rankLevel);
                        $avatarColor = $controller->getAvatarColor($downline);
                        $level = $downline->level ?? 1;
                    @endphp
                    <div class="downline-card network-show-downline" onclick="navigateToUser({{ $downline->id }})">
                        <div class="network-show-downline-main">
                            <div class="avatar avatar-md {{ $avatarColor }}">
                                {{ strtoupper(substr($downline->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-[var(--text-primary)] text-sm truncate">{{ $downline->name }}</p>
                                <p class="network-show-downline-mobile-meta text-[10px] text-[var(--text-secondary)] mt-0.5 truncate">
                                    {{ number_format($downline->pv_balance ?? 0) }} PV · {{ $rankName }} · Niv. {{ $level }}
                                </p>
                            </div>
                        </div>
                        <button type="button" class="network-show-downline-btn" onclick="event.stopPropagation(); navigateToUser({{ $downline->id }})" aria-label="Voir le profil de {{ $downline->name }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-[var(--text-secondary)]">
                <p class="font-medium">Aucun filleul</p>
                <p class="text-sm text-[var(--text-tertiary)] mt-1">Ce membre n'a pas encore de filleuls dans son réseau</p>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function navigateToUser(userId) {
    window.location.href = @json(url('/network/show')) + '/' + userId;
}
</script>
@endpush
@endsection
