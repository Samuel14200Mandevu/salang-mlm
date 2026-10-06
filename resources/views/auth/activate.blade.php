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
            Saisissez le code reçu après paiement au guichet, ou souscrivez un package en ligne.
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

        <details class="auth-activate-details">
            <summary>Code non reçu · renvoi par email ou SMS</summary>
            <div class="auth-activate-resend">
                <form action="{{ route('activate.resend') }}" method="POST" class="auth-activate-resend-form">
                    @csrf
                    <input type="hidden" name="method" value="email">
                    <button type="submit" class="auth-btn-outline">Renvoyer par email</button>
                </form>

                <form action="{{ route('activate.resend') }}" method="POST" class="auth-activate-resend-form">
                    @csrf
                    <input type="hidden" name="method" value="sms">
                    <div class="auth-form-group mb-0">
                        <label for="activate_phone" class="sr-only">Numéro de téléphone</label>
                        <input type="tel" name="phone" id="activate_phone" class="auth-input"
                               placeholder="+243 …" required autocomplete="tel">
                    </div>
                    <button type="submit" class="auth-btn-outline" data-loading-text="Envoi…">Envoyer par SMS</button>
                </form>
                <p class="auth-hint">Orange, Airtel, Vodacom — détection automatique de l’opérateur.</p>
            </div>
        </details>
    </section>

    <div class="auth-divider" role="separator">ou</div>

    <p class="text-sm text-center text-[var(--text-secondary)]">
        <a href="{{ route('subscriptions.index') }}" class="auth-link">Souscrire un package en ligne</a>
    </p>

    <div class="auth-meta-links">
        <p class="text-xs text-[var(--text-muted)]">
            Assistance :
            <a href="mailto:support@salang.com" class="auth-link font-normal">support@salang.com</a>
        </p>
    </div>
@endsection

@include('partials.auth.flash-toasts')
