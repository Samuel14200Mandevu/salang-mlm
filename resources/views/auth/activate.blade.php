{{-- resources/views/auth/activate.blade.php --}}

@extends('layouts.app')

@push('styles')
<style>
    /* ===== ANIMATIONS ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .animate-fadeInScale { animation: fadeInScale 0.5s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    
    /* ===== CARD PRINCIPAL ===== */
    .activate-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 2rem;
        transition: all var(--transition-base);
        position: relative;
        overflow: hidden;
        max-width: 500px;
        margin: 0 auto;
    }
    .activate-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-500), var(--primary-600), var(--primary-700), var(--primary-500));
        background-size: 300% 100%;
        animation: shimmer 3s linear infinite;
    }
    .activate-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    
    /* ===== TITRE ===== */
    .activate-title {
        font-size: 1.5rem;
        font-weight: 800;
        text-align: center;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .activate-title .highlight {
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .activate-subtitle {
        text-align: center;
        color: var(--text-secondary);
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
    }
    
    /* ===== BANNIERE ===== */
    .banner-info {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: var(--radius-md);
        padding: 1rem;
        margin-bottom: 1.5rem;
        transition: all var(--transition-base);
    }
    .banner-info:hover {
        border-color: rgba(245, 158, 11, 0.4);
        transform: translateX(4px);
    }
    .banner-info .banner-icon {
        font-size: 1.25rem;
        margin-right: 0.5rem;
        line-height: 1;
        color: #f59e0b;
    }
    .banner-info p {
        color: var(--text-primary);
        font-size: 0.875rem;
        line-height: 1.6;
    }
    .banner-info ul {
        list-style: none;
        padding: 0;
        margin: 0.25rem 0 0 0;
    }
    .banner-info ul li {
        padding: 0.125rem 0 0.125rem 1.25rem;
        position: relative;
        font-size: 0.813rem;
        color: var(--text-secondary);
    }
    .banner-info ul li::before {
        content: '▸';
        position: absolute;
        left: 0;
        color: #f59e0b;
        font-weight: bold;
    }
    
    /* ===== FORMULAIRE CODE ===== */
    .form-section {
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        border: 1px solid transparent;
        transition: all var(--transition-base);
    }
    .form-section:hover {
        border-color: var(--border-color);
        box-shadow: var(--shadow-sm);
    }
    .form-section .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section .section-title .badge {
        font-size: 0.65rem;
        padding: 0.15rem 0.6rem;
        border-radius: var(--radius-full);
        background: rgba(var(--primary-500-rgb), 0.12);
        color: var(--primary-500);
        font-weight: 600;
    }
    
    /* ===== INPUT ===== */
    .input-activate {
        width: 100%;
        padding: 0.625rem 1rem;
        border-radius: var(--radius-md);
        border: 2px solid var(--border-color);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: all var(--transition-base);
        outline: none;
    }
    .input-activate:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
        transform: translateY(-2px);
    }
    .input-activate::placeholder {
        color: var(--text-tertiary);
    }
    
    /* ===== BOUTONS ===== */
    .btn-activate {
        width: 100%;
        padding: 0.75rem 1.25rem;
        border-radius: var(--radius-md);
        border: none;
        font-weight: 700;
        font-size: 0.875rem;
        color: white;
        cursor: pointer;
        transition: all var(--transition-bounce);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
    }
    .btn-activate::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.1);
        opacity: 0;
        transition: opacity var(--transition-base);
    }
    .btn-activate:hover::after {
        opacity: 1;
    }
    .btn-activate-primary {
        background: var(--gradient-primary);
        box-shadow: 0 4px 20px rgba(var(--primary-500-rgb), 0.35);
    }
    .btn-activate-primary:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 8px 32px rgba(var(--primary-500-rgb), 0.45);
    }
    .btn-activate .btn-icon {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
    }
    
    /* ===== BOUTON VERS PACKAGES ===== */
    .btn-packages {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 0.875rem 1.5rem;
        border-radius: var(--radius-md);
        border: none;
        font-weight: 700;
        font-size: 1rem;
        color: white;
        cursor: pointer;
        transition: all var(--transition-bounce);
        width: 100%;
        text-decoration: none;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        box-shadow: 0 4px 20px rgba(34, 197, 94, 0.35);
        position: relative;
        overflow: hidden;
    }
    .btn-packages::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.1);
        opacity: 0;
        transition: opacity var(--transition-base);
    }
    .btn-packages:hover::after {
        opacity: 1;
    }
    .btn-packages:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.45);
    }
    .btn-packages .btn-icon {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
    }
    
    /* ===== DIVISEUR ===== */
    .divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.25rem 0;
    }
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }
    .divider span {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0 0.5rem;
    }
    
    /* ===== BOUTON RENVOI ===== */
    .btn-resend {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 0.813rem;
        cursor: pointer;
        transition: all var(--transition-base);
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .btn-resend:hover {
        color: var(--primary-500);
        text-decoration: none;
    }
    
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border: none;
        white-space: nowrap;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    
    /* ===== SPINNER ===== */
    .spinner {
        display: inline-block;
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s ease-in-out infinite;
    }
    
    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .activate-card { padding: 1.25rem; }
        .activate-title { font-size: 1.25rem; }
        .form-section { padding: 1rem; }
    }
    
    @media (max-width: 640px) {
        .activate-card { padding: 1rem; }
        .activate-title { font-size: 1.125rem; }
        .form-section { padding: 0.875rem; }
        .btn-activate { padding: 0.625rem 1rem; font-size: 0.813rem; }
        .btn-packages { padding: 0.75rem 1rem; font-size: 0.875rem; }
        .btn-outline, .btn-primary { font-size: 0.7rem; padding: 0.375rem 0.75rem; }
        .banner-info { padding: 0.75rem; }
        .banner-info .banner-icon { font-size: 1rem; }
        .flex-col-sm { flex-direction: column; }
    }
    
    @media (max-width: 480px) {
        .activate-card { padding: 0.875rem; }
        .activate-title { font-size: 1rem; }
        .form-section .section-title { font-size: 0.875rem; }
        .input-activate { padding: 0.5rem 0.75rem; font-size: 0.813rem; }
        .btn-activate { padding: 0.5rem 0.75rem; font-size: 0.75rem; }
        .btn-packages { padding: 0.625rem 0.875rem; font-size: 0.813rem; }
        .divider { margin: 0.75rem 0; }
        .divider span { font-size: 0.65rem; }
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8 sm:py-12">
    
    <!-- CARD PRINCIPAL -->
    <div class="activate-card animate-fadeInScale">
        
        <!-- TITRE -->
        <div class="animate-fadeInUp delay-1">
            <h1 class="activate-title">
                <span class="highlight">Activation du compte</span>
            </h1>
            <p class="activate-subtitle">
                Activez votre compte pour commencer à gagner des commissions
            </p>
        </div>
        
        <!-- BANNIERE INFO -->
        <div class="banner-info animate-fadeInUp delay-2">
            <div class="flex items-start gap-3">
                <span class="banner-icon">⚠</span>
                <div>
                    <p class="font-semibold text-yellow-700 dark:text-yellow-300">
                        Votre compte est actuellement <strong>inactif</strong>
                    </p>
                    <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">
                        Pour recevoir des commissions, vous devez activer votre compte :
                    </p>
                    <ul>
                        <li>Entrer un code d'activation (si vous avez déjà payé au guichet)</li>
                        <li>Acheter un package en ligne</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- ACTIVATION PAR CODE -->
        <div class="form-section animate-fadeInUp delay-3">
            <div class="section-title">
                J'ai déjà payé mon package au guichet
                <span class="badge">Code d'activation</span>
            </div>
            
            <form action="{{ route('activate.code') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="activation_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Code d'activation
                    </label>
                    <input type="text" 
                           name="activation_code" 
                           id="activation_code"
                           class="input-activate"
                           placeholder="Ex: ACT-123456789ABC"
                           required>
                    @error('activation_code')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-activate btn-activate-primary">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Activer mon compte
                </button>
            </form>
            
            <!-- ✅ OPTIONS DE RENVOI DU CODE -->
            <div class="mt-4 pt-3 border-t border-[var(--border-color)]">
                <p class="text-xs text-[var(--text-secondary)] text-center mb-2">
                    Vous n'avez pas reçu votre code ?
                </p>
                <div class="flex flex-col sm:flex-row gap-2">
                    
                    <!-- Renvoi par EMAIL -->
                    <form action="{{ route('activate.resend') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="method" value="email">
                        <button type="submit" class="w-full btn-outline text-xs py-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Renvoyer par email
                        </button>
                    </form>
                    
                    <!-- Renvoi par SMS -->
                    <form action="{{ route('activate.resend') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="method" value="sms">
                        <div class="flex gap-2">
                            <input type="text" 
                                   name="phone" 
                                   placeholder="Numéro de téléphone"
                                   class="input-activate text-sm flex-1"
                                   required>
                            <button type="submit" class="btn-primary text-xs py-2 whitespace-nowrap">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                SMS
                            </button>
                        </div>
                        <p class="text-[10px] text-[var(--text-tertiary)] text-center mt-1">
                            Provider (Orange, Airtel, Vodacom) détecté automatiquement
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- DIVISEUR -->
        <div class="divider animate-fadeInUp delay-4">
            <span>ou</span>
        </div>
        
        <!-- LIEN VERS LES PACKAGES -->
        <div class="animate-fadeInUp delay-5">
            <div class="text-center">
                <p class="text-center text-sm text-[var(--text-secondary)] mt-2">
                    <a href="{{ route('subscriptions.index') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">
                        Acheter un package pour activer mon compte
                    </a>
                </p>
                <p class="text-xs text-[var(--text-tertiary)] mt-2">
                    L'achat d'un package active instantanément votre compte
                </p>
            </div>
        </div>
        
        <!-- FOOTER -->
        <div class="text-center mt-6 pt-4 border-t border-[var(--border-color)] animate-fadeInUp delay-6">
            <p class="text-xs text-[var(--text-tertiary)]">
                Une question ? Contactez le support à 
                <a href="mailto:support@salang.com" class="text-primary-500 hover:underline">
                    support@salang.com
                </a>
            </p>
        </div>
        
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span> Chargement...';
            }
        });
    });
});
</script>
@endpush
@endsections