{{-- resources/views/admin/dashboard.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
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

.stat-icon-primary { background: rgba(30, 93, 173, 0.1); color: var(--color-primary-600); }
.stat-icon-success { background: rgba(28, 126, 74, 0.08); color: var(--success); }
.stat-icon-purple { background: rgba(30, 93, 173, 0.1); color: var(--color-primary-600); }
.stat-icon-info { background: rgba(37, 99, 235, 0.08); color: var(--ui-stat-info); }
.stat-icon-warning { background: rgba(245, 166, 35, 0.12); color: var(--color-accent-600); }
.stat-icon-danger { background: rgba(185, 28, 28, 0.08); color: var(--danger); }

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

/* Inscriptions mensuelles — chart premium */
.registrations-chart-meta {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.registrations-chart-meta .meta-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-blue, #0A2A6C);
    line-height: 1;
    display: inline-block;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
}
.registrations-chart-meta .meta-label {
    font-size: 0.65rem;
    color: var(--text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.registrations-chart-shell {
    position: relative;
    width: 100%;
    height: 220px;
    margin-top: 0.5rem;
    opacity: 0;
    transform: translateY(10px);
    animation: registrationsChartIn 0.7s ease-out 0.15s forwards;
}
@keyframes registrationsChartIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Progress bars - CORRIGÉ */
.progress-item {
    margin-bottom: 0.75rem;
}
.progress-item:last-child {
    margin-bottom: 0;
}

.progress-item .progress-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
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
    height: 0.5rem;
    background: var(--bg-secondary);
    border-radius: 9999px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 9999px;
    background: var(--primary-blue);
    transition: width 0.8s ease;
    min-width: 6px;
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

/* Animations — fluides (eau) */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(18px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes progressFill {
    from { transform: scaleX(0); }
    to { transform: scaleX(1); }
}
.animate-fadeInUp {
    opacity: 0;
    animation: fadeInUp 0.85s cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
}
.animate-fadeInLeft { opacity: 0; animation: fadeInUp 0.85s cubic-bezier(0.22, 0.61, 0.36, 1) forwards; }
.animate-fadeInRight { opacity: 0; animation: fadeInUp 0.85s cubic-bezier(0.22, 0.61, 0.36, 1) forwards; }
.delay-1 { animation-delay: 0.08s; }
.delay-2 { animation-delay: 0.16s; }
.delay-3 { animation-delay: 0.24s; }
.delay-4 { animation-delay: 0.32s; }
.delay-5 { animation-delay: 0.42s; }
.delay-6 { animation-delay: 0.52s; }
.delay-7 { animation-delay: 0.62s; }
.delay-8 { animation-delay: 0.72s; }
.delay-9 { animation-delay: 0.82s; }
.delay-10 { animation-delay: 0.92s; }
.delay-11 { animation-delay: 1.02s; }
.delay-12 { animation-delay: 1.12s; }
.stat-card .stat-value,
.stat-card .stat-detail .value-success,
.stat-card .stat-detail .value-danger,
.stat-card .stat-detail .value-warning {
    font-variant-numeric: tabular-nums;
}
.progress-fill {
    transform-origin: left center;
    transform: scaleX(0);
    animation: progressFill 1.4s cubic-bezier(0.22, 0.61, 0.36, 1) 0.7s forwards;
}

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
    .registrations-chart-shell {
        height: 180px;
    }
    .registrations-chart-meta .meta-total {
        font-size: 1.05rem;
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
    .progress-item .progress-header {
        font-size: 0.625rem;
    }
    .progress-track {
        height: 0.375rem;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .stat-card .stat-value {
        font-size: 1.25rem;
    }
    .registrations-chart-shell {
        height: 200px;
    }
}
</style>
@endpush

@section('content')
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
@push('admin_mobile_page_extra_classes')
admin-mobile-stack admin-mobile-no-auto-greeting
@endpush

    <!-- Header desktop -->
    <div class="admin-mobile-page-head hidden md:block animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
            Tableau de bord Admin
        </h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">
            {{ $salutation }}, <span class="font-semibold text-[var(--primary-blue)]">{{ Auth::user()?->name }}</span>
            <span class="text-[var(--text-tertiary)] text-xs ml-1">({{ date('H:i') }})</span>
        </p>
    </div>

    <!-- Statistics -->
    <div class="admin-kpi-rail grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">

        <!-- Users -->
        <div class="stat-card animate-fadeInUp delay-1">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Utilisateurs</p>
                    <p class="stat-value text-[var(--primary-blue)]" data-count-to="{{ (int) ($totalUsers ?? 0) }}">0</p>
                </div>
                <div class="stat-icon stat-icon-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Actifs <span class="value-success" data-count-to="{{ (int) ($activeUsers ?? 0) }}">0</span></span>
                <span>Inactifs <span class="value-danger" data-count-to="{{ (int) (($totalUsers ?? 0) - ($activeUsers ?? 0)) }}">0</span></span>
            </div>
        </div>

        <!-- Commissions -->
        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Commissions</p>
                    <p class="stat-value text-[var(--ui-stat-success)]" data-count-to="{{ (float) ($totalCommissions ?? 0) }}" data-count-prefix="$" data-count-decimals="2">$0.00</p>
                </div>
                <div class="stat-icon stat-icon-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>En attente <span class="value-warning" data-count-to="{{ (float) ($pendingCommissions ?? 0) }}" data-count-prefix="$" data-count-decimals="2">$0.00</span></span>
                <span>Payées <span class="value-success" data-count-to="{{ (float) (($totalCommissions ?? 0) - ($pendingCommissions ?? 0)) }}" data-count-prefix="$" data-count-decimals="2">$0.00</span></span>
            </div>
        </div>

        <!-- Withdrawals -->
        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Retraits</p>
                    <p class="stat-value text-[var(--primary-blue)]" data-count-to="{{ (float) ($totalWithdrawn ?? 0) }}" data-count-prefix="$" data-count-decimals="2">$0.00</p>
                </div>
                <div class="stat-icon stat-icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>En attente <span class="value-warning" data-count-to="{{ (int) ($pendingWithdrawals ?? 0) }}">0</span></span>
                <span>Traités <span class="value-success" data-count-to="{{ (int) (($totalWithdrawals ?? 0) - ($pendingWithdrawals ?? 0)) }}">0</span></span>
            </div>
        </div>

        <!-- Packages -->
        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Packages</p>
                    <p class="stat-value text-[var(--ui-stat-info)]" data-count-to="{{ (int) ($totalPackages ?? 0) }}">0</p>
                </div>
                <div class="stat-icon stat-icon-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Vendus <span class="value-success" data-count-to="{{ (int) ($soldPackages ?? 0) }}">0</span></span>
                <span>Produits <span class="value-success" data-count-to="{{ (int) ($totalProducts ?? 0) }}">0</span></span>
            </div>
        </div>
    </div>

    <!-- Graph + Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">

        <!-- Monthly registrations — premium -->
        @php
            $registrationSeries = collect($monthlyData ?? []);
            $registrationsTotal = (int) $registrationSeries->sum('users');
            $registrationsPeak = (int) ($registrationSeries->max('users') ?? 0);
        @endphp
        <div class="card registrations-chart-card lg:col-span-2 animate-fadeInUp delay-5">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-2">
                <div>
                    <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                        Inscriptions mensuelles
                    </h3>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5">
                        Tendance sur 6 mois · {{ now()->format('Y') }}
                    </p>
                </div>
                <div class="registrations-chart-meta">
                    <span class="meta-total"
                          id="registrationsTotalCount"
                          data-count-to="{{ $registrationsTotal }}">0</span>
                    <span class="meta-label">
                        total · pic <span id="registrationsPeakCount" data-count-to="{{ $registrationsPeak }}">0</span>
                    </span>
                </div>
            </div>

            <div class="registrations-chart-shell">
                @if($registrationSeries->isEmpty())
                    <div class="w-full h-full flex items-center justify-center text-[var(--text-secondary)] text-sm">
                        Aucune donnée disponible
                    </div>
                @else
                    <canvas id="adminRegistrationsChart" aria-label="Graphique des inscriptions mensuelles"></canvas>
                @endif
            </div>
        </div>

        <!-- Package distribution - CORRIGÉ -->
        <div class="card animate-fadeInUp delay-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                Distribution des packages
            </h3>

            @php
                $totalPackageUsers = $packageDistribution->sum('users_count') ?? 0;
                $progressColors = ['#0A2A6C', '#1C7E4A', '#065F9C', '#B54708'];
            @endphp

            @forelse($packageDistribution ?? [] as $index => $pkg)
                @php
                    $percent = $totalPackageUsers > 0 ? ($pkg->users_count / $totalPackageUsers) * 100 : 0;
                    $displayPercent = max(8, $percent);
                    $color = $progressColors[$index % count($progressColors)];
                @endphp
                <div class="progress-item animate-fadeInUp" style="animation-delay: {{ 0.58 + ($index * 0.1) }}s;">
                    <div class="progress-header">
                        <span class="progress-label">{{ $pkg->name }}</span>
                        <span class="progress-value" data-count-to="{{ (int) $pkg->users_count }}">0</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ $displayPercent }}%; background: {{ $color }}; animation-delay: {{ 0.55 + ($index * 0.12) }}s;"></div>
                    </div>
                </div>
            @empty
                <p class="text-center text-[var(--text-secondary)] py-3 text-sm">
                    Aucun package disponible
                </p>
            @endforelse

            <div class="mt-3 p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                <p class="text-xl font-bold text-[var(--primary-blue)]" data-count-to="{{ (int) ($totalUsers ?? 0) }}">0</p>
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
    <div class="admin-quick-rail grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">

        <a href="{{ route('admin.users.create') }}" class="quick-action animate-fadeInUp delay-9">
            <div class="quick-icon stat-icon-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <p class="quick-label">Ajouter un utilisateur</p>
        </a>

        <a href="{{ route('admin.packages.create') }}" class="quick-action animate-fadeInUp delay-10">
            <div class="quick-icon stat-icon-success">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </div>
            <p class="quick-label">Créer un package</p>
        </a>

        <a href="{{ route('admin.products.create') }}" class="quick-action animate-fadeInUp delay-11">
            <div class="quick-icon stat-icon-info">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="quick-label">Ajouter un produit</p>
        </a>

        <a href="{{ route('admin.commissions') }}" class="quick-action animate-fadeInUp delay-12">
            <div class="quick-icon stat-icon-purple">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="quick-label">Voir commissions</p>
        </a>

    </div>

@endsection

@push('scripts')
@php
    $registrationsChartPayload = collect($monthlyData ?? [])->map(fn ($row) => [
        'month' => (string) ($row->month ?? ''),
        'users' => (int) ($row->users ?? 0),
    ])->values();
@endphp
<script defer>
document.addEventListener('DOMContentLoaded', function () {
    function easeInOutSine(t) {
        return -(Math.cos(Math.PI * t) - 1) / 2;
    }

    function animateCount(el, duration) {
        if (!el || el.dataset.countReady === '1') return;
        el.dataset.countReady = '1';

        const target = Number(el.getAttribute('data-count-to') || 0);
        const prefix = el.getAttribute('data-count-prefix') || '';
        const decimals = Number(el.getAttribute('data-count-decimals') || 0);
        const start = performance.now();

        const format = function (n) {
            if (decimals > 0) {
                return prefix + Number(n).toLocaleString('fr-FR', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });
            }
            return prefix + Math.round(n).toLocaleString('fr-FR');
        };

        if (!Number.isFinite(target) || target <= 0) {
            el.textContent = format(0);
            return;
        }

        el.textContent = format(0);

        function frame(now) {
            const progress = Math.min(1, (now - start) / duration);
            const value = target * easeInOutSine(progress);
            el.textContent = format(value);
            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                el.textContent = format(target);
            }
        }

        requestAnimationFrame(frame);
    }

    document.querySelectorAll('[data-count-to]').forEach(function (el, index) {
        const duration = el.id === 'registrationsTotalCount'
            ? 3200
            : (el.id === 'registrationsPeakCount' ? 2800 : 1800 + (index * 40));
        animateCount(el, duration);
    });
});
</script>
@if($registrationsChartPayload->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" defer></script>
<script defer>
document.addEventListener('DOMContentLoaded', function () {
    function bootAdminRegistrationsChart() {
        if (typeof Chart === 'undefined') {
            return false;
        }

        const canvas = document.getElementById('adminRegistrationsChart');
        if (!canvas) {
            return true;
        }

        if (canvas.dataset.chartReady === '1') {
            return true;
        }
        canvas.dataset.chartReady = '1';

        const monthlyData = @json($registrationsChartPayload);
        const labels = monthlyData.map((item) => item.month);
        const values = monthlyData.map((item) => Number(item.users || 0));
        const shell = canvas.parentElement;
        const height = (shell && shell.clientHeight) ? shell.clientHeight : 220;

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, 'rgba(30, 93, 173, 0.32)');
        gradient.addColorStop(0.55, 'rgba(30, 93, 173, 0.10)');
        gradient.addColorStop(1, 'rgba(30, 93, 173, 0.01)');

        const totalDuration = 3200;
        const delayBetweenPoints = totalDuration / Math.max(values.length, 1);
        const previousY = function (ctx) {
            if (ctx.index === 0) {
                return ctx.chart.scales.y.getPixelForValue(0);
            }
            const meta = ctx.chart.getDatasetMeta(ctx.datasetIndex);
            return meta.data[ctx.index - 1].getProps(['y'], true).y;
        };

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Inscriptions',
                    data: values,
                    borderColor: '#1E5DAD',
                    backgroundColor: gradient,
                    borderWidth: 2.75,
                    borderCapStyle: 'round',
                    borderJoinStyle: 'round',
                    fill: true,
                    tension: 0.5,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#1E5DAD',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#1E5DAD',
                    pointHoverBorderColor: '#FFFFFF',
                    pointHoverBorderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    // Courbe qui avance doucement, comme de l’eau
                    x: {
                        type: 'number',
                        easing: 'easeInOutSine',
                        duration: delayBetweenPoints,
                        from: NaN,
                        delay: function (ctx) {
                            if (ctx.type !== 'data' || ctx.xStarted) {
                                return 0;
                            }
                            ctx.xStarted = true;
                            return ctx.index * delayBetweenPoints;
                        },
                    },
                    y: {
                        type: 'number',
                        easing: 'easeInOutSine',
                        duration: delayBetweenPoints,
                        from: previousY,
                        delay: function (ctx) {
                            if (ctx.type !== 'data' || ctx.yStarted) {
                                return 0;
                            }
                            ctx.yStarted = true;
                            return ctx.index * delayBetweenPoints;
                        },
                    },
                    radius: {
                        duration: 650,
                        easing: 'easeOutQuad',
                        from: 0,
                        delay: function (ctx) {
                            if (ctx.type !== 'data') return 0;
                            return (ctx.index * delayBetweenPoints) + (delayBetweenPoints * 0.55);
                        },
                    },
                },
                transitions: {
                    active: {
                        animation: { duration: 320, easing: 'easeOutQuad' },
                    },
                },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(10, 42, 108, 0.95)',
                        titleColor: '#FFFFFF',
                        bodyColor: 'rgba(255,255,255,0.9)',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        animation: { duration: 220, easing: 'easeOutQuad' },
                        callbacks: {
                            label: function (context) {
                                const n = context.parsed.y || 0;
                                return n + ' inscription' + (n > 1 ? 's' : '');
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: 'rgba(100, 116, 139, 0.9)',
                            font: { size: 11, weight: '500' },
                            maxRotation: 0,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grace: '12%',
                        border: { display: false },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)',
                            drawTicks: false,
                        },
                        ticks: {
                            precision: 0,
                            color: 'rgba(100, 116, 139, 0.85)',
                            font: { size: 10 },
                            padding: 8,
                        },
                    },
                },
            },
            plugins: [{
                id: 'revealPointsAfterFlow',
                afterRender: function (chart) {
                    if (chart.$pointsRevealed) return;
                    chart.$pointsRevealed = true;
                    const ds = chart.data.datasets[0];
                    if (!ds) return;
                    setTimeout(function () {
                        ds.pointRadius = 4;
                        chart.update({
                            duration: 700,
                            easing: 'easeInOutSine',
                        });
                    }, totalDuration + 120);
                },
            }],
        });

        return true;
    }

    if (!bootAdminRegistrationsChart()) {
        let tries = 0;
        const timer = setInterval(function () {
            tries += 1;
            if (bootAdminRegistrationsChart() || tries > 40) {
                clearInterval(timer);
            }
        }, 100);
    }
});
</script>
@endif
@endpush