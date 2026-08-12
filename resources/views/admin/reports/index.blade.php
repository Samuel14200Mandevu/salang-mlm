@extends('admin.layouts.app')

@push('styles')
<style>
    .report-stat:hover { transform: translateY(-4px); transition: all 0.3s ease; }
    
    @media (max-width: 640px) {
        .card-stats { padding: 0.75rem; }
        .card-stats .text-2xl { font-size: 1.25rem; }
        .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.7rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Rapports & Statistiques</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Analyse complète de la plateforme</p>
        </div>
        <div class="flex flex-wrap gap-1.5 sm:gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'users']) }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter CSV
            </a>
            <a href="{{ route('admin.reports.pdf', ['type' => 'users']) }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                PDF
            </a>
        </div>
    </div>

    <!-- Global Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats report-stat p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Utilisateurs</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ number_format($stats['total_users'] ?? 0) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ number_format($stats['active_users'] ?? 0) }} actifs</p>
        </div>
        <div class="card-stats report-stat p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Commissions</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">En attente: ${{ number_format($stats['pending_commissions'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats report-stat p-3 sm:p-4 border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Ventes</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">${{ number_format($stats['total_sales'] ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ number_format($stats['total_packages_sold'] ?? 0) }} packages vendus</p>
        </div>
        <div class="card-stats report-stat p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Retraits</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">${{ number_format($stats['total_withdrawn'] ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Total retiré par les membres</p>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="card animate-fadeInUp delay-5 p-3 sm:p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Évolution mensuelle</h3>
            <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">12 derniers mois</span>
        </div>
        <div class="h-40 sm:h-48 md:h-56">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Commissions by Type & Users by Rank -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-6">
        <div class="card p-3 sm:p-4 md:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commissions par type</h3>
                <a href="{{ route('admin.reports.commissions') }}" class="text-xs text-primary-500 hover:underline">Voir tout →</a>
            </div>
            @php 
                $totalCommissionType = $commissionByType->sum('total') ?? 1;
            @endphp
            @forelse($commissionByType ?? [] as $item)
                @php 
                    $percent = ($item->total / max($totalCommissionType, 1)) * 100;
                @endphp
                <div class="mb-2 sm:mb-3">
                    <div class="flex justify-between text-xs sm:text-sm">
                        <span class="text-[var(--text-secondary)]">{{ ucfirst($item->type) }}</span>
                        <span class="font-semibold text-primary-500">${{ number_format($item->total, 2) }}</span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-fill" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] text-sm py-4">Aucune donnée de commission</p>
            @endforelse
        </div>

        <div class="card p-3 sm:p-4 md:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Utilisateurs par grade</h3>
                <a href="{{ route('admin.reports.users') }}" class="text-xs text-primary-500 hover:underline">Voir tout →</a>
            </div>
            @php 
                $totalUsersByRank = $usersByRank->sum('count') ?? 1;
            @endphp
            @forelse($usersByRank ?? [] as $item)
                @php 
                    $percent = ($item->count / max($totalUsersByRank, 1)) * 100;
                @endphp
                <div class="mb-2 sm:mb-3">
                    <div class="flex justify-between text-xs sm:text-sm">
                        <span class="text-[var(--text-secondary)]">{{ $item->rank ?? 'Non défini' }}</span>
                        <span class="font-semibold text-primary-500">{{ $item->count }}</span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-fill bg-purple-500" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] text-sm py-4">Aucune donnée de grade</p>
            @endforelse
        </div>
    </div>

    <!-- Top Sponsors & Package Revenue -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-7">
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Top 10 Parrains</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-xs sm:text-sm">#</th>
                            <th class="text-xs sm:text-sm">Nom</th>
                            <th class="text-xs sm:text-sm hidden sm:table-cell">Email</th>
                            <th class="text-xs sm:text-sm text-right">Parrainages</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSponsors ?? [] as $index => $sponsor)
                            <tr>
                                <td class="text-xs sm:text-sm">{{ $index + 1 }}</td>
                                <td class="font-medium text-sm sm:text-base">{{ $sponsor->name }}</td>
                                <td class="text-[var(--text-secondary)] text-xs sm:text-sm hidden sm:table-cell">{{ $sponsor->email }}</td>
                                <td class="text-right font-bold text-primary-500 text-sm sm:text-base">{{ $sponsor->total_sponsors }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-[var(--text-secondary)] text-sm">Aucune donnée de parrainage</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Revenus par package</h3>
            <div class="space-y-2 sm:space-y-3">
                @forelse($packageRevenue ?? [] as $package)
                    <div>
                        <div class="flex justify-between text-xs sm:text-sm">
                            <span class="text-[var(--text-secondary)]">{{ $package->name }}</span>
                            <span class="font-semibold text-primary-500">${{ number_format($package->total_revenue ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] sm:text-xs text-[var(--text-secondary)]">
                            <span>{{ $package->users_count ?? 0 }} utilisateurs</span>
                            <span>${{ number_format($package->price ?? 0, 2) }} / package</span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] text-sm py-4">Aucune donnée de package</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-8">
        <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Rapport Ventes
        </a>
        <a href="{{ route('admin.reports.commissions') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Rapport Commissions
        </a>
        <a href="{{ route('admin.reports.users') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Rapport Utilisateurs
        </a>
        <a href="{{ route('admin.reports.withdrawals') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Rapport Retraits
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;
    
    const monthlyData = @json($monthlySales ?? []);
    const labels = monthlyData.map(item => item.month);
    const salesData = monthlyData.map(item => item.sales || 0);
    const commissionsData = monthlyData.map(item => item.commissions || 0);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ventes',
                    data: salesData,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Commissions',
                    data: commissionsData,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(0);
                        },
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 9 },
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            }
        }
    });
});
</script>
@endpush