{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Commande #' . $order->order_number)



@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Commande #{{ $order->order_number }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Détails de la commande</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-primary btn-sm sm:btn-md" target="_blank">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                Voir la facture
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
                        @elseif($order->status == 'completed') Terminée
                        @elseif($order->status == 'cancelled') Annulée
                        @else {{ ucfirst($order->status) }}
                        @endif
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Date de la commande</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails -->
    <div class="detail-grid grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-2">
        
        <!-- Articles -->
        <div class="lg:col-span-2 order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Articles</h3>

            <div class="space-y-2 sm:space-y-3">
                @foreach($order->items as $item)
                    <div class="item-card">
                        <div class="item-icon">
                            @if($item->product_id && $item->product && $item->product->image && file_exists(storage_path('app/public/products/' . $item->product->image)))
                                <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                     alt="{{ $item->name }}"
                                     class="w-full h-full object-cover rounded-md">
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                            @endif
                        </div>
                        <div class="item-info">
                            <p class="item-name">{{ $item->name }}</p>
                            <p class="item-meta">
                                Qté: {{ $item->quantity }}
                                @if($item->product_id)
                                    • SKU: {{ $item->sku ?? 'N/A' }}
                                @else
                                    • Package
                                @endif
                            </p>
                        </div>
                        <div class="item-total">
                            ${{ number_format($item->total, 2) }}
                            <div class="unit-price">${{ number_format($item->price, 2) }} / unité</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Résumé -->
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Résumé</h3>

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
                    <span class="text-[var(--text-secondary)]">Réduction</span>
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
                    <span class="text-[var(--text-secondary)]">Statut du paiement</span>
                    <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                        {{ $order->payment_status == 'completed' ? 'Payé' : 'En attente' }}
                    </span>
                </div>
                @if($order->payment_method)
                <div class="flex justify-between text-xs sm:text-sm mt-1 sm:mt-2">
                    <span class="text-[var(--text-secondary)]">Méthode de paiement</span>
                    <span class="font-medium">{{ $order->payment_method }}</span>
                </div>
                @endif
            </div>

            @if($order->status == 'pending')
            <div class="mt-3 sm:mt-4">
                <form action="{{ route('orders.cancel', $order) }}" method="POST" 
                      onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
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
    <div class="address-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-3">
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