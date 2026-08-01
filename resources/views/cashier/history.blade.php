{{-- resources/views/cashier/history.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .history-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .history-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    .history-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }
    .history-stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .history-stat-card .number.green { color: #22c55e; }
    .history-stat-card .number.blue { color: #3b82f6; }
    .history-stat-card .number.purple { color: #8b5cf6; }
    .history-stat-card .number.orange { color: #f59e0b; }
    .history-stat-card .number.red { color: #ef4444; }
    .history-stat-card .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .history-stat-card .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    .icon-green { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .icon-blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .icon-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .icon-orange { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .icon-red { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    
    .history-table {
        width: 100%;
        font-size: 0.813rem;
    }
    .history-table th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
        font-weight: 700;
        white-space: nowrap;
    }
    .history-table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        vertical-align: middle;
    }
    .history-table tr:hover td {
        background: var(--bg-hover);
    }
    .history-table .text-right {
        text-align: right;
    }
    .history-table .text-center {
        text-align: center;
    }
    
    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .filter-section input {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.813rem;
        flex: 1;
        min-width: 120px;
    }
    .filter-section input:focus {
        border-color: var(--primary-500);
        outline: none;
        box-shadow: 0 0 0 3px var(--border-focus);
    }
    .filter-section .btn-filter {
        padding: 0.375rem 1.5rem;
        background: var(--primary-500);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-section .btn-filter:hover {
        background: var(--primary-600);
        transform: translateY(-1px);
    }
    .filter-section .btn-reset {
        padding: 0.375rem 1.5rem;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-weight: 500;
        font-size: 0.813rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-section .btn-reset:hover {
        background: var(--bg-hover);
    }
    
    .badge-cash {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    
    .badge-paid {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    
    .badge-partial {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    
    .badge-commission {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    
    .total-pv {
        font-weight: 600;
        color: #22c55e;
    }
    
    .commission-amount {
        font-weight: 600;
        color: #d97706;
    }
    
    .rest-amount {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .rest-amount.positive {
        color: #ef4444;
    }
    
    .rest-amount.zero {
        color: #22c55e;
    }
    
    @media (max-width: 768px) {
        .history-stats {
            grid-template-columns: 1fr 1fr;
        }
        .history-table {
            font-size: 0.7rem;
        }
        .history-table th,
        .history-table td {
            padding: 0.3rem 0.4rem;
        }
        .filter-section {
            flex-direction: column;
        }
        .filter-section input,
        .filter-section button {
            width: 100%;
            min-width: unset;
        }
    }
</style>
@endpush

@section('title', 'Historique des ventes POS')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <span class="text-primary-500"></span> Historique des ventes POS
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Historique complet des ventes effectuées au guichet
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle vente
            </a>
            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="history-stats animate-fadeInUp delay-1">
        <div class="history-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="number blue">{{ $stats['total_orders'] ?? 0 }}</div>
            <div class="label">Total commandes</div>
        </div>
        
        <div class="history-stat-card animate-fadeInUp delay-2">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number green">${{ number_format($stats['total_sales'] ?? 0, 2) }}</div>
            <div class="label">Total encaissé</div>
        </div>
        
        <div class="history-stat-card animate-fadeInUp delay-3">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number purple">{{ number_format($stats['total_pv'] ?? 0) }}</div>
            <div class="label">Total PV distribués</div>
        </div>
        
        <div class="history-stat-card animate-fadeInUp delay-4">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="number orange">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <div class="label">Total commissions CASH</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card animate-fadeInUp delay-2">
        <form method="GET" action="{{ route('cashier.history') }}" class="filter-section">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Rechercher (nom, téléphone, commande)" class="flex-1">
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Date début" class="flex-1">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Date fin" class="flex-1">
            <button type="submit" class="btn-filter">🔍 Filtrer</button>
            <a href="{{ route('cashier.history') }}" class="btn-reset">↺ Réinitialiser</a>
        </form>
    </div>

    <!-- Tableau d'historique -->
    <div class="card animate-fadeInUp delay-3">
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
                            // Total des produits
                            $totalProducts = $order->subtotal;
                            
                            // Récupérer le sponsor
                            $sponsor = null;
                            if (isset($order->metadata['sponsor_id'])) {
                                $sponsor = \App\Models\User::find($order->metadata['sponsor_id']);
                            }
                            
                            // Récupérer la commission POS
                            $commission = \App\Models\Commission::where('order_id', $order->id)
                                ->where('source', 'pos')
                                ->where('type', 'cash_pos')
                                ->first();
                            $commissionAmount = $commission ? $commission->amount : 0;
                            
                            // Montant payé = total (le client a payé le prix total)
                            $paidAmount = $totalProducts;
                            
                            // Reste = 0 car le client a payé le montant total
                            $rest = 0;
                            
                            // Total PV
                            $totalPV = $order->items->sum(function($item) {
                                return ($item->pv_value ?? 0) * $item->quantity;
                            });
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap text-xs text-[var(--text-secondary)]">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="font-mono text-xs text-primary-500">
                                #{{ $order->order_number }}
                            </td>
                            <td>
                                <div class="font-medium text-sm">{{ $order->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">{{ $order->user?->phone ?? 'N/A' }}</div>
                            </td>
                            <td>
                                @if($sponsor)
                                    <div class="font-mono text-xs text-primary-500">{{ $sponsor->sponsor_id ?? 'N/A' }}</div>
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
                                <span class="text-green-500 font-semibold">
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
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-lg font-medium text-[var(--text-primary)]">Aucune vente POS</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">L'historique des ventes apparaîtra ici</p>
                                <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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