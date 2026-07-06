@extends('layouts.auth')

@section('title', 'Mot de passe oublié - Salang MLM')

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
        padding: 0 1rem;
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
    
    .success-box {
        padding: 0.75rem 1rem;
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.2);
        border-radius: var(--radius-md);
        color: #22c55e;
        font-size: 0.875rem;
        margin-bottom: 1.25rem;
        text-align: center;
    }
    
    @media (max-width: 640px) {
        .auth-card { padding: 1.5rem; max-width: 100%; }
        .auth-logo img { height: 50px; }
        .auth-logo .brand-name { font-size: 1.25rem; }
        .auth-title { font-size: 1.25rem; }
        .auth-subtitle { font-size: 0.813rem; padding: 0; }
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
        <div class="logo-light">
            <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang MLM">
        </div>
        <div class="logo-dark">
            <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang MLM">
        </div>
        <span class="brand-name">Salang MLM</span>
    </div>

    <div class="icon-big animate-float">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
    </div>
    
    <h2 class="auth-title">Mot de passe oublié</h2>
    <p class="auth-subtitle">
        Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
    </p>

    @if (session('status'))
        <div class="success-box animate-fadeIn">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

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

        <button type="submit" class="btn btn-primary w-full">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Envoyer le lien
        </button>
    </form>

    <p class="text-center text-sm text-[var(--text-secondary)] mt-6">
        <a href="{{ route('login') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
            ← Retour à la connexion
        </a>
    </p>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const emailInput = document.getElementById('email');

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            showToast('{{ $error }}', 'error');
        @break
        @endforeach
    @endif

    @if (session('status'))
        showToast('{{ session('status') }}', 'success');
    @endif

    @if (session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif

    form.addEventListener('submit', function(e) {
        const email = emailInput.value.trim();
        
        if (!email) {
            e.preventDefault();
            showToast('Veuillez saisir votre adresse email.', 'error');
            emailInput.focus();
            emailInput.classList.add('input-error');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            e.preventDefault();
            showToast('Veuillez saisir une adresse email valide.', 'error');
            emailInput.focus();
            emailInput.classList.add('input-error');
        }
    });

    emailInput.addEventListener('input', function() {
        this.classList.remove('input-error');
    });
});
</script>
@endpush
@endsection