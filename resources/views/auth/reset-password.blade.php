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
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
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

    <span class="icon-big animate-float">🔐</span>
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

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email
            </label>
            <input type="email" 
                   name="email" 
                   value="{{ old('email', $request->email) }}" 
                   class="input @error('email') input-error @enderror"
                   required 
                   autofocus>
            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Nouveau mot de passe
            </label>
            <div class="relative">
                <input type="password" 
                       id="password"
                       name="password" 
                       class="input @error('password') input-error @enderror"
                       placeholder="••••••••"
                       required>
                <button type="button" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)] hover:text-[var(--text-primary)] transition"
                        onclick="togglePassword(this)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            <div class="password-strength">
                <div class="password-strength-bar" id="passwordStrength"></div>
            </div>
            @error('password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Confirmer
            </label>
            <input type="password" 
                   name="password_confirmation" 
                   class="input"
                   placeholder="••••••••"
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
    const input = btn.closest('.relative').querySelector('input');
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
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('passwordStrength');

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;

            if (value.length >= 8) strength += 25;
            if (/[a-z]/.test(value)) strength += 25;
            if (/[A-Z]/.test(value)) strength += 25;
            if (/[0-9]/.test(value)) strength += 25;

            strengthBar.style.width = strength + '%';

            if (strength <= 25) {
                strengthBar.style.background = '#ef4444';
            } else if (strength <= 50) {
                strengthBar.style.background = '#f59e0b';
            } else if (strength <= 75) {
                strengthBar.style.background = '#3b82f6';
            } else {
                strengthBar.style.background = '#22c55e';
            }
        });
    }
});
</script>
@endpush
@endsection