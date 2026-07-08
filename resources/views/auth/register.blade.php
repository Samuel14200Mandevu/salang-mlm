@extends('layouts.auth')

@section('title', 'Inscription - Salang MLM')

@push('styles')
<style>
    .auth-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-lg);
        animation: fadeInUp 0.6s ease forwards;
        position: relative;
        overflow: hidden;
        max-width: 480px;
        width: 100%;
        margin: 0 auto;
    }
    .auth-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
    }
    .auth-logo {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .auth-logo img {
        height: 60px;
        width: auto;
        margin: 0 auto;
    }
    .auth-logo .brand-name {
        font-size: 1.5rem;
        font-weight: 800;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }
    .auth-title {
        text-align: center;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .auth-subtitle {
        text-align: center;
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }
    .password-strength {
        height: 4px;
        border-radius: var(--radius-full);
        background: var(--bg-secondary);
        margin-top: 0.5rem;
        overflow: hidden;
    }
    .password-strength-bar {
        height: 100%;
        border-radius: var(--radius-full);
        transition: width 0.3s ease, background 0.3s ease;
        width: 0%;
    }
    .password-strength-text {
        font-size: 0.7rem;
        margin-top: 0.25rem;
    }
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.5rem 0;
        color: var(--text-tertiary);
        font-size: 0.75rem;
    }
    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }
    .social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }
    .social-btn:hover {
        background: var(--bg-hover);
        border-color: var(--primary-500);
        transform: translateY(-1px);
    }
    .social-btn:active {
        transform: scale(0.98);
    }
    .social-btn svg {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
    }
    
    .form-group {
        margin-bottom: 1rem;
        position: relative;
    }
    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.375rem;
    }
    .form-group label svg {
        display: inline;
        width: 1rem;
        height: 1rem;
        margin-right: 0.375rem;
        vertical-align: middle;
    }
    .form-group label .required {
        color: #ef4444;
        font-weight: 700;
    }
    .form-group .input {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
    }
    .form-group .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    .form-group .input-error {
        border-color: #ef4444;
    }
    .form-group .input-error:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
    }
    .form-group .input-success {
        border-color: #22c55e;
    }
    .form-group .input-success:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12);
    }
    .form-group .error-message {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .form-group .error-message svg {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }
    .form-group .success-message {
        color: #22c55e;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .form-group .success-message svg {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }
    .form-group .checking-message {
        color: #f59e0b;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .password-wrapper {
        position: relative;
    }
    .password-wrapper .input {
        padding-right: 2.75rem;
    }
    .password-toggle {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-tertiary);
        cursor: pointer;
        padding: 0.25rem;
        transition: color 0.2s ease;
    }
    .password-toggle:hover {
        color: var(--text-primary);
    }
    .password-toggle svg {
        width: 1.25rem;
        height: 1.25rem;
    }
    .form-hint {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-top: 0.25rem;
    }
    
    /* Toast */
    .toast-modern {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(-100px);
        padding: 1rem 1.5rem;
        border-radius: var(--radius-md);
        background: var(--bg-card);
        color: var(--text-primary);
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
        border: 1px solid var(--border-color);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        min-width: 280px;
        max-width: 90vw;
    }
    .toast-modern.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    .toast-modern .toast-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1rem;
    }
    .toast-modern .toast-icon.error {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .toast-modern .toast-icon.success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .toast-modern .toast-close {
        background: none;
        border: none;
        color: var(--text-tertiary);
        cursor: pointer;
        padding: 0.25rem;
        transition: color 0.2s ease;
        margin-left: auto;
    }
    .toast-modern .toast-close:hover {
        color: var(--text-primary);
    }
    
    .social-info-box {
        background: rgba(99, 102, 241, 0.06);
        border: 1px solid rgba(99, 102, 241, 0.15);
        border-radius: var(--radius-md);
        padding: 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .social-info-box .avatar-social {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-500);
    }
    .social-info-box .social-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .social-info-box .social-email {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .email-availability-checking {
        color: #f59e0b;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .email-availability-available {
        color: #22c55e;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .email-availability-unavailable {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    @media (max-width: 640px) {
        .auth-card { padding: 1.5rem; max-width: 100%; }
        .auth-logo img { height: 50px; }
        .auth-logo .brand-name { font-size: 1.25rem; }
        .auth-title { font-size: 1.25rem; }
        .form-group label { font-size: 0.813rem; }
        .form-group .input { font-size: 0.813rem; padding: 0.5rem 0.875rem; }
        .social-btn { font-size: 0.813rem; padding: 0.5rem 0.75rem; }
        .social-btn svg { width: 1.125rem; height: 1.125rem; }
        .toast-modern { min-width: auto; max-width: 90vw; padding: 0.75rem 1rem; font-size: 0.813rem; }
        .toast-modern .toast-icon { width: 28px; height: 28px; font-size: 0.875rem; }
        .social-info-box { flex-direction: column; text-align: center; }
        .social-info-box .avatar-social { width: 48px; height: 48px; }
    }
    
    @media (max-width: 480px) {
        .auth-card { padding: 1.25rem; }
        .form-group .input { font-size: 0.75rem; padding: 0.5rem 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="auth-card">
    
   <!-- Logo -->
<div class="auth-logo">
    <img src="{{ asset('images/salang_logo.png') }}" 
         alt="Salang MLM" 
         class="logo-themeable h-12 sm:h-16 w-auto mx-auto">
    <span class="brand-name block mt-2">Salang Group</span>
</div>

    <h2 class="auth-title">Créer un compte</h2>
    <p class="auth-subtitle">Rejoignez la communauté Salang</p>

    <!-- Messages de session -->
    @if (session('success'))
        <div class="mb-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Info connexion sociale -->
    @if(session('social_data'))
        @php $socialData = session('social_data'); @endphp
        <div class="social-info-box">
            @if(isset($socialData['avatar']))
                <img src="{{ $socialData['avatar'] }}" alt="Avatar" class="avatar-social">
            @else
                <div class="avatar-social" style="background: var(--gradient-primary); display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:1.2rem;">
                    {{ substr($socialData['name'] ?? 'U', 0, 1) }}
                </div>
            @endif
            <div>
                <p class="social-label">Inscription avec <strong>{{ ucfirst($socialData['provider'] ?? 'social') }}</strong></p>
                <p class="social-email">{{ $socialData['email'] ?? '' }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <!-- Nom -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Nom complet <span class="required">*</span>
            </label>
            <input type="text" 
                   name="name" 
                   id="name"
                   value="{{ old('name', session('social_data.name', '')) }}" 
                   class="input @error('name') input-error @enderror"
                   placeholder="Entrez votre nom complet"
                   required 
                   autofocus>
            @error('name')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Email avec vérification AJAX -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email <span class="required">*</span>
            </label>
            <input type="email" 
                   name="email" 
                   id="email"
                   value="{{ old('email', session('social_data.email', '')) }}" 
                   class="input @error('email') input-error @enderror"
                   placeholder="Entrez votre email"
                   required
                   autocomplete="email">
            
            <!-- Conteneur pour le message de vérification AJAX -->
            <div id="emailAvailability"></div>
            
            @error('email')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Téléphone -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                Téléphone
            </label>
            <input type="tel" 
                   name="phone" 
                   id="phone"
                   value="{{ old('phone') }}" 
                   class="input @error('phone') input-error @enderror"
                   placeholder="Entrez votre numéro de téléphone">
            @error('phone')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Sponsor ID -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                ID du parrain <span class="required">*</span>
            </label>
            <input type="text" 
                   name="sponsor_id" 
                   id="sponsor_id"
                   value="{{ old('sponsor_id', session('social_data.sponsor_id', request()->query('ref', ''))) }}" 
                   class="input @error('sponsor_id') input-error @enderror"
                   placeholder="Ex: SALABCDEF"
                   required>
            <p class="form-hint">Entrez l'ID de la personne qui vous a invité</p>
            @error('sponsor_id')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Mot de passe -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Mot de passe <span class="required">*</span>
            </label>
            <div class="password-wrapper">
                <input type="password" 
                       id="password"
                       name="password" 
                       class="input @error('password') input-error @enderror"
                       placeholder="8 caractères minimum"
                       required
                       minlength="8">
                <button type="button" 
                        class="password-toggle"
                        onclick="togglePassword(this)"
                        aria-label="Afficher/masquer le mot de passe">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            <div class="password-strength">
                <div class="password-strength-bar" id="passwordStrength"></div>
            </div>
            <p class="password-strength-text text-[var(--text-tertiary)]" id="passwordStrengthText">
                Minimum 8 caractères
            </p>
            @error('password')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Confirmer le mot de passe -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Confirmer le mot de passe <span class="required">*</span>
            </label>
            <input type="password" 
                   name="password_confirmation" 
                   id="password_confirmation"
                   class="input"
                   placeholder="Confirmez votre mot de passe"
                   required>
        </div>

        <!-- Conditions -->
        <div class="form-group mb-6">
            <label class="flex items-start gap-2 text-sm text-[var(--text-secondary)] cursor-pointer">
                <input type="checkbox" 
                       name="terms" 
                       id="terms"
                       value="1"
                       class="mt-0.5 w-4 h-4 rounded border-[var(--border-color)] text-primary-500 focus:ring-primary-500 focus:ring-offset-0"
                       required>
                <span>
                    J'accepte les 
                    <a href="#" class="text-primary-500 hover:text-primary-600 font-medium transition">
                        conditions générales
                    </a>
                    et la 
                    <a href="#" class="text-primary-500 hover:text-primary-600 font-medium transition">
                        politique de confidentialité
                    </a>
                </span>
            </label>
            @error('terms')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-full" id="submitBtn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Créer mon compte
        </button>
    </form>

    <div class="auth-divider">ou</div>

    <!-- Social Login - Google -->
    <div class="space-y-2">
        <a href="{{ route('social.redirect', 'google') }}" class="social-btn social-btn-google" id="googleBtn">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continuer avec Google
        </a>
    </div>

    <p class="text-center text-sm text-[var(--text-secondary)] mt-6">
        Déjà un compte ?
        <a href="{{ route('login') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
            Se connecter
        </a>
    </p>
    
    <p class="text-center text-sm text-[var(--text-secondary)] mt-2">
        <a href="{{ route('home') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
            ← Retour à l'accueil
        </a>
    </p>
</div>

<!-- Toast -->
<div id="toastModern" class="toast-modern" role="alert" aria-live="polite">
    <div class="toast-icon error" id="toastIcon">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <span id="toastMessage">Message</span>
    <button type="button" class="toast-close" onclick="hideToast()" aria-label="Fermer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

@push('scripts')
<script>
function togglePassword(btn) {
    const input = btn.closest('.password-wrapper').querySelector('input');
    const icon = btn.querySelector('svg');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    }
}

function showToast(message, type = 'error') {
    const toast = document.getElementById('toastModern');
    const icon = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');
    
    icon.className = 'toast-icon';
    icon.classList.add(type);
    
    if (type === 'error') {
        icon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    } else {
        icon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    }
    
    messageEl.textContent = message;
    toast.classList.add('show');
    
    clearTimeout(window.toastTimeout);
    window.toastTimeout = setTimeout(hideToast, 5000);
}

function hideToast() {
    document.getElementById('toastModern').classList.remove('show');
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Validation du formulaire
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const sponsorInput = document.getElementById('sponsor_id');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const termsCheckbox = document.getElementById('terms');
    const submitBtn = document.getElementById('submitBtn');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    const emailAvailability = document.getElementById('emailAvailability');

    // Afficher les erreurs Laravel
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            showToast('{{ $error }}', 'error');
        @break
        @endforeach
    @endif

    @if (session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif

    @if (session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif

    // ✅ Vérification en temps réel de l'email
    let emailTimeout = null;
    let emailChecked = false;

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            emailChecked = false;
            
            // Réinitialiser l'état
            this.classList.remove('input-error', 'input-success');
            
            if (emailAvailability) {
                emailAvailability.innerHTML = '';
            }

            // Ne vérifier que si l'email est valide
            if (isValidEmail(email)) {
                emailAvailability.innerHTML = `
                    <div class="email-availability-checking">
                        <svg class="animate-spin h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Vérification de l'email...
                    </div>
                `;
                
                clearTimeout(emailTimeout);
                emailTimeout = setTimeout(function() {
                    fetch(`/check-email?email=${encodeURIComponent(email)}`)
                        .then(response => response.json())
                        .then(data => {
                            emailChecked = true;
                            
                            if (data.exists) {
                                emailInput.classList.add('input-error');
                                emailInput.classList.remove('input-success');
                                
                                if (emailAvailability) {
                                    emailAvailability.innerHTML = `
                                        <div class="email-availability-unavailable">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            ${data.message}
                                        </div>
                                    `;
                                }
                            } else {
                                emailInput.classList.remove('input-error');
                                emailInput.classList.add('input-success');
                                
                                if (emailAvailability) {
                                    emailAvailability.innerHTML = `
                                        <div class="email-availability-available">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            ${data.message}
                                        </div>
                                    `;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Erreur de vérification:', error);
                            if (emailAvailability) {
                                emailAvailability.innerHTML = '';
                            }
                        });
                }, 500);
            }
        });
    }

    // Force du mot de passe
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            let label = '';

            if (value.length >= 8) strength += 25;
            if (/[a-z]/.test(value)) strength += 25;
            if (/[A-Z]/.test(value)) strength += 25;
            if (/[0-9]/.test(value)) strength += 25;

            strengthBar.style.width = strength + '%';

            if (strength <= 25) {
                strengthBar.style.background = '#ef4444';
                label = 'Faible';
            } else if (strength <= 50) {
                strengthBar.style.background = '#f59e0b';
                label = 'Moyen';
            } else if (strength <= 75) {
                strengthBar.style.background = '#3b82f6';
                label = 'Fort';
            } else {
                strengthBar.style.background = '#22c55e';
                label = 'Très fort';
            }

            if (value.length > 0) {
                strengthText.textContent = 'Force: ' + label;
                strengthText.className = 'password-strength-text mt-0.5';
            } else {
                strengthText.textContent = 'Minimum 8 caractères';
                strengthText.className = 'password-strength-text text-[var(--text-tertiary)]';
            }
        });
    }

    // Validation du formulaire
    form.addEventListener('submit', function(e) {
        let hasError = false;
        let errorMessage = '';

        // Vérifier le nom
        const name = nameInput.value.trim();
        if (!name) {
            errorMessage = 'Veuillez saisir votre nom complet.';
            hasError = true;
        }

        // Vérifier l'email
        const email = emailInput.value.trim();
        if (!hasError && !email) {
            errorMessage = 'Veuillez saisir votre adresse email.';
            hasError = true;
        } else if (!hasError && !isValidEmail(email)) {
            errorMessage = 'Veuillez saisir une adresse email valide.';
            hasError = true;
        } else if (!hasError && emailChecked === false) {
            // Vérifier que l'email a été vérifié
            errorMessage = 'Veuillez attendre la vérification de votre email.';
            hasError = true;
        } else if (!hasError && emailInput.classList.contains('input-error')) {
            errorMessage = 'Cet email est déjà utilisé. Veuillez en utiliser un autre.';
            hasError = true;
        }

        // Vérifier le sponsor
        const sponsor = sponsorInput.value.trim();
        if (!hasError && !sponsor) {
            errorMessage = 'Veuillez saisir l\'ID de votre parrain.';
            hasError = true;
        } else if (!hasError && sponsor.length < 3) {
            errorMessage = 'L\'ID du parrain doit contenir au moins 3 caractères.';
            hasError = true;
        }

        // Vérifier le mot de passe
        const password = passwordInput.value;
        if (!hasError && !password) {
            errorMessage = 'Veuillez saisir un mot de passe.';
            hasError = true;
        } else if (!hasError && password.length < 8) {
            errorMessage = 'Le mot de passe doit contenir au moins 8 caractères.';
            hasError = true;
        }

        // Vérifier la confirmation
        const confirm = confirmInput.value;
        if (!hasError && confirm !== password) {
            errorMessage = 'Les mots de passe ne correspondent pas.';
            hasError = true;
        }

        // Vérifier les conditions
        if (!hasError && !termsCheckbox.checked) {
            errorMessage = 'Vous devez accepter les conditions générales.';
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            showToast(errorMessage, 'error');
        }
    });

    // Vérification du sponsor pour les réseaux sociaux
    document.querySelectorAll('.social-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var sponsorId = sponsorInput.value.trim();
            
            if (!sponsorId) {
                e.preventDefault();
                showToast('Veuillez entrer un ID de parrain.', 'error');
                sponsorInput.focus();
                sponsorInput.classList.add('input-error');
                return false;
            }

            sponsorInput.classList.remove('input-error');
        });
    });

    sponsorInput.addEventListener('input', function() {
        this.classList.remove('input-error');
    });
});
</script>
@endpush
@endsection