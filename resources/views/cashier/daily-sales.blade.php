{{-- resources/views/cashier/daily-sales.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem 1.25rem;
        transition: background 0.2s ease;
    }

    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-card .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
    }

    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-orders { background: rgba(15, 43, 79, 0.10); color: var(--primary); }
    .stat-icon-amount { background: rgba(34, 197, 94, 0.10); color: #22c55e; }
    .stat-icon-average { background: rgba(139, 92, 246, 0.10); color: #8b5cf6; }
    .stat-icon-ratio { background: rgba(59, 130, 246, 0.10); color: #3b82f6; }

    .badge-source-pos {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }
    .badge-source-mlm {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
    }

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
        background: var(--primary-hover, #091E3B);
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

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

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
        padding: 0.6rem 0.75rem;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody td {
        padding: 0.6rem 0.75rem;
        color: var(--text-secondary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
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

    .badge-success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #2563eb; }

    .payment-method-card {
        background: var(--bg-secondary);
        border-radius: var(--radius-md, 8px);
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
    }

    .payment-method-card .method-name {
        font-size: 0.813rem;
        color: var(--text-secondary);
    }

    .payment-method-card .method-count {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
    }

    .payment-method-card .method-total {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    @media (max-width: 640px) {
        .stat-card { padding: 0.75rem 1rem; }
        .stat-card .stat-value { font-size: 1.25rem; }
        .stat-icon { width: 2rem; height: 2rem; }
        .stat-icon svg { width: 1.25rem; height: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.4rem 0.5rem; font-size: 0.7rem; }
        .card { padding: 0.875rem; }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .payment-method-card { padding: 0.5rem 0.75rem; }
        .payment-method-card .method-count { font-size: 1rem; }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        .table thead th, .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.6rem;
        }
    }
</style>
@endpush

@section('title', 'Ventes du jour')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-6 h-6 text-[var(--primary)] mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Ventes du jour
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {{ now()->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle vente
            </a>
        </div>
    </div>

    {{-- STATISTIQUES --}}
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Commandes</p>
                    <p class="stat-value text-[var(--primary)]">{{ $stats['total_orders'] ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-orders">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total encaissé</p>
                    <p class="stat-value text-[#22c55e]">${{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-amount">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Moyenne</p>
                    <p class="stat-value text-[#8b5cf6]">${{ number_format($stats['average_order'] ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon stat-icon-average">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">POS / MLM</p>
                    <p class="stat-value text-[#3b82f6]">
                        {{ $stats['pos_count'] ?? 0 }} / {{ $stats['mlm_count'] ?? 0 }}
                    </p>
                </div>
                <div class="stat-icon stat-icon-ratio">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- PAR MÉTHODE DE PAIEMENT --}}
    @if(isset($stats['payment_methods']) && count($stats['payment_methods']) > 0)
    <div class="card">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Par méthode de paiement</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            @foreach($stats['payment_methods'] as $method => $data)
                <div class="payment-method-card">
                    <p class="method-name">{{ ucfirst(str_replace('_', ' ', $method)) }}</p>
                    <p class="method-count">{{ $data['count'] }} commandes</p>
                    <p class="method-total">${{ number_format($data['total'], 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- LISTE DES VENTES --}}
    <div class="card">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Détail des ventes</h3>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° commande</th>
                        <th>Client / Membre</th>
                        <th>Source</th>
                        <th class="text-right">Total</th>
                        <th>Paiement</th>
                        <th class="hidden sm:table-cell">Heure</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales ?? [] as $sale)
                        <tr>
                            <td class="font-mono text-[var(--primary)] text-xs sm:text-sm">#{{ $sale->order_number }}</td>
                            <td class="text-sm">{{ $sale->user?->name ?? 'N/A' }}</td>
                            <td>
                                @if($sale->source == 'pos')
                                    <span class="badge badge-source-pos">POS</span>
                                @elseif($sale->source == 'mlm')
                                    <span class="badge badge-source-mlm">MLM</span>
                                @else
                                    <span class="badge badge-info">{{ strtoupper($sale->source ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td class="text-right font-semibold text-sm">${{ number_format($sale->total, 2) }}</td>
                            <td>
                                @if($sale->payment_method == 'cash')
                                    <span class="badge badge-success">Espèces</span>
                                @elseif($sale->payment_method == 'mobile_money')
                                    <span class="badge badge-info">Mobile Money</span>
                                @elseif($sale->payment_method == 'card')
                                    <span class="badge badge-info">Carte</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'N/A')) }}</span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-tertiary)] text-xs">
                                {{ $sale->created_at->format('H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-tertiary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                                <p class="text-base font-medium">Aucune vente aujourd'hui</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Commencez à vendre !</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection