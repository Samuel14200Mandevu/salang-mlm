@extends('layouts.app')

@section('title', $paymentStatus === 'completed' ? 'Paiement réussi' : 'Paiement en cours')

@section('content')
@php
    $status = $paymentStatus ?? 'unknown';
    $isCompleted = $status === 'completed';
    $isPending = $status === 'pending';
    $isFailed = in_array($status, ['failed', 'cancelled'], true);
@endphp
<div class="max-w-md mx-auto py-8 sm:py-12 px-3 sm:px-4"
     @if(!empty($autoRefreshPending))
         data-payment-auto-refresh="1"
         data-refresh-interval="10000"
     @endif
>
    <div class="card text-center animate-fadeInUp">

        @if($isCompleted)
            <svg class="payment-icon mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <h1 class="text-2xl sm:text-3xl font-bold text-green-500">Paiement confirmé</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
                {{ $message ?? 'Votre paiement a été confirmé avec succès.' }}
                <br>
                <span class="text-green-500 font-medium">Votre portefeuille a été crédité.</span>
            </p>
        @elseif($isPending)
            <svg class="payment-icon mx-auto text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h1 class="text-2xl sm:text-3xl font-bold text-amber-600">Paiement en cours de vérification</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
                Votre paiement est en cours de vérification par le réseau.
                Votre solde sera mis à jour dès réception de la confirmation.
            </p>
            <p class="text-xs text-[var(--text-secondary)] mt-3">
                Cette page se rafraîchit automatiquement…
            </p>
        @elseif($isFailed)
            <svg class="payment-icon mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <h1 class="text-2xl sm:text-3xl font-bold text-red-500">Paiement non abouti</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
                Le paiement a échoué ou a été annulé. Aucun crédit n’a été appliqué à votre portefeuille.
            </p>
        @else
            <svg class="payment-icon mx-auto text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
            </svg>
            <h1 class="text-2xl sm:text-3xl font-bold text-[var(--text-primary)]">Retour de paiement</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-2">
                {{ $message ?? 'Consultez votre portefeuille pour confirmer le statut du dépôt.' }}
            </p>
        @endif

        @if($transaction ?? null)
        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-left text-sm">
            <p class="text-[var(--text-secondary)]">
                <span class="font-medium">Référence:</span>
                {{ $transaction->reference ?? 'N/A' }}
            </p>
            <p class="text-[var(--text-secondary)]">
                <span class="font-medium">Montant:</span>
                ${{ number_format((float) $transaction->amount, 2) }}
            </p>
            <p class="text-[var(--text-secondary)]">
                <span class="font-medium">Statut:</span>
                {{ $transaction->status_label ?? ucfirst($transaction->status) }}
            </p>
            <p class="text-[var(--text-secondary)]">
                <span class="font-medium">Date:</span>
                {{ optional($transaction->created_at)->format('d/m/Y H:i') }}
            </p>
        </div>
        @endif

        <div class="success-actions mt-4 sm:mt-6 flex flex-wrap justify-center gap-2 sm:gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                Accéder au Dashboard
            </a>
            <a href="{{ route('wallet.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                Voir Mon Portefeuille
            </a>
        </div>
    </div>
</div>
@endsection

@if(!empty($autoRefreshPending))
@push('scripts')
<script>
(function () {
    var el = document.querySelector('[data-payment-auto-refresh="1"]');
    if (!el) return;
    var ms = parseInt(el.getAttribute('data-refresh-interval') || '10000', 10) || 10000;
    setTimeout(function () { window.location.reload(); }, ms);
})();
</script>
@endpush
@endif
