{{-- resources/views/admin/pos-reports/index.blade.php --}}
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

.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.stat-card .number {
    font-size: 1.375rem;
    font-weight: 700;
}
.stat-card .label {
    font-size: 0.688rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.stat-card .icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.icon-blue { background: rgba(10, 42, 108, 0.08); color: #0A2A6C; }
.icon-green { background: rgba(28, 126, 74, 0.08); color: #1C7E4A; }
.icon-purple { background: rgba(10, 42, 108, 0.08); color: #0A2A6C; }
.icon-orange { background: rgba(181, 71, 8, 0.08); color: #B54708; }
.icon-red { background: rgba(185, 28, 28, 0.08); color: #B91C1C; }
.icon-teal { background: rgba(6, 95, 156, 0.08); color: #065F9C; }

.chart-container {
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

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-info { background: rgba(6, 95, 156, 0.12); color: #065F9C; border-color: rgba(6, 95, 156, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.813rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-primary);
    vertical-align: middle;
}

.mini-chart {
    height: 60px;
    display: flex;
    align-items: flex-end;
    gap: 2px;
}
.mini-chart .bar {
    flex: 1;
    background: var(--primary-blue);
    border-radius: 2px 2px 0 0;
    min-height: 4px;
    transition: height 0.2s ease;
    opacity: 0.7;
}
.mini-chart .bar:hover {
    opacity: 1;
}
.mini-chart .bar.purple {
    background: var(--primary-blue);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }
.delay-4 { animation-delay: 0.2s; }
.delay-5 { animation-delay: 0.25s; }
.delay-6 { animation-delay: 0.3s; }

@media (max-width: 640px) {
    .stat-card .number {
        font-size: 1.125rem;
    }
    .stat-card {
        padding: 0.625rem;
    }
    .chart-container {
        padding: 0.875rem;
    }
    .table thead th, .table tbody td {
        padding: 0.3rem 0.4rem;
        font-size: 0.7rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.65rem;
    }
    .grid-cols-6 { grid-template-columns: 1fr 1fr 1fr !important; }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Dashboard POS
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Rapports et statistiques des ventes au guichet
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.pos-reports.sales') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Voir les ventes
            </a>
            <a href="{{ route('admin.pos-reports.export') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Ventes totales</p>
                    <p class="number text-[var(--primary-blue)]">${{ number_format($stats['total_sales'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Commandes</p>
                    <p class="number text-[#1C7E4A]">{{ number_format($stats['total_orders'] ?? 0) }}</p>
                </div>
                <div class="icon icon-green">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Commissions</p>
                    <p class="number text-[var(--primary-blue)]">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">PV distribués</p>
                    <p class="number text-[#B54708]">{{ number_format($stats['total_pv'] ?? 0) }}</p>
                </div>
                <div class="icon icon-orange">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Caissiers</p>
                    <p class="number text-[#065F9C]">{{ number_format($stats['total_cashiers'] ?? 0) }}</p>
                </div>
                <div class="icon icon-teal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Aujourd'hui</p>
                    <p class="number text-[#B91C1C]">${{ number_format($stats['today_sales'] ?? 0, 2) }}</p>
                    <p class="text-[10px] text-[var(--text-tertiary)]">{{ number_format($stats['today_orders'] ?? 0) }} commandes</p>
                </div>
                <div class="icon icon-red">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-2">

        <!-- Daily Sales -->
        <div class="chart-container">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm">Ventes (7 derniers jours)</h3>
                <span class="badge badge-info">{{ count($dailySales) }} jours</span>
            </div>
            <div class="mini-chart" style="height: 80px;">
                @php
                    $max = collect($dailySales)->max('total') ?: 1;
                @endphp
                @foreach($dailySales as $day)
                    <div class="flex flex-col items-center flex-1 gap-1">
                        <div class="bar" style="height: {{ ($day['total'] / $max) * 70 + 10 }}%; width: 100%;"></div>
                        <span class="text-[7px] text-[var(--text-tertiary)]">{{ $day['date'] }}</span>
                        <span class="text-[6px] font-bold text-[var(--primary-blue)]">${{ number_format($day['total'], 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Monthly Sales -->
        <div class="chart-container">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm">Ventes (12 derniers mois)</h3>
                <span class="badge badge-info">{{ count($monthlySales) }} mois</span>
            </div>
            <div class="mini-chart" style="height: 80px;">
                @php
                    $maxMonth = collect($monthlySales)->max('total') ?: 1;
                @endphp
                @foreach($monthlySales as $month)
                    <div class="flex flex-col items-center flex-1 gap-1">
                        <div class="bar purple" style="height: {{ ($month['total'] / $maxMonth) * 70 + 10 }}%; width: 100%;"></div>
                        <span class="text-[6px] text-[var(--text-tertiary)]">{{ $month['month'] }}</span>
                        <span class="text-[5px] font-bold text-[var(--primary-blue)]">${{ number_format($month['total'], 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Cashiers & Top Sponsors -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-3">

        <!-- Top Cashiers -->
        <div class="chart-container">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">
                Meilleurs caissiers
            </h3>
            <div class="space-y-2">
                @forelse($topCashiers as $cashier)
                    <div class="flex items-center justify-between p-2 bg-[var(--bg-secondary)] rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[var(--primary-blue)] text-white flex items-center justify-center text-xs font-semibold">
                                {{ $loop->iteration }}
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $cashier->name }}</p>
                                <p class="text-xs text-[var(--text-secondary)]">{{ $cashier->orders_count }} commandes</p>
                            </div>
                        </div>
                        <span class="font-bold text-[var(--primary-blue)]">${{ number_format($cashier->orders_sum_total ?? 0, 2) }}</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] text-sm py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>

        <!-- Top Sponsors -->
        <div class="chart-container">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">
                Meilleurs parrains (PV)
            </h3>
            <div class="space-y-2">
                @forelse($topSponsors as $sponsor)
                    <div class="flex items-center justify-between p-2 bg-[var(--bg-secondary)] rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[var(--primary-blue)] text-white flex items-center justify-center text-xs font-semibold">
                                {{ $loop->iteration }}
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $sponsor->name }}</p>
                                <p class="text-xs text-[var(--text-secondary)]">Code: {{ $sponsor->sponsor_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <span class="font-bold text-[var(--primary-blue)]">{{ number_format($sponsor->commissions_sum_amount ?? 0) }} PV</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] text-sm py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="chart-container animate-fadeInUp delay-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm">Dernières commandes POS</h3>
            <a href="{{ route('admin.pos-reports.sales') }}" class="text-sm text-[var(--primary-blue)] hover:underline">Voir tout</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° commande</th>
                        <th>Client</th>
                        <th>Caissier</th>
                        <th class="text-right">Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders ?? [] as $order)
                        <tr>
                            <td class="font-mono text-xs text-[var(--primary-blue)]">#{{ $order->order_number }}</td>
                            <td class="text-sm">{{ $order->user->name ?? 'N/A' }}</td>
                            <td class="text-sm">
                                @php
                                    $cashierName = $order->cashier_name;
                                @endphp
                                @if($cashierName != 'Système')
                                    <span class="font-medium text-[#065F9C]">
                                        {{ $cashierName }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Système</span>
                                @endif
                            </td>
                            <td class="text-right font-bold text-[var(--primary-blue)]">${{ number_format($order->total, 2) }}</td>
                            <td class="text-xs text-[var(--text-secondary)]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-[var(--text-secondary)] text-sm">Aucune commande POS</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection