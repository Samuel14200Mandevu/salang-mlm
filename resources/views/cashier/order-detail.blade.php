{{-- resources/views/cashier/order-detail.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .detail-card:hover {
        border-color: var(--primary-500);
    }
    
    .badge-source-pos {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-source-web {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
    }
    .badge-source-mlm {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
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
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
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
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .item-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-light);
    }
    .item-line:last-child {
        border-bottom: none;
    }
    .item-line .item-name {
        font-weight: 500;
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    .item-line .item-meta {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .item-line .item-total {
        font-weight: 700;
        color: var(--primary-500);
        font-size: 0.875rem;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    
    @media (max-width: 640px) {
        .detail-card { padding: 0.875rem; }
        .card { padding: 0.875rem; }
        .btn-sm { font-size: 0.65rem; padding: 0.25rem 0.5rem; }
        .item-line { padding: 0.5rem 0; }
        .item-line .item-name { font-size: 0.8rem; }
        .detail-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@section('title', 'Détail commande')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Commande #{{ $order->order_number }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Détails de la commande
            </p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('cashier.orders.invoice', $order->id) }}" class="btn btn-primary btn-sm sm:btn-md" target="_blank">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                Facture
            </a>
            <a href="{{ route('cashier.orders') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Badge Source -->
    <div class="animate-fadeInUp delay-1">
        <div class="inline-flex items-center gap-2 rounded-lg px-4 py-2
            @if($order->source == 'pos')
                bg-green-500/10 border border-green-500/20
            @elseif($order->source == 'web' || $order->source == 'online')
                bg-purple-500/10 border border-purple-500/20
            @else
                bg-blue-500/10 border border-blue-500/20
            @endif
        ">
            <span class="text-lg">
                @if($order->source == 'pos') 
                @elseif($order->source == 'web' || $order->source == 'online') 
                @else 
                @endif
            </span>
            <span class="font-semibold
                @if($order->source == 'pos') text-green-600 dark:text-green-400
                @elseif($order->source == 'web' || $order->source == 'online') text-purple-600 dark:text-purple-400
                @else text-blue-600 dark:text-blue-400
                @endif
            ">
                @if($order->source == 'pos') Commande POS (Guichet)
                @elseif($order->source == 'web' || $order->source == 'online') Commande en ligne
                @else Commande MLM
                @endif
            </span>
            <span class="text-xs text-[var(--text-secondary)] ml-2">#{{ $order->order_number }}</span>
            <span class="badge {{ $order->status == 'completed' ? 'badge-success' : ($order->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                {{ $order->status == 'completed' ? 'Terminée' : ($order->status == 'pending' ? 'En attente' : ($order->status == 'cancelled' ? 'Annulée' : ucfirst($order->status))) }}
            </span>
        </div>
    </div>

    <!-- Détails -->
    <div class="detail-grid grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-2">
        
        <!-- Articles -->
        <div class="lg:col-span-2 card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Articles</h3>

            <div class="space-y-1">
                @foreach($order->items as $item)
                    <div class="item-line">
                        <div>
                            <p class="item-name">{{ $item->name }}</p>
                            <p class="item-meta">
                                Qté: {{ $item->quantity }} x ${{ number_format($item->price, 2) }}
                                @if($item->pv_value > 0)
                                    <span class="ml-2 text-green-500 font-medium">{{ $item->pv_value }} PV</span>
                                @endif
                                @if($item->bv_value > 0)
                                    <span class="ml-2 text-purple-500 font-medium">{{ $item->bv_value }} BV</span>
                                @endif
                            </p>
                        </div>
                        <p class="item-total">${{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
                
                @php
                    $totalPV = $order->items->sum(function($item) {
                        return ($item->pv_value ?? 0) * $item->quantity;
                    });
                    $totalBV = $order->items->sum(function($item) {
                        return ($item->bv_value ?? 0) * $item->quantity;
                    });
                @endphp
                
                @if($totalPV > 0 || $totalBV > 0)
                    <div class="mt-3 p-3 bg-green-500/5 border border-green-500/20 rounded-lg">
                        <div class="flex flex-wrap items-center justify-center gap-4 text-sm">
                            @if($totalPV > 0)
                                <span class="font-medium text-green-500"> Total PV: {{ $totalPV }} PV</span>
                            @endif
                            @if($totalBV > 0)
                                <span class="font-medium text-purple-500"> Total BV: {{ $totalBV }} BV</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Résumé -->
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Résumé</h3>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Sous-total</span>
                    <span class="font-medium">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->tax > 0)
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Taxe</span>
                    <span class="font-medium">${{ number_format($order->tax, 2) }}</span>
                </div>
                @endif
                @if($order->shipping > 0)
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Livraison</span>
                    <span class="font-medium">${{ number_format($order->shipping, 2) }}</span>
                </div>
                @endif
                @if($order->discount > 0)
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Réduction</span>
                    <span class="font-medium text-red-500">-${{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="border-t border-[var(--border-color)] pt-3 mt-2">
                    <div class="flex justify-between text-base sm:text-lg font-bold">
                        <span>Total</span>
                        <span class="text-primary-500">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-[var(--border-color)] space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Statut paiement</span>
                    <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : 'badge-warning' }}">
                        {{ $order->payment_status == 'completed' ? 'Payé' : 'En attente' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Méthode paiement</span>
                    <span class="font-medium">
                        @if($order->payment_method == 'cash')
                            Espèces
                        @elseif($order->payment_method == 'mobile_money')
                            Mobile Money
                        @elseif($order->payment_method == 'card')
                            Carte bancaire
                        @elseif($order->payment_method == 'bank_transfer')
                            Virement bancaire
                        @else
                            {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Client</span>
                    <span class="font-medium">{{ $order->user?->name ?? 'N/A' }}</span>
                </div>
                @if(isset($order->metadata['sponsor_id']))
                    @php
                        $sponsor = \App\Models\User::find($order->metadata['sponsor_id']);
                    @endphp
                    @if($sponsor)
                    <div class="flex justify-between">
                        <span class="text-[var(--text-secondary)]">Parrain</span>
                        <span class="font-medium text-primary-500">{{ $sponsor->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[var(--text-secondary)]">Code parrain</span>
                        <span class="font-mono text-xs">{{ $sponsor->sponsor_id ?? 'N/A' }}</span>
                    </div>
                    @endif
                @endif
                @if(isset($order->metadata['cashier_name']))
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Caissier</span>
                    <span class="font-medium">{{ $order->metadata['cashier_name'] }}</span>
                </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-[var(--border-light)]">
                    <span class="text-[var(--text-secondary)]">Source</span>
                    <span class="font-medium
                        @if($order->source == 'pos') text-green-500
                        @elseif($order->source == 'web' || $order->source == 'online') text-purple-500
                        @else text-blue-500
                        @endif
                    ">
                        @if($order->source == 'pos') POS (Guichet)
                        @elseif($order->source == 'web' || $order->source == 'online') En ligne
                        @else MLM
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Date</span>
                    <span class="font-medium text-sm">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection