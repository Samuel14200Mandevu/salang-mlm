@extends('layouts.auth')

@section('title', 'Vérification email — Salang Group')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Vérifiez votre email',
        'lead' => 'Un message de confirmation a été envoyé à l’adresse de votre compte.',
    ])

    @if (session('status') == 'verification-link-sent')
        <div class="auth-alert-success" role="status">Un nouveau lien de vérification vient d’être envoyé.</div>
    @endif

    <div class="auth-prose">
        Ouvrez l’email reçu (pensez aux courriers indésirables), cliquez sur le lien de confirmation,
        puis reconnectez-vous pour accéder à l’espace membre.
    </div>

    <form method="POST" action="{{ route('verification.send') }}" class="auth-form mb-3">
        @csrf
        <button type="submit" class="auth-btn-primary" data-loading-text="Envoi…">Renvoyer l’email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="auth-form-skip">
        @csrf
        <button type="submit" class="auth-btn-outline" data-no-loading>Se déconnecter</button>
    </form>

    <div class="auth-meta-links">
        <p><a href="{{ url('/') }}" class="auth-link font-normal">Retour à l’accueil</a></p>
    </div>
@endsection

@include('partials.auth.flash-toasts')
