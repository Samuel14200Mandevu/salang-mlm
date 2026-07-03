@extends('layouts.app')

@push('styles')
<style>
    .order-detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .order-detail-card:hover { border-color: var(--primary-500); }
    .order-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.875rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }
    .order-status-badge-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .order-status-badge-processing { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .order-status-badge-completed { background: rgba(34,197,94,0.15); color: #22c55e; }
    .order-status-badge-cancelled { background: rgba(239,68,68,0.15); color: #ef4444; }
    
    @media (max-width: 640px) {
        .order-detail-card { padding: 0.75rem; }
        .order-status-badge { font-size: 0.65rem; padding: 0.25rem 0.6rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .text-2xl { font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Commande #{{ $order->order_number }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Details de votre commande</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                Facture
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Statut -->
    <div class="animate-fadeInUp delay-1">
        <div class="order-detail-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Statut de la commande</p>
                    <span class="order-status-badge order-status-badge-{{ $order->status }}">
                        @if($order->status == 'pending') En attente
                        @elseif($order->status == 'processing') En traitement
                        @elseif($order->status == 'completed') Livree
                        @elseif($order->status == 'cancelled') Annulee
                        @else {{ ucfirst($order->status) }}
                        @endif
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Date de commande</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-2">
        <!-- Articles -->
        <div class="lg:col-span-2 order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Articles</h3>

            <div class="space-y-2 sm:space-y-3">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg gap-2">
                        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-[var(--bg-primary)] flex items-center justify-center flex-shrink-0">
                                @if($item->product_id && $item->product && $item->product->image)
                                    <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                         alt="{{ $item->name }}"
                                         class="w-full h-full object-cover rounded-lg">
                                @else
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-[var(--text-primary)] text-xs sm:text-sm truncate">{{ $item->name }}</p>
                                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                                    Quantite: {{ $item->quantity }}
                                    @if($item->product_id)
                                        • SKU: {{ $item->sku ?? 'N/A' }}
                                    @else
                                        • Package
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-primary-500 text-xs sm:text-sm">${{ number_format($item->total, 2) }}</p>
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">${{ number_format($item->price, 2) }} / unite</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Resume -->
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Resume</h3>

            <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Sous-total</span>
                    <span class="font-medium">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">TVA (18%)</span>
                    <span class="font-medium">${{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Livraison</span>
                    <span class="font-medium">${{ number_format($order->shipping, 2) }}</span>
                </div>
                @if($order->discount > 0)
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Reduction</span>
                    <span class="font-medium text-red-500">-${{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="border-t border-[var(--border-color)] pt-2 sm:pt-3 mt-2 sm:mt-3">
                    <div class="flex justify-between text-base sm:text-lg font-bold">
                        <span>Total</span>
                        <span class="text-primary-500">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-[var(--border-color)]">
                <div class="flex justify-between text-xs sm:text-sm">
                    <span class="text-[var(--text-secondary)]">Statut paiement</span>
                    <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                        {{ $order->payment_status == 'completed' ? 'Paye' : 'En attente' }}
                    </span>
                </div>
                @if($order->payment_method)
                <div class="flex justify-between text-xs sm:text-sm mt-1 sm:mt-2">
                    <span class="text-[var(--text-secondary)]">Methode de paiement</span>
                    <span class="font-medium">{{ $order->payment_method }}</span>
                </div>
                @endif
            </div>

            @if($order->status == 'pending')
            <div class="mt-3 sm:mt-4">
                <form action="{{ route('orders.cancel', $order) }}" method="POST" 
                      onsubmit="return confirm('Voulez-vous vraiment annuler cette commande ?')">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-danger w-full text-sm sm:text-base py-2 sm:py-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Annuler la commande
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Adresses -->
    @if($order->shipping_address || $order->billing_address)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-3">
        @if($order->shipping_address)
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2 sm:mb-3">Adresse de livraison</h3>
            <p class="text-[var(--text-secondary)] text-sm whitespace-pre-line">{{ $order->shipping_address }}</p>
        </div>
        @endif

        @if($order->billing_address)
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2 sm:mb-3">Adresse de facturation</h3>
            <p class="text-[var(--text-secondary)] text-sm whitespace-pre-line">{{ $order->billing_address }}</p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection