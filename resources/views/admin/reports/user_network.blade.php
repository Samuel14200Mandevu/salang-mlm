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

.tree-node {
    border-left: 2px solid var(--border-color);
    padding-left: 1.25rem;
    margin-left: 1.25rem;
    position: relative;
}
.tree-node:last-child {
    border-left: 2px solid transparent;
}
.user-node {
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    margin: 0.25rem 0;
    display: inline-block;
    background: var(--bg-card);
}
.user-node:hover {
    border-color: var(--primary-blue);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.user-node.root {
    border-color: var(--primary-blue);
    background: var(--primary-blue-bg);
    border-width: 2px;
}

.stat-card {
    transition: box-shadow 0.15s ease;
}
.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.ancestor-item {
    background: rgba(181, 71, 8, 0.08);
    border: 1px solid rgba(181, 71, 8, 0.2);
    border-radius: 6px;
    padding: 0.25rem 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.125rem 0.25rem;
    font-size: 0.813rem;
}
.ancestor-item .ancestor-rank {
    font-weight: 600;
    color: #B54708;
}
.ancestor-item .ancestor-name {
    font-weight: 500;
    color: var(--text-primary);
}
.ancestor-item .ancestor-pv {
    color: var(--text-tertiary);
    font-size: 0.75rem;
}

.tree-connector {
    display: inline-block;
    margin: 0 0.5rem;
    color: var(--text-tertiary);
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-primary { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }
.badge-info { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }
.badge-secondary { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }

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

.progress-bar {
    width: 100%;
    height: 0.375rem;
    background: var(--bg-secondary);
    border-radius: 9999px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    border-radius: 9999px;
    background: var(--primary-blue);
    transition: width 0.6s ease;
}
.progress-fill.bg-green-500 { background: #1C7E4A; }
.progress-fill.bg-yellow-500 { background: #B54708; }
.progress-fill.bg-red-500 { background: #B91C1C; }
.progress-fill.bg-gray-500 { background: var(--text-tertiary); }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }

@media (max-width: 640px) {
    .card { padding: 0.875rem; }
    .tree-node {
        padding-left: 0.75rem;
        margin-left: 0.75rem;
    }
    .user-node { padding: 0.375rem 0.5rem; font-size: 0.813rem; }
    .ancestor-item { font-size: 0.75rem; padding: 0.125rem 0.5rem; }
    .tree-connector { margin: 0 0.25rem; }
    .grid-cols-2 { grid-template-columns: 1fr !important; }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Réseau de {{ $user->name }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Arbre généalogique complet — {{ $stats['total_descendants'] }} descendants
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.user-network.pdf', $user->id) }}"
               class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                PDF
            </a>
            <a href="{{ route('admin.reports.user-network') }}"
               class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Rechercher
            </a>
            <a href="{{ route('admin.reports.users') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- User Information -->
    <div class="card p-3 sm:p-4 animate-fadeInUp delay-1">
        <div class="flex flex-wrap items-start gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-[var(--primary-blue)] flex items-center justify-center text-white text-xl font-semibold flex-shrink-0">
                    {{ substr($user->name, 0, 2) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-[var(--text-primary)]">{{ $user->name }}</h2>
                    <p class="text-sm text-[var(--text-secondary)]">{{ $user->email }}</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        <span class="badge badge-primary">{{ $user->rank?->name ?? 'Distributeur' }}</span>
                        <span class="badge badge-info">{{ $user->package?->name ?? 'Aucun package' }}</span>
                        <span class="badge badge-secondary">Code: {{ $user->sponsor_id }}</span>
                    </div>
                </div>
            </div>
            <div class="ml-auto grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-[var(--text-secondary)]">PV personnel</p>
                    <p class="font-bold text-[var(--primary-blue)]">{{ number_format($user->pv_balance ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-[var(--text-secondary)]">PV équipe</p>
                    <p class="font-bold text-[#1C7E4A]">{{ number_format($user->team_pv ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-[var(--text-secondary)]">Gains totaux</p>
                    <p class="font-bold text-[#B54708]">${{ number_format($user->total_earnings ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-[var(--text-secondary)]">Parrainages</p>
                    <p class="font-bold text-[var(--primary-blue)]">{{ number_format($user->total_sponsors ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Genealogy Tree -->
    <div class="card p-3 sm:p-4 animate-fadeInUp delay-1">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Arbre généalogique</h3>
            <div class="flex gap-3 text-xs text-[var(--text-secondary)]">
                <span><span class="inline-block w-3 h-3 rounded-full bg-[#1C7E4A] mr-1"></span> Actif</span>
                <span><span class="inline-block w-3 h-3 rounded-full bg-[#B91C1C] mr-1"></span> Inactif</span>
                <span><span class="inline-block w-3 h-3 rounded-full bg-[#065F9C] mr-1"></span> Avec package</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <!-- Ancestors -->
            @if(!empty($networkData['ancestors']))
                <div class="mb-4 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <p class="text-sm font-medium text-[var(--text-secondary)] mb-2">⬆ Ancêtres ({{ count($networkData['ancestors']) }})</p>
                    <div class="flex flex-wrap items-center">
                        @foreach(array_reverse($networkData['ancestors']) as $index => $ancestor)
                            <div class="ancestor-item">
                                <span class="ancestor-rank">{{ $ancestor['relationship'] ?? 'Parrain' }}</span>
                                <span class="ancestor-name">{{ $ancestor['user']['name'] ?? 'N/A' }}</span>
                                <span class="ancestor-pv">({{ $ancestor['user']['rank'] ?? 'Distributeur' }})</span>
                                <span class="ancestor-pv">PV: {{ number_format($ancestor['user']['pv_balance'] ?? 0) }}</span>
                            </div>
                            @if(!$loop->last)
                                <span class="tree-connector">→</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Root User -->
            <div class="mb-3 text-center">
                <div class="user-node root inline-block">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-[var(--primary-blue)]">{{ $user->name }}</span>
                        <span class="badge badge-primary">{{ $user->rank?->name ?? 'Distributeur' }}</span>
                        <span class="text-sm text-[var(--text-secondary)]">PV: {{ number_format($user->pv_balance ?? 0) }}</span>
                        <span class="badge badge-secondary">Racine</span>
                    </div>
                </div>
            </div>

            <!-- Descendants -->
            @if(!empty($networkData['children']))
                <div class="tree-container mt-3">
                    @php
                        function renderTreeNodes($nodes, $depth = 0) {
                            $html = '';
                            foreach ($nodes as $node) {
                                $user = $node['user'];
                                $isActive = $user['is_active'];
                                $hasPackage = !empty($user['package']) && $user['package'] !== 'Aucun';

                                $html .= '<div class="tree-node">';
                                $html .= '<div class="user-node" style="border-left: 3px solid ' . ($hasPackage ? '#065F9C' : 'var(--border-color)') . ';">';
                                $html .= '<div class="flex flex-wrap items-center gap-2">';
                                $html .= '<span class="font-medium">' . e($user['name']) . '</span>';
                                $html .= '<span class="badge ' . ($isActive ? 'badge-success' : 'badge-danger') . ' text-xs">' . ($isActive ? 'Actif' : 'Inactif') . '</span>';
                                $html .= '<span class="text-xs text-[var(--text-secondary)]">' . e($user['rank']) . '</span>';
                                if ($hasPackage) {
                                    $html .= '<span class="badge badge-info text-xs">' . e($user['package']) . '</span>';
                                }
                                $html .= '<span class="text-xs text-[var(--text-tertiary)]">PV: ' . number_format($user['pv_balance'] ?? 0) . '</span>';
                                $html .= '<span class="badge badge-secondary text-xs">Niv ' . ($depth + 1) . '</span>';
                                if ($node['is_direct'] ?? false) {
                                    $html .= '<span class="badge badge-warning text-xs">Direct</span>';
                                }
                                if (!empty($node['children'])) {
                                    $html .= '<span class="text-xs text-[var(--text-tertiary)]">(' . count($node['children']) . ' filleuls)</span>';
                                }
                                $html .= '</div>';
                                $html .= '</div>';

                                if (!empty($node['children'])) {
                                    $html .= renderTreeNodes($node['children'], $depth + 1);
                                }

                                $html .= '</div>';
                            }
                            return $html;
                        }
                    @endphp
                    {!! renderTreeNodes($networkData['children']) !!}
                </div>
            @else
                <p class="text-center text-[var(--text-secondary)] py-4 text-sm">
                    Aucun descendant dans le réseau
                </p>
            @endif
        </div>
    </div>

    <!-- Rank & KYC Distribution -->
    @if(!empty($stats['rank_distribution']) || !empty($stats['kyc_distribution']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-1">
            @if(!empty($stats['rank_distribution']))
                <div class="card p-3 sm:p-4">
                    <h4 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Distribution des grades</h4>
                    <div class="space-y-2">
                        @foreach($stats['rank_distribution'] as $rank => $count)
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-[var(--text-secondary)] w-24 sm:w-32">{{ $rank }}</span>
                                <div class="flex-1 progress-bar">
                                    <div class="progress-fill"
                                         style="width: {{ $stats['total_descendants'] > 0 ? ($count / $stats['total_descendants'] * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-[var(--text-primary)]">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($stats['kyc_distribution']))
                <div class="card p-3 sm:p-4">
                    <h4 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Statut KYC</h4>
                    <div class="space-y-2">
                        @foreach($stats['kyc_distribution'] as $status => $count)
                            @php
                                $colorClass = 'bg-gray-500';
                                if ($status == 'verified') $colorClass = 'bg-green-500';
                                elseif ($status == 'pending') $colorClass = 'bg-yellow-500';
                                elseif ($status == 'rejected') $colorClass = 'bg-red-500';
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-[var(--text-secondary)] w-24 sm:w-32">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                <div class="flex-1 progress-bar">
                                    <div class="progress-fill {{ $colorClass }}"
                                         style="width: {{ $stats['total_descendants'] > 0 ? ($count / $stats['total_descendants'] * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-[var(--text-primary)]">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection