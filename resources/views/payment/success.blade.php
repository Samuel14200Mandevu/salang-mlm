@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto py-12">
    <div class="card text-center">
        <div class="text-6xl mb-4 animate-float">✅</div>
        <h1 class="text-3xl font-bold text-green-500">Paiement réussi !</h1>
        <p class="text-[var(--text-secondary)] mt-2">
            Votre paiement a été confirmé avec succès.
            <br>
            Votre portefeuille a été crédité.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                Retour au dashboard
            </a>
            <a href="{{ route('wallet.index') }}" class="btn btn-outline">
                Voir mon portefeuille
            </a>
        </div>
    </div>
</div>
@endsection