@extends('layouts.auth')

@section('title', 'Inscription — Salang Group')

@section('auth-inner-class', 'max-w-[480px]')
@section('auth-card-class', 'auth-card-wide')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Inscription membre',
        'lead' => 'Adhésion unique de 30&nbsp;USD · code parrain obligatoire.',
    ])

    @include('partials.auth.alerts', ['showErrors' => true])

    @if (session('social_data'))
        @php $social = session('social_data'); @endphp
        <div class="social-info-box">
            @if (!empty($social['avatar']))
                <img src="{{ $social['avatar'] }}" alt="" class="avatar-social" loading="lazy" width="44" height="44">
            @else
                <div class="avatar-social flex items-center justify-center bg-primary-600 text-white font-semibold text-lg" aria-hidden="true">
                    {{ mb_strtoupper(mb_substr($social['name'] ?? 'U', 0, 1)) }}
                </div>
            @endif
            <div class="text-left">
                <p class="social-label">Profil {{ ucfirst($social['provider'] ?? 'social') }}</p>
                <p class="social-email">{{ $social['email'] ?? '' }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" id="registerForm" class="auth-form" data-password-form
          data-check-sponsor="{{ url('/check-sponsor') }}"
          data-check-email="{{ url('/check-email') }}"
          data-google-redirect="{{ route('social.redirect', 'google') }}">
        @csrf

        <div class="auth-form-group">
            <label for="name">Nom complet<span class="required" aria-hidden="true">*</span></label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', session('social_data.name', '')) }}"
                   class="auth-input @error('name') auth-input-error @enderror"
                   placeholder="Prénom et nom" required autofocus autocomplete="name">
            @error('name')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="email">Adresse email<span class="required" aria-hidden="true">*</span></label>
            <input type="email" name="email" id="email"
                   value="{{ old('email', session('social_data.email', '')) }}"
                   class="auth-input @error('email') auth-input-error @enderror"
                   placeholder="@salanggroup.com" required autocomplete="email">
            <div id="emailAvailability" aria-live="polite"></div>
            @error('email')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="phone">Téléphone <span class="text-[var(--text-muted)] font-normal">(facultatif)</span></label>
            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                   class="auth-input @error('phone') auth-input-error @enderror"
                   placeholder="+243 …" autocomplete="tel">
            @error('phone')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="sponsor_id">Code parrain<span class="required" aria-hidden="true">*</span></label>
            <input type="text" name="sponsor_id" id="sponsor_id"
                   value="{{ old('sponsor_id', session('sponsor_id', request()->query('ref', ''))) }}"
                   class="auth-input @error('sponsor_id') auth-input-error @enderror"
                   placeholder="Entre le code parrain" required autocomplete="off" autocapitalize="characters">
            <p class="auth-hint">Transmis par votre parrain · contrôle automatique.</p>
            <div id="sponsorStatus" class="sponsor-status" aria-live="polite"></div>
            @error('sponsor_id')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password">Mot de passe<span class="required" aria-hidden="true">*</span></label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" data-password-meter
                       class="auth-input @error('password') auth-input-error @enderror"
                       placeholder="8 caractères minimum" required minlength="8" autocomplete="new-password">
                @include('partials.auth.password-toggle')
            </div>
            <div class="password-strength" aria-hidden="true"><div class="password-strength-bar" data-password-meter-bar id="passwordStrength"></div></div>
            <p class="auth-hint" id="passwordStrengthText" data-password-meter-label data-empty-label="8 caractères minimum">8 caractères minimum</p>
            @error('password')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password_confirmation">Confirmation<span class="required" aria-hidden="true">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="auth-input" placeholder="Répétez le mot de passe" required autocomplete="new-password">
        </div>

        <div class="auth-form-group mb-6">
            <label class="flex items-start gap-2.5 text-sm text-[var(--text-secondary)] cursor-pointer leading-snug">
                <input type="checkbox" name="terms" id="terms" value="1" required
                       class="mt-0.5 w-4 h-4 rounded border-[var(--border-color)] text-primary-600 focus:ring-primary-500">
                <span>
                    J’accepte les
                    <a href="{{ route('legal.terms') }}" class="auth-link font-normal" target="_blank" rel="noopener noreferrer">conditions générales</a>
                    et la
                    <a href="{{ route('legal.privacy') }}" class="auth-link font-normal" target="_blank" rel="noopener noreferrer">politique de confidentialité</a>.
                </span>
            </label>
            @error('terms')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-btn-primary" data-loading-text="Création du compte…">Créer mon compte</button>
    </form>

    <div class="auth-divider" role="separator">ou</div>

    @include('partials.auth.google-signin', ['asButton' => true, 'id' => 'googleBtn'])

    <div class="auth-meta-links">
        <p>Déjà membre ? <a href="{{ route('login') }}" class="auth-link">Se connecter</a></p>
        <p><a href="{{ url('/') }}" class="auth-link font-normal">Retour à l’accueil</a></p>
    </div>
@endsection

@include('partials.auth.flash-toasts')
