{{-- resources/views/admin/dashboard.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ============================================================
   DASHBOARD STYLES
   ============================================================ */

.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem 1.25rem;
    transition: box-shadow 0.15s ease, transform 0.1s ease;
    cursor: pointer;
}
.stat-card:hover {
    box-shadow: var(--shadow-md);
}
.stat-card:active {
    transform: scale(0.98);
}

.stat-card .stat-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-primary { background: rgba(10, 42, 108, 0.08); color: #0A2A6C; }
.stat-icon-success { background: rgba(28, 126, 74, 0.08); color: #1C7E4A; }
.stat-icon-purple { background: rgba(10, 42, 108, 0.08); color: #0A2A6C; }
.stat-icon-info { background: rgba(6, 95, 156, 0.08); color: #065F9C; }
.stat-icon-warning { background: rgba(181, 71, 8, 0.08); color: #B54708; }
.stat-icon-danger { background: rgba(185, 28, 28, 0.08); color: #B91C1C; }

.stat-card .stat-value {
    font-size: 1.375rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 0.125rem;
}

.stat-card .stat-label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}

.stat-card .stat-detail {
    font-size: 0.625rem;
    color: var(--text-tertiary);
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.stat-card .stat-detail .value-success { color: #1C7E4A; font-weight: 600; }
.stat-card .stat-detail .value-danger { color: #B91C1C; font-weight: 600; }
.stat-card .stat-detail .value-warning { color: #B54708; font-weight: 600; }

/* Quick actions */
.quick-action {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    text-align: center;
    transition: box-shadow 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    text-decoration: none !important;
    display: block;
}
.quick-action:hover {
    box-shadow: var(--shadow-md);
}
.quick-action:active {
    transform: scale(0.97);
}

.quick-action .quick-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.375rem;
}

.quick-action .quick-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-primary);
}

/* Activity items */
.activity-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.5rem 0.625rem;
    border-radius: 6px;
    transition: background 0.15s ease;
    border: 1px solid transparent;
}
.activity-item:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.activity-item .activity-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.688rem;
    color: white;
    flex-shrink: 0;
    background: var(--primary-blue);
}

.activity-item .activity-content {
    flex: 1;
    min-width: 0;
}
.activity-item .activity-title {
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.activity-item .activity-title .highlight {
    font-weight: 600;
}
.activity-item .activity-subtitle {
    font-size: 0.688rem;
    color: var(--text-secondary);
}
.activity-item .activity-time {
    font-size: 0.625rem;
    color: var(--text-tertiary);
    flex-shrink: 0;
}
.activity-item .activity-amount {
    font-size: 0.813rem;
    font-weight: 600;
    color: #1C7E4A;
    flex-shrink: 0;
}

/* Card */
.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }

/* Graph bars */
.graph-container {
    display: flex;
    align-items: flex-end;
    gap: 0.25rem;
    height: 160px;
    padding-top: 0.75rem;
}

.graph-bar-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    justify-content: flex-end;
}

.graph-bar {
    width: 100%;
    min-height: 4px;
    border-radius: 4px 4px 0 0;
    background: var(--primary-blue);
    opacity: 0.7;
    transition: opacity 0.2s ease, transform 0.2s ease;
    max-width: 36px;
    margin: 0 auto;
    position: relative;
}
.graph-bar:hover {
    opacity: 1;
}

.graph-bar .tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--bg-card);
    color: var(--text-primary);
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.6rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
    border: 1px solid var(--border-color);
    white-space: nowrap;
    box-shadow: var(--shadow-sm);
}
.graph-bar:hover .tooltip {
    opacity: 1;
}

.graph-label {
    font-size: 0.55rem;
    color: var(--text-tertiary);
    margin-top: 0.25rem;
    text-align: center;
}

/* Progress bars */
.progress-item {
    margin-bottom: 0.625rem;
}
.progress-item:last-child {
    margin-bottom: 0;
}

.progress-item .progress-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    margin-bottom: 0.125rem;
}
.progress-item .progress-header .progress-label {
    color: var(--text-secondary);
    font-weight: 500;
}
.progress-item .progress-header .progress-value {
    font-weight: 600;
    color: var(--text-primary);
}

.progress-track {
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
    transition: width 0.8s ease;
}

/* Table */
.table-wrap {
    overflow-x: auto;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}
.table th {
    padding: 0.375rem 0.5rem;
    text-align: left;
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    border-bottom: 2px solid var(--border-color);
    background: var(--bg-secondary);
}
.table td {
    padding: 0.375rem 0.5rem;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-primary);
}
.table tr:hover td {
    background: var(--bg-hover);
}

/* Animations */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.animate-fadeInLeft { animation: fadeInUp 0.3s ease forwards; }
.animate-fadeInRight { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }
.delay-4 { animation-delay: 0.2s; }
.delay-5 { animation-delay: 0.25s; }
.delay-6 { animation-delay: 0.3s; }
.delay-7 { animation-delay: 0.35s; }
.delay-8 { animation-delay: 0.4s; }
.delay-9 { animation-delay: 0.45s; }

/* Responsive */
@media (max-width: 640px) {
    .stat-card {
        padding: 0.75rem 0.875rem;
    }
    .stat-card .stat-value {
        font-size: 1.125rem;
    }
    .stat-card .stat-icon {
        width: 2rem;
        height: 2rem;
    }
    .stat-card .stat-icon svg {
        width: 1rem;
        height: 1rem;
    }
    .stat-card .stat-detail {
        font-size: 0.55rem;
    }
    .graph-container {
        height: 100px;
        gap: 0.125rem;
    }
    .graph-bar {
        max-width: 20px;
        min-height: 3px;
    }
    .graph-label {
        font-size: 0.45rem;
    }
    .quick-action {
        padding: 0.625rem 0.75rem;
    }
    .quick-action .quick-icon {
        width: 1.75rem;
        height: 1.75rem;
    }
    .quick-action .quick-icon svg {
        width: 0.875rem;
        height: 0.875rem;
    }
    .quick-action .quick-label {
        font-size: 0.625rem;
    }
    .activity-item {
        padding: 0.375rem 0.5rem;
    }
    .activity-item .activity-avatar {
        width: 1.75rem;
        height: 1.75rem;
        font-size: 0.6rem;
    }
    .activity-item .activity-title {
        font-size: 0.75rem;
    }
    .activity-item .activity-subtitle {
        font-size: 0.625rem;
    }
    .activity-item .activity-amount {
        font-size: 0.75rem;
    }
    .activity-item .activity-time {
        font-size: 0.55rem;
    }
    .card {
        padding: 0.875rem;
    }
    .table th,
    .table td {
        padding: 0.25rem 0.375rem;
        font-size: 0.75rem;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .stat-card .stat-value {
        font-size: 1.25rem;
    }
    .graph-container {
        height: 130px;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
            Tableau de bord Admin
        </h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">
            @php
                $heure = date('H');
                if ($heure >= 5 && $heure < 12) {
                    $salutation = 'Bonjour';
                } elseif ($heure >= 12 && $heure < 18) {
                    $salutation = 'Bon après-midi';
                } elseif ($heure >= 18 && $heure < 22) {
                    $salutation = 'Bonsoir';
                } else {
                    $salutation = 'Bonne nuit';
                }
            @endphp
            {{ $salutation }}, <span class="font-semibold text-[var(--primary-blue)]">{{ Auth::user()->name }}</span>
            <span class="text-[var(--text-tertiary)] text-xs ml-1">({{ date('H:i') }})</span>
        </p>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">

        <!-- Users -->
        <div class="stat-card animate-fadeInUp delay-1">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Utilisateurs</p>
                    <p class="stat-value text-[var(--primary-blue)]">{{ number_format($totalUsers ?? 0) }}</p>
                </div>
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Actifs <span class="value-success">{{ number_format($activeUsers ?? 0) }}</span></span>
                <span>Inactifs <span class="value-danger">{{ number_format(($totalUsers ?? 0) - ($activeUsers ?? 0)) }}</span></span>
            </div>
        </div>

        <!-- Commissions -->
        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Commissions</p>
                    <p class="stat-value text-[#1C7E4A]">${{ number_format($totalCommissions ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>En attente <span class="value-warning">${{ number_format($pendingCommissions ?? 0, 2) }}</span></span>
                <span>Payées <span class="value-success">${{ number_format(($totalCommissions ?? 0) - ($pendingCommissions ?? 0), 2) }}</span></span>
            </div>
        </div>

        <!-- Withdrawals -->
        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Retraits</p>
                    <p class="stat-value text-[var(--primary-blue)]">${{ number_format($totalWithdrawn ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>En attente <span class="value-warning">{{ number_format($pendingWithdrawals ?? 0) }}</span></span>
                <span>Traités <span class="value-success">{{ number_format(($totalWithdrawals ?? 0) - ($pendingWithdrawals ?? 0)) }}</span></span>
            </div>
        </div>

        <!-- Packages -->
        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Packages</p>
                    <p class="stat-value text-[#065F9C]">{{ number_format($totalPackages ?? 0) }}</p>
                </div>
                <div class="stat-icon stat-icon-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Vendus <span class="value-success">{{ number_format($soldPackages ?? 0) }}</span></span>
                <span>Produits <span class="value-success">{{ number_format($totalProducts ?? 0) }}</span></span>
            </div>
        </div>
    </div>

    <!-- Graph + Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">

        <!-- Monthly registrations -->
        <div class="card lg:col-span-2 animate-fadeInUp delay-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Inscriptions mensuelles
                </h3>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                    {{ now()->format('Y') }}
                </span>
            </div>

            <div class="graph-container">
                @php
                    $max = max(array_column($monthlyData ?? [], 'users') ?: [1]);
                @endphp

                @forelse($monthlyData ?? [] as $data)
                    @php
                        $height = ($data->users / max($max, 1)) * 100;
                    @endphp
                    <div class="graph-bar-wrapper">
                        <div class="graph-bar" style="height: {{ max(6, $height) }}%;">
                            <span class="tooltip">{{ $data->users }} inscription(s)</span>
                        </div>
                        <span class="graph-label">{{ substr($data->month, 0, 3) }}</span>
                    </div>
                @empty
                    <div class="w-full text-center text-[var(--text-secondary)] py-6 text-sm">
                        Aucune donnée disponible
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Package distribution -->
        <div class="card animate-fadeInUp delay-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                Distribution des packages
            </h3>

            @php
                $totalPackageUsers = $packageDistribution->sum('users_count') ?? 0;
            @endphp

            @forelse($packageDistribution ?? [] as $pkg)
                @php
                    $percent = $totalPackageUsers > 0 ? ($pkg->users_count / $totalPackageUsers) * 100 : 0;
                @endphp
                <div class="progress-item">
                    <div class="progress-header">
                        <span class="progress-label">{{ $pkg->name }}</span>
                        <span class="progress-value">{{ $pkg->users_count }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ $percent }}%;"></div>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-3 text-sm">
                    Aucun package disponible
                </p>
            @endforelse

            <div class="mt-3 p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                <p class="text-xl font-bold text-[var(--primary-blue)]">
                    {{ number_format($totalUsers ?? 0) }}
                </p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                    Total utilisateurs
                </p>
            </div>
        </div>
    </div>

    <!-- Recent activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">

        <!-- Recent users -->
        <div class="card animate-fadeInUp delay-7">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Derniers inscrits
                </h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">
                    {{ $recentUsers->count() ?? 0 }}
                </span>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th class="hidden sm:table-cell">Email</th>
                            <th class="text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers ?? [] as $user)
                            <tr>
                                <td class="font-medium text-sm">{{ $user->name }}</td>
                                <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs">{{ $user->email }}</td>
                                <td class="text-right">
                                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px]">
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-[var(--text-secondary)] text-sm">
                                    Aucun utilisateur
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2 text-right">
                <a href="{{ route('admin.users') }}"
                   class="text-xs text-[var(--primary-blue)] hover:underline font-medium transition">
                    Voir tous →
                </a>
            </div>
        </div>

        <!-- Recent commissions -->
        <div class="card animate-fadeInUp delay-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                    Dernières commissions
                </h3>
                <span class="badge badge-neutral text-[10px] sm:text-xs">
                    {{ $recentCommissions->count() ?? 0 }}
                </span>
            </div>

            <div class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar">
                @forelse($recentCommissions ?? [] as $commission)
                    <div class="activity-item">
                        <div class="activity-avatar">
                            {{ substr($commission->user?->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">
                                <span class="highlight">{{ $commission->user?->name ?? 'Système' }}</span>
                                <span class="text-[var(--text-secondary)]">{{ $commission->type_label ?? 'commission' }}</span>
                            </p>
                            <p class="activity-subtitle">de {{ $commission->fromUser?->name ?? 'Système' }}</p>
                            <p class="activity-time">{{ $commission->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="activity-amount">+${{ number_format($commission->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-center text-[var(--text-secondary)] py-3 text-sm">
                        Aucune commission
                    </p>
                @endforelse
            </div>

            <div class="mt-2 text-right">
                <a href="{{ route('admin.commissions') }}"
                   class="text-xs text-[var(--primary-blue)] hover:underline font-medium transition">
                    Voir toutes →
                </a>
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-9">

        <a href="{{ route('admin.users.create') }}" class="quick-action">
            <div class="quick-icon stat-icon-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <p class="quick-label">Ajouter un utilisateur</p>
        </a>

        <a href="{{ route('admin.packages.create') }}" class="quick-action">
            <div class="quick-icon stat-icon-success">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </div>
            <p class="quick-label">Créer un package</p>
        </a>

        <a href="{{ route('admin.products.create') }}" class="quick-action">
            <div class="quick-icon stat-icon-info">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="quick-label">Ajouter un produit</p>
        </a>

        <a href="{{ route('admin.commissions') }}" class="quick-action">
            <div class="quick-icon stat-icon-purple">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="quick-label">Voir commissions</p>
        </a>

    </div>

</div>
@endsection