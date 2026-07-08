{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Connexion - Salang MLM')

@push('styles')
<style>
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
    }
    .toast-modern .toast-icon.error {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .toast-modern .toast-icon.success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .toast-modern .toast-icon svg {
        width: 20px;
        height: 20px;
    }
    .toast-modern .toast-close {
        background: none;
        border: none;
        color: var(--text-tertiary);
        cursor: pointer;
        padding: 0.25rem;
        transition: color 0.2s ease;
        margin-left: auto;
        font-size: 1.25rem;
        line-height: 1;
    }
    .toast-modern .toast-close:hover {
        color: var(--text-primary);
    }

    @media (max-width: 640px) {
        .toast-modern {
            min-width: auto;
            max-width: 90vw;
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
    }
</style>
@endpush

@section('content')
    <div class="auth-logo">
        <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang MLM" class="h-12 sm:h-16 w-auto mx-auto">
        <span class="brand-name block mt-2">Salang Group</span>
    </div>

    <h2 class="auth-title">Bienvenue</h2>
    <p class="auth-subtitle">Connectez-vous à votre compte</p>

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

        <div class="form-group">
            <label for="email">Adresse email <span class="required">*</span></label>
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
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Mot de passe <span class="required">*</span></label>
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
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-6">
            <label class="flex items-center gap-2 text-sm text-[var(--text-secondary)] cursor-pointer">
                <input type="checkbox" 
                       name="remember" 
                       class="w-4 h-4 rounded border-[var(--border-color)] text-[#0E2F76] focus:ring-[#0E2F76] focus:ring-offset-0">
                Se souvenir de moi
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary" id="submitBtn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Se connecter
        </button>
    </form>

    <div class="auth-divider">ou</div>

    <a href="{{ route('social.redirect', 'google') }}" class="social-btn" id="googleBtn">
        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Continuer avec Google
    </a>

    <p class="text-center text-sm text-[var(--text-secondary)] mt-6">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="auth-link">
            Créer un compte
        </a>
    </p>

    <!-- Toast -->
    <div id="toastModern" class="toast-modern" role="alert">
        <div class="toast-icon error" id="toastIcon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <span id="toastMessage">Message</span>
        <button type="button" class="toast-close" onclick="hideToast()">×</button>
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

function showToast(message, type) {
    type = type || 'error';
    const toast = document.getElementById('toastModern');
    const icon = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');
    
    icon.className = 'toast-icon ' + type;
    
    if (type === 'error') {
        icon.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    } else {
        icon.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    }
    
    messageEl.textContent = message;
    toast.classList.add('show');
    
    clearTimeout(window.toastTimeout);
    window.toastTimeout = setTimeout(hideToast, 5000);
}

function hideToast() {
    document.getElementById('toastModern').classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function() {
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

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        
        if (!email) {
            e.preventDefault();
            showToast('Veuillez saisir votre adresse email.', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            e.preventDefault();
            showToast('Veuillez saisir une adresse email valide.', 'error');
            return;
        }
        
        if (!password) {
            e.preventDefault();
            showToast('Veuillez saisir votre mot de passe.', 'error');
            return;
        }
    });
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});
</script>
@endpush
@endsection