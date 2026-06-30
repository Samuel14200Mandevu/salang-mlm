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

    <span class="icon-big animate-float">🔑</span>
    <h2 class="auth-title">Mot de passe oublié</h2>
    <p class="auth-subtitle">
        Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
    </p>

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

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-6">
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
                   required 
                   autofocus>
            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
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
@endsection