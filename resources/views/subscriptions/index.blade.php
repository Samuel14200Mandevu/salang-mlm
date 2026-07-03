@extends('layouts.app')

@push('styles')
<style>
    .subscription-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    .subscription-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .subscription-card:hover::before { opacity: 1; }
    .subscription-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary-500);
    }
    .subscription-card.current {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
    }
    .subscription-card.current::before { opacity: 1; }
    .subscription-card .sub-icon { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; transition: transform 0.4s ease; }
    .subscription-card:hover .sub-icon { transform: scale(1.1) rotate(-5deg); }
    .subscription-badge {
        position: absolute;
        top: -1px;
        right: 1rem;
        padding: 0.25rem 1rem;
        border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .subscription-badge-popular {
        background: var(--gradient-primary);
        color: white;
    }
    .benefit-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .benefit-item svg { flex-shrink: 0; color: var(--primary-500); }
    
    @media (max-width: 640px) {
        .subscription-card { padding: 0.75rem; }
        .subscription-card .sub-icon { font-size: 2rem; }
        .benefit-item { font-size: 0.65rem; }
        .text-3xl { font-size: 1.5rem; }
        .btn { font-size: 0.7rem; padding: 0.25rem 0.5rem; }
        .btn svg { width: 0.875rem; height: 0.875rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Packages</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Choisissez l'abonnement qui correspond a vos objectifs</p>
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

    <!-- Abonnement actuel -->
    <div class="card animate-fadeInUp delay-1 border-l-4 border-primary-500 p-3 sm:p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Votre abonnement actuel</p>
                <h2 class="text-xl sm:text-2xl font-bold text-primary-500">
                    {{ Auth::user()->package ? Auth::user()->package->name : 'Aucun abonnement' }}
                </h2>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    {{ Auth::user()->pv_balance ?? 0 }} PV
                </p>
            </div>
            <div>
                <span class="badge {{ Auth::user()->package_id ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                    {{ Auth::user()->package_id ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Liste des abonnements -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        @forelse($subscriptions as $package)
            @php
                $isCurrent = Auth::user()->package_id == $package->id;
                $isUpgrade = Auth::user()->package_id && Auth::user()->package_id < $package->id;
                $isLocked = Auth::user()->package_id && Auth::user()->package_id > $package->id;
                $isPopular = $package->id == 4;
            @endphp

            <div class="subscription-card card text-center p-3 sm:p-4 {{ $isCurrent ? 'current' : '' }} animate-fadeInUp delay-{{ $loop->index + 2 }}">
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
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Commission jusqu'a {{ $package->commission_rate ?? 30 }}%
                    </div>
                    <div class="benefit-item">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $package->pv_value ?? 0 }} PV
                    </div>
                    <div class="benefit-item">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Acces a la boutique
                    </div>
                    <div class="benefit-item">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Parrainage illimite
                    </div>
                </div>

                <div class="mt-3 sm:mt-4">
                    @if($isCurrent)
                        <span class="badge badge-success text-[10px] sm:text-xs">Abonnement actuel</span>
                    @elseif($isLocked)
                        <span class="badge badge-danger text-[10px] sm:text-xs">Verrouille</span>
                    @else
                        <div class="space-y-1.5 sm:space-y-2">
                            <form action="{{ route('subscriptions.buy') }}" method="POST">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $package->id }}">
                                <button type="submit" class="btn btn-primary w-full text-[10px] sm:text-sm py-1.5 sm:py-2">
                                    {{ $isUpgrade ? 'Mettre a niveau' : 'Souscrire' }}
                                </button>
                            </form>
                            <form action="{{ route('cart.add-package') }}" method="POST">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $package->id }}">
                                <button type="submit" class="btn btn-outline w-full text-[10px] sm:text-sm py-1.5 sm:py-2">
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
                <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Aucun abonnement disponible</h3>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Revenez plus tard pour decouvrir nos offres</p>
            </div>
        @endforelse
    </div>
</div>
@endsection