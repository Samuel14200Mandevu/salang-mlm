@extends('layouts.auth')

@section('title', 'Connexion - Salang MLM')

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
        max-width: 420px;
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
        height: 200px; /* Taille agrandie */
        width: auto;
        margin: 0 auto;
        display: block;
    }
    .auth-logo .brand-name {
        font-size: 1.75rem;
        font-weight: 800;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
        margin-top: 0.5rem;
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
    
    .form-group {
        margin-bottom: 1.25rem;
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
    
    /* ===== MODERN TOAST ===== */
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
    
    /* ===== SPONSOR FIELD ===== */
    .sponsor-field {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        margin-bottom: 1.25rem;
    }
    .sponsor-field .sponsor-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
    }
    .sponsor-field .sponsor-value {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    
    /* ===== RESPONSIVE ===== */
    @media (max-width: 640px) {
        .auth-card { padding: 1.5rem; max-width: 100%; }
        .auth-logo img { 
            height: 90px; /* Taille adaptée pour mobile */
        }
        .auth-logo .brand-name { 
            font-size: 1.5rem;
        }
        .auth-title { font-size: 1.25rem; }
        .form-group label { font-size: 0.813rem; }
        .form-group .input { font-size: 0.813rem; padding: 0.5rem 0.875rem; }
        .toast-modern { min-width: auto; max-width: 90vw; padding: 0.75rem 1rem; font-size: 0.813rem; }
        .toast-modern .toast-icon { width: 28px; height: 28px; font-size: 0.875rem; }
    }
    
    @media (max-width: 480px) {
        .auth-card { padding: 1.25rem; }
        .auth-logo img { 
            height: 80px; /* Taille adaptée pour très petits écrans */
        }
        .auth-logo .brand-name {
            font-size: 1.25rem;
        }
        .form-group .input { font-size: 0.75rem; padding: 0.5rem 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="auth-card">
    
    <!-- Logo - Version agrandie -->
    <div class="auth-logo">
        <a href="/" class="block">
            <img src="{{ asset('images/salang_logo.png') }}" 
                 alt="Salang" 
                 class="mx-auto transition-transform hover:scale-105">
            <span class="brand-name">
                Salang Group
            </span>
        </a>
    </div>

    <h2 class="auth-title">Bienvenue</h2>
    <p class="auth-subtitle">Connectez-vous à votre compte</p>

    <!-- Messages de session -->
    @if (session('status'))
        <div class="mb-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <!-- Email -->
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
                   value="{{ old('email') }}" 
                   class="input @error('email') input-error @enderror"
                   placeholder="exemple@email.com"
                   required 
                   autofocus
                   autocomplete="email">
            @error('email')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Mot de passe
            </label>
            <div class="password-wrapper">
                <input type="password" 
                       name="password" 
                       id="password"
                       class="input @error('password') input-error @enderror"
                       placeholder="Entrez votre mot de passe"
                       required
                       autocomplete="current-password">
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
            @error('password')
                <p class="error-message">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Remember & Forgot -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-6">
            <label class="flex items-center gap-2 text-sm text-[var(--text-secondary)] cursor-pointer">
                <input type="checkbox" 
                       name="remember" 
                       class="w-4 h-4 rounded border-[var(--border-color)] text-primary-500 focus:ring-primary-500 focus:ring-offset-0">
                Se souvenir de moi
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" 
                   class="text-sm text-primary-500 hover:text-primary-600 font-medium transition">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-full" id="submitBtn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Se connecter
        </button>
    </form>

    <p class="text-center text-sm text-[var(--text-secondary)] mt-6">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
            Créer un compte
        </a>
    </p>
    
    <p class="text-center text-sm text-[var(--text-secondary)] mt-2">
        <a href="{{ route('home') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
            ← Retour à l'accueil
        </a>
    </p>
</div>

<!-- Modern Toast -->
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
/**
 * Basculer l'affichage du mot de passe
 */
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

/**
 * Afficher un toast de notification
 */
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

/**
 * Masquer le toast
 */
function hideToast() {
    document.getElementById('toastModern').classList.remove('show');
}

/**
 * Validation du formulaire avant soumission
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');

    // Afficher les erreurs de validation Laravel
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

    // Validation côté client avant soumission
    form.addEventListener('submit', function(e) {
        let hasError = false;
        let errorMessage = '';

        // Vérifier l'email
        const email = emailInput.value.trim();
        if (!email) {
            errorMessage = 'Veuillez saisir votre adresse email.';
            hasError = true;
        } else if (!isValidEmail(email)) {
            errorMessage = 'Veuillez saisir une adresse email valide.';
            hasError = true;
        }

        // Vérifier le mot de passe
        const password = passwordInput.value.trim();
        if (!hasError && !password) {
            errorMessage = 'Veuillez saisir votre mot de passe.';
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            showToast(errorMessage, 'error');
        }
    });

    /**
     * Valider le format d'un email
     */
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});
</script>
@endpush
@endsection