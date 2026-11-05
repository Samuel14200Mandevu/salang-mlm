@extends(auth()->check() ? 'layouts.app' : 'layouts.public')

@section('title', 'Paiement annulé')

@section('content')
@php
    $isGuest = ! empty($isGuestPaymentReturn) || ! auth()->check();
@endphp
<div class="max-w-md mx-auto py-8 sm:py-12 px-3 sm:px-4">
    <div class="card text-center animate-fadeInUp">
        <svg class="payment-icon mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>

        <h1 class="text-2xl sm:text-3xl font-bold text-red-500">Paiement Annulé</h1>

        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
            {{ $message ?? 'Vous avez annulé le paiement.' }}
            <br>
            <span class="text-yellow-500 font-medium">Aucun montant n'a été débité.</span>
        </p>

        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-left text-sm text-yellow-700 dark:text-yellow-300">
            <p>
                <strong>Conseil :</strong> Vérifiez votre solde, votre numéro de téléphone
                et réessayez. Si le problème persiste, contactez notre support.
            </p>
        </div>

        <div class="cancel-actions mt-4 sm:mt-6 flex flex-wrap justify-center gap-2 sm:gap-3">
            @if($isGuest)
                <a href="{{ route('login') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Se connecter
                </a>
                <a href="{{ url('/') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Accueil
                </a>
            @else
                <a href="{{ route('wallet.deposit') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Réessayer un dépôt
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Accéder au Dashboard
                </a>
            @endif
        </div>

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
