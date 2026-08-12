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
    @keyframes slideDown {
        from { opacity: 0; max-height: 0; transform: translateY(-10px); }
        to { opacity: 1; max-height: 400px; transform: translateY(0); }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .animate-fadeInScale { animation: fadeInScale 0.5s ease forwards; }
    .animate-slideDown { animation: slideDown 0.4s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    
    /* ===== PAGE D'ACTIVATION ===== */
    .activate-page {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    
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
        width: 100%;
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
        flex-wrap: wrap;
    }
    .form-section .section-title .badge {
        font-size: 0.65rem;
        padding: 0.15rem 0.6rem;
        border-radius: var(--radius-full);
        background: rgba(var(--primary-500-rgb), 0.12);
        color: var(--primary-500);
        font-weight: 600;
    }
    
    /* ===== LIEN RENVOI CODE ===== */
    .resend-trigger {
        display: block;
        text-align: center;
        color: var(--primary-500);
        font-size: 0.813rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        padding: 0.5rem;
        border-radius: var(--radius-sm);
        text-decoration: underline;
        text-underline-offset: 2px;
        letter-spacing: 0.02em;
    }
    .resend-trigger:hover {
        color: var(--primary-600);
        background: rgba(var(--primary-500-rgb), 0.05);
        text-decoration: none;
    }
    .resend-trigger .icon {
        display: inline-block;
        transition: transform 0.3s ease;
        margin-right: 0.3rem;
    }
    .resend-trigger .icon.open {
        transform: rotate(180deg);
    }
    
    .resend-options {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: all 0.4s ease;
    }
    .resend-options.open {
        max-height: 400px;
        opacity: 1;
        margin-top: 0.75rem;
    }
    
    /* ===== OPTIONS DE RENVOI - DESIGN AMÉLIORÉ ===== */
    .resend-options .options-header {
        text-align: center;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px dashed var(--border-color);
    }
    
    .resend-options .option-card {
        background: var(--bg-card);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        width: 100%;
    }
    .resend-options .option-card:hover {
        border-color: var(--primary-500);
        box-shadow: var(--shadow-hover);
        transform: translateY(-2px);
    }
    .resend-options .option-card .option-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.3rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .resend-options .option-card .option-label .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .resend-options .option-card .option-label .dot.email-dot { background: #3b82f6; }
    .resend-options .option-card .option-label .dot.sms-dot { background: #22c55e; }
    
    .resend-options .option-card .input-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .resend-options .option-card .input-group .input-activate {
        flex: 1;
        min-width: 120px;
        padding: 0.5rem 0.75rem;
        font-size: 0.813rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-sm);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.3s ease;
        outline: none;
    }
    .resend-options .option-card .input-group .input-activate:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    .resend-options .option-card .input-group .input-activate::placeholder {
        color: var(--text-tertiary);
        font-size: 0.7rem;
    }
    
    .resend-options .option-card .btn-send {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        border: none;
        font-weight: 600;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 2px 12px rgba(var(--primary-500-rgb), 0.25);
    }
    .resend-options .option-card .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(var(--primary-500-rgb), 0.35);
    }
    .resend-options .option-card .btn-send svg {
        width: 1rem;
        height: 1rem;
    }
    
    .resend-options .option-card .btn-send-email {
        background: #3b82f6;
        box-shadow: 0 2px 12px rgba(59, 130, 246, 0.25);
    }
    .resend-options .option-card .btn-send-email:hover {
        box-shadow: 0 4px 20px rgba(59, 130, 246, 0.35);
    }
    
    .resend-options .option-card .btn-send-sms {
        background: #22c55e;
        box-shadow: 0 2px 12px rgba(34, 197, 94, 0.25);
    }
    .resend-options .option-card .btn-send-sms:hover {
        box-shadow: 0 4px 20px rgba(34, 197, 94, 0.35);
    }
    
    .resend-options .provider-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        padding: 0.4rem 0.75rem;
        background: rgba(59, 130, 246, 0.06);
        border-radius: var(--radius-sm);
        border: 1px solid rgba(59, 130, 246, 0.1);
        font-size: 0.6rem;
        color: var(--text-secondary);
    }
    .resend-options .provider-info .provider-icon {
        display: flex;
        gap: 0.3rem;
    }
    .resend-options .provider-info .provider-icon span {
        display: inline-block;
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
    }
    .resend-options .provider-info .provider-icon .orange { background: #ff6600; }
    .resend-options .provider-info .provider-icon .airtel { background: #ff0000; }
    .resend-options .provider-info .provider-icon .vodacom { background: #00a651; }
    
    .resend-options .option-divider {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0.5rem 0;
    }
    .resend-options .option-divider::before,
    .resend-options .option-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }
    .resend-options .option-divider span {
        font-size: 0.6rem;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
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
        .activate-page { padding: 1rem; }
    }
    
    @media (max-width: 640px) {
        .activate-card { padding: 1rem; }
        .activate-title { font-size: 1.125rem; }
        .form-section { padding: 0.875rem; }
        .btn-activate { padding: 0.625rem 1rem; font-size: 0.813rem; }
        .btn-packages { padding: 0.75rem 1rem; font-size: 0.875rem; }
        .banner-info { padding: 0.75rem; }
        .banner-info .banner-icon { font-size: 1rem; }
        .flex-col-sm { flex-direction: column; }
        .activate-page { padding: 0.75rem; min-height: 70vh; }
        .resend-options .option-card .input-group { flex-direction: column; }
        .resend-options .option-card .input-group .input-activate { width: 100%; min-width: unset; }
        .resend-options .option-card .input-group .btn-send { width: 100%; justify-content: center; }
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
        .activate-page { padding: 0.5rem; min-height: 60vh; }
        .resend-options .option-card { padding: 0.5rem; }
        .resend-options .option-card .option-label { font-size: 0.6rem; }
        .resend-options .option-card .input-group .input-activate { font-size: 0.7rem; padding: 0.4rem 0.6rem; }
        .resend-options .option-card .btn-send { font-size: 0.65rem; padding: 0.4rem 0.6rem; }
    }
</style>
@endpush

@section('content')
<div class="activate-page">
    
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
            
            <!-- ✅ LIEN RENVOI CODE - DESIGN AMÉLIORÉ -->
            <div class="mt-3">
                <span class="resend-trigger" onclick="toggleResendOptions()">
                    <span class="icon" id="resendIcon">▼</span>
                    Vous n'avez pas reçu votre code ?
                </span>
                
                <!-- Options de renvoi -->
                <div id="resendOptions" class="resend-options">
                    <div class="options-header">
                        Choisissez comment recevoir votre code
                    </div>
                    
                    <!-- Option Email -->
                    <div class="option-card">
                        <div class="option-label">
                            <span class="dot email-dot"></span>
                            Par email
                        </div>
                        <form action="{{ route('activate.resend') }}" method="POST">
                            @csrf
                            <input type="hidden" name="method" value="email">
                            <button type="submit" class="btn-send btn-send-email w-full">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Renvoyer par email
                            </button>
                        </form>
                    </div>
                    
                    <div class="option-divider">
                        <span>ou</span>
                    </div>
                    
                    <!-- Option SMS -->
                    <div class="option-card">
                        <div class="option-label">
                            <span class="dot sms-dot"></span>
                            Par SMS
                        </div>
                        <form action="{{ route('activate.resend') }}" method="POST">
                            @csrf
                            <input type="hidden" name="method" value="sms">
                            <div class="input-group">
                                <input type="text" 
                                       name="phone" 
                                       placeholder="Numéro de téléphone"
                                       class="input-activate"
                                       required>
                                <button type="submit" class="btn-send btn-send-sms">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    SMS
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Provider Info -->
                    <div class="provider-info">
                        <span>Provider détecté automatiquement</span>
                        <span class="provider-icon">
                            <span class="orange"></span>
                            <span class="airtel"></span>
                            <span class="vodacom"></span>
                        </span>
                        <span>(Orange, Airtel, Vodacom)</span>
                    </div>
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
function toggleResendOptions() {
    var options = document.getElementById('resendOptions');
    var icon = document.getElementById('resendIcon');
    
    if (options.classList.contains('open')) {
        options.classList.remove('open');
        icon.classList.remove('open');
        icon.textContent = '▼';
    } else {
        options.classList.add('open');
        icon.classList.add('open');
        icon.textContent = '▲';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span> Chargement...';
            }
        });
    });
});
</script>
@endpush
@endsection