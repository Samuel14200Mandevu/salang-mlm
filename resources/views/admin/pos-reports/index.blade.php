{{-- resources/views/admin/pos-reports/index.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary-300);
    }
    .stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .stat-card .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .stat-card .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .icon-blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .icon-green { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .icon-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .icon-orange { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .icon-red { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .icon-teal { background: rgba(20, 184, 166, 0.12); color: #14b8a6; }
    
    .chart-container {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
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
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.813rem;
    }
    .table thead th {
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
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
        background: var(--primary-500);
        border-radius: 2px 2px 0 0;
        min-height: 4px;
        transition: height 0.3s ease;
        opacity: 0.7;
    }
    .mini-chart .bar:hover {
        opacity: 1;
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
    
    @media (max-width: 640px) {
        .stat-card .number {
            font-size: 1.25rem;
        }
        .stat-card {
            padding: 0.75rem;
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
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <span class="text-green-500">
                    <svg class="inline-block w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </span>
                Dashboard POS
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Rapports et statistiques des ventes au guichet (POS)
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.pos-reports.sales') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Voir les ventes
            </a>
            <a href="{{ route('admin.pos-reports.export') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Ventes totales</p>
                    <p class="number text-primary-500">${{ number_format($stats['total_sales'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Commandes</p>
                    <p class="number text-green-500">{{ number_format($stats['total_orders'] ?? 0) }}</p>
                </div>
                <div class="icon icon-green">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Commissions CASH</p>
                    <p class="number text-purple-500">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">PV distribués</p>
                    <p class="number text-orange-500">{{ number_format($stats['total_pv'] ?? 0) }}</p>
                </div>
                <div class="icon icon-orange">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="stat-card animate-fadeInUp delay-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Caissiers</p>
                    <p class="number text-teal-500">{{ number_format($stats['total_cashiers'] ?? 0) }}</p>
                </div>
                <div class="icon icon-teal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="stat-card animate-fadeInUp delay-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Aujourd'hui</p>
                    <p class="number text-red-500">${{ number_format($stats['today_sales'] ?? 0, 2) }}</p>
                    <p class="text-[10px] text-[var(--text-tertiary)]">{{ number_format($stats['today_orders'] ?? 0) }} commandes</p>
                </div>
                <div class="icon icon-red">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-2">
        
        <!-- Ventes par jour -->
        <div class="chart-container">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Ventes (7 derniers jours)</h3>
                <span class="badge badge-info">{{ count($dailySales) }} jours</span>
            </div>
            <div class="mini-chart" style="height: 100px;">
                @php
                    $max = collect($dailySales)->max('total') ?: 1;
                @endphp
                @foreach($dailySales as $day)
                    <div class="flex flex-col items-center flex-1 gap-1">
                        <div class="bar" style="height: {{ ($day['total'] / $max) * 80 + 10 }}%; width: 100%;"></div>
                        <span class="text-[8px] text-[var(--text-tertiary)]">{{ $day['date'] }}</span>
                        <span class="text-[7px] font-bold text-primary-500">${{ number_format($day['total'], 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Ventes par mois -->
        <div class="chart-container">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Ventes (12 derniers mois)</h3>
                <span class="badge badge-info">{{ count($monthlySales) }} mois</span>
            </div>
            <div class="mini-chart" style="height: 100px;">
                @php
                    $maxMonth = collect($monthlySales)->max('total') ?: 1;
                @endphp
                @foreach($monthlySales as $month)
                    <div class="flex flex-col items-center flex-1 gap-1">
                        <div class="bar" style="height: {{ ($month['total'] / $maxMonth) * 80 + 10 }}%; width: 100%; background: var(--purple-500);"></div>
                        <span class="text-[7px] text-[var(--text-tertiary)]">{{ $month['month'] }}</span>
                        <span class="text-[6px] font-bold text-purple-500">${{ number_format($month['total'], 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Caissiers & Top Parrains -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-3">
        
        <!-- Top Caissiers -->
        <div class="chart-container">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                 Meilleurs caissiers
            </h3>
            <div class="space-y-2">
                @forelse($topCashiers as $cashier)
                    <div class="flex items-center justify-between p-2 bg-[var(--bg-secondary)] rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-primary-500 text-white flex items-center justify-center text-xs font-bold">
                                {{ $loop->iteration }}
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $cashier->name }}</p>
                                <p class="text-xs text-[var(--text-secondary)]">{{ $cashier->orders_count }} commandes</p>
                            </div>
                        </div>
                        <span class="font-bold text-primary-500">${{ number_format($cashier->orders_sum_total ?? 0, 2) }}</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] text-sm py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>

        <!-- Top Parrains (PV générés) -->
        <div class="chart-container">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                 Meilleurs parrains (PV)
            </h3>
            <div class="space-y-2">
                @forelse($topSponsors as $sponsor)
                    <div class="flex items-center justify-between p-2 bg-[var(--bg-secondary)] rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-purple-500 text-white flex items-center justify-center text-xs font-bold">
                                {{ $loop->iteration }}
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $sponsor->name }}</p>
                                <p class="text-xs text-[var(--text-secondary)]">Code: {{ $sponsor->sponsor_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <span class="font-bold text-purple-500">{{ number_format($sponsor->commissions_sum_amount ?? 0) }} PV</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] text-sm py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
    </div>

   <!-- Dernières commandes -->
<div class="chart-container animate-fadeInUp delay-4">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Dernières commandes POS</h3>
        <a href="{{ route('admin.pos-reports.sales') }}" class="text-sm text-primary-500 hover:underline">Voir tout</a>
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
                        <td class="font-mono text-xs text-primary-500">#{{ $order->order_number }}</td>
                        <td class="text-sm">{{ $order->user->name ?? 'N/A' }}</td>
                        <td class="text-sm">
                            @php
                                $cashierName = $order->cashier_name;
                            @endphp
                            @if($cashierName != 'Système')
                                <span class="font-medium text-blue-600">
                                    {{ $cashierName }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">Système</span>
                            @endif
                        </td>
                        <td class="text-right font-bold">${{ number_format($order->total, 2) }}</td>
                        <td class="text-xs text-[var(--text-secondary)]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-[var(--text-secondary)]">Aucune commande POS</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection