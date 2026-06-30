@extends('admin.layouts.app')

@push('styles')
<style>
    .report-stat:hover { transform: translateY(-4px); transition: all 0.3s ease; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📊 Rapports et Statistiques</h1>
            <p class="text-[var(--text-secondary)] mt-1">Analyse complète de votre plateforme</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'users']) }}" class="btn btn-outline btn-sm">
                📊 Exporter
            </a>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats report-stat border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Utilisateurs</p>
            <p class="text-2xl font-bold text-primary-500">{{ $stats['total_users'] ?? 0 }}</p>
            <p class="text-xs text-[var(--text-secondary)]">Dont {{ $stats['active_users'] ?? 0 }} actifs</p>
        </div>
        <div class="card-stats report-stat border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Commissions</p>
            <p class="text-2xl font-bold text-green-500">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)]">En attente: ${{ number_format($stats['pending_commissions'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats report-stat border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Ventes</p>
            <p class="text-2xl font-bold text-blue-500">${{ number_format($stats['total_sales'] ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)]">{{ $stats['total_packages_sold'] ?? 0 }} packages vendus</p>
        </div>
        <div class="card-stats report-stat border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Retraits</p>
            <p class="text-2xl font-bold text-purple-500">${{ number_format($stats['total_withdrawn'] ?? 0, 2) }}</p>
            <p class="text-xs text-[var(--text-secondary)]">Total retiré par les membres</p>
        </div>
    </div>

    <!-- Graphique mensuel -->
    <div class="card animate-fadeInUp delay-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[var(--text-primary)]">📈 Évolution mensuelle</h3>
            <span class="text-xs text-[var(--text-secondary)]">12 derniers mois</span>
        </div>
        <div class="h-56 flex items-end gap-1 md:gap-2">
            @php 
                $maxSales = max(array_column($monthlySales ?? [], 'sales') ?: [1]);
                $maxCommissions = max(array_column($monthlySales ?? [], 'commissions') ?: [1]);
                $max = max($maxSales, $maxCommissions);
            @endphp
            @foreach($monthlySales ?? [] as $data)
                @php 
                    $salesHeight = ($data['sales'] / max($max, 1)) * 100;
                    $commissionsHeight = ($data['commissions'] / max($max, 1)) * 100;
                @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="flex items-end gap-1 w-full" style="height: {{ max(8, $salesHeight) }}%">
                        <div class="w-1/2 bg-primary-500/70 hover:bg-primary-500 transition rounded-t-sm"
                             style="height: 100%">
                            <span class="tooltip">Ventes: ${{ number_format($data['sales'], 2) }}</span>
                        </div>
                        <div class="w-1/2 bg-green-500/70 hover:bg-green-500 transition rounded-t-sm"
                             style="height: {{ max(8, ($data['commissions'] / max($max, 1)) * 100) }}%">
                            <span class="tooltip">Commissions: ${{ number_format($data['commissions'], 2) }}</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-[var(--text-secondary)] mt-1">{{ substr($data['month'], 0, 3) }}</span>
                </div>
            @endforeach
        </div>
        <div class="flex justify-center gap-4 mt-4 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-primary-500 rounded"></span> Ventes</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-500 rounded"></span> Commissions</span>
        </div>
    </div>

    <!-- Commissions par type -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fadeInUp delay-6">
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">🎯 Commissions par type</h3>
            @foreach($commissionByType ?? [] as $item)
                @php 
                    $total = $commissionByType->sum('total') ?? 1;
                    $percent = ($item->total / $total) * 100;
                @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-[var(--text-secondary)]">{{ ucfirst($item->type) }}</span>
                        <span class="font-semibold text-primary-500">${{ number_format($item->total, 2) }}</span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-fill" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">👥 Utilisateurs par grade</h3>
            @foreach($usersByRank ?? [] as $item)
                @php 
                    $total = $usersByRank->sum('count') ?? 1;
                    $percent = ($item->count / $total) * 100;
                @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-[var(--text-secondary)]">{{ $item->rank ?? 'Non défini' }}</span>
                        <span class="font-semibold text-primary-500">{{ $item->count }}</span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-fill bg-purple-500" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Top parrains & Revenus packages -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fadeInUp delay-7">
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">🏆 Top 10 parrains</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th class="text-right">Parrainages</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSponsors ?? [] as $index => $sponsor)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-medium">{{ $sponsor->name }}</td>
                                <td class="text-[var(--text-secondary)]">{{ $sponsor->email }}</td>
                                <td class="text-right font-bold text-primary-500">{{ $sponsor->total_sponsors }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-[var(--text-secondary)]">Aucun parrainage</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">📦 Revenus par package</h3>
            <div class="space-y-3">
                @foreach($packageRevenue ?? [] as $package)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-[var(--text-secondary)]">{{ $package->name }}</span>
                            <span class="font-semibold text-primary-500">${{ number_format($package->total_revenue ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-[var(--text-secondary)]">
                            <span>{{ $package->users_count }} utilisateurs</span>
                            <span>${{ number_format($package->price, 2) }} / package</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="flex flex-wrap gap-3 animate-fadeInUp delay-8">
        <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary btn-sm">
            📈 Rapport des ventes
        </a>
        <a href="{{ route('admin.reports.commissions') }}" class="btn btn-outline btn-sm">
            💰 Rapport des commissions
        </a>
        <a href="{{ route('admin.reports.users') }}" class="btn btn-outline btn-sm">
            👥 Rapport des utilisateurs
        </a>
    </div>
</div>
@endsection