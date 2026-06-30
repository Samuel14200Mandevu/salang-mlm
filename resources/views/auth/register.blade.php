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

    <h2 class="auth-title">Créer un compte</h2>
    <p class="auth-subtitle">Rejoignez la communauté Salang</p>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nom -->
        <div class="mb-3">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Nom complet
            </label>
            <input type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   class="input @error('name') input-error @enderror"
                   placeholder="Entrez votre nom complet"
                   required 
                   autofocus>
            @error('name')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email
            </label>
            <input type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   class="input @error('email') input-error @enderror"
                   placeholder="Entrez votre email"
                   required>
            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Téléphone -->
        <div class="mb-3">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                Téléphone
            </label>
            <input type="tel" 
                   name="phone" 
                   value="{{ old('phone') }}" 
                   class="input @error('phone') input-error @enderror"
                   placeholder="Entrez votre numéro de téléphone"
                   required>
            @error('phone')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Sponsor ID -->
        <div class="mb-3">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                ID du parrain (optionnel)
            </label>
            <input type="text" 
                   name="sponsor_id" 
                   value="{{ request()->query('ref') ?? old('sponsor_id') }}" 
                   class="input @error('sponsor_id') input-error @enderror"
                   placeholder="Entrez l'ID du parrain">
            <p class="text-xs text-[var(--text-tertiary)] mt-1">
                Entrez l'ID de la personne qui vous a invité
            </p>
            @error('sponsor_id')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Mot de passe
            </label>
            <div class="relative">
                <input type="password" 
                       id="password"
                       name="password" 
                       class="input @error('password') input-error @enderror"
                       placeholder="•••••••• (8 caractères min)"
                       required
                       minlength="8">
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
            <p class="password-strength-text text-[var(--text-tertiary)]" id="passwordStrengthText">
                Minimum 8 caractères
            </p>
            @error('password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Confirmer le mot de passe
            </label>
            <input type="password" 
                   name="password_confirmation" 
                   class="input"
                   placeholder="••••••••"
                   required>
        </div>

        <!-- Terms -->
        <div class="mb-6">
            <label class="flex items-start gap-2 text-sm text-[var(--text-secondary)] cursor-pointer">
                <input type="checkbox" 
                       name="terms" 
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
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-full">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Créer mon compte
        </button>
    </form>

    <p class="text-center text-sm text-[var(--text-secondary)] mt-6">
        Déjà un compte ?
        <a href="{{ route('login') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
            Se connecter
        </a>
    </p>
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
    const strengthText = document.getElementById('passwordStrengthText');

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
});
</script>
@endpush
@endsection