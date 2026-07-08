@extends('layouts.app')

@push('styles')
<style>
    .stat-card { transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); }
    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .stat-change {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.5rem;
        border-radius: var(--radius-full);
        font-size: 0.65rem;
        font-weight: 600;
    }
    .stat-change-up { background: rgba(34,197,94,0.15); color: #22c55e; }
    .stat-change-down { background: rgba(239,68,68,0.15); color: #ef4444; }
    
    .graph-bar {
        border-radius: 4px 4px 0 0;
        min-height: 8px;
        position: relative;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .graph-bar .tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--bg-card);
        color: var(--text-primary);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }
    .graph-bar:hover .tooltip { opacity: 1; }
    
    .progress {
        width: 100%;
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 9999px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.8s ease;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    
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
    .btn-sm { padding: 0.375rem 1rem; font-size: 0.75rem; }
    .btn-md { padding: 0.625rem 1.5rem; font-size: 0.875rem; }
    .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4); }
    .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border-color); }
    .btn-outline:hover { border-color: var(--primary-500); color: var(--primary-500); }
    
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
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 4px;
    }
    
    @keyframes fadeInUp {
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
    .delay-7 { animation-delay: 0.35s; }
    
    @media (max-width: 640px) {
        .stat-number { font-size: 1.5rem; }
        .card-stats { padding: 0.75rem; }
        .card { padding: 0.75rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .stats-grid { grid-template-columns: 1fr 1fr !important; }
    }
    
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Statistiques des Commissions</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Analyse détaillée de vos gains</p>
        </div>
        <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux commissions
        </a>
    </div>

    <!-- Vue d'ensemble -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="stat-card card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total des gains</p>
            <p class="stat-number text-primary-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Toutes commissions confondues</p>
        </div>
        <div class="stat-card card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Moyenne par commission</p>
            <p class="stat-number text-green-500">${{ number_format(($stats['total_count'] ?? 1) > 0 ? ($stats['total'] ?? 0) / ($stats['total_count'] ?? 1) : 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Sur {{ $stats['total_count'] ?? 0 }} commissions</p>
        </div>
        <div class="stat-card card-stats p-3 sm:p-4 border-l-4 border-yellow-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">En attente</p>
            <p class="stat-number text-yellow-500">${{ number_format($stats['pending'] ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $stats['pending_count'] ?? 0 }} commission(s) en attente</p>
        </div>
        <div class="stat-card card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Commissions ce mois</p>
            <p class="stat-number text-purple-500">${{ number_format($stats['monthly'] ? end($stats['monthly'])['amount'] ?? 0 : 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ now()->format('F Y') }}</p>
        </div>
    </div>

    <!-- Graphique mensuel -->
    <div class="card animate-fadeInUp delay-5 p-3 sm:p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Evolution mensuelle</h3>
            <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">12 derniers mois</span>
        </div>
        <div class="h-40 sm:h-48 md:h-56 flex items-end gap-1 sm:gap-2">
            @php $max = max(array_column($stats['monthly'] ?? [], 'amount') ?: [1]); @endphp
            @foreach($stats['monthly'] ?? [] as $data)
                @php $height = ($data['amount'] / max($max, 1)) * 100; @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="graph-bar w-full bg-primary-500/30 hover:bg-primary-500 transition"
                         style="height: {{ max(8, $height) }}%">
                        <span class="tooltip">${{ number_format($data['amount'], 2) }}</span>
                    </div>
                    <span class="text-[8px] sm:text-[10px] text-[var(--text-secondary)] mt-1">{{ substr($data['month'], 0, 3) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Repartition -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-6">
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Répartition par type</h3>
            <div class="space-y-2 sm:space-y-3">
                @foreach($stats['by_type'] ?? [] as $type => $data)
                    <div>
                        <div class="flex justify-between text-xs sm:text-sm">
                            <span class="text-[var(--text-secondary)]">{{ $data['label'] }}</span>
                            <span class="font-semibold text-{{ $data['color'] }}-500">${{ number_format($data['total'], 2) }}</span>
                        </div>
                        <div class="progress mt-1">
                            @php $percent = ($stats['total'] ?? 1) > 0 ? ($data['total'] / ($stats['total'] ?? 1)) * 100 : 0; @endphp
                            <div class="progress-fill bg-{{ $data['color'] }}-500" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5">{{ $data['count'] }} commission(s)</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Dernieres commissions -->
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Dernières commissions</h3>
            <div class="space-y-1.5 sm:space-y-2 max-h-48 sm:max-h-64 overflow-y-auto custom-scrollbar">
                @forelse($stats['recent'] ?? [] as $commission)
                    <div class="flex items-center justify-between p-1.5 sm:p-2 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-[var(--text-primary)] truncate">
                                {{ $commission->fromUser?->name ?? 'Système' }}
                            </p>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                                {{ $commission->type_label ?? $commission->type }}
                                &bull; {{ $commission->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="font-bold text-green-500 text-xs sm:text-sm flex-shrink-0">+${{ number_format($commission->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-4 text-sm">Aucune commission récente</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-7">
        <a href="{{ route('commissions.export') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exporter en CSV
        </a>
        <a href="{{ route('commissions.pdf') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
            </svg>
            Exporter en PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer
        </button>
    </div>
</div>
@endsection