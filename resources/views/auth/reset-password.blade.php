@extends('layouts.auth')

@section('title', 'Nouveau mot de passe — Salang Group')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Nouveau mot de passe',
        'lead' => 'Choisissez un mot de passe d’au moins 8 caractères, distinct de vos anciens accès.',
    ])

    @include('partials.auth.alerts', ['showErrors' => true])

    <form method="POST" action="{{ route('password.store') }}" id="resetForm" class="auth-form" data-password-form novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-form-group">
            <label for="email">Adresse email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $request->email) }}"
                   class="auth-input @error('email') auth-input-error @enderror"
                   required autofocus autocomplete="email">
            @error('email')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password">Nouveau mot de passe</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" data-password-meter
                       class="auth-input @error('password') auth-input-error @enderror"
                       minlength="8" required autocomplete="new-password">
                @include('partials.auth.password-toggle')
            </div>
            <div class="password-strength"><div class="password-strength-bar" data-password-meter-bar></div></div>
            <p class="auth-hint" data-password-meter-label data-empty-label="8 caractères minimum">8 caractères minimum</p>
            @error('password')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password_confirmation">Confirmation</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="auth-input" required autocomplete="new-password">
        </div>

        <button type="submit" class="auth-btn-primary" data-loading-text="Enregistrement…">Enregistrer le mot de passe</button>
    </form>

    <div class="auth-meta-links">
        <p><a href="{{ route('login') }}" class="auth-link font-normal">Retour à la connexion</a></p>
    </div>
@endsection

@include('partials.auth.flash-toasts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('resetForm');
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    form?.addEventListener('submit', function (e) {
        const email = form.querySelector('#email')?.value.trim() ?? '';
        const password = form.querySelector('#password')?.value ?? '';
        const confirm = form.querySelector('#password_confirmation')?.value ?? '';
        if (!emailRe.test(email)) {
            e.preventDefault();
            window.showToast?.('Adresse email invalide.', 'error');
            return;
        }
        if (password.length < 8 || password !== confirm) {
            e.preventDefault();
            window.showToast?.('Vérifiez le mot de passe et sa confirmation.', 'error');
        }
    });
});
</script>
@endpush
