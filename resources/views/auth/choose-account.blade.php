@extends('layouts.auth')

@section('title', 'Choisir un compte — Salang Group')
@section('auth-body-class', 'auth-choose-account')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Plusieurs comptes trouvés',
        'lead' => 'Ce numéro est lié à plusieurs comptes. Choisissez celui avec lequel vous voulez vous connecter.',
    ])

    @include('partials.auth.alerts', ['showErrors' => true])

    <form method="POST" action="{{ route('login.choose-account.store') }}" class="auth-form" id="chooseAccountForm">
        @csrf

        <fieldset class="auth-account-list">
            <legend class="sr-only">Comptes disponibles</legend>

            @foreach ($accounts as $index => $account)
                <label class="auth-account-option">
                    <input
                        type="radio"
                        name="user_id"
                        value="{{ $account['id'] }}"
                        class="auth-account-option__radio"
                        @checked(old('user_id', $index === 0 ? $account['id'] : null) == $account['id'])
                        required
                    >
                    <span class="auth-account-option__body">
                        <span class="auth-account-option__name">{{ $account['name'] }}</span>
                        <span class="auth-account-option__meta">
                            ID {{ $account['sponsor_id'] }}
                            <span aria-hidden="true">·</span>
                            {{ $account['email_masked'] }}
                        </span>
                    </span>
                </label>
            @endforeach
        </fieldset>

        @error('user_id')
            <p class="auth-error" role="alert">{{ $message }}</p>
        @enderror

        <button type="submit" class="auth-btn-primary" data-loading-text="Connexion…">Continuer</button>
    </form>

    <div class="auth-meta-links">
        <p><a href="{{ route('login') }}" class="auth-link">← Retour à la connexion</a></p>
    </div>
@endsection

@include('partials.auth.flash-toasts')
