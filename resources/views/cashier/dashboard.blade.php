@extends('cashier.layouts.app')

@section('title', 'Tableau de bord')

@push('styles')
<style>
    /* ============================================================
       STAT CARDS – Sobres, sans ombres excessives
       ============================================================ */
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem 1.25rem;
        transition: background 0.2s ease;
    }

    .stat-card .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-sales { background: rgba(34, 197, 94, 0.10); color: #22c55e; }
    .stat-icon-orders { background: rgba(30, 93, 173, 0.10); color: var(--primary); }
    .stat-icon-customers { background: rgba(245, 166, 35, 0.12); color: var(--color-accent-600); }
    .stat-icon-pending { background: rgba(179, 42, 42, 0.10); color: #b32a2a; }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
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

    .stat-card .stat-detail .value-warning {
        color: var(--color-accent-600);
        font-weight: 600;
    }

    /* ============================================================
       QUICK ACTIONS (aligné admin mobile)
       ============================================================ */
    .quick-action {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 0.875rem 1rem;
        text-align: center;
        transition: background 0.2s ease, border-color 0.2s ease;
        text-decoration: none !important;
        display: block;
    }

    .quick-action:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
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
        line-height: 1.2;
    }

    /* ============================================================
       ALERTE – Compte inactif
       ============================================================ */
    .alert-inactive {
        background: rgba(245, 158, 11, 0.06);
        border: 1px solid rgba(245, 158, 11, 0.20);
        border-radius: var(--radius-md, 8px);
        padding: 0.75rem 1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-inactive .alert-icon {
        color: #f59e0b;
        flex-shrink: 0;
    }

    .alert-inactive .alert-title {
        font-weight: 600;
        color: #f59e0b;
        font-size: 0.875rem;
    }

    .alert-inactive .alert-text {
        color: var(--text-secondary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 120px;
    }

    /* ============================================================
       BOUTONS AMÉLIORÉS
       ============================================================ */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: var(--primary-hover, #134178);
    }

    .btn-success {
        background: #22c55e;
        color: #FFFFFF;
    }
    .btn-success:hover {
        background: #16a34a;
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.15rem 0.5rem;
        font-size: 0.65rem;
    }

    /* ============================================================
       BADGE MEMBERSHIP
       ============================================================ */
    .badge-membership {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: rgba(139, 92, 246, 0.12);
        color: #7c3aed;
        border: 1px solid rgba(139, 92, 246, 0.15);
    }

    /* ============================================================
       TABLE – Sobriété
       ============================================================ */
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.813rem;
    }

    .table thead th {
        text-align: left;
        padding: 0.6rem 0.75rem;
        font-weight: 600;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody td {
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover td {
        background: var(--bg-secondary);
    }

    .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }

    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
    }

    .badge-danger {
        background: rgba(179, 42, 42, 0.12);
        color: #b32a2a;
    }

    .badge-info {
        background: rgba(30, 93, 173, 0.10);
        color: var(--primary);
    }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 640px) {
        .stat-card { padding: 0.75rem 1rem; }
        .stat-value { font-size: 1.25rem; }
        .quick-action { padding: 0.75rem 0.5rem; }
        .quick-action .icon { width: 1.25rem; height: 1.25rem; }
        .alert-inactive { padding: 0.625rem 0.75rem; }
        .alert-inactive .alert-text { font-size: 0.75rem; }
        .table thead th, .table tbody td { padding: 0.4rem 0.5rem; font-size: 0.7rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .btn-xs { padding: 0.1rem 0.375rem; font-size: 0.6rem; }
    }

    @media (max-width: 480px) {
        .stat-value { font-size: 1rem; }
        .stat-card { padding: 0.5rem 0.75rem; }
        .stat-card .stat-icon { width: 2rem; height: 2rem; }
        .stat-card .stat-icon svg { width: 1.25rem; height: 1.25rem; }
        .quick-action .label { font-size: 0.65rem; }
    }
</style>
@endpush

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

@push('cashier_mobile_page_extra_classes')
admin-mobile-stack
@endpush

@push('cashier_mobile_greeting')
    <div class="admin-mobile-greeting md:hidden animate-fadeInUp">
        <h1>Tableau de bord</h1>
        <p>{{ $salutation }}, {{ Auth::user()->name ?? 'Caissier' }} · {{ date('H:i') }}</p>
    </div>
@endpush

@section('content')

    {{-- ALERTE : Compte inactif --}}
    @if(isset($user) && !$user->is_active)
        <div class="alert-inactive">
            <svg class="alert-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="alert-title">Compte inactif</span>
            <span class="alert-text">Activez votre compte pour recevoir des commissions et accéder à toutes les fonctionnalités.</span>
            <a href="{{ route('activate.index') }}" class="btn btn-primary btn-sm">
                Activer maintenant
            </a>
        </div>
    @endif

    {{-- EN-TÊTE desktop --}}
    <div class="admin-mobile-page-head animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
            Tableau de bord caisse
        </h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">
            {{ $salutation }}, <span class="font-semibold text-[var(--primary)]">{{ Auth::user()->name ?? 'Caissier' }}</span>
            <span class="text-[var(--text-tertiary)] text-xs ml-1">({{ date('H:i') }})</span>
        </p>
    </div>

    <div class="admin-sticky-toolbar md:hidden w-full">
        <div class="admin-page-header-actions flex w-full">
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nouvelle vente</span>
            </a>
        </div>
    </div>

    <div class="hidden md:flex justify-end -mt-2 mb-1">
        <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle vente
        </a>
    </div>

    {{-- STATISTIQUES --}}
    <div class="admin-kpi-rail grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <div class="stat-card animate-fadeInUp delay-1">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Commandes</p>
                    <p class="stat-value text-[var(--primary)]">{{ $stats['total_orders_today'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-orders">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Aujourd'hui</span>
                <span>En attente <span class="value-warning">{{ $stats['pending_orders'] ?? 0 }}</span></span>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Ventes</p>
                    <p class="stat-value text-[var(--ui-stat-success)]">${{ number_format($stats['total_sales_today'] ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-sales">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Aujourd'hui</span>
                <span>Caisse</span>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">Clients</p>
                    <p class="stat-value text-[var(--color-accent-600)]">{{ $stats['customers_today'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-customers">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Aujourd'hui</span>
                <span>Comptés</span>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="stat-label">En attente</p>
                    <p class="stat-value text-[var(--ui-stat-danger)]">{{ $stats['pending_orders'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-pending">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-detail">
                <span>Commandes</span>
                <span>À traiter</span>
            </div>
        </div>
    </div>

    {{-- COMMANDES RÉCENTES --}}
    <div class="card p-3 sm:p-4 animate-fadeInUp delay-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Commandes récentes</h3>
            <a href="{{ route('cashier.orders') }}" class="text-sm text-[var(--primary)] hover:underline">
                Voir tout
            </a>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>N° commande</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th class="text-right">Total</th>
                        <th>Statut</th>
                        <th class="hidden sm:table-cell">Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders ?? [] as $order)
                        @php
                            $isMembership = ($order->source ?? '') === 'membership' || isset($order->metadata['is_membership']);
                        @endphp
                        <tr>
                            <td class="font-mono text-[var(--primary)] text-xs sm:text-sm">#{{ $order->order_number }}</td>
                            <td class="text-sm">{{ $order->user?->name ?? 'N/A' }}</td>
                            <td>
                                @if($isMembership)
                                    <span class="badge-membership">Adhésion</span>
                                @elseif($order->source === 'pos')
                                    <span class="badge badge-info">POS</span>
                                @elseif($order->source === 'mlm')
                                    <span class="badge badge-success">MLM</span>
                                @else
                                    <span class="badge badge-warning">{{ $order->source ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td class="text-right font-semibold text-sm">${{ number_format($order->total, 2) }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        'completed' => ['label' => 'Terminée', 'class' => 'badge-success'],
                                        'pending' => ['label' => 'En attente', 'class' => 'badge-warning'],
                                        'cancelled' => ['label' => 'Annulée', 'class' => 'badge-danger'],
                                        'processing' => ['label' => 'En cours', 'class' => 'badge-info'],
                                    ];
                                    $status = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'badge-info'];
                                @endphp
                                <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-tertiary)] text-xs">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('cashier.orders.show', $order->id) }}" class="btn btn-primary btn-xs">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-[var(--text-tertiary)] text-sm">
                                Aucune commande récente
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ACTIONS RAPIDES --}}
    <div class="admin-quick-rail grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-6">
        <a href="{{ route('cashier.pos') }}" class="quick-action">
            <div class="quick-icon stat-icon-orders">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="quick-label">Point de vente</p>
        </a>

        <a href="{{ route('cashier.members') }}" class="quick-action">
            <div class="quick-icon stat-icon-customers">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="quick-label">Membres</p>
        </a>

        <a href="{{ route('cashier.orders') }}" class="quick-action">
            <div class="quick-icon stat-icon-orders">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="quick-label">Commandes</p>
        </a>

        <a href="{{ route('cashier.commissions') }}" class="quick-action">
            <div class="quick-icon stat-icon-sales">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="quick-label">Commissions</p>
        </a>
    </div>

@endsection