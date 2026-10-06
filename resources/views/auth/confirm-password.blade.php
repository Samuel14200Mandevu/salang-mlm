@extends('layouts.auth')

@section('title', 'Confirmation requise — Salang Group')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Confirmation requise',
        'lead' => 'Cette étape protège les opérations sensibles de votre compte membre.',
    ])

    @include('partials.auth.alerts', ['showErrors' => true])

    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
        @csrf
        <div class="auth-form-group">
            <label for="password">Mot de passe actuel</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password"
                       class="auth-input @error('password') auth-input-error @enderror"
                       required autofocus autocomplete="current-password">
                @include('partials.auth.password-toggle')
            </div>
            @error('password')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="auth-btn-primary" data-loading-text="Vérification…">Continuer</button>
    </form>

    <div class="auth-meta-links">
        <p><a href="{{ route('dashboard') }}" class="auth-link font-normal">Retour au tableau de bord</a></p>
    </div>
@endsection

@include('partials.auth.flash-toasts')
