@extends('layouts.app')

@section('title', 'Paiement annulé')



@section('content')
<div class="max-w-md mx-auto py-8 sm:py-12 px-3 sm:px-4">
    <div class="card text-center animate-fadeInUp">
        
        <!-- Cancel Icon -->
        <svg class="payment-icon mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        
        <h1 class="text-2xl sm:text-3xl font-bold text-red-500">Paiement Annulé</h1>
        
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
            {{ $message ?? 'Vous avez annulé le paiement.' }}
            <br>
            <span class="text-yellow-500 font-medium">Aucun montant n'a été débité.</span>
        </p>
        
        <!-- Raison possible -->
        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-left text-sm text-yellow-700 dark:text-yellow-300">
            <p class="flex items-start gap-2">
                <span class="text-lg"></span>
                <span>
                    <strong>Conseil :</strong> Vérifiez votre solde, votre numéro de téléphone 
                    et réessayez. Si le problème persiste, contactez notre support.
                </span>
            </p>
        </div>
        
        <!-- Actions -->
        <div class="cancel-actions mt-4 sm:mt-6 flex flex-wrap justify-center gap-2 sm:gap-3">
            <a href="{{ route('cart.index') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Réessayer
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Accéder au Dashboard
            </a>
        </div>
        
        <!-- Support -->
        <div class="mt-4">
            <p class="text-xs text-[var(--text-tertiary)]">
                Besoin d'aide ? 
                <a href="{{ route('contact') }}" class="text-primary-500 hover:underline">
                    Contactez notre support
                </a>
            </p>
        </div>
    </div>
</div>
@endsection