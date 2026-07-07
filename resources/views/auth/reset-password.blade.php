@extends('layouts.auth')

@section('title', 'Réinitialisation - Salang MLM')

@push('styles')
<style>
    .auth-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-lg);
        animation: fadeInUp 0.6s ease forwards;
        max-width: 440px;
        width: 100%;
        margin: 0 auto;
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
    .icon-big {
        text-align: center;
        margin-bottom: 1rem;
        display: block;
    }
    .icon-big svg {
        width: 4rem;
        height: 4rem;
        color: var(--primary-500);
        margin: 0 auto;
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
    
    .form-group {
        margin-bottom: 1rem;
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
    
    @media (max-width: 640px) {
        .auth-card { padding: 1.5rem; max-width: 100%; }
        .auth-logo img { height: 50px; }
        .auth-logo .brand-name { font-size: 1.25rem; }
        .auth-title { font-size: 1.25rem; }
        .auth-subtitle { font-size: 0.813rem; }
        .form-group label { font-size: 0.813rem; }
        .form-group .input { font-size: 0.813rem; padding: 0.5rem 0.875rem; }
        .icon-big svg { width: 3rem; height: 3rem; }
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

    <div class="icon-big animate-float">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>
    
    <h2 class="auth-title">Réinitialiser le mot de passe</h2>
    <p class="auth-subtitle">Créez un nouveau mot de passe sécurisé</p>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" id="resetForm">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Adresse email
            </label>
            <input type="email" 
                   name="email" 
                   id="email"
                   value="{{ old('email', $request->email) }}" 
                   class="input @error('email') input-error @enderror"
                   required 
                   autofocus>
            @error('email')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Nouveau mot de passe
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

        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Confirmer
            </label>
            <input type="password" 
                   name="password_confirmation" 
                   id="password_confirmation"
                   class="input"
                   placeholder="Confirmez votre mot de passe"
                   required>
        </div>

        <button type="submit" class="btn btn-primary w-full">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Réinitialiser
        </button>
    </form>
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

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetForm');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const emailInput = document.getElementById('email');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            showToast('{{ $error }}', 'error');
        @break
        @endforeach
    @endif

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

        const email = emailInput.value.trim();
        if (!email) {
            errorMessage = 'Veuillez saisir votre adresse email.';
            hasError = true;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errorMessage = 'Veuillez saisir une adresse email valide.';
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

        if (hasError) {
            e.preventDefault();
            showToast(errorMessage, 'error');
        }
    });
});
</script>
@endpush
@endsection