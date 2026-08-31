@extends('admin.layouts.app')

@push('styles')
<style>
    .tree-node {
        border-left: 2px solid var(--border-color);
        padding-left: 20px;
        margin-left: 20px;
        position: relative;
    }
    .tree-node:last-child {
        border-left: 2px solid transparent;
    }
    .user-node {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        transition: all 0.2s;
        margin: 4px 0;
        display: inline-block;
        background: var(--bg-card);
    }
    .user-node:hover {
        border-color: var(--primary-500);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .user-node.root {
        border-color: var(--primary-500);
        background: var(--primary-50);
        border-width: 2px;
    }
    .stat-card {
        transition: all 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .ancestor-item {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 6px;
        padding: 4px 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 2px 4px;
    }
    .tree-connector {
        display: inline-block;
        margin: 0 8px;
        color: var(--text-tertiary);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-[var(--text-primary)]">
                Réseau de {{ $user->name }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-1">
                Arbre généalogique complet - {{ $stats['total_descendants'] }} descendants
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.user-network.pdf', $user->id) }}" 
               class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                PDF
            </a>
            <a href="{{ route('admin.reports.user-network') }}" 
               class="btn btn-outline btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Rechercher un autre membre
            </a>
            <a href="{{ route('admin.reports.users') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div><br>

    <!-- Informations du menbre -->
    <div class="card p-6">
        <div class="flex flex-wrap items-start gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-primary-500 flex items-center justify-center text-white text-2xl font-bold">
                    {{ substr($user->name, 0, 2) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[var(--text-primary)]">{{ $user->name }}</h2>
                    <p class="text-sm text-[var(--text-secondary)]">{{ $user->email }}</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        <span class="badge badge-primary">{{ $user->rank?->name ?? 'Distributeur' }}</span>
                        <span class="badge badge-info">{{ $user->package?->name ?? 'Aucun package' }}</span>
                        <span class="badge badge-secondary">Code: {{ $user->sponsor_id }}</span>
                    </div>
                </div>
            </div>
            <div class="ml-auto grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-[var(--text-secondary)]">PV personnel</p>
                    <p class="font-bold text-primary-500">{{ number_format($user->pv_balance ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-[var(--text-secondary)]">PV équipe</p>
                    <p class="font-bold text-success-500">{{ number_format($user->team_pv ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-[var(--text-secondary)]">Gains totaux</p>
                    <p class="font-bold text-warning-500">${{ number_format($user->total_earnings ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-[var(--text-secondary)]">Parrainages</p>
                    <p class="font-bold text-purple-500">{{ number_format($user->total_sponsors ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div><br>

    <!-- Arbre généalogique -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[var(--text-primary)]">Arbre généalogique</h3>
            <div class="flex gap-4 text-xs text-[var(--text-secondary)]">
                <span><span class="inline-block w-3 h-3 rounded-full bg-green-500 mr-1"></span> Actif</span>
                <span><span class="inline-block w-3 h-3 rounded-full bg-red-500 mr-1"></span> Inactif</span>
                <span><span class="inline-block w-3 h-3 rounded-full bg-blue-500 mr-1"></span> Avec package</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <!-- Ancêtres -->
@if(!empty($networkData['ancestors']))
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <p class="text-sm font-medium text-[var(--text-secondary)] mb-3">⬆ Ancêtres ({{ count($networkData['ancestors']) }})</p>
        <div class="flex flex-wrap items-center">
            @foreach(array_reverse($networkData['ancestors']) as $index => $ancestor)
                <div class="ancestor-item">
                    <span class="text-xs font-bold text-amber-700">{{ $ancestor['relationship'] ?? 'Parrain' }}</span>
                    <span class="font-medium">{{ $ancestor['user']['name'] ?? 'N/A' }}</span>
                    <span class="text-xs text-[var(--text-secondary)]">({{ $ancestor['user']['rank'] ?? 'Distributeur' }})</span>
                    <span class="text-xs text-[var(--text-tertiary)]">PV: {{ number_format($ancestor['user']['pv_balance'] ?? 0) }}</span>
                </div>
                @if(!$loop->last)
                    <span class="tree-connector">→</span>
                @endif
            @endforeach
        </div>
    </div>
@endif

            <u menbre racine -->
            <div class="mb-4 text-center">
                <div class="user-node root inline-block">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-primary-600"> {{ $user->name }}</span>
                        <span class="badge badge-primary">{{ $user->rank?->name ?? 'Distributeur' }}</span>
                        <span class="text-sm text-[var(--text-secondary)]">PV: {{ number_format($user->pv_balance ?? 0) }}</span>
                        <span class="badge badge-secondary">Racine</span>
                    </div>
                </div>
            </div>

            <!-- Descendants -->
            @if(!empty($networkData['children']))
                <div class="tree-container mt-4">
                    @php
                        function renderTreeNodes($nodes, $depth = 0) {
                            $html = '';
                            foreach ($nodes as $node) {
                                $user = $node['user'];
                                $isActive = $user['is_active'];
                                $hasPackage = !empty($user['package']) && $user['package'] !== 'Aucun';
                                
                                $html .= '<div class="tree-node">';
                                $html .= '<div class="user-node" style="border-left: 3px solid ' . ($hasPackage ? '#3b82f6' : 'var(--border-color)') . ';">';
                                $html .= '<div class="flex flex-wrap items-center gap-2">';
                                $html .= '<span class="font-medium">' . e($user['name']) . '</span>';
                                $html .= '<span class="badge ' . ($isActive ? 'badge-success' : 'badge-danger') . ' text-xs">' . ($isActive ? 'Actif' : 'Inactif') . '</span>';
                                $html .= '<span class="text-xs text-[var(--text-secondary)]">' . e($user['rank']) . '</span>';
                                if ($hasPackage) {
                                    $html .= '<span class="badge badge-info text-xs"> ' . e($user['package']) . '</span>';
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
                                
                                // filleuls
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
                <p class="text-center text-[var(--text-secondary)] py-8">
                    Aucun descendant dans le réseau
                </p>
            @endif
        </div>
    </div>

    <br>

    <!-- Distribution des grades -->
    @if(!empty($stats['rank_distribution']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h4 class="font-semibold text-[var(--text-primary)] mb-4">Distribution des grades</h4>
                <div class="space-y-2">
                    @foreach($stats['rank_distribution'] as $rank => $count)
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-[var(--text-secondary)] w-32">{{ $rank }}</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full" 
                                     style="width: {{ $stats['total_descendants'] > 0 ? ($count / $stats['total_descendants'] * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-[var(--text-primary)]">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Distribution KYC -->
            @if(!empty($stats['kyc_distribution']))
            <div class="card p-6">
                <h4 class="font-semibold text-[var(--text-primary)] mb-4">Statut KYC</h4>
                <div class="space-y-2">
                    @foreach($stats['kyc_distribution'] as $status => $count)
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-[var(--text-secondary)] w-32">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full 
                                    @if($status == 'verified') bg-green-500
                                    @elseif($status == 'pending') bg-yellow-500
                                    @elseif($status == 'rejected') bg-red-500
                                    @else bg-gray-500 @endif"
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