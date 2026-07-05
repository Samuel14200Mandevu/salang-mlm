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
    }
    .social-btn:hover {
        background: var(--bg-hover);
        border-color: var(--primary);
        transform: translateY(-1px);
    }
    .social-btn svg {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
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
    
    @media (max-width: 640px) {
        .auth-card { padding: 1.5rem; }
        .auth-logo img { height: 50px; }
        .auth-logo .brand-name { font-size: 1.25rem; }
        .auth-title { font-size: 1.25rem; }
        .form-group label { font-size: 0.813rem; }
        .form-group .input { font-size: 0.813rem; padding: 0.5rem 0.875rem; }
        .social-btn { font-size: 0.813rem; padding: 0.5rem 0.75rem; }
        .social-btn svg { width: 1.125rem; height: 1.125rem; }
    }
    
    @media (max-width: 480px) {
        .auth-card { padding: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="auth-card">
    <!-- Logo -->
    <div class="auth-logo">
        <div class="logo-light">
            <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang MLM">
        </div>
        <div class="logo-dark">
            <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang MLM">
        </div>
        <span class="brand-name">Salang MLM</span>
    </div>

    <h2 class="auth-title">Bienvenue</h2>
    <p class="auth-subtitle">Connectez-vous a votre compte</p>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email
            </label>
            <input type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   class="input @error('email') input-error @enderror"
                   placeholder="Entrez votre email"
                   required 
                   autofocus>
            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
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
                       class="input @error('password') input-error @enderror"
                       placeholder="Entrez votre mot de passe"
                       required>
                <button type="button" 
                        class="password-toggle"
                        onclick="togglePassword(this)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Sponsor ID - Obligatoire pour connexion sociale -->
        <div class="form-group">
            <label>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                ID du parrain (obligatoire pour connexion sociale)
            </label>
            <input type="text" 
                   id="sponsor_id"
                   name="sponsor_id"
                   class="input"
                   placeholder="Entrez l'ID de votre parrain"
                   required>
            <p class="form-hint">Entrez l'ID de la personne qui vous a invite</p>
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
                    Mot de passe oublie ?
                </a>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-full">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Se connecter
        </button>
    </form>

    <div class="auth-divider">ou</div>

    <!-- Social Login -->
    <div class="space-y-2">
        <a href="{{ route('social.redirect', 'google') }}" class="social-btn">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continuer avec Google
        </a>

        <a href="{{ route('social.redirect', 'facebook') }}" class="social-btn">
            <svg viewBox="0 0 24 24" fill="#1877F2">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Continuer avec Facebook
        </a>
    </div>

    <p class="text-center text-sm text-[var(--text-secondary)] mt-6">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
            Creer un compte
        </a>
    </p>
    <!-- Lien retour accueil -->
    <div class="mb-4">
        <p class="text-center text-sm text-[var(--text-secondary)] mt-6">
        <a href="{{ route('home') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
            &larr; Retour a l'accueil
        </a>
    </p>    
    </div>
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
    // Vérifier la présence du champ sponsor_id
    var sponsorInput = document.getElementById('sponsor_id');
    if (!sponsorInput) return;

    // Intercepter les clics sur les boutons sociaux
    document.querySelectorAll('.social-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var sponsorId = sponsorInput.value.trim();
            
            if (!sponsorId) {
                e.preventDefault();
                alert('Veuillez entrer un ID de parrain.');
                sponsorInput.focus();
                return false;
            }

            // Vérifier l'ID du parrain avant de continuer
            fetch('{{ route("social.store-sponsor") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sponsor_id: sponsorId })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    e.preventDefault();
                    alert(data.message || 'ID de parrain invalide.');
                    sponsorInput.focus();
                    return false;
                }
            })
            .catch(function() {
                e.preventDefault();
                alert('Erreur lors de la verification du parrain.');
                return false;
            });
        });
    });
});
</script>
@endpush
@endsection