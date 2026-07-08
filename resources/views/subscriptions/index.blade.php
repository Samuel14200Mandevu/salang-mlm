@extends('layouts.app')

@push('styles')
<style>
    .subscription-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: default;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        text-align: center;
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
    .subscription-card:hover::before {
        opacity: 1;
    }
    .subscription-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary-500);
    }
    .subscription-card.current {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 2px rgba(90, 182, 56, 0.2);
    }
    .subscription-card.current::before {
        opacity: 1;
    }
    .subscription-card .sub-icon {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 0.5rem;
        transition: transform 0.4s ease;
    }
    .subscription-card:hover .sub-icon {
        transform: scale(1.1) rotate(-5deg);
    }
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
    .benefit-item svg {
        flex-shrink: 0;
        color: var(--primary-500);
        width: 0.875rem;
        height: 0.875rem;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
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
        width: 100%;
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
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
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
    .btn-warning {
        background: var(--gradient-warning);
        color: white;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .text-3xl {
        font-size: 1.875rem;
        line-height: 2.25rem;
    }
    .text-xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    .delay-7 { animation-delay: 0.35s; }
    
    .insufficient-balance {
        font-size: 0.6rem;
        color: #ef4444;
        margin-top: 0.25rem;
    }
    
    @media (max-width: 640px) {
        .subscription-card {
            padding: 0.875rem;
        }
        .subscription-card .sub-icon {
            font-size: 2rem;
        }
        .benefit-item {
            font-size: 0.65rem;
        }
        .benefit-item svg {
            width: 0.75rem;
            height: 0.75rem;
        }
        .text-3xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }
        .btn {
            font-size: 0.7rem;
            padding: 0.375rem 0.75rem;
        }
        .btn svg {
            width: 0.875rem;
            height: 0.875rem;
        }
        .card {
            padding: 0.875rem;
        }
        .subscription-badge {
            font-size: 0.5rem;
            padding: 0.125rem 0.625rem;
        }
    }
    
    @media (max-width: 480px) {
        .subscription-card {
            padding: 0.75rem;
        }
        .subscription-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        .subscription-card .sub-icon {
            font-size: 1.75rem;
        }
        .subscription-card h3 {
            font-size: 0.875rem;
        }
        .subscription-card .text-3xl {
            font-size: 1.25rem;
        }
        .benefit-item {
            font-size: 0.6rem;
        }
        .benefit-item svg {
            width: 0.625rem;
            height: 0.625rem;
        }
    }
    
    @media (max-width: 380px) {
        .subscription-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Packages</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Choose the subscription that fits your goals</p>
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

    <!-- Current Subscription & Balance -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="card border-l-4 border-primary-500">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Your current subscription</p>
            <h2 class="text-xl sm:text-2xl font-bold text-primary-500">
                {{ Auth::user()->package ? Auth::user()->package->name : 'No subscription' }}
            </h2>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                {{ Auth::user()->pv_balance ?? 0 }} PV
            </p>
            <span class="badge {{ Auth::user()->package_id ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs mt-1">
                {{ Auth::user()->package_id ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <!-- ✅ AFFICHAGE DU SOLDE -->
        <div class="card border-l-4 border-yellow-500">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Your Wallet Balance</p>
            <h2 class="text-xl sm:text-2xl font-bold text-yellow-500">
                ${{ number_format(Auth::user()->wallet->balance ?? 0, 2) }}
            </h2>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                Available for purchases
            </p>
            <a href="{{ route('wallet.deposit') }}" class="btn btn-primary btn-sm mt-2 inline-flex w-auto">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Deposit
            </a>
        </div>
    </div>

    <!-- Packages List -->
    <div class="subscription-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        @forelse($subscriptions as $package)
            @php
                $isCurrent = Auth::user()->package_id == $package->id;
                $isUpgrade = Auth::user()->package_id && Auth::user()->package_id < $package->id;
                $isLocked = Auth::user()->package_id && Auth::user()->package_id > $package->id;
                $isPopular = $package->id == 4;
                $delay = min($loop->index + 2, 7);
                $balance = Auth::user()->wallet->balance ?? 0;
                $canAfford = $balance >= $package->price;
            @endphp

            <div class="subscription-card animate-fadeInUp delay-{{ $delay }} {{ $isCurrent ? 'current' : '' }}">
                @if($isPopular)
                    <span class="subscription-badge subscription-badge-popular text-[8px] sm:text-[10px]">Popular</span>
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
                        Commission up to {{ $package->commission_rate ?? 30 }}%
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
                        Shop access
                    </div>
                    <div class="benefit-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Unlimited referrals
                    </div>
                </div>

                <div class="mt-3 sm:mt-4">
                    @if($isCurrent)
                        <span class="badge badge-success text-[10px] sm:text-xs">Current subscription</span>
                    @elseif($isLocked)
                        <span class="badge badge-danger text-[10px] sm:text-xs">Locked</span>
                    @else
                        <div class="space-y-1.5 sm:space-y-2">
                            @if($canAfford)
                                <form action="{{ route('subscriptions.buy') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                                    <button type="submit" class="btn btn-primary text-[10px] sm:text-sm py-1.5 sm:py-2">
                                        {{ $isUpgrade ? 'Upgrade' : 'Subscribe' }}
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-primary text-[10px] sm:text-sm py-1.5 sm:py-2 cursor-not-allowed opacity-50" disabled>
                                    Insufficient Balance
                                </button>
                                <p class="insufficient-balance">Need ${{ number_format($package->price - $balance, 2) }} more</p>
                            @endif
                            
                            <form action="{{ route('cart.add-package') }}" method="POST">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $package->id }}">
                                <button type="submit" class="btn btn-outline text-[10px] sm:text-sm py-1.5 sm:py-2 {{ !$canAfford ? 'opacity-50' : '' }}">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Add to cart
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
                <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">No packages available</h3>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Check back later for our offers</p>
            </div>
        @endforelse
    </div>
</div>
@endsection