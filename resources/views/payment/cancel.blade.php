@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto py-12">
    <div class="card text-center">
        <div class="text-6xl mb-4">❌</div>
        <h1 class="text-3xl font-bold text-red-500">Paiement annulé</h1>
        <p class="text-[var(--text-secondary)] mt-2">
            Vous avez annulé le paiement.
            <br>
            Aucun montant n'a été débité.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('packages.index') }}" class="btn btn-primary">
                Réessayer
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline">
                Retour au dashboard
            </a>
        </div>
    </div>
</div>
@endsection