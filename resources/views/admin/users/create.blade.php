{{-- resources/views/admin/users/create.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }
    .form-group .required {
        color: #ef4444;
    }
    .form-group .help-text {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        margin-top: 0.125rem;
    }
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .card:hover {
        border-color: var(--primary-500);
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .input {
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
    .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    .input-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
    }
    .role-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .role-option:hover {
        border-color: var(--primary-500);
        background: var(--bg-hover);
    }
    .role-option input[type="radio"] {
        width: 1.25rem;
        height: 1.25rem;
        accent-color: var(--primary-500);
        cursor: pointer;
        flex-shrink: 0;
    }
    .role-option .role-info {
        flex: 1;
    }
    .role-option .role-info .role-name {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-primary);
    }
    .role-option .role-info .role-desc {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .role-option .role-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .role-option .role-badge.admin {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .role-option .role-badge.user {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .role-option .role-badge.cashier {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .role-option.selected {
        border-color: var(--primary-500);
        background: rgba(90, 182, 56, 0.05);
    }
    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #ef4444;
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        margin-bottom: 1rem;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    @media (max-width: 640px) {
        .form-group label {
            font-size: 0.75rem;
        }
        .form-group .help-text {
            font-size: 0.65rem;
        }
        .form-grid {
            grid-template-columns: 1fr !important;
        }
        .card {
            padding: 0.875rem;
        }
        .btn {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        .role-option {
            padding: 0.5rem 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                    <span class="text-primary-500">
                        <svg class="inline-block w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </span>
                    Créer un utilisateur
                </h1>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                    Créer un nouveau compte utilisateur (membre, caissier ou administrateur)
                </p>
            </div>
            <a href="{{ route('admin.users') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert-danger animate-fadeInUp delay-1">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-medium">Des erreurs sont survenues :</p>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="card animate-fadeInUp delay-2 max-w-2xl">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Nom -->
                <div class="form-group">
                    <label>Nom complet <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" 
                           placeholder="Jean Dupont" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="input text-sm sm:text-base @error('email') input-error @enderror" 
                           placeholder="jean.dupont@email.com" required>
                    @error('email') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div class="form-group">
                    <label>Mot de passe <span class="required">*</span></label>
                    <input type="password" name="password" 
                           class="input text-sm sm:text-base @error('password') input-error @enderror" 
                           placeholder="••••••••" required>
                    <p class="help-text">Minimum 8 caractères</p>
                    @error('password') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Confirmation mot de passe -->
                <div class="form-group">
                    <label>Confirmer le mot de passe <span class="required">*</span></label>
                    <input type="password" name="password_confirmation" 
                           class="input text-sm sm:text-base" 
                           placeholder="••••••••" required>
                </div>

                <!-- Téléphone -->
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" 
                           class="input text-sm sm:text-base @error('phone') input-error @enderror" 
                           placeholder="+225 07 00 00 00 00">
                    @error('phone') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Pays -->
                <div class="form-group">
                    <label>Pays</label>
                    <input type="text" name="country" value="{{ old('country') }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="Côte d'Ivoire">
                </div>

                <!-- Ville -->
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="city" value="{{ old('city') }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="Abidjan">
                </div>

                <!-- Adresse -->
                <div class="form-group md:col-span-2">
                    <label>Adresse</label>
                    <textarea name="address" rows="2" class="input text-sm sm:text-base" 
                              placeholder="Adresse complète...">{{ old('address') }}</textarea>
                </div>

                <!-- Package - Masqué pour les caissiers -->
                <div class="form-group" id="packageGroup">
                    <label>Package</label>
                    <select name="package_id" class="input text-sm sm:text-base">
                        <option value="">Aucun</option>
                        @if(isset($packages) && $packages->count() > 0)
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} (${{ number_format($package->price, 2) }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <p class="help-text">Sélectionnez un package pour ce nouvel utilisateur</p>
                </div>

                <!-- Sponsor (Parrain) - Masqué pour les caissiers -->
                <div class="form-group" id="sponsorGroup">
                    <label>Sponsor (Parrain)</label>
                    <select name="parrain_id" class="input text-sm sm:text-base">
                        <option value="">Aucun</option>
                        @if(isset($users) && $users->count() > 0)
                            @foreach($users as $sponsor)
                                <option value="{{ $sponsor->id }}" {{ old('parrain_id') == $sponsor->id ? 'selected' : '' }}>
                                    {{ $sponsor->name }} ({{ $sponsor->sponsor_id }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <p class="help-text">Sélectionnez le parrain pour ce nouvel utilisateur</p>
                </div>

                <!-- RÔLE -->
                <div class="form-group md:col-span-2">
                    <label>Rôle de l'utilisateur <span class="required">*</span></label>
                    <p class="help-text mb-2">Sélectionnez le rôle que cet utilisateur aura dans la plateforme</p>
                    
                    <div class="space-y-2" id="roleSelector">
                        <!-- Option : Utilisateur -->
                        <label class="role-option selected" id="role-user">
                            <input type="radio" name="role" value="user" checked>
                            <div class="role-info">
                                <div class="role-name"> Membre</div>
                                <div class="role-desc">Accès au dashboard client, achats, commissions, réseau MLM</div>
                            </div>
                            <span class="role-badge user">Standard</span>
                        </label>

                        <!-- Option : Caissier -->
                        <label class="role-option" id="role-cashier">
                            <input type="radio" name="role" value="cashier">
                            <div class="role-info">
                                <div class="role-name"> Caissier</div>
                                <div class="role-desc">Accès au Point de Vente (POS), gestion des ventes au guichet</div>
                            </div>
                            <span class="role-badge cashier">Ventes</span>
                        </label>

                        <!-- Option : Administrateur -->
                        <label class="role-option" id="role-admin">
                            <input type="radio" name="role" value="admin">
                            <div class="role-info">
                                <div class="role-name"> Administrateur</div>
                                <div class="role-desc">Accès complet à l'administration, gestion des utilisateurs, MLM</div>
                            </div>
                            <span class="role-badge admin">Admin</span>
                        </label>
                    </div>
                    @error('role') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Statut -->
                <div class="form-group">
                    <label>Statut</label>
                    <select name="is_active" class="input text-sm sm:text-base">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
            </div>

            <!-- Information dynamique -->
            <div class="mt-4 p-3 bg-primary-500/5 border border-primary-500/20 rounded-lg" id="roleInfoBox">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Informations</p>
                        <p class="text-sm text-[var(--text-secondary)]" id="roleInfoText">
                            Un code de parrain unique sera généré automatiquement pour ce nouvel utilisateur.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5" id="submitBtn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Créer l'utilisateur
                </button>
                <a href="{{ route('admin.users') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleOptions = document.querySelectorAll('.role-option');
    const roleInfoText = document.getElementById('roleInfoText');
    const packageGroup = document.getElementById('packageGroup');
    const sponsorGroup = document.getElementById('sponsorGroup');
    
    const infoMessages = {
        user: 'Un code de parrain unique sera généré automatiquement pour ce nouvel utilisateur. Il pourra bénéficier du système MLM et des commissions.',
        cashier: ' Les caissiers sont des employés de la société. Ils n\'ont pas de code de parrainage, ne reçoivent pas de commissions et ne font pas partie du réseau MLM. Accès uniquement au Point de Vente (POS).',
        admin: ' Administrateur avec accès complet à l\'administration. Un code de parrain sera généré automatiquement.'
    };
    
    function updateRoleDisplay(role) {
        roleInfoText.textContent = infoMessages[role] || infoMessages.user;
        
        if (role === 'cashier') {
            if (packageGroup) packageGroup.style.display = 'none';
            if (sponsorGroup) sponsorGroup.style.display = 'none';
        } else {
            if (packageGroup) packageGroup.style.display = 'block';
            if (sponsorGroup) sponsorGroup.style.display = 'block';
        }
    }
    
    const selectedRadio = document.querySelector('input[name="role"]:checked');
    if (selectedRadio) {
        updateRoleDisplay(selectedRadio.value);
    }
    
    roleOptions.forEach(option => {
        option.addEventListener('click', function() {
            roleOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                updateRoleDisplay(radio.value);
            }
        });
    });
});
</script>
@endpush
@endsection