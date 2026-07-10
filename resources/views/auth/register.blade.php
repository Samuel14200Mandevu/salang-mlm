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
    
    /* ✅ Sponsor Status - CORRIGÉ */
    .sponsor-status {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        padding: 0.375rem 0.625rem;
        border-radius: 0.375rem;
        display: none;
        align-items: center;
        gap: 0.5rem;
    }
    .sponsor-status.visible {
        display: flex;
    }
    .sponsor-status.success {
        color: #22c55e;
        background: rgba(34, 197, 94, 0.08);
        border-left: 3px solid #22c55e;
    }
    .sponsor-status.error {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.08);
        border-left: 3px solid #ef4444;
    }
    .sponsor-status.loading {
        color: #6b7280;
        background: rgba(107, 114, 128, 0.08);
        border-left: 3px solid #6b7280;
    }
    .sponsor-status .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #e5e7eb;
        border-top: 2px solid #6366f1;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        flex-shrink: 0;
    }
    
    /* Email Status Messages */
    .email-checking {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.813rem;
        margin-top: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: #f3f4f6;
        border-radius: 0.375rem;
        border-left: 3px solid #9ca3af;
    }
    .email-checking-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #e5e7eb;
        border-top: 2px solid #6366f1;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        flex-shrink: 0;
    }
    .email-status-success {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #16a34a;
        font-size: 0.813rem;
        margin-top: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: #f0fdf4;
        border-radius: 0.375rem;
        border-left: 3px solid #22c55e;
        animation: slideDown 0.3s ease;
    }
    .email-status-error {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #dc2626;
        font-size: 0.813rem;
        margin-top: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: #fef2f2;
        border-radius: 0.375rem;
        border-left: 3px solid #ef4444;
        animation: slideDown 0.3s ease;
    }
    .email-status-warning {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #d97706;
        font-size: 0.813rem;
        margin-top: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: #fffbeb;
        border-radius: 0.375rem;
        border-left: 3px solid #f59e0b;
        animation: slideDown 0.3s ease;
    }
    .email-status-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        font-size: 0.688rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .email-status-success .email-status-icon {
        background: #22c55e;
        color: #ffffff;
    }
    .email-status-error .email-status-icon {
        background: #ef4444;
        color: #ffffff;
    }
    .email-status-warning .email-status-icon {
        background: #f59e0b;
        color: #ffffff;
    }

    /* Toast Notification */
    .toast-modern {
        position: fixed;
        top: 24px;
        right: 24px;
        transform: translateX(calc(100% + 40px));
        padding: 1rem 1.25rem;
        border-radius: 0.625rem;
        background: #ffffff;
        color: #111827;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        border: 1px solid #e5e7eb;
        border-left: 4px solid #6366f1;
        z-index: 9999;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.875rem;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        min-width: 320px;
        max-width: 440px;
        width: calc(100% - 32px);
    }
    .toast-modern.show {
        transform: translateX(0);
        opacity: 1;
    }
    .toast-modern .toast-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .toast-modern .toast-icon.success {
        background: #dcfce7;
        color: #22c55e;
    }
    .toast-modern .toast-icon.error {
        background: #fee2e2;
        color: #ef4444;
    }
    .toast-modern .toast-icon.warning {
        background: #fef3c7;
        color: #f59e0b;
    }
    .toast-modern .toast-icon svg {
        width: 20px;
        height: 20px;
    }
    .toast-modern .toast-content {
        flex: 1;
        min-width: 0;
    }
    .toast-modern .toast-title {
        font-weight: 600;
        font-size: 0.875rem;
        color: #111827;
        margin-bottom: 2px;
        display: block;
    }
    .toast-modern .toast-message {
        font-size: 0.813rem;
        color: #6b7280;
        line-height: 1.4;
        display: block;
    }
    .toast-modern .toast-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0.25rem;
        font-size: 1.25rem;
        line-height: 1;
        transition: color 0.2s ease;
        flex-shrink: 0;
        margin-top: -2px;
    }
    .toast-modern .toast-close:hover {
        color: #374151;
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

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
        width: 100%;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
        .toast-modern {
            top: 16px;
            right: 16px;
            min-width: unset;
            max-width: calc(100% - 32px);
            width: calc(100% - 32px);
            padding: 0.75rem 1rem;
            font-size: 0.813rem;
        }
        .toast-modern .toast-icon {
            width: 28px;
            height: 28px;
        }
        .toast-modern .toast-icon svg {
            width: 16px;
            height: 16px;
        }
        .toast-modern .toast-title {
            font-size: 0.813rem;
        }
        .toast-modern .toast-message {
            font-size: 0.75rem;
        }
        .social-info-box { flex-direction: column; text-align: center; }
        .social-info-box .avatar-social { width: 48px; height: 48px; }
        .email-checking,
        .email-status-success,
        .email-status-error,
        .email-status-warning {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .sponsor-status {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
    }
    
    @media (max-width: 480px) {
        .auth-card { padding: 1.25rem; }
        .form-group .input { font-size: 0.75rem; padding: 0.5rem 0.75rem; }
        .toast-modern {
            top: 12px;
            right: 12px;
            padding: 0.625rem 0.875rem;
            max-width: calc(100% - 24px);
            width: calc(100% - 24px);
            border-radius: 0.5rem;
        }
        .toast-modern .toast-icon {
            width: 24px;
            height: 24px;
        }
        .toast-modern .toast-icon svg {
            width: 14px;
            height: 14px;
        }
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
                <p class="error-message">{{ $message }}</p>
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
                <p class="error-message">{{ $message }}</p>
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
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <!-- ✅ SPONSOR ID - CORRIGÉ AVEC VÉRIFICATION -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Code du Parrain <span class="required">*</span>
            </label>
            <input type="text" 
                   name="sponsor_id" 
                   id="sponsor_id"
                   value="{{ old('sponsor_id', session('social_data.sponsor_id', request()->query('ref', ''))) }}" 
                   class="input @error('sponsor_id') input-error @enderror"
                   placeholder="Ex: SALADMIN ou SALDEBF71"
                   required>
            <p class="form-hint">Entrez le code de parrain (ex: SALDEBF71) ou l'email de votre parrain</p>
            
            <!-- ✅ Sponsor Status -->
            <div id="sponsorStatus" class="sponsor-status"></div>
            
            @error('sponsor_id')
                <p class="error-message">{{ $message }}</p>
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
            <p class="password-strength-text" id="passwordStrengthText">
                Minimum 8 caractères
            </p>
            @error('password')
                <p class="error-message">{{ $message }}</p>
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
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-primary" id="submitBtn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Créer mon compte
        </button>
    </form>

    <div class="auth-divider">ou</div>

    <!-- Social Login - Google -->
    <div class="space-y-2">
        <button type="button" class="social-btn social-btn-google" id="googleBtn">
            <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continuer avec Google
        </button>
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
    <div class="toast-icon success" id="toastIcon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </div>
    <div class="toast-content">
        <span class="toast-title" id="toastTitle">Succès</span>
        <span class="toast-message" id="toastMessage">Message</span>
    </div>
    <button type="button" class="toast-close" onclick="hideToast()" aria-label="Fermer">×</button>
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

function showToast(data) {
    const toast = document.getElementById('toastModern');
    const icon = document.getElementById('toastIcon');
    const titleEl = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');
    
    if (!toast) return;
    
    icon.className = 'toast-icon';
    icon.classList.add(data.type || 'success');
    
    switch(data.type) {
        case 'success':
            icon.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            `;
            break;
        case 'error':
            icon.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            `;
            break;
        case 'warning':
            icon.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                    <path d="M12 3a9 9 0 100 18 9 9 0 000-18z"></path>
                </svg>
            `;
            break;
    }
    
    titleEl.textContent = data.title || 'Notification';
    messageEl.textContent = data.detail || data.message || '';
    
    const colors = {
        success: '#22c55e',
        error: '#ef4444',
        warning: '#f59e0b'
    };
    toast.style.borderLeftColor = colors[data.type] || '#6366f1';
    toast.style.borderLeftWidth = '4px';
    toast.style.borderLeftStyle = 'solid';
    
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

// ✅ VÉRIFICATION DU SPONSOR EN TEMPS RÉEL - CORRIGÉ
document.addEventListener('DOMContentLoaded', function() {
    const sponsorInput = document.getElementById('sponsor_id');
    const sponsorStatus = document.getElementById('sponsorStatus');
    let sponsorTimeout = null;

    if (sponsorInput && sponsorStatus) {
        sponsorInput.addEventListener('input', function() {
            const value = this.value.trim();
            
            // Réinitialiser
            sponsorStatus.className = 'sponsor-status';
            sponsorStatus.textContent = '';
            this.classList.remove('input-success', 'input-error');
            
            if (value.length < 3) {
                return;
            }
            
            // Afficher le chargement
            sponsorStatus.className = 'sponsor-status visible loading';
            sponsorStatus.innerHTML = '<span class="spinner"></span> Vérification du parrain...';
            
            clearTimeout(sponsorTimeout);
            sponsorTimeout = setTimeout(function() {
                fetch('/check-sponsor?sponsor_id=' + encodeURIComponent(value))
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            sponsorStatus.className = 'sponsor-status visible success';
                            sponsorStatus.innerHTML = '✅ Parrain trouvé: <strong>' + data.name + '</strong> (' + data.email + ')';
                            sponsorInput.classList.add('input-success');
                            sponsorInput.classList.remove('input-error');
                        } else {
                            sponsorStatus.className = 'sponsor-status visible error';
                            sponsorStatus.innerHTML = '❌ ' + data.message;
                            sponsorInput.classList.add('input-error');
                            sponsorInput.classList.remove('input-success');
                        }
                    })
                    .catch(() => {
                        sponsorStatus.className = 'sponsor-status';
                        sponsorStatus.textContent = '';
                    });
            }, 500);
        });
    }
});

// Validation du formulaire
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const sponsorInput = document.getElementById('sponsor_id');
    const sponsorStatus = document.getElementById('sponsorStatus');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const termsCheckbox = document.getElementById('terms');
    const submitBtn = document.getElementById('submitBtn');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    const emailAvailability = document.getElementById('emailAvailability');

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            showToast({
                type: 'error',
                title: 'Erreur',
                message: '{{ $error }}'
            });
        @break
        @endforeach
    @endif

    @if (session('success'))
        showToast({
            type: 'success',
            title: 'Succès',
            message: '{{ session('success') }}'
        });
    @endif

    @if (session('error'))
        showToast({
            type: 'error',
            title: 'Erreur',
            message: '{{ session('error') }}'
        });
    @endif

    // ✅ Vérification de l'email
    let emailTimeout = null;
    let emailChecked = false;

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            emailChecked = false;
            this.classList.remove('input-error', 'input-success');
            
            if (emailAvailability) {
                emailAvailability.innerHTML = '';
            }

            if (isValidEmail(email)) {
                emailAvailability.innerHTML = `
                    <div class="email-checking">
                        <span class="email-checking-spinner"></span>
                        Vérification en cours...
                    </div>
                `;
                
                clearTimeout(emailTimeout);
                emailTimeout = setTimeout(function() {
                    fetch(`/check-email?email=${encodeURIComponent(email)}`)
                        .then(response => response.json())
                        .then(data => {
                            emailChecked = true;
                            
                            if (data.field_status === 'error') {
                                emailInput.classList.add('input-error');
                                emailInput.classList.remove('input-success');
                                
                                if (emailAvailability) {
                                    emailAvailability.innerHTML = `
                                        <div class="email-status-error">
                                            <span class="email-status-icon">✕</span>
                                            ${data.message}
                                        </div>
                                    `;
                                }
                            } else if (data.field_status === 'success') {
                                emailInput.classList.remove('input-error');
                                emailInput.classList.add('input-success');
                                
                                if (emailAvailability) {
                                    emailAvailability.innerHTML = `
                                        <div class="email-status-success">
                                            <span class="email-status-icon">✓</span>
                                            ${data.message}
                                        </div>
                                    `;
                                }
                            } else {
                                emailInput.classList.add('input-error');
                                
                                if (emailAvailability) {
                                    emailAvailability.innerHTML = `
                                        <div class="email-status-warning">
                                            <span class="email-status-icon">!</span>
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

    // ✅ Validation du formulaire - CORRIGÉE
    form.addEventListener('submit', function(e) {
        let hasError = false;
        let errorMessage = '';

        const name = nameInput.value.trim();
        if (!name) {
            errorMessage = 'Veuillez saisir votre nom complet.';
            hasError = true;
        }

        const email = emailInput.value.trim();
        if (!hasError && !email) {
            errorMessage = 'Veuillez saisir votre adresse email.';
            hasError = true;
        } else if (!hasError && !isValidEmail(email)) {
            errorMessage = 'Veuillez saisir une adresse email valide.';
            hasError = true;
        } else if (!hasError && emailChecked === false && email.length > 0) {
            errorMessage = 'Veuillez attendre la vérification de votre email.';
            hasError = true;
        } else if (!hasError && emailInput.classList.contains('input-error')) {
            errorMessage = 'Cet email est déjà utilisé. Veuillez en utiliser un autre.';
            hasError = true;
        }

        const sponsor = sponsorInput.value.trim();
        if (!hasError && !sponsor) {
            errorMessage = 'Veuillez saisir le code de votre parrain.';
            hasError = true;
        } else if (!hasError && sponsor.length < 3) {
            errorMessage = 'Le code du parrain doit contenir au moins 3 caractères.';
            hasError = true;
        } else if (!hasError && sponsorInput.classList.contains('input-error')) {
            errorMessage = 'Le code du parrain est invalide. Veuillez vérifier.';
            hasError = true;
        } else if (!hasError && sponsorStatus.classList.contains('loading')) {
            errorMessage = 'Veuillez attendre la vérification du parrain.';
            hasError = true;
        }

        const password = passwordInput.value;
        if (!hasError && !password) {
            errorMessage = 'Veuillez saisir un mot de passe.';
            hasError = true;
        } else if (!hasError && password.length < 8) {
            errorMessage = 'Le mot de passe doit contenir au moins 8 caractères.';
            hasError = true;
        }

        const confirm = confirmInput.value;
        if (!hasError && confirm !== password) {
            errorMessage = 'Les mots de passe ne correspondent pas.';
            hasError = true;
        }

        if (!hasError && !termsCheckbox.checked) {
            errorMessage = 'Vous devez accepter les conditions générales.';
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            showToast({
                type: 'error',
                title: 'Erreur de validation',
                message: errorMessage
            });
            
            // Scroll vers le premier champ en erreur
            const firstError = form.querySelector('.input-error, .form-group .error-message');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // ✅ Gestion du bouton Google - CORRIGÉE
    document.getElementById('googleBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        const sponsorId = document.getElementById('sponsor_id').value.trim();
        const sponsorStatus = document.getElementById('sponsorStatus');
        
        if (!sponsorId) {
            showToast({
                type: 'error',
                title: 'Code du parrain requis',
                message: 'Veuillez entrer un code de parrain avant de continuer.'
            });
            document.getElementById('sponsor_id').focus();
            document.getElementById('sponsor_id').classList.add('input-error');
            return;
        }

        if (document.getElementById('sponsor_id').classList.contains('input-error')) {
            showToast({
                type: 'error',
                title: 'Parrain invalide',
                message: 'Le code du parrain est invalide. Veuillez vérifier.'
            });
            document.getElementById('sponsor_id').focus();
            return;
        }

        if (sponsorStatus.classList.contains('loading')) {
            showToast({
                type: 'warning',
                title: 'Vérification en cours',
                message: 'Veuillez attendre la fin de la vérification du parrain.'
            });
            return;
        }

        // Stocker le sponsor en session avant la redirection
        fetch('{{ route('social.store-sponsor') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ sponsor_id: sponsorId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('social.redirect', 'google') }}';
            } else {
                showToast({
                    type: 'error',
                    title: 'Erreur',
                    message: data.message || 'Erreur lors de la validation du parrain.'
                });
                document.getElementById('sponsor_id').classList.add('input-error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast({
                type: 'error',
                title: 'Erreur',
                message: 'Erreur lors de la validation du parrain. Veuillez réessayer.'
            });
        });
    });
});

// Gestion des erreurs de validation (quand le formulaire est soumis avec erreur)
document.addEventListener('DOMContentLoaded', function() {
    // Si des erreurs de validation existent, afficher une notification
    if (document.querySelector('.error-message')) {
        const firstError = document.querySelector('.error-message');
        if (firstError) {
            showToast({
                type: 'error',
                title: 'Erreur de validation',
                message: firstError.textContent.trim()
            });
        }
    }
});
</script>
@endpush
@endsection