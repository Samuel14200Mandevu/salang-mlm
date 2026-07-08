{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.auth')

@section('title', 'Inscription - Salang MLM')

@push('styles')
<style>
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
        color: var(--text-tertiary);
    }

    .email-checking {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.813rem;
        margin-top: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: #f3f4f6;
        border-radius: 0.375rem;
        border-left: 3px solid #9ca3af;
    }
    .email-checking-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #e5e7eb;
        border-top: 2px solid #6366f1;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        flex-shrink: 0;
    }
    .email-status-success {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #16a34a;
        font-size: 0.813rem;
        margin-top: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: #f0fdf4;
        border-radius: 0.375rem;
        border-left: 3px solid #22c55e;
        animation: slideDown 0.3s ease;
    }
    .email-status-error {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #dc2626;
        font-size: 0.813rem;
        margin-top: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: #fef2f2;
        border-radius: 0.375rem;
        border-left: 3px solid #ef4444;
        animation: slideDown 0.3s ease;
    }
    .email-status-warning {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #d97706;
        font-size: 0.813rem;
        margin-top: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: #fffbeb;
        border-radius: 0.375rem;
        border-left: 3px solid #f59e0b;
        animation: slideDown 0.3s ease;
    }
    .email-status-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        font-size: 0.688rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .email-status-success .email-status-icon {
        background: #22c55e;
        color: #ffffff;
    }
    .email-status-error .email-status-icon {
        background: #ef4444;
        color: #ffffff;
    }
    .email-status-warning .email-status-icon {
        background: #f59e0b;
        color: #ffffff;
    }

    .social-info-box {
        background: rgba(14, 47, 118, 0.06);
        border: 1px solid rgba(14, 47, 118, 0.15);
        border-radius: var(--radius-md);
        padding: 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .social-info-box .avatar-social {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #0E2F76;
    }
    .social-info-box .social-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .social-info-box .social-email {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
    }

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
    .toast-modern .toast-icon.warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
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

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 640px) {
        .social-info-box { 
            flex-direction: column; 
            text-align: center; 
        }
        .social-info-box .avatar-social { 
            width: 48px; 
            height: 48px; 
        }
        .email-checking,
        .email-status-success,
        .email-status-error,
        .email-status-warning {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
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

    @media (max-width: 480px) {
        .social-info-box { 
            padding: 0.75rem; 
        }
        .social-info-box .social-label {
            font-size: 0.688rem;
        }
        .social-info-box .social-email {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="auth-logo">
        <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang MLM" class="h-12 sm:h-16 w-auto mx-auto">
        <span class="brand-name block mt-2">Salang Group</span>
    </div>

    <h2 class="auth-title">Créer un compte</h2>
    <p class="auth-subtitle">Rejoignez la communaute Salang</p>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if(session('social_data'))
        @php $socialData = session('social_data'); @endphp
        <div class="social-info-box">
            @if(isset($socialData['avatar']))
                <img src="{{ $socialData['avatar'] }}" alt="Avatar" class="avatar-social">
            @else
                <div class="avatar-social" style="background: linear-gradient(135deg, #0E2F76, #0038BD); display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:1.2rem;">
                    {{ substr($socialData['name'] ?? 'U', 0, 1) }}
                </div>
            @endif
            <div>
                <p class="social-label">Inscription avec <strong>{{ ucfirst($socialData['provider'] ?? 'social') }}</strong></p>
                <p class="social-email">{{ $socialData['email'] ?? '' }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <div class="form-group">
            <label for="name">Nom complet <span class="required">*</span></label>
            <input type="text" 
                   name="name" 
                   id="name"
                   value="{{ old('name', session('social_data.name', '')) }}" 
                   class="input @error('name') input-error @enderror"
                   placeholder="Entrez votre nom complet"
                   required 
                   autofocus>
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Adresse email <span class="required">*</span></label>
            <input type="email" 
                   name="email" 
                   id="email"
                   value="{{ old('email', session('social_data.email', '')) }}" 
                   class="input @error('email') input-error @enderror"
                   placeholder="exemple@email.com"
                   required
                   autocomplete="email">
            <div id="emailAvailability"></div>
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">Téléphone</label>
            <input type="tel" 
                   name="phone" 
                   id="phone"
                   value="{{ old('phone') }}" 
                   class="input @error('phone') input-error @enderror"
                   placeholder="Entrez votre numéro de téléphone">
            @error('phone')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="sponsor_id">Identifiant du parrain <span class="required">*</span></label>
            <input type="text" 
                   name="sponsor_id" 
                   id="sponsor_id"
                   value="{{ old('sponsor_id', session('social_data.sponsor_id', request()->query('ref', ''))) }}" 
                   class="input @error('sponsor_id') input-error @enderror"
                   placeholder="Exemple: SALABCDEF"
                   required>
            <p class="form-hint">Entrez l'identifiant de la personne qui vous a invite</p>
            @error('sponsor_id')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Mot de passe <span class="required">*</span></label>
            <div class="password-wrapper">
                <input type="password" 
                       id="password"
                       name="password" 
                       class="input @error('password') input-error @enderror"
                       placeholder="8 caracteres minimum"
                       required
                       minlength="8">
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
            <div class="password-strength">
                <div class="password-strength-bar" id="passwordStrength"></div>
            </div>
            <p class="password-strength-text" id="passwordStrengthText">Minimum 8 caracteres</p>
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmer le mot de passe <span class="required">*</span></label>
            <input type="password" 
                   name="password_confirmation" 
                   id="password_confirmation"
                   class="input"
                   placeholder="Confirmez votre mot de passe"
                   required>
        </div>

        <div class="form-group mb-6">
            <label class="flex items-start gap-2 text-sm text-[var(--text-secondary)] cursor-pointer">
                <input type="checkbox" 
                       name="terms" 
                       id="terms"
                       value="1"
                       class="mt-0.5 w-4 h-4 rounded border-[var(--border-color)] text-[#0E2F76] focus:ring-[#0E2F76] focus:ring-offset-0"
                       required>
                <span>
                    J'accepte les 
                    <a href="{{ route('terms-of-service') }}" class="text-[#0E2F76] hover:text-[#0038BD] font-semibold transition">
                        conditions generales
                    </a>
                    et la 
                    <a href="{{ route('privacy-policy') }}" class="text-[#0E2F76] hover:text-[#0038BD] font-semibold transition">
                        politique de confidentialite
                    </a>
                </span>
            </label>
            @error('terms')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary" id="submitBtn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Creer mon compte
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
        Deja un compte ?
        <a href="{{ route('login') }}" class="auth-link">
            Se connecter
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
    } else if (type === 'warning') {
        icon.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
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

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
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

    const emailInput = document.getElementById('email');
    const emailAvailability = document.getElementById('emailAvailability');
    let emailTimeout = null;
    let emailChecked = false;

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            emailChecked = false;
            this.classList.remove('input-error', 'input-success');
            
            if (emailAvailability) {
                emailAvailability.innerHTML = '';
            }

            if (isValidEmail(email)) {
                emailAvailability.innerHTML = `
                    <div class="email-checking">
                        <span class="email-checking-spinner"></span>
                        Verification en cours...
                    </div>
                `;
                
                clearTimeout(emailTimeout);
                emailTimeout = setTimeout(function() {
                    fetch('/check-email?email=' + encodeURIComponent(email))
                        .then(response => response.json())
                        .then(data => {
                            emailChecked = true;
                            
                            showToast(data.message, data.type);
                            
                            if (data.field_status === 'error') {
                                emailInput.classList.add('input-error');
                                emailInput.classList.remove('input-success');
                                emailAvailability.innerHTML = `
                                    <div class="email-status-error">
                                        <span class="email-status-icon">✕</span>
                                        ${data.message}
                                    </div>
                                `;
                            } else if (data.field_status === 'success') {
                                emailInput.classList.remove('input-error');
                                emailInput.classList.add('input-success');
                                emailAvailability.innerHTML = `
                                    <div class="email-status-success">
                                        <span class="email-status-icon">✓</span>
                                        ${data.message}
                                    </div>
                                `;
                            } else {
                                emailInput.classList.add('input-error');
                                emailAvailability.innerHTML = `
                                    <div class="email-status-warning">
                                        <span class="email-status-icon">!</span>
                                        ${data.message}
                                    </div>
                                `;
                            }
                        })
                        .catch(function(error) {
                            console.error('Erreur de verification:', error);
                            if (emailAvailability) {
                                emailAvailability.innerHTML = '';
                            }
                        });
                }, 500);
            }
        });
    }

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
                label = 'Tres fort';
            }

            if (value.length > 0) {
                strengthText.textContent = 'Force: ' + label;
                strengthText.className = 'password-strength-text';
            } else {
                strengthText.textContent = 'Minimum 8 caracteres';
                strengthText.className = 'password-strength-text text-[var(--text-tertiary)]';
            }
        });
    }

    const form = document.getElementById('registerForm');
    const nameInput = document.getElementById('name');
    const sponsorInput = document.getElementById('sponsor_id');
    const confirmInput = document.getElementById('password_confirmation');
    const termsCheckbox = document.getElementById('terms');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        let hasError = false;
        let errorMessage = '';

        const name = nameInput.value.trim();
        if (!name) {
            errorMessage = 'Veuillez saisir votre nom complet.';
            hasError = true;
        }

        const email = emailInput.value.trim();
        if (!hasError && !email) {
            errorMessage = 'Veuillez saisir votre adresse email.';
            hasError = true;
        } else if (!hasError && !isValidEmail(email)) {
            errorMessage = 'Veuillez saisir une adresse email valide.';
            hasError = true;
        } else if (!hasError && emailChecked === false && email.length > 0) {
            errorMessage = 'Veuillez attendre la verification de votre email.';
            hasError = true;
        } else if (!hasError && emailInput.classList.contains('input-error')) {
            errorMessage = 'Cet email est deja utilise. Veuillez en utiliser un autre.';
            hasError = true;
        }

        const sponsor = sponsorInput.value.trim();
        if (!hasError && !sponsor) {
            errorMessage = 'Veuillez saisir l\'identifiant de votre parrain.';
            hasError = true;
        } else if (!hasError && sponsor.length < 3) {
            errorMessage = 'L\'identifiant du parrain doit contenir au moins 3 caracteres.';
            hasError = true;
        }

        const password = passwordInput.value;
        if (!hasError && !password) {
            errorMessage = 'Veuillez saisir un mot de passe.';
            hasError = true;
        } else if (!hasError && password.length < 8) {
            errorMessage = 'Le mot de passe doit contenir au moins 8 caracteres.';
            hasError = true;
        }

        const confirm = confirmInput.value;
        if (!hasError && confirm !== password) {
            errorMessage = 'Les mots de passe ne correspondent pas.';
            hasError = true;
        }

        if (!hasError && !termsCheckbox.checked) {
            errorMessage = 'Vous devez accepter les conditions generales.';
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            showToast(errorMessage, 'error');
        }
    });

    document.querySelectorAll('.social-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var sponsorId = sponsorInput.value.trim();
            if (!sponsorId) {
                e.preventDefault();
                showToast('Veuillez entrer un identifiant de parrain.', 'error');
                sponsorInput.focus();
                sponsorInput.classList.add('input-error');
                return false;
            }
            sponsorInput.classList.remove('input-error');
        });
    });

    sponsorInput.addEventListener('input', function() {
        this.classList.remove('input-error');
    });
});
</script>
@endpush
@endsection