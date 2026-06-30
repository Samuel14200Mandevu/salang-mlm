@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🛒 Mon Panier</h1>
        <p class="text-[var(--text-secondary)] mt-1">Vérifiez vos articles avant de commander</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 animate-fadeIn">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 animate-fadeIn">
            ❌ {{ session('error') }}
        </div>
    @endif

    @if(empty($cart))
        <div class="card text-center py-12 animate-fadeIn">
            <div class="text-6xl mb-4">🛒</div>
            <h3 class="text-xl font-semibold text-[var(--text-primary)]">Votre panier est vide</h3>
            <p class="text-[var(--text-secondary)] mt-2">Découvrez nos produits et abonnements</p>
            <div class="flex flex-wrap justify-center gap-3 mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary">🛍️ Voir les produits</a>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline">📦 Voir les abonnements</a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Articles -->
            <div class="lg:col-span-2 animate-fadeInLeft">
                <div class="card">
                    <div class="divide-y divide-[var(--border-color)]">
                        @php $total = 0; @endphp
                        @foreach($cart as $key => $item)
                            @php $itemTotal = $item['price'] * $item['quantity']; $total += $itemTotal; @endphp
                            <div class="py-4 flex items-center gap-4 first:pt-0 last:pb-0">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-[var(--text-primary)]">{{ $item['name'] }}</h4>
                                    <p class="text-sm text-[var(--text-secondary)]">
                                        {{ $item['type'] == 'package' ? '📦 Abonnement' : '🛍️ Produit' }}
                                        x {{ $item['quantity'] }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-primary-500">${{ number_format($itemTotal, 2) }}</p>
                                    <form action="{{ route('cart.remove', $key) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm transition">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-outline text-sm">
                        ← Continuer les achats
                    </a>
                    <form action="{{ route('cart.clear') }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger text-sm">Vider le panier</button>
                    </form>
                </div>
            </div>

            <!-- Résumé -->
            <div class="lg:col-span-1 animate-fadeInRight">
                <div class="card sticky top-24">
                    <h3 class="font-bold text-[var(--text-primary)] mb-4">📋 Résumé</h3>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--text-secondary)]">Sous-total</span>
                            <span class="font-medium">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--text-secondary)]">TVA (18%)</span>
                            <span class="font-medium">${{ number_format($total * 0.18, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--text-secondary)]">Livraison</span>
                            <span class="font-medium">{{ $total > 100 ? 'Gratuite' : '$10.00' }}</span>
                        </div>
                        <div class="border-t border-[var(--border-color)] pt-3 mt-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-primary-500">${{ number_format($total + ($total * 0.18) + ($total > 100 ? 0 : 10), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('cart.checkout') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Valider la commande
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection