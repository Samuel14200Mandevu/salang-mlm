{{-- resources/views/admin/users/edit.blade.php --}}
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
    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary-500);
        box-shadow: 0 0 0 4px rgba(90, 182, 56, 0.15);
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
    @media (max-width: 640px) {
        .form-group label {
            font-size: 0.75rem;
        }
        .form-group .help-text {
            font-size: 0.65rem;
        }
        .user-avatar {
            width: 60px;
            height: 60px;
        }
        .form-grid {
            grid-template-columns: 1fr !important;
        }
        .edit-header {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .edit-header .avatar-wrapper {
            margin-left: 0 !important;
            margin-top: 0.5rem;
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
        <div class="edit-header flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                    <span class="text-primary-500">
                        <svg class="inline-block w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    Modifier {{ $user->name }}
                </h1>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                    ID: {{ $user->id }}
                </p>
            </div>
            <div class="avatar-wrapper ml-auto flex-shrink-0">
                @if($user->avatar)
                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                         alt="Avatar" class="user-avatar">
                @else
                    <div class="user-avatar bg-primary-500 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
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

    <div class="card animate-fadeInUp delay-2 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Name -->
                <div class="form-group">
                    <label>Nom complet <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="input text-sm sm:text-base @error('email') input-error @enderror" required>
                    @error('email') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="password" 
                           class="input text-sm sm:text-base @error('password') input-error @enderror" 
                           placeholder="Laissez vide pour conserver l'actuel">
                    <p class="help-text">Minimum 8 caractères</p>
                    @error('password') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" 
                           class="input text-sm sm:text-base" 
                           placeholder="Confirmer le nouveau mot de passe">
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label>Pays</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- City -->
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- Address -->
                <div class="form-group md:col-span-2">
                    <label>Adresse</label>
                    <textarea name="address" rows="2" class="input text-sm sm:text-base">{{ old('address', $user->address) }}</textarea>
                </div>

                <!-- Package -->
                <div class="form-group" id="packageGroup">
                    <label>Package</label>
                    <select name="package_id" class="input text-sm sm:text-base">
                        <option value="">Aucun</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}" {{ $user->package_id == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} (${{ number_format($package->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <p class="help-text">Sélectionnez un package pour cet utilisateur</p>
                </div>

                <!-- Sponsor (Parrain) -->
                <div class="form-group" id="sponsorGroup">
                    <label>Sponsor (Parrain)</label>
                    <select name="parrain_id" class="input text-sm sm:text-base">
                        <option value="">Aucun</option>
                        @foreach($users ?? [] as $sponsor)
                            @if($sponsor->id != $user->id)
                                <option value="{{ $sponsor->id }}" 
                                    {{ $user->parrain_id == $sponsor->id ? 'selected' : '' }}>
                                    {{ $sponsor->name }} ({{ $sponsor->sponsor_id }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <p class="help-text">Sélectionnez le parrain pour cet utilisateur</p>
                </div>

                <!-- Sponsor Code (lecture seule) -->
                <div class="form-group">
                    <label>Code de parrain</label>
                    <input type="text" value="{{ $user->sponsor_id ?? 'Aucun' }}" 
                           class="input text-sm sm:text-base bg-[var(--bg-secondary)] cursor-not-allowed" disabled>
                    <p class="help-text">Code unique de parrain (non modifiable)</p>
                </div>

                <!-- Rôle -->
                <div class="form-group md:col-span-2">
                    <label>Rôle de l'utilisateur <span class="required">*</span></label>
                    <p class="help-text mb-2">Sélectionnez le rôle que cet utilisateur aura dans la plateforme</p>
                    
                    <div class="space-y-2" id="roleSelector">
                        @php
                            $currentRole = $user->roles->first()?->name ?? 'user';
                        @endphp
                        
                        <label class="role-option {{ $currentRole === 'user' ? 'selected' : '' }}" id="role-user">
                            <input type="radio" name="role" value="user" {{ $currentRole === 'user' ? 'checked' : '' }}>
                            <div class="role-info">
                                <div class="role-name"> Membre</div>
                                <div class="role-desc">Accès au dashboard client, achats, commissions, réseau MLM</div>
                            </div>
                            <span class="role-badge user">Standard</span>
                        </label>

                        <label class="role-option {{ $currentRole === 'cashier' ? 'selected' : '' }}" id="role-cashier">
                            <input type="radio" name="role" value="cashier" {{ $currentRole === 'cashier' ? 'checked' : '' }}>
                            <div class="role-info">
                                <div class="role-name"> Caissier</div>
                                <div class="role-desc">Accès au Point de Vente (POS), gestion des ventes au guichet</div>
                            </div>
                            <span class="role-badge cashier">Ventes</span>
                        </label>

                        <label class="role-option {{ $currentRole === 'admin' ? 'selected' : '' }}" id="role-admin">
                            <input type="radio" name="role" value="admin" {{ $currentRole === 'admin' ? 'checked' : '' }}>
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

                <!-- Status -->
                <div class="form-group">
                    <label>Statut</label>
                    <select name="is_active" class="input text-sm sm:text-base">
                        <option value="1" {{ $user->is_active ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactif</option>
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
                            @if($currentRole === 'cashier')
                                 Les caissiers sont des employés de la société. Ils n'ont pas de code de parrainage, ne reçoivent pas de commissions et ne font pas partie du réseau MLM.
                            @elseif($currentRole === 'admin')
                                 Administrateur avec accès complet à l'administration.
                            @else
                                Un code de parrain unique a été généré pour cet utilisateur. Il peut bénéficier du système MLM.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre à jour
                </button>
                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
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
        user: 'Un code de parrain unique a été généré pour cet utilisateur. Il peut bénéficier du système MLM et des commissions.',
        cashier: ' Les caissiers sont des employés de la société. Ils n\'ont pas de code de parrainage, ne reçoivent pas de commissions et ne font pas partie du réseau MLM. Accès uniquement au Point de Vente (POS).',
        admin: ' Administrateur avec accès complet à l\'administration. Un code de parrain a été généré automatiquement.'
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