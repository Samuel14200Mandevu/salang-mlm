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
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
    }
    .btn-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    }
    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
    }
    .btn-success {
        background: var(--gradient-success);
        color: white;
        box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.4);
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
    .badge-cancellation-pending { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-cancellation-approved { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-cancellation-rejected { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    
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
    
    /* Modal d'annulation */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        box-shadow: var(--shadow-xl);
        transform: scale(0.9);
        transition: transform 0.3s ease;
        border: 1px solid var(--border-color);
    }
    .modal-overlay.active .modal-box {
        transform: scale(1);
    }
    .modal-icon {
        width: 4rem;
        height: 4rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .modal-icon-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    .modal-title {
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .modal-text {
        text-align: center;
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    .modal-text .text-danger {
        color: #ef4444;
    }
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .modal-actions .btn {
        min-width: 100px;
        justify-content: center;
    }
    .modal-actions .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .modal-actions .btn:disabled:hover {
        transform: none !important;
    }
    
    .cancellation-status {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    .cancellation-status.pending {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.2);
        color: #f59e0b;
    }
    .cancellation-status.approved {
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.2);
        color: #22c55e;
    }
    .cancellation-status.rejected {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }
    
    /* Timer d'annulation */
    .cancellation-timer {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .cancellation-timer .time {
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        font-weight: 700;
    }
    .cancellation-timer.expired {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.2);
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
        .modal-box {
            padding: 1.5rem;
        }
        .modal-actions {
            flex-direction: column;
        }
        .modal-actions .btn {
            width: 100%;
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
            
            {{-- Statut de la demande d'annulation --}}
            @if(isset($order->metadata['cancellation_request']))
                @php
                    $cancellationStatus = $order->metadata['cancellation_request']['status'] ?? 'pending';
                @endphp
                <span class="badge 
                    @if($cancellationStatus == 'pending') badge-cancellation-pending
                    @elseif($cancellationStatus == 'approved') badge-cancellation-approved
                    @else badge-cancellation-rejected
                    @endif
                ">
                    @if($cancellationStatus == 'pending') ⏳ Demande d'annulation
                    @elseif($cancellationStatus == 'approved') ✅ Annulation approuvée
                    @else ❌ Annulation rejetée
                    @endif
                </span>
            @endif
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

            {{-- ✅ SECTION DEMANDE D'ANNULATION AVEC TIMER 10 MINUTES --}}
            @php
                $canRequestCancellation = false;
                $timeRemaining = 0;
                $minutesRemaining = 0;
                $secondsRemaining = 0;
                $isTimeExpired = false;
                
                if (in_array($order->status, ['pending', 'processing', 'completed'])) {
                    $createdAt = $order->created_at;
                    $now = now();
                    $diffInMinutes = $createdAt->diffInMinutes($now);
                    
                    // ✅ Limite de 10 minutes (600 secondes)
                    if ($diffInMinutes <= 10) {
                        $canRequestCancellation = true;
                        $timeRemaining = 600 - ($diffInMinutes * 60);
                        $minutesRemaining = floor($timeRemaining / 60);
                        $secondsRemaining = $timeRemaining % 60;
                    } else {
                        $isTimeExpired = true;
                    }
                }
                
                $hasCancellationRequest = isset($order->metadata['cancellation_request']);
                $cancellationStatus = $hasCancellationRequest ? $order->metadata['cancellation_request']['status'] : null;
            @endphp

            @if($order->status == 'pending' || $order->status == 'processing')
                <div class="mt-3 pt-3 border-t border-[var(--border-color)]">
                    
                    {{-- ✅ TIMER D'ANNULATION --}}
                    @if($canRequestCancellation)
                        <div class="flex items-center justify-between mb-2">
                            <div class="cancellation-timer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Temps restant :</span>
                                <span class="time" id="cancellationTimer">
                                    {{ sprintf('%02d:%02d', $minutesRemaining, $secondsRemaining) }}
                                </span>
                            </div>
                            <span class="text-[10px] text-[var(--text-secondary)]">⏳ 10 min max</span>
                        </div>
                    @elseif($isTimeExpired)
                        <div class="cancellation-timer expired">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Délai dépassé</span>
                            <span class="text-[10px]">(+10 min)</span>
                        </div>
                    @endif

                    {{-- ✅ BOUTON DEMANDE D'ANNULATION --}}
                    @if(!$hasCancellationRequest || $cancellationStatus == 'rejected')
                        @if($canRequestCancellation)
                            <button onclick="openCancelModal()" 
                                    class="btn btn-danger w-full mt-2 text-sm sm:text-base py-2 sm:py-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Demander l'annulation
                            </button>
                            <p class="text-[10px] text-[var(--text-secondary)] text-center mt-1">
                                Une demande sera envoyée à l'administrateur pour validation
                            </p>
                        @elseif($isTimeExpired)
                            <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-lg mt-2">
                                <div class="flex items-center gap-2 text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span class="font-medium">Délai d'annulation dépassé</span>
                                </div>
                                <p class="text-xs text-[var(--text-secondary)] mt-1">
                                    La commande a été créée il y a plus de 10 minutes.
                                </p>
                                <p class="text-xs text-[var(--text-secondary)]">
                                    Créée le : {{ $order->created_at->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        @endif
                    @endif
                    
                    {{-- STATUT DE LA DEMANDE --}}
                    @if($hasCancellationRequest && $cancellationStatus == 'pending')
                        <div class="mt-2">
                            <div class="cancellation-status pending">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Demande d'annulation en attente de validation</span>
                                </div>
                                <p class="text-xs text-[var(--text-secondary)] mt-1">
                                    Demandé le {{ \Carbon\Carbon::parse($order->metadata['cancellation_request']['requested_at'])->format('d/m/Y H:i') }}
                                </p>
                                @if(isset($order->metadata['cancellation_request']['reason']))
                                    <p class="text-xs text-[var(--text-secondary)] mt-1">
                                        <strong>Motif :</strong> {{ $order->metadata['cancellation_request']['reason'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    @if($hasCancellationRequest && $cancellationStatus == 'approved')
                        <div class="mt-2">
                            <div class="cancellation-status approved">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Annulation approuvée par l'administrateur</span>
                                </div>
                                <p class="text-xs text-[var(--text-secondary)] mt-1">
                                    Approuvée le {{ \Carbon\Carbon::parse($order->metadata['cancellation_request']['processed_at'])->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    @if($hasCancellationRequest && $cancellationStatus == 'rejected')
                        <div class="mt-2">
                            <div class="cancellation-status rejected">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span>Annulation rejetée par l'administrateur</span>
                                </div>
                                <p class="text-xs text-[var(--text-secondary)] mt-1">
                                    Rejetée le {{ \Carbon\Carbon::parse($order->metadata['cancellation_request']['processed_at'])->format('d/m/Y H:i') }}
                                </p>
                                @if(isset($order->metadata['cancellation_request']['admin_notes']))
                                    <p class="text-xs text-[var(--text-secondary)] mt-1">
                                        <strong>Motif du rejet :</strong> {{ $order->metadata['cancellation_request']['admin_notes'] }}
                                    </p>
                                @endif
                            </div>
                            {{-- Refaire une demande si le délai n'est pas dépassé --}}
                            @if($canRequestCancellation)
                                <button onclick="openCancelModal()" 
                                        class="btn btn-danger w-full mt-2 text-sm sm:text-base py-2 sm:py-2.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Refaire une demande
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL DEMANDE D'ANNULATION --}}
<div id="cancelModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title">Demander l'annulation</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir demander l'annulation de cette commande ?
            <br>
            Cette demande sera envoyée à l'<strong>administrateur</strong> pour validation.
            <br>
            <span class="text-danger">Une fois approuvée, la commande sera annulée définitivement.</span>
        </p>
        
        <div class="mb-3">
            <label for="cancellationReason" class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                Motif de l'annulation <span class="text-red-500">*</span>
            </label>
            <textarea id="cancellationReason" 
                      class="input w-full p-2 border border-[var(--border-color)] rounded-md text-sm"
                      rows="3"
                      placeholder="Expliquez pourquoi vous souhaitez annuler cette commande (minimum 10 caractères)..."
                      required></textarea>
            <p class="text-[10px] text-[var(--text-secondary)] mt-1">Minimum 10 caractères</p>
        </div>
        
        <div class="modal-actions">
            <button type="button" onclick="closeCancelModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <button type="button" onclick="submitCancelRequest()" class="btn btn-danger btn-sm" id="submitCancelBtn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Envoyer la demande
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
//  TIMER POUR L'ANNULATION (10 MINUTES)
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const timerElement = document.getElementById('cancellationTimer');
    if (timerElement) {
        let timeRemaining = {{ $timeRemaining ?? 0 }};
        
        if (timeRemaining > 0) {
            const countdown = setInterval(function() {
                if (timeRemaining <= 0) {
                    clearInterval(countdown);
                    timerElement.textContent = '00:00';
                    // Recharger la page après 2 secondes pour mettre à jour l'interface
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                    return;
                }
                
                timeRemaining--;
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                timerElement.textContent = 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            }, 1000);
        }
    }
});

// ============================================================
//  MODAL D'ANNULATION
// ============================================================
function openCancelModal() {
    document.getElementById('cancelModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    document.getElementById('cancellationReason').value = '';
    document.getElementById('cancellationReason').focus();
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Fermer en cliquant à l'extérieur
document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// Fermer avec Echap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

// ============================================================
//  SOUMETTRE LA DEMANDE D'ANNULATION
// ============================================================
function submitCancelRequest() {
    const reason = document.getElementById('cancellationReason').value.trim();
    const submitBtn = document.getElementById('submitCancelBtn');
    const reasonTextarea = document.getElementById('cancellationReason');
    
    // Vérifier la longueur du motif
    if (reason.length < 10) {
        reasonTextarea.focus();
        reasonTextarea.style.borderColor = '#ef4444';
        reasonTextarea.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.15)';
        
        // Afficher un message d'erreur
        let errorMsg = document.getElementById('reasonError');
        if (!errorMsg) {
            errorMsg = document.createElement('p');
            errorMsg.id = 'reasonError';
            errorMsg.className = 'text-red-500 text-xs mt-1';
            errorMsg.textContent = 'Veuillez entrer un motif d\'au moins 10 caractères';
            reasonTextarea.parentNode.appendChild(errorMsg);
        }
        
        setTimeout(() => {
            reasonTextarea.style.borderColor = '';
            reasonTextarea.style.boxShadow = '';
            if (errorMsg) errorMsg.remove();
        }, 3000);
        return;
    }
    
    // Désactiver le bouton
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Envoi en cours...
    `;
    submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
    
    // Envoyer la demande
    fetch(`/cashier/orders/{{ $order->id }}/request-cancellation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCancelModal();
            // Recharger la page pour afficher le nouveau statut
            window.location.reload();
        } else {
            alert(' ' + (data.message || 'Une erreur est survenue'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert(' Erreur lors de l\'envoi de la demande');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
        submitBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Envoyer la demande
        `;
    });
}
</script>
@endpush
@endsection