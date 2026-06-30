@extends('layouts.app')

@push('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .stat-change {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: var(--radius-full);
        font-size: 0.7rem;
        font-weight: 600;
    }
    .stat-change-up { background: rgba(34,197,94,0.15); color: #22c55e; }
    .stat-change-down { background: rgba(239,68,68,0.15); color: #ef4444; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📊 Statistiques des Commissions</h1>
            <p class="text-[var(--text-secondary)] mt-1">Analyse détaillée de vos gains</p>
        </div>
        <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm">
            ← Retour aux commissions
        </a>
    </div>

    <!-- Vue d'ensemble -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="stat-card card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total des gains</p>
            <p class="stat-number text-primary-500">${{ number_format($stats['total'] ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)]">Toutes commissions confondues</p>
        </div>
        <div class="stat-card card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Moyenne par commission</p>
            <p class="stat-number text-green-500">${{ number_format(($stats['total_count'] ?? 1) > 0 ? ($stats['total'] ?? 0) / ($stats['total_count'] ?? 1) : 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)]">Sur {{ $stats['total_count'] ?? 0 }} commissions</p>
        </div>
        <div class="stat-card card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="stat-number text-yellow-500">${{ number_format($stats['pending'] ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)]">{{ $stats['pending_count'] ?? 0 }} commission(s) en attente</p>
        </div>
        <div class="stat-card card-stats border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Commissions ce mois</p>
            <p class="stat-number text-purple-500">${{ number_format($stats['monthly'] ? end($stats['monthly'])['amount'] ?? 0 : 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)]">{{ now()->format('F Y') }}</p>
        </div>
    </div>

    <!-- Graphique mensuel -->
    <div class="card animate-fadeInUp delay-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[var(--text-primary)]">📈 Évolution mensuelle</h3>
            <span class="text-xs text-[var(--text-secondary)]">12 derniers mois</span>
        </div>
        <div class="h-56 flex items-end gap-1 md:gap-2">
            @php $max = max(array_column($stats['monthly'] ?? [], 'amount') ?: [1]); @endphp
            @foreach($stats['monthly'] ?? [] as $data)
                @php $height = ($data['amount'] / max($max, 1)) * 100; @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="graph-bar w-full bg-primary-500/30 hover:bg-primary-500 transition"
                         style="height: {{ max(8, $height) }}%">
                        <span class="tooltip">${{ number_format($data['amount'], 2) }}</span>
                    </div>
                    <span class="text-[10px] text-[var(--text-secondary)] mt-1">{{ substr($data['month'], 0, 3) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Répartition par type -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fadeInUp delay-6">
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">🎯 Répartition par type</h3>
            <div class="space-y-3">
                @foreach($stats['by_type'] ?? [] as $type => $data)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-[var(--text-secondary)]">{{ $data['label'] }}</span>
                            <span class="font-semibold text-{{ $data['color'] }}-500">${{ number_format($data['total'], 2) }}</span>
                        </div>
                        <div class="progress mt-1">
                            @php $percent = ($stats['total'] ?? 1) > 0 ? ($data['total'] / ($stats['total'] ?? 1)) * 100 : 0; @endphp
                            <div class="progress-fill bg-{{ $data['color'] }}-500" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="text-xs text-[var(--text-secondary)] mt-0.5">{{ $data['count'] }} commission(s)</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Dernières commissions -->
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">🕐 Dernières commissions</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar">
                @forelse($stats['recent'] ?? [] as $commission)
                    <div class="flex items-center justify-between p-2 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                        <div>
                            <p class="text-sm font-medium text-[var(--text-primary)]">
                                {{ $commission->fromUser?->name ?? 'Système' }}
                            </p>
                            <p class="text-xs text-[var(--text-secondary)]">
                                {{ $commission->type_label ?? $commission->type }}
                                • {{ $commission->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="font-bold text-green-500">+${{ number_format($commission->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-4">Aucune commission récente</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="flex flex-wrap gap-3 animate-fadeInUp delay-7">
        <a href="{{ route('commissions.export') }}" class="btn btn-primary btn-sm">
            📊 Exporter en CSV
        </a>
        <a href="{{ route('commissions.pdf') }}" class="btn btn-outline btn-sm">
            📄 Exporter en PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            🖨️ Imprimer
        </button>
    </div>
</div>
@endsection