@extends('layouts.app')

@push('styles')
<style>
    .payment-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        color: #ef4444;
    }
    
    @media (max-width: 640px) {
        .payment-icon { width: 3rem; height: 3rem; }
        .text-3xl { font-size: 1.5rem; }
        .card { padding: 1.25rem; }
        .btn { font-size: 0.813rem; padding: 0.5rem 1rem; }
    }
</style>
@endpush

@section('content')
<div class="max-w-md mx-auto py-8 sm:py-12 px-3 sm:px-4">
    <div class="card text-center p-4 sm:p-6 md:p-8">
        <svg class="payment-icon mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <h1 class="text-2xl sm:text-3xl font-bold text-red-500">Paiement annule</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
            Vous avez annule le paiement.
            <br>
            Aucun montant n'a ete debite.
        </p>
        <div class="mt-4 sm:mt-6 flex flex-wrap justify-center gap-2 sm:gap-3">
            <a href="{{ route('subscriptions.index') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reessayer
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Retour au dashboard
            </a>
        </div>
    </div>
</div>
@endsection