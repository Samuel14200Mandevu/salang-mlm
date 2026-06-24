@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- En-tête -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-[var(--text-primary)]">📦 Packages</h1>
            <p class="text-[var(--text-secondary)] mt-1">Choisissez le package qui correspond à vos objectifs</p>
        </div>

        <!-- Package actuel -->
        <div class="mb-6 bg-[var(--bg-card)] rounded-xl shadow-sm p-6 border border-[var(--border-color)]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[var(--text-secondary)]">Votre package actuel</p>
                    <h2 class="text-2xl font-bold text-primary-600">{{ $user->package?->name ?? 'Aucun package' }}</h2>
                    <p class="text-sm text-[var(--text-secondary)] mt-1">{{ $user->pv_balance ?? 0 }} PV</p>
                </div>
                <div>
                    <span class="badge-modern {{ $user->package_id ? 'badge-success' : 'badge-danger' }}">
                        {{ $user->package_id ? '✅ Actif' : '❌ Inactif' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Liste des packages -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
            @php
                $packages = [
                    ['id' => 1, 'name' => 'Starter', 'price' => 30, 'pv' => 0, 'icon' => '🌟', 'color' => 'gray'],
                    ['id' => 2, 'name' => 'Silver', 'price' => 85, 'pv' => 0, 'icon' => '🥈', 'color' => 'gray-400'],
                    ['id' => 3, 'name' => 'Bronze', 'price' => 350, 'pv' => 200, 'icon' => '🥉', 'color' => 'amber-600'],
                    ['id' => 4, 'name' => 'Gold', 'price' => 1450, 'pv' => 1000, 'icon' => '🥇', 'color' => 'yellow-500'],
                    ['id' => 5, 'name' => 'Emerald', 'price' => 4850, 'pv' => 3800, 'icon' => '💎', 'color' => 'emerald-600'],
                ];
            @endphp

            @foreach($packages as $pkg)
                @php
                    $isCurrent = $user->package_id == $pkg['id'];
                    $isUpgrade = $user->package_id && $user->package_id < $pkg['id'];
                    $isLocked = $user->package_id && $user->package_id > $pkg['id'];
                @endphp

                <div class="bg-[var(--bg-card)] rounded-xl shadow-sm p-6 border {{ $isCurrent ? 'border-primary-500 ring-2 ring-primary-500/50' : 'border-[var(--border-color)]' }} hover:shadow-lg transition-all">
                    <div class="text-center">
                        <div class="text-4xl mb-2">{{ $pkg['icon'] }}</div>
                        <h3 class="text-xl font-bold text-[var(--text-primary)]">{{ $pkg['name'] }}</h3>
                        <p class="text-3xl font-bold text-primary-600 mt-2">${{ $pkg['price'] }}</p>
                        <p class="text-sm text-[var(--text-secondary)]">{{ $pkg['pv'] }} PV</p>
                        
                        @if($isCurrent)
                            <span class="inline-block mt-3 px-4 py-1 bg-primary-500/20 text-primary-600 text-sm font-semibold rounded-full">
                                ✅ Package actuel
                            </span>
                        @elseif($isLocked)
                            <span class="inline-block mt-3 px-4 py-1 bg-red-500/20 text-red-500 text-sm font-semibold rounded-full">
                                🔒 Verrouillé
                            </span>
                        @else
                            <form action="{{ route('packages.buy') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $pkg['id'] }}">
                                <button type="submit" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-all hover:scale-105">
                                    {{ $isUpgrade ? 'Mettre à niveau' : 'Acheter' }}
                                </button>
                            </form>
                        @endif

                        <!-- Avantages -->
                        <div class="mt-3 text-left text-sm text-[var(--text-secondary)] border-t border-[var(--border-color)] pt-3">
                            <p>✅ Commission jusqu'à 30%</p>
                            <p>✅ {{ $pkg['pv'] }} PV</p>
                            <p>✅ Accès à la boutique</p>
                            <p>✅ Parrainage illimité</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<style>
    .badge-modern {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
</style>
@endsection
