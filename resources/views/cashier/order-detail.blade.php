{{-- resources/views/cashier/order-detail.blade.php --}}
@extends('cashier.layouts.app')

@section('title', 'Détail commande POS')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Commande POS #{{ $order->order_number }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Détails de la commande au guichet
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

    <!-- Badge Source POS -->
    <div class="animate-fadeInUp delay-1">
        <div class="inline-flex items-center gap-2 bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-2">
            <span class="text-lg"></span>
            <span class="font-semibold text-green-600 dark:text-green-400">Commande POS (Guichet)</span>
            <span class="text-xs text-[var(--text-secondary)] ml-2">#{{ $order->order_number }}</span>
        </div>
    </div>

    <!-- Détails -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-2">
        
        <!-- Articles -->
        <div class="lg:col-span-2 card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Articles</h3>

            <div class="space-y-2 sm:space-y-3">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between py-2 border-b border-[var(--border-light)]">
                        <div>
                            <p class="font-medium text-[var(--text-primary)] text-sm sm:text-base">{{ $item->name }}</p>
                            <p class="text-sm text-[var(--text-secondary)]">
                                Qté: {{ $item->quantity }} x ${{ number_format($item->price, 2) }}
                                @if($item->pv_value > 0)
                                    <span class="ml-2 text-green-500 font-medium">{{ $item->pv_value }} PV</span>
                                @endif
                            </p>
                        </div>
                        <p class="font-bold text-primary-500 text-sm sm:text-base">${{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
                
                @php
                    $totalPV = $order->items->sum(function($item) {
                        return ($item->pv_value ?? 0) * $item->quantity;
                    });
                @endphp
                @if($totalPV > 0)
                    <div class="mt-2 p-2 bg-green-500/10 border border-green-500/20 rounded-lg text-center">
                        <span class="text-sm font-medium text-green-500"> Total PV: {{ $totalPV }} PV</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Résumé -->
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Résumé</h3>

            <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between border-t border-[var(--border-color)] pt-2 sm:pt-3 mt-2 sm:mt-3">
                    <div class="flex justify-between text-base sm:text-lg font-bold w-full">
                        <span>Total</span>
                        <span class="text-primary-500">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-[var(--border-color)]">
                <div class="flex justify-between text-xs sm:text-sm">
                    <span class="text-[var(--text-secondary)]">Statut paiement</span>
                    <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : 'badge-warning' }}">
                        {{ $order->payment_status == 'completed' ? ' Payé' : ' En attente' }}
                    </span>
                </div>
                <div class="flex justify-between text-xs sm:text-sm mt-1 sm:mt-2">
                    <span class="text-[var(--text-secondary)]">Méthode paiement</span>
                    <span class="font-medium">
                        @if($order->payment_method == 'cash')
                             Espèces
                        @else
                            {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}
                        @endif
                    </span>
                </div>
                <div class="flex justify-between text-xs sm:text-sm mt-1 sm:mt-2">
                    <span class="text-[var(--text-secondary)]">Client</span>
                    <span class="font-medium">{{ $order->user?->name ?? 'N/A' }}</span>
                </div>
                @if(isset($order->metadata['sponsor_id']))
                    @php
                        $sponsor = \App\Models\User::find($order->metadata['sponsor_id']);
                    @endphp
                    @if($sponsor)
                    <div class="flex justify-between text-xs sm:text-sm mt-1 sm:mt-2">
                        <span class="text-[var(--text-secondary)]"> Parrain</span>
                        <span class="font-medium text-primary-500">{{ $sponsor->name }}</span>
                    </div>
                    <div class="flex justify-between text-xs sm:text-sm mt-1">
                        <span class="text-[var(--text-secondary)]">Code parrain</span>
                        <span class="font-mono text-xs">{{ $sponsor->sponsor_id ?? 'N/A' }}</span>
                    </div>
                    @endif
                @endif
                @if(isset($order->metadata['cashier_name']))
                <div class="flex justify-between text-xs sm:text-sm mt-1 sm:mt-2">
                    <span class="text-[var(--text-secondary)]"> Caissier</span>
                    <span class="font-medium">{{ $order->metadata['cashier_name'] }}</span>
                </div>
                @endif
                <div class="flex justify-between text-xs sm:text-sm mt-1 sm:mt-2 pt-2 border-t border-[var(--border-light)]">
                    <span class="text-[var(--text-secondary)]">Source</span>
                    <span class="font-medium text-green-500"> POS (Guichet)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Information PV -->
    @if($totalPV > 0)
    <div class="card animate-fadeInUp delay-3" style="background: rgba(34, 197, 94, 0.05); border-color: rgba(34, 197, 94, 0.15);">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <span class="font-semibold text-green-600 dark:text-green-400">{{ $totalPV }} PV</span>
                <span class="text-sm text-[var(--text-secondary)] ml-1">crédités au parrain pour la montée en grade</span>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection