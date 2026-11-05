@extends('layouts.auth')

@section('title', 'Activation du compte — Salang Group')

@section('auth-inner-class', 'max-w-[480px]')
@section('auth-card-class', 'auth-card-wide')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Activation du compte',
        'lead' => 'Finalisez votre adhésion pour accéder aux commissions et au réseau.',
    ])

    @include('partials.auth.alerts', ['showErrors' => true])

    <div class="auth-notice" role="status">
        <p class="font-semibold text-[var(--text-primary)]">Compte en attente d’activation</p>
        <p class="mt-1 text-sm text-[var(--text-secondary)]">
            Souscrivez un package en ligne, saisissez le code reçu au guichet, ou demandez l’activation à l’administration.
            Un rappel détaillé est disponible dans vos notifications.
        </p>
    </div>

    <section class="auth-activate-block" aria-labelledby="activate-code-heading">
        <h2 id="activate-code-heading" class="auth-activate-heading">
            Paiement au guichet
            <span class="auth-activate-badge">Code reçu</span>
        </h2>

        <form action="{{ route('activate.code') }}" method="POST" class="auth-form">
            @csrf
            <div class="auth-form-group">
                <label for="activation_code">Code d’activation</label>
                <input type="text"
                       name="activation_code"
                       id="activation_code"
                       class="auth-input font-mono uppercase @error('activation_code') auth-input-error @enderror"
                       placeholder="ACT-XXXXXXXX"
                       required
                       autocomplete="off"
                       autofocus>
                @error('activation_code')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="auth-btn-primary" data-loading-text="Activation…">
                Activer mon compte
            </button>
        </form>

        <p class="auth-hint mt-3">
            Code non reçu ? Contactez l’administrateur, ou utilisez le bouton « Demander l’activation à l’admin » ci-dessous.
        </p>
    </section>

    <div class="auth-divider" role="separator">ou</div>

    <p class="text-sm text-center text-[var(--text-secondary)]">
        <a href="{{ route('subscriptions.index') }}" class="auth-link">Souscrire un package en ligne</a>
    </p>

    <section class="auth-activate-block mt-6" aria-labelledby="activate-admin-heading">
        <h2 id="activate-admin-heading" class="auth-activate-heading">
            Demande à l’administration
        </h2>
        <p class="text-sm text-[var(--text-secondary)] mb-3">
            Si vous avez déjà réglé votre adhésion ou besoin d’une activation manuelle, envoyez une demande à l’équipe Salang.
        </p>
        <form action="{{ route('activate.request-admin') }}" method="POST" class="auth-form">
            @csrf
            <button type="submit" class="auth-btn-outline w-full" data-loading-text="Envoi…">
                Demander l’activation à l’admin
            </button>
        </form>
    </section>

    <div class="auth-meta-links">
        <p class="text-xs text-[var(--text-muted)]">
            Assistance :
            <a href="mailto:support@salanggroup.com" class="auth-link font-normal">support@salanggroup.com</a>
        </p>
    </div>
@endsection

@include('partials.auth.flash-toasts')
