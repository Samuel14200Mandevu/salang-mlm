@extends('layouts.app')

@section('title', 'Paiement réussi')



@section('content')
<div class="max-w-md mx-auto py-8 sm:py-12 px-3 sm:px-4">
    <div class="card text-center animate-fadeInUp">
        
        <!-- Success Icon -->
        <svg class="payment-icon mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        
        <h1 class="text-2xl sm:text-3xl font-bold text-green-500">Paiement Réussi</h1>
        
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
            {{ $message ?? 'Votre paiement a été confirmé avec succès.' }}
            <br>
            <span class="text-green-500 font-medium">Votre portefeuille a été crédité.</span>
        </p>
        
        <!-- Détails du paiement (si disponibles) -->
        @if(session('transaction'))
        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-left text-sm">
            <p class="text-[var(--text-secondary)]">
                <span class="font-medium">Référence:</span> 
                {{ session('transaction.reference') ?? 'N/A' }}
            </p>
            <p class="text-[var(--text-secondary)]">
                <span class="font-medium">Montant:</span> 
                {{ session('transaction.amount') ?? '0' }} USD
            </p>
            <p class="text-[var(--text-secondary)]">
                <span class="font-medium">Date:</span> 
                {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>
        @endif
        
        <!-- Actions -->
        <div class="success-actions mt-4 sm:mt-6 flex flex-wrap justify-center gap-2 sm:gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Accéder au Dashboard
            </a>
            <a href="{{ route('wallet.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Voir Mon Portefeuille
            </a>
        </div>
        
        <!-- Retour aux commandes -->
        <div class="mt-4">
            <a href="{{ route('orders.index') }}" class="text-sm text-[var(--text-secondary)] hover:text-primary-500 transition">
                ← Voir mes commandes
            </a>
        </div>
    </div>
</div>
@endsection