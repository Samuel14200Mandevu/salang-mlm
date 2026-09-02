{{-- resources/views/cashier/history.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .history-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .history-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem;
        text-align: center;
    }
    .history-stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .history-stat-card .number.green { color: #22c55e; }
    .history-stat-card .number.blue { color: #2563eb; }
    .history-stat-card .number.purple { color: #8b5cf6; }
    .history-stat-card .number.orange { color: #d97706; }
    .history-stat-card .number.red { color: #b32a2a; }
    .history-stat-card .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .history-stat-card .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 6px);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    .icon-green { background: rgba(34, 197, 94, 0.10); color: #22c55e; }
    .icon-blue { background: rgba(59, 130, 246, 0.10); color: #2563eb; }
    .icon-purple { background: rgba(139, 92, 246, 0.10); color: #8b5cf6; }
    .icon-orange { background: rgba(245, 158, 11, 0.10); color: #d97706; }
    .icon-red { background: rgba(179, 42, 42, 0.10); color: #b32a2a; }

    .history-table {
        width: 100%;
        font-size: 0.813rem;
        border-collapse: collapse;
    }
    .history-table th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
        white-space: nowrap;
    }
    .history-table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }
    .history-table tr:hover td {
        background: var(--bg-secondary);
    }
    .history-table .text-right { text-align: right; }
    .history-table .text-center { text-align: center; }

    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
        align-items: center;
    }
    .filter-section input {
        padding: 0.3rem 0.6rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 120px;
    }
    .filter-section input:focus {
        border-color: var(--primary);
        outline: none;
    }

    .badge-cash {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.15);
    }

    .badge-paid {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.15);
    }

    .badge-commission {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.15);
    }

    .total-pv {
        font-weight: 600;
        color: #16a34a;
    }

    .commission-amount {
        font-weight: 600;
        color: #d97706;
    }

    .rest-amount.zero { color: #16a34a; }
    .rest-amount.positive { color: #b32a2a; }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
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

    .filter-section .btn-filter {
        padding: 0.3rem 1.25rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius-md, 6px);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .filter-section .btn-filter:hover {
        background: var(--primary-hover, #091E3B);
    }

    .filter-section .btn-reset {
        padding: 0.3rem 1.25rem;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: background 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .filter-section .btn-reset:hover {
        background: var(--bg-secondary);
    }

    @media (max-width: 768px) {
        .history-stats { grid-template-columns: 1fr 1fr; }
        .history-table { font-size: 0.7rem; }
        .history-table th, .history-table td { padding: 0.3rem 0.4rem; }
        .filter-section { flex-direction: column; }
        .filter-section input, .filter-section button { width: 100%; min-width: unset; }
        .card { padding: 0.875rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
    }
</style>
@endpush

@section('title', 'Historique des ventes POS')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Historique des ventes POS
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Historique complet des ventes effectuées au guichet
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle vente
            </a>
            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
        </div>
    </div>

    {{-- STATISTIQUES --}}
    <div class="history-stats">
        <div class="history-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="number blue">{{ $stats['total_orders'] ?? 0 }}</div>
            <div class="label">Total commandes</div>
        </div>

        <div class="history-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number green">${{ number_format($stats['total_sales'] ?? 0, 2) }}</div>
            <div class="label">Total encaissé</div>
        </div>

        <div class="history-stat-card">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number purple">{{ number_format($stats['total_pv'] ?? 0) }}</div>
            <div class="label">Total PV distribués</div>
        </div>

        <div class="history-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number orange">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <div class="label">Total commissions CASH</div>
        </div>
    </div>

    {{-- FILTRES --}}
    <div class="card">
        <form method="GET" action="{{ route('cashier.history') }}" class="filter-section">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher (nom, téléphone, commande)">
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Date début">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Date fin">
            <button type="submit" class="btn-filter">Filtrer</button>
            <a href="{{ route('cashier.history') }}" class="btn-reset">↺ Réinitialiser</a>
        </form>
    </div>

    {{-- TABLEAU --}}
    <div class="card">
        <div class="table-wrap">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N° commande</th>
                        <th>Client</th>
                        <th>Code parrain</th>
                        <th class="text-right">Prix produits</th>
                        <th class="text-right">Commission CASH</th>
                        <th class="text-right">Payé</th>
                        <th class="text-right">Reste</th>
                        <th class="text-right">PV gagnés</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
                        @php
                            $totalProducts = $order->subtotal;

                            $sponsor = null;
                            if (isset($order->metadata['sponsor_id'])) {
                                $sponsor = \App\Models\User::find($order->metadata['sponsor_id']);
                            }

                            $commission = \App\Models\Commission::where('order_id', $order->id)
                                ->where('source', 'pos')
                                ->where('type', 'cash_pos')
                                ->first();
                            $commissionAmount = $commission ? $commission->amount : 0;

                            $paidAmount = $totalProducts;
                            $rest = 0;

                            $totalPV = $order->items->sum(function($item) {
                                return ($item->pv_value ?? 0) * $item->quantity;
                            });
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap text-xs text-[var(--text-tertiary)]">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="font-mono text-xs text-[var(--primary)]">
                                #{{ $order->order_number }}
                            </td>
                            <td>
                                <div class="font-medium text-sm">{{ $order->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">{{ $order->user?->phone ?? 'N/A' }}</div>
                            </td>
                            <td>
                                @if($sponsor)
                                    <div class="font-mono text-xs text-[var(--primary)]">{{ $sponsor->sponsor_id ?? 'N/A' }}</div>
                                    <div class="text-xs text-[var(--text-secondary)]">{{ $sponsor->name }}</div>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">Aucun</span>
                                @endif
                            </td>
                            <td class="text-right font-medium">
                                ${{ number_format($totalProducts, 2) }}
                            </td>
                            <td class="text-right">
                                <span class="commission-amount">${{ number_format($commissionAmount, 2) }}</span>
                                <span class="badge-commission ml-1">Commission</span>
                            </td>
                            <td class="text-right">
                                <span class="text-[#16a34a] font-semibold">
                                    ${{ number_format($paidAmount, 2) }}
                                </span>
                                <span class="badge-paid ml-1">Payé</span>
                            </td>
                            <td class="text-right">
                                <span class="rest-amount zero">
                                    ${{ number_format($rest, 2) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="total-pv">{{ number_format($totalPV) }} PV</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucune vente POS</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">L'historique des ventes apparaîtra ici</p>
                                <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Faire une vente
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($orders) && $orders->hasPages())
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection