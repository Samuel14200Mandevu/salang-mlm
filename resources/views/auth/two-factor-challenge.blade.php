@extends('layouts.auth')

@section('title', '2FA - Salang MLM')

@push('styles')
<style>
    .auth-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-lg);
        animation: fadeInUp 0.6s ease forwards;
    }
    .auth-logo {
        text-align: center;
        margin-bottom: 1.25rem;
    }
    .auth-logo img {
        height: 50px;
        width: auto;
        margin: 0 auto;
    }
    .auth-logo .brand-name {
        font-size: 1.25rem;
        font-weight: 800;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }
    .auth-title {
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .auth-subtitle {
        text-align: center;
        color: var(--text-secondary);
        font-size: 0.813rem;
        margin-bottom: 1.25rem;
        padding: 0 0.5rem;
    }
    .icon-big {
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        display: block;
    }
    .code-inputs {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        margin: 1.25rem 0;
    }
    .code-input {
        width: 2.75rem;
        height: 3.25rem;
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all var(--transition-base);
        outline: none;
    }
    .code-input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px rgba(90, 182, 56, 0.12);
    }
    .code-input::-webkit-inner-spin-button,
    .code-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .code-input[type=number] {
        -moz-appearance: textfield;
    }
    
    @media (min-width: 640px) {
        .auth-card { padding: 2rem; }
        .auth-logo img { height: 60px; }
        .auth-logo .brand-name { font-size: 1.5rem; }
        .auth-title { font-size: 1.5rem; }
        .auth-subtitle { font-size: 0.875rem; }
        .icon-big { font-size: 3.5rem; }
        .code-inputs { gap: 0.75rem; }
        .code-input { width: 3.5rem; height: 4rem; font-size: 1.5rem; }
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
        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>
    
    <h2 class="auth-title">Authentification a deux facteurs</h2>
    <p class="auth-subtitle">
        Entrez le code de verification de votre application d'authentification.
    </p>

    @if ($errors->any())
        <div class="mb-4 p-2.5 sm:p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-xs sm:text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login') }}">
        @csrf

        <div class="code-inputs">
            <input type="number" 
                   name="code" 
                   class="code-input" 
                   placeholder="&#8226;"
                   maxlength="1"
                   pattern="[0-9]"
                   inputmode="numeric"
                   required>
        </div>
        @error('code')
            <p class="text-xs text-red-500 text-center mt-1">{{ $message }}</p>
        @enderror

        <div class="text-center mb-4 sm:mb-6">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)] leading-relaxed">
                Vous avez perdu l'acces a votre application ?
                <a href="#" id="showRecovery" class="text-primary-500 hover:text-primary-600 font-medium transition cursor-pointer">
                    Utiliser un code de recuperation
                </a>
            </p>
        </div>

        <div id="recoverySection" style="display: none;">
            <div class="mb-4 sm:mb-6">
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1.5">
                    Code de recuperation
                </label>
                <input type="text" 
                       name="recovery_code" 
                       class="input text-sm sm:text-base"
                       placeholder="xxxx-xxxx-xxxx-xxxx">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full text-sm sm:text-base py-2.5 sm:py-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Verifier
        </button>
    </form>

    <p class="text-center text-xs sm:text-sm text-[var(--text-secondary)] mt-4 sm:mt-6">
        <a href="{{ route('logout') }}" class="text-primary-500 hover:text-primary-600 font-medium transition">
            &larr; Retour a la connexion
        </a>
    </p>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const firstInput = document.querySelector('.code-input');
    if (firstInput) firstInput.focus();

    const inputs = document.querySelectorAll('.code-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            if (this.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    });

    const showRecovery = document.getElementById('showRecovery');
    const recoverySection = document.getElementById('recoverySection');
    const codeInputs = document.querySelector('.code-inputs');

    if (showRecovery) {
        showRecovery.addEventListener('click', function(e) {
            e.preventDefault();
            if (recoverySection.style.display === 'none') {
                recoverySection.style.display = 'block';
                codeInputs.style.display = 'none';
                this.textContent = 'Retour au code de verification';
            } else {
                recoverySection.style.display = 'none';
                codeInputs.style.display = 'flex';
                this.textContent = 'Utiliser un code de recuperation';
            }
        });
    }

    document.addEventListener('paste', function(e) {
        const pasteData = e.clipboardData.getData('text');
        if (pasteData && /^[0-9]{6}$/.test(pasteData.trim())) {
            const digits = pasteData.trim().split('');
            inputs.forEach((input, index) => {
                if (index < digits.length) {
                    input.value = digits[index];
                }
            });
            if (digits.length === inputs.length) {
                inputs[inputs.length - 1].form.submit();
            }
        }
    });
});
</script>
@endpush
@endsection