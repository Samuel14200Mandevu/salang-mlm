@extends('layouts.auth')

@section('auth-body-class', 'auth-mobile-recover')

@section('title', 'Mot de passe oublié — Salang Group')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Mot de passe oublié',
        'lead' => 'Nous enverrons un lien sécurisé à l’adresse enregistrée sur votre compte.',
    ])

    @include('partials.auth.alerts', ['showErrors' => true])

    <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" class="auth-form" novalidate>
        @csrf
        <div class="auth-form-group">
            <label for="email">Adresse email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="auth-input @error('email') auth-input-error @enderror"
                   placeholder="@salanggroup.com" required autofocus autocomplete="email">
            @error('email')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="auth-btn-primary" data-loading-text="Envoi…">Recevoir le lien</button>
    </form>

    <div class="auth-meta-links">
        <p><a href="{{ route('login') }}" class="auth-link font-normal">Retour à la connexion</a></p>
    </div>
@endsection

@include('partials.auth.flash-toasts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    form?.addEventListener('submit', function (e) {
        const email = emailInput?.value.trim() ?? '';
        if (!emailRe.test(email)) {
            e.preventDefault();
            window.salangAuthSetFieldError?.(emailInput, 'Adresse email invalide.');
            emailInput?.focus();
        }
    });
});
</script>
@endpush
