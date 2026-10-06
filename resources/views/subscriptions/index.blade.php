{{-- resources/views/subscriptions/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Packages')



@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- En-tête -->
    <div class="member-page-intro animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Packages</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Choisissez l'abonnement qui correspond à vos objectifs</p>
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

    <!-- Abonnement actuel et solde -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="card border-l-4 border-primary-500">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Votre abonnement actuel</p>
            <h2 class="text-xl sm:text-2xl font-bold text-primary-500">
                {{ Auth::user()->package ? Auth::user()->package->name : 'Aucun abonnement' }}
            </h2>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                {{ Auth::user()->pv_balance ?? 0 }} PV
            </p>
            <span class="badge {{ Auth::user()->package_id ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs mt-1">
                {{ Auth::user()->package_id ? 'Actif' : 'Inactif' }}
            </span>
        </div>

        <div class="card border-l-4 border-yellow-500">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Solde de votre portefeuille</p>
            <h2 class="text-xl sm:text-2xl font-bold text-yellow-500">
                ${{ number_format(Auth::user()->wallet?->balance ?? 0, 2) }}
            </h2>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                Disponible pour les achats
            </p>
            <a href="{{ route('wallet.deposit') }}" class="btn btn-primary btn-sm mt-2 inline-flex w-auto">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Déposer
            </a>
        </div>
    </div>

    <!-- Liste des packages -->
    <div class="subscription-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        @forelse($subscriptions as $package)
            @php
                $isCurrent = Auth::user()->package_id == $package->id;
                $isUpgrade = Auth::user()->package_id && Auth::user()->package_id < $package->id;
                $isLocked = Auth::user()->package_id && Auth::user()->package_id > $package->id;
                $isPopular = $package->id == 4;
                $delay = min($loop->index + 2, 7);
                $balance = Auth::user()->wallet?->balance ?? 0;
                $canAfford = $balance >= $package->price;
            @endphp

            <div class="subscription-card animate-fadeInUp delay-{{ $delay }} {{ $isCurrent ? 'current' : '' }}">
                @if($isPopular)
                    <span class="subscription-badge subscription-badge-popular text-[8px] sm:text-[10px]">Populaire</span>
                @endif
                
                <span class="sub-icon">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                    </svg>
                </span>
                <h3 class="text-base sm:text-xl font-bold text-[var(--text-primary)]">{{ $package->name }}</h3>
                <p class="text-xl sm:text-3xl font-bold text-primary-500 mt-1 sm:mt-2">${{ number_format($package->price, 2) }}</p>
                <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">{{ $package->pv_value ?? 0 }} PV</p>

                <div class="mt-3 sm:mt-4 space-y-0.5 sm:space-y-1 border-t border-[var(--border-color)] pt-3 sm:pt-4 text-left">
                    <div class="benefit-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Commission jusqu'à {{ $package->commission_rate ?? 30 }}%
                    </div>
                    <div class="benefit-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $package->pv_value ?? 0 }} PV
                    </div>
                    <div class="benefit-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Accès à la boutique
                    </div>
                    <div class="benefit-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Parrainage illimité
                    </div>
                </div>

                <div class="mt-3 sm:mt-4">
                    @if($isCurrent)
                        <span class="badge badge-success text-[10px] sm:text-xs">Abonnement actuel</span>
                    @elseif($isLocked)
                        <span class="badge badge-danger text-[10px] sm:text-xs">Verrouillé</span>
                    @else
                        <div class="space-y-1.5 sm:space-y-2">
                            @if($canAfford)
                                <form action="{{ route('subscriptions.buy') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                                    <button type="submit" class="btn btn-primary text-[10px] sm:text-sm py-1.5 sm:py-2">
                                        {{ $isUpgrade ? 'Mettre à niveau' : 'S\'abonner' }}
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-primary text-[10px] sm:text-sm py-1.5 sm:py-2 cursor-not-allowed opacity-50" disabled>
                                    Solde insuffisant
                                </button>
                                <p class="insufficient-balance">Il manque ${{ number_format($package->price - $balance, 2) }}</p>
                            @endif
                            
                            <form action="{{ route('cart.add-package') }}" method="POST">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $package->id }}">
                                <button type="submit" class="btn btn-outline text-[10px] sm:text-sm py-1.5 sm:py-2 {{ !$canAfford ? 'opacity-50' : '' }}">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Ajouter au panier
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8 sm:py-12">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
                <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun package disponible</h3>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Revenez plus tard pour découvrir nos offres</p>
            </div>
        @endforelse
    </div>
</div>
@endsection