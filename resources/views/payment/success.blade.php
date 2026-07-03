@extends('layouts.app')

@push('styles')
<style>
    .payment-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        color: #22c55e;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
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
        <svg class="payment-icon mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <h1 class="text-2xl sm:text-3xl font-bold text-green-500">Paiement reussi !</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
            Votre paiement a ete confirme avec succes.
            <br>
            Votre portefeuille a ete credite.
        </p>
        <div class="mt-4 sm:mt-6 flex flex-wrap justify-center gap-2 sm:gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Retour au dashboard
            </a>
            <a href="{{ route('wallet.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Voir mon portefeuille
            </a>
        </div>
    </div>
</div>
@endsection