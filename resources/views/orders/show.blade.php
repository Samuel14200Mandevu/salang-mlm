@extends('layouts.app')

@push('styles')
<style>
    .order-detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    .order-detail-card:hover {
        border-color: var(--primary-500);
    }
    .order-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-size: 0.875rem;
        font-weight: 600;
    }
    .order-status-badge-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .order-status-badge-processing { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .order-status-badge-completed { background: rgba(34,197,94,0.15); color: #22c55e; }
    .order-status-badge-cancelled { background: rgba(239,68,68,0.15); color: #ef4444; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                📦 Commande #{{ $order->order_number }}
            </h1>
            <p class="text-[var(--text-secondary)] mt-1">Détails de votre commande</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-primary btn-sm">
                📄 Facture
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-outline btn-sm">
                ← Retour
            </a>
        </div>
    </div>

    <!-- Statut -->
    <div class="animate-fadeInUp delay-1">
        <div class="order-detail-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-[var(--text-secondary)]">Statut de la commande</p>
                    <span class="order-status-badge order-status-badge-{{ $order->status }}">
                        @if($order->status == 'pending') ⏳ En attente
                        @elseif($order->status == 'processing') 🔄 En traitement
                        @elseif($order->status == 'completed') ✅ Livrée
                        @elseif($order->status == 'cancelled') ❌ Annulée
                        @else 📦 {{ ucfirst($order->status) }}
                        @endif
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-sm text-[var(--text-secondary)]">Date de commande</p>
                    <p class="font-semibold text-[var(--text-primary)]">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 animate-fadeInUp delay-2">
        <!-- Articles -->
        <div class="lg:col-span-2 order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">🛍️ Articles</h3>

            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between p-3 bg-[var(--bg-secondary)] rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-[var(--bg-primary)] flex items-center justify-center text-2xl">
                                @if($item->product_id)
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                             alt="{{ $item->name }}"
                                             class="w-full h-full object-cover rounded-lg">
                                    @else
                                        🛍️
                                    @endif
                                @else
                                    📦
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-[var(--text-primary)]">{{ $item->name }}</p>
                                <p class="text-sm text-[var(--text-secondary)]">
                                    Quantité: {{ $item->quantity }}
                                    @if($item->product_id)
                                        • SKU: {{ $item->sku ?? 'N/A' }}
                                    @else
                                        • Package
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-primary-500">${{ number_format($item->total, 2) }}</p>
                            <p class="text-xs text-[var(--text-secondary)]">${{ number_format($item->price, 2) }} / unité</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Résumé -->
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">📋 Résumé</h3>

            <div class="space-y-2 text-sm">
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
                <div class="border-t border-[var(--border-color)] pt-3 mt-3">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span class="text-primary-500">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
                <div class="flex justify-between text-sm">
                    <span class="text-[var(--text-secondary)]">Statut paiement</span>
                    <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : 'badge-warning' }}">
                        {{ $order->payment_status == 'completed' ? '✅ Payé' : '⏳ En attente' }}
                    </span>
                </div>
                @if($order->payment_method)
                <div class="flex justify-between text-sm mt-2">
                    <span class="text-[var(--text-secondary)]">Méthode de paiement</span>
                    <span class="font-medium">{{ $order->payment_method }}</span>
                </div>
                @endif
            </div>

            @if($order->status == 'pending')
            <div class="mt-4">
                <form action="{{ route('orders.cancel', $order) }}" method="POST" 
                      onsubmit="return confirm('Voulez-vous vraiment annuler cette commande ?')">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-danger w-full">
                        ❌ Annuler la commande
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Adresses -->
    @if($order->shipping_address || $order->billing_address)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fadeInUp delay-3">
        @if($order->shipping_address)
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-3">📦 Adresse de livraison</h3>
            <p class="text-[var(--text-secondary)] whitespace-pre-line">{{ $order->shipping_address }}</p>
        </div>
        @endif

        @if($order->billing_address)
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-3">💳 Adresse de facturation</h3>
            <p class="text-[var(--text-secondary)] whitespace-pre-line">{{ $order->billing_address }}</p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection