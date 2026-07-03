@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 640px) {
        .card { padding: 0.75rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .text-6xl { font-size: 3rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Panier</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Verifiez vos articles avant de commander</p>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($cart))
        <div class="card text-center py-8 sm:py-12 animate-fadeIn p-4 sm:p-6">
            <svg class="w-16 h-16 sm:w-24 sm:h-24 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Votre panier est vide</h3>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Decouvrez nos produits et abonnements</p>
            <div class="flex flex-wrap justify-center gap-2 sm:gap-3 mt-3 sm:mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary text-sm sm:text-base">Voir les produits</a>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline text-sm sm:text-base">Voir les abonnements</a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Articles -->
            <div class="lg:col-span-2 animate-fadeInLeft">
                <div class="card p-3 sm:p-4 md:p-6">
                    <div class="divide-y divide-[var(--border-color)]">
                        @php $total = 0; @endphp
                        @foreach($cart as $key => $item)
                            @php $itemTotal = $item['price'] * $item['quantity']; $total += $itemTotal; @endphp
                            <div class="py-3 sm:py-4 flex items-center gap-3 sm:gap-4 first:pt-0 last:pb-0">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-[var(--text-primary)] text-sm sm:text-base">{{ $item['name'] }}</h4>
                                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                                        {{ $item['type'] == 'package' ? 'Abonnement' : 'Produit' }}
                                        x {{ $item['quantity'] }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-primary-500 text-sm sm:text-base">${{ number_format($itemTotal, 2) }}</p>
                                    <form action="{{ route('cart.remove', $key) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs sm:text-sm transition">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-3 sm:mt-4 flex flex-wrap gap-2 sm:gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-outline text-xs sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Continuer les achats
                    </a>
                    <form action="{{ route('cart.clear') }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger text-xs sm:text-sm">Vider le panier</button>
                    </form>
                </div>
            </div>

            <!-- Resume -->
            <div class="lg:col-span-1 animate-fadeInRight">
                <div class="card sticky top-24 p-3 sm:p-4 md:p-6">
                    <h3 class="font-bold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Resume</h3>
                    
                    <div class="space-y-2 text-xs sm:text-sm">
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
                            <div class="flex justify-between text-base sm:text-lg font-bold">
                                <span>Total</span>
                                <span class="text-primary-500">${{ number_format($total + ($total * 0.18) + ($total > 100 ? 0 : 10), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('cart.checkout') }}" method="POST" class="mt-3 sm:mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full text-sm sm:text-base py-2 sm:py-2.5">
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