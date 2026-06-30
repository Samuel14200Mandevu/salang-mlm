@extends('layouts.auth')

@section('title', 'Vérification email - Salang MLM')

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
        font-size: 3.5rem;
        margin-bottom: 1rem;
        display: block;
    }
    .success-box {
        padding: 1rem;
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.2);
        border-radius: var(--radius-md);
        text-align: center;
        color: #22c55e;
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
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

    <span class="icon-big animate-float">📧</span>
    <h2 class="auth-title">Vérifiez votre email</h2>
    <p class="auth-subtitle">
        Un lien de vérification a été envoyé à votre adresse email.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="success-box animate-fadeIn">
            ✅ Un nouveau lien de vérification a été envoyé à votre email.
        </div>
    @endif

    <div class="p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)] mb-6">
        <p class="text-sm text-[var(--text-secondary)] text-center">
            Avant de continuer, veuillez vérifier votre email pour le lien de confirmation.
            <br>
            <span class="text-xs text-[var(--text-tertiary)]">(Si vous ne l'avez pas reçu, cliquez sur le bouton ci-dessous)</span>
        </p>
    </div>

    <div class="flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Renvoyer le lien de vérification
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline w-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Se déconnecter
            </button>
        </form>
    </div>
</div>
@endsection