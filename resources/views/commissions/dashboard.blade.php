{{-- resources/views/commissions/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Tableau des commissions')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Tableau de bord des commissions</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Vue d'ensemble de vos gains</p>
        </div>
        <div class="flex flex-wrap gap-1.5 sm:gap-2">
            <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Toutes les commissions
            </a>
            <a href="{{ route('commissions.levels') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                Par niveau
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stat-grid grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="stat-card border-l-4 border-primary-500">
            <div class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="stat-label">Total gagné</p>
            <p class="stat-number text-primary-500">${{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">{{ $stats['total_count'] ?? 0 }} commissions</p>
        </div>
        
        <div class="stat-card border-l-4 border-success-500">
            <div class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="stat-label">Payé</p>
            <p class="stat-number text-success-500">${{ number_format($stats['paid_amount'] ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                {{ $stats['paid_count'] ?? 0 }} commissions
                @if(($stats['paid_offline_historical'] ?? 0) > 0)
                    · historique ${{ number_format($stats['paid_offline_historical'], 2) }}
                @endif
            </p>
        </div>
        
        <div class="stat-card border-l-4 border-warning-500">
            <div class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="stat-label">En attente (payable)</p>
            <p class="stat-number text-warning-500">${{ number_format($stats['pending_amount'] ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">{{ $stats['pending_count'] ?? 0 }} commissions · bande B</p>
        </div>
        
        <div class="stat-card border-l-4 border-purple-500">
            <div class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="stat-label">Moyenne</p>
            <p class="stat-number text-purple-500">${{ number_format($avgCommission ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">par commission</p>
        </div>
    </div>

    @if(!empty($commissionBands))
    <div class="card animate-fadeInUp delay-1 mb-3">
        <div class="card-header">
            <h5>Synthèse bandes</h5>
            <span class="text-xs text-[var(--text-secondary)]">Périodes masquées (C) exclues de l’affichage membre</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            <div>
                <p class="text-[var(--text-secondary)] uppercase text-xs font-semibold">A — Historique payé</p>
                <p class="font-bold text-success-500">${{ number_format($commissionBands['paid_offline_historical'] ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-[var(--text-secondary)] uppercase text-xs font-semibold">B — À payer (système)</p>
                <p class="font-bold text-warning-500">${{ number_format($commissionBands['pending_payable'] ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-[var(--text-secondary)] uppercase text-xs font-semibold">Payé via wallet</p>
                <p class="font-bold text-primary-500">${{ number_format($commissionBands['paid_system'] ?? 0, 2) }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Répartition par type -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 animate-fadeInUp delay-2">
        <div class="card lg:col-span-2">
            <div class="card-header">
                <h5>Répartition par type</h5>
                <span class="text-xs text-[var(--text-secondary)]">{{ $stats['total_count'] ?? 0 }} commissions</span>
            </div>
            <div class="space-y-3">
                @foreach($byType as $key => $data)
                    @php
                        $total = $stats['total_amount'] ?? 1;
                        $percent = $total > 0 ? round($data['total'] / $total * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="type-badge type-badge-{{ $key }}">
                                    {{ $data['label'] }}
                                </span>
                                <span class="text-xs text-[var(--text-secondary)]">{{ $data['count'] }} commissions</span>
                            </div>
                            <span class="font-bold text-{{ $data['color'] }}-500">
                                ${{ number_format($data['total'], 2) }}
                                <span class="text-xs text-[var(--text-secondary)] font-normal">({{ $percent }}%)</span>
                            </span>
                        </div>
                        <div class="progress-bar mt-1">
                            <div class="fill bg-{{ $data['color'] }}-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Meilleur mois -->
        <div class="card">
            <div class="card-header">
                <h5>Meilleur mois</h5>
            </div>
            @if($bestMonth)
                <div class="text-center py-4">
                    <p class="text-2xl font-bold text-primary-500">
                        ${{ number_format($bestMonth->total, 2) }}
                    </p>
                    <p class="text-sm text-[var(--text-secondary)]">
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $bestMonth->month)->format('F Y') }}
                    </p>
                    <div class="mt-2 inline-block px-3 py-1 bg-primary-500/10 rounded-full text-xs text-primary-500">
                        Meilleur mois
                    </div>
                </div>
            @else
                <p class="text-center text-[var(--text-secondary)] py-4">Aucune donnée</p>
            @endif
            
            <div class="border-t border-[var(--border-color)] pt-3 mt-3">
                <div class="flex justify-between text-sm">
                    <span class="text-[var(--text-secondary)]">Total commissions</span>
                    <span class="font-bold">{{ $stats['total_count'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between text-sm mt-1">
                    <span class="text-[var(--text-secondary)]">Taux de paiement</span>
                    <span class="font-bold text-success-500">
                        @php
                            $total = $stats['total_count'] ?? 1;
                            $paid = $stats['paid_count'] ?? 0;
                            echo round($paid / $total * 100, 1) . '%';
                        @endphp
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Évolution mensuelle -->
    <div class="card animate-fadeInUp delay-3">
        <div class="card-header">
            <h5>Évolution mensuelle</h5>
            <span class="text-xs text-[var(--text-secondary)]">12 derniers mois</span>
        </div>
        <div class="h-48 sm:h-56 flex items-end gap-1 sm:gap-2">
            @php 
                $max = max(array_column($monthly ?? [], 'total') ?: [1]);
                $colors = ['#5ab638', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#3d8a2a'];
            @endphp
            @foreach($monthly as $index => $data)
                @php 
                    $height = $max > 0 ? ($data['total'] / $max) * 100 : 0;
                    $height = max(4, min(100, $height));
                    $color = $colors[$index % count($colors)];
                @endphp
                <div class="flex-1 flex flex-col items-center group relative">
                    <div class="w-full rounded-t-sm transition-all duration-300 hover:opacity-80"
                         style="height: {{ $height }}%; background: {{ $color }}; min-height: 4px;">
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 
                                    bg-[var(--bg-card)] border border-[var(--border-color)] 
                                    rounded-md px-2 py-1 text-xs opacity-0 group-hover:opacity-100 
                                    transition-opacity whitespace-nowrap">
                            ${{ number_format($data['total'], 2) }}
                            <br>
                            <span class="text-[10px] text-[var(--text-secondary)]">{{ $data['count'] }} commissions</span>
                        </div>
                    </div>
                    <span class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] mt-1">
                        {{ substr($data['label'], 0, 3) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Dernières commissions + Top parrains -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 animate-fadeInUp delay-4">
        <div class="card">
            <div class="card-header">
                <h5>Dernières commissions</h5>
                <a href="{{ route('commissions.index') }}" class="text-xs text-primary-500 hover:underline">
                    Voir tout
                </a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>De</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $commission)
                            <tr>
                                <td>
                                    <span class="type-badge type-badge-{{ $commission->type }}">
                                        {{ ucfirst($commission->type) }}
                                    </span>
                                </td>
                                <td class="font-bold text-success-500">
                                    +${{ number_format($commission->amount, 2) }}
                                </td>
                                <td class="text-[var(--text-secondary)] text-sm">
                                    {{ $commission->fromUser?->name ?? 'Système' }}
                                </td>
                                <td class="text-[var(--text-secondary)] text-xs">
                                    {{ $commission->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-[var(--text-secondary)] py-4">
                                    Aucune commission récente
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top parrains -->
        <div class="card">
            <div class="card-header">
                <h5>Meilleurs parrains</h5>
                <span class="text-xs text-[var(--text-secondary)]">Top 10</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Parrain</th>
                            <th>Comm.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topReferrals as $index => $referral)
                            <tr>
                                <td>
                                    @if($index == 0) 1
                                    @elseif($index == 1) 2
                                    @elseif($index == 2) 3
                                    @else {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="avatar avatar-gradient">
                                            {{ substr($referral->fromUser?->name ?? 'N/A', 0, 2) }}
                                        </div>
                                        <span class="text-sm">{{ $referral->fromUser?->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">{{ $referral->count }}</td>
                                <td class="font-bold text-primary-500">
                                    ${{ number_format($referral->total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-[var(--text-secondary)] py-4">
                                    Aucune donnée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Commissions en attente -->
    @if($pending->count() > 0)
    <div class="card animate-fadeInUp delay-5 border-l-4 border-warning-500">
        <div class="card-header">
            <h5>Commissions en attente ({{ $pending->count() }})</h5>
            <span class="text-xs text-[var(--text-secondary)]">Total: ${{ number_format($pending->sum('amount'), 2) }}</span>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>De</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pending as $commission)
                        <tr>
                            <td>
                                <span class="type-badge type-badge-{{ $commission->type }}">
                                    {{ ucfirst($commission->type) }}
                                </span>
                            </td>
                            <td class="font-bold text-warning-500">
                                ${{ number_format($commission->amount, 2) }}
                            </td>
                            <td>{{ $commission->fromUser?->name ?? 'Système' }}</td>
                            <td class="text-[var(--text-secondary)] text-sm">
                                {{ Str::limit($commission->description ?? '', 30) }}
                            </td>
                            <td class="text-[var(--text-secondary)] text-xs">
                                {{ $commission->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Quick Navigation -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <a href="{{ route('commissions.index') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            Voir toutes les commissions
        </a>
        <a href="{{ route('commissions.levels') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            Commissions par niveau
        </a>
    </div>
</div>
@endsection