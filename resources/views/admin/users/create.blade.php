{{-- resources/views/admin/users/create.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}
.form-group .required {
    color: #B91C1C;
}
.form-group .help-text {
    font-size: 0.75rem;
    color: var(--text-tertiary);
    margin-top: 0.125rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    outline: none;
}
.form-control:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}
.form-control-error {
    border-color: #B91C1C;
}
.form-control-error:focus {
    border-color: #B91C1C;
    box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.12);
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.role-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.role-option:hover {
    border-color: var(--primary-blue);
    background: var(--bg-hover);
}
.role-option input[type="radio"] {
    width: 1.125rem;
    height: 1.125rem;
    accent-color: var(--primary-blue);
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
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.role-option .role-badge.admin {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}
.role-option .role-badge.user {
    background: var(--primary-blue-bg);
    color: var(--primary-blue);
    border-color: var(--primary-blue-border);
}
.role-option .role-badge.cashier {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.role-option.selected {
    border-color: var(--primary-blue);
    background: var(--primary-blue-bg);
}

.alert-danger {
    background: rgba(185, 28, 28, 0.08);
    border: 1px solid rgba(185, 28, 28, 0.15);
    color: #B91C1C;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.info-box {
    background: var(--primary-blue-bg);
    border: 1px solid var(--primary-blue-border);
    border-radius: 8px;
    padding: 0.75rem 1rem;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
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
        padding: 0.5rem 0.625rem;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Créer un utilisateur
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Créer un nouveau compte utilisateur
            </p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert-danger animate-fadeInUp delay-1">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-medium">Des erreurs sont survenues</p>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="card animate-fadeInUp delay-2 max-w-2xl p-3 sm:p-4">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                <!-- Name -->
                <div class="form-group">
                    <label>Nom complet <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') form-control-error @enderror"
                           placeholder="Jean Dupont" required>
                    @error('name')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') form-control-error @enderror"
                           placeholder="jean.dupont@email.com" required>
                    @error('email')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label>Mot de passe <span class="required">*</span></label>
                    <input type="password" name="password"
                           class="form-control @error('password') form-control-error @enderror"
                           placeholder="••••••••" required>
                    <span class="help-text">Minimum 8 caractères</span>
                    @error('password')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password confirmation -->
                <div class="form-group">
                    <label>Confirmer le mot de passe <span class="required">*</span></label>
                    <input type="password" name="password_confirmation"
                           class="form-control"
                           placeholder="••••••••" required>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="form-control @error('phone') form-control-error @enderror"
                           placeholder="+225 07 00 00 00 00">
                    @error('phone')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label>Pays</label>
                    <input type="text" name="country" value="{{ old('country') }}"
                           class="form-control"
                           placeholder="Côte d'Ivoire">
                </div>

                <!-- City -->
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="form-control"
                           placeholder="Abidjan">
                </div>

                <!-- Address -->
                <div class="form-group md:col-span-2">
                    <label>Adresse</label>
                    <textarea name="address" rows="2" class="form-control"
                              placeholder="Adresse complète...">{{ old('address') }}</textarea>
                </div>

                <!-- Package -->
                <div class="form-group" id="packageGroup">
                    <label>Package</label>
                    <select name="package_id" class="form-control">
                        <option value="">Aucun</option>
                        @if(isset($packages) && $packages->count() > 0)
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} (${{ number_format($package->price, 2) }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <span class="help-text">Sélectionnez un package pour ce nouvel utilisateur</span>
                </div>

                <!-- Sponsor -->
                <div class="form-group" id="sponsorGroup">
                    <label>Sponsor (Parrain)</label>
                    <select name="parrain_id" class="form-control">
                        <option value="">Aucun</option>
                        @if(isset($users) && $users->count() > 0)
                            @foreach($users as $sponsor)
                                <option value="{{ $sponsor->id }}" {{ old('parrain_id') == $sponsor->id ? 'selected' : '' }}>
                                    {{ $sponsor->name }} ({{ $sponsor->sponsor_id }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <span class="help-text">Sélectionnez le parrain pour ce nouvel utilisateur</span>
                </div>

                <!-- Role -->
                <div class="form-group md:col-span-2">
                    <label>Rôle de l'utilisateur <span class="required">*</span></label>
                    <span class="help-text block mb-2">Sélectionnez le rôle que cet utilisateur aura</span>

                    <div class="space-y-2" id="roleSelector">

                        <label class="role-option selected" id="role-user">
                            <input type="radio" name="role" value="user" checked>
                            <div class="role-info">
                                <div class="role-name">Membre</div>
                                <div class="role-desc">Accès au dashboard, achats, commissions, réseau MLM</div>
                            </div>
                            <span class="role-badge user">Standard</span>
                        </label>

                        <label class="role-option" id="role-cashier">
                            <input type="radio" name="role" value="cashier">
                            <div class="role-info">
                                <div class="role-name">Caissier</div>
                                <div class="role-desc">Accès au Point de Vente (POS), ventes au guichet</div>
                            </div>
                            <span class="role-badge cashier">Ventes</span>
                        </label>

                        <label class="role-option" id="role-admin">
                            <input type="radio" name="role" value="admin">
                            <div class="role-info">
                                <div class="role-name">Administrateur</div>
                                <div class="role-desc">Accès complet à l'administration, gestion utilisateurs</div>
                            </div>
                            <span class="role-badge admin">Admin</span>
                        </label>
                    </div>
                    @error('role')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>Statut</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
            </div>

            <!-- Info box -->
            <div class="info-box mt-3" id="roleInfoBox">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-[var(--primary-blue)] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Informations</p>
                        <p class="text-sm text-[var(--text-secondary)]" id="roleInfoText">
                            Un code de parrain unique sera généré automatiquement.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none" id="submitBtn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Créer l'utilisateur
                </button>
                <a href="{{ route('admin.users') }}" class="btn btn-outline flex-1 sm:flex-none">
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
        user: 'Un code de parrain unique sera généré automatiquement. Il pourra bénéficier du système MLM et des commissions.',
        cashier: 'Les caissiers sont des employés. Pas de code de parrainage, pas de commissions, pas de réseau MLM. Accès POS uniquement.',
        admin: 'Administrateur avec accès complet. Un code de parrain sera généré automatiquement.'
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

    roleOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            roleOptions.forEach(function(opt) {
                opt.classList.remove('selected');
            });
            this.classList.add('selected');

            var radio = this.querySelector('input[type="radio"]');
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