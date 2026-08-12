@extends('layouts.app')

@push('styles')
<style>
    .payment-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        color: #ef4444;
        animation: float 3s ease-in-out infinite;
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
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-lg);
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    
    @media (max-width: 640px) {
        .payment-icon {
            width: 3rem;
            height: 3rem;
        }
        .text-3xl {
            font-size: 1.5rem;
        }
        .card {
            padding: 1.25rem;
        }
        .btn {
            font-size: 0.813rem;
            padding: 0.5rem 1rem;
        }
        .cancel-actions {
            flex-direction: column;
        }
        .cancel-actions .btn {
            width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .card {
            padding: 0.875rem;
        }
        .payment-icon {
            width: 2.5rem;
            height: 2.5rem;
        }
        .text-2xl {
            font-size: 1.25rem;
        }
        .text-sm {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

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