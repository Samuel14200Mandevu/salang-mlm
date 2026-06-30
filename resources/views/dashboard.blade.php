@extends('layouts.app')

@push('styles')
<style>
    .stat-icon svg { width: 1.25rem; height: 1.25rem; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Dashboard</h1>
        <p class="text-[var(--text-secondary)] mt-1">
            Bonjour, <span class="font-semibold text-primary-500">{{ Auth::user()->name }}</span>
        </p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        <div class="card-stats animate-fadeInUp delay-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Portefeuille</p>
                    <p class="text-xl md:text-2xl font-bold text-primary-500">${{ number_format($walletBalance ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card-stats animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Commissions</p>
                    <p class="text-xl md:text-2xl font-bold text-green-500">${{ number_format($totalCommission ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card-stats animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Filleuls</p>
                    <p class="text-xl md:text-2xl font-bold text-blue-500">{{ $totalDownlines ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card-stats animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Rang</p>
                    <p class="text-xl md:text-2xl font-bold text-purple-500">{{ Auth::user()->rank ?? 'Distributor' }}</p>
                </div>
                <div class="stat-icon stat-icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Profil + Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Carte Profil -->
        <div class="card animate-fadeInLeft delay-2">
            <div class="flex items-center gap-4">
                <div class="avatar avatar-xl avatar-gradient avatar-ring">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div>
                    <h3 class="font-bold text-[var(--text-primary)]">{{ Auth::user()->name }}</h3>
                    <p class="text-xs text-[var(--text-secondary)]">MEMBER</p>
                    <span class="badge badge-success text-xs">Rank {{ Auth::user()->rank_id ?? 1 }}</span>
                </div>
            </div>
            
            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <div>
                    <p class="text-xs text-[var(--text-secondary)]">ID</p>
                    <p class="font-semibold text-[var(--text-primary)]">#{{ Auth::user()->id }}</p>
                </div>
                <div>
                    <p class="text-xs text-[var(--text-secondary)]">Inscrit le</p>
                    <p class="font-semibold text-[var(--text-primary)]">{{ Auth::user()->created_at->format('d M Y') }}</p>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-xs text-[var(--text-secondary)]">Parrain</p>
                <p class="font-semibold text-[var(--text-primary)]">{{ Auth::user()->sponsor?->name ?? 'Aucun' }}</p>
                <p class="text-xs text-[var(--text-secondary)]">{{ Auth::user()->sponsor?->email ?? '--' }}</p>
            </div>
        </div>

        <!-- 3 Stats supplémentaires -->
        <div class="lg:col-span-3 grid grid-cols-2 lg:grid-cols-3 gap-3">
            <div class="card-stats animate-fadeInUp delay-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">PV Total</p>
                        <p class="text-2xl font-bold text-primary-500">{{ number_format(Auth::user()->pv_balance ?? 0) }}</p>
                    </div>
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card-stats animate-fadeInUp delay-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Niveau 1</p>
                        <p class="text-2xl font-bold text-blue-500">{{ $level1 ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card-stats animate-fadeInUp delay-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Package</p>
                        <p class="text-2xl font-bold text-green-500">{{ Auth::user()->package?->name ?? 'Starter' }}</p>
                    </div>
                    <div class="stat-icon stat-icon-success">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique + Activités -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Graphique des gains -->
        <div class="card lg:col-span-2 animate-fadeInUp delay-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-[var(--text-primary)]">Évolution des gains</h3>
                <span class="text-xs text-[var(--text-secondary)]">{{ now()->format('Y') }}</span>
            </div>
            <div class="h-48 flex items-end gap-1 md:gap-2">
                @php $max = max(array_column($monthlyData ?? [], 'amount') ?: [1]); @endphp
                @foreach($monthlyData ?? [] as $data)
                    @php $height = ($data['amount'] / max($max, 1)) * 100; @endphp
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="graph-bar w-full bg-primary-500/30 hover:bg-primary-500"
                             style="height: {{ max(8, $height) }}%">
                            <span class="tooltip">${{ number_format($data['amount'], 2) }}</span>
                        </div>
                        <span class="text-[10px] text-[var(--text-secondary)] mt-1">{{ substr($data['month'], 0, 3) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Activités récentes -->
        <div class="card animate-fadeInRight delay-7">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-[var(--text-primary)]">Activités</h3>
                <span class="badge badge-neutral text-xs">{{ $recentActivities->count() ?? 0 }}</span>
            </div>
            <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                @forelse($recentActivities ?? [] as $activity)
                    <div class="activity-item">
                        <div class="avatar avatar-sm avatar-gradient">
                            {{ substr($activity->fromUser->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-[var(--text-primary)] truncate">
                                <span class="font-semibold">{{ $activity->fromUser->name ?? 'Système' }}</span>
                                <span class="text-[var(--text-secondary)]">{{ $activity->type_label ?? 'a effectué une action' }}</span>
                            </p>
                            <p class="text-xs text-[var(--text-secondary)]">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                        @if($activity->amount)
                            <span class="text-sm font-bold text-green-500">+${{ number_format($activity->amount, 2) }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-8">Aucune activité récente</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-8">
        <a href="{{ route('subscriptions.index') }}" class="card-stats text-center">
            <div class="stat-icon stat-icon-primary mx-auto mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-[var(--text-primary)]">Packages</p>
        </a>
        <a href="{{ route('products.index') }}" class="card-stats text-center">
            <div class="stat-icon stat-icon-success mx-auto mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-[var(--text-primary)]">Boutique</p>
        </a>
        <a href="{{ route('withdrawal.index') }}" class="card-stats text-center">
            <div class="stat-icon stat-icon-warning mx-auto mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-[var(--text-primary)]">Retrait</p>
        </a>
        <a href="{{ route('network.index') }}" class="card-stats text-center">
            <div class="stat-icon stat-icon-purple mx-auto mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-[var(--text-primary)]">Mon Équipe</p>
        </a>
    </div>
</div>
@endsection