@extends('layouts.auth')

@section('title', 'Connexion — Salang Group')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Connexion',
        'lead' => 'Accédez à votre espace : hub Services, boutique, réseau et assistance.',
    ])

    @include('partials.auth.alerts', ['showErrors' => true])

    <form method="POST" action="{{ route('login') }}" id="loginForm" class="auth-form" novalidate>
        @csrf

        <div class="auth-form-group">
            <label for="email">Adresse email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="auth-input @if($errors->has('email') || $errors->has('credentials')) auth-input-error @endif"
                   placeholder="@salanggroup.com" required autofocus autocomplete="email">
            @error('email')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password">Mot de passe</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password"
                       class="auth-input @if($errors->has('password') || $errors->has('credentials')) auth-input-error @endif"
                       placeholder="••••••••" required autocomplete="current-password">
                @include('partials.auth.password-toggle')
            </div>
            @error('password')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-actions">
            <label class="flex items-center gap-2 text-sm text-[var(--text-secondary)] cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-[var(--border-color)] text-primary-600 focus:ring-primary-500 focus:ring-offset-0">
                Rester connecté
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link text-sm">Mot de passe oublié</a>
            @endif
        </div>

        <button type="submit" class="auth-btn-primary" data-loading-text="Connexion…">Se connecter</button>
    </form>

    <div class="auth-divider" role="separator">ou</div>

    @include('partials.auth.google-signin', ['id' => 'googleLoginBtn'])

    <div class="auth-meta-links">
        <p>Pas encore membre ? <a href="{{ route('register') }}" class="auth-link">Créer un compte</a></p>
        <p><a href="{{ url('/') }}" class="auth-link font-normal">Retour à l’accueil</a></p>
    </div>
@endsection

@include('partials.auth.flash-toasts')
