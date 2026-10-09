{{-- resources/views/admin/users/create.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
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
.role-option .role-badge.it-manager {
    background: rgba(13, 148, 136, 0.12);
    color: #0f766e;
    border-color: rgba(13, 148, 136, 0.18);
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

.parrain-feedback {
    font-size: 0.75rem;
    margin-top: 0.25rem;
    min-height: 1rem;
}
.parrain-feedback.success {
    color: #1C7E4A;
}
.parrain-feedback.error {
    color: #B91C1C;
}
.parrain-feedback.loading {
    color: var(--text-tertiary);
}

.admin-user-create-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0.85rem 1rem;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.admin-user-create-actions .btn-primary {
    flex: 1 1 auto;
    min-height: 2.75rem;
}

.admin-user-create-actions .btn-outline {
    flex: 1 1 auto;
    min-height: 2.75rem;
    justify-content: center;
}

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

    .admin-mobile-page--user-create {
        padding-bottom: 5.5rem;
    }

    .admin-user-create-actions {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 40;
        margin: 0;
        border-radius: 0;
        border-left: none;
        border-right: none;
        border-bottom: none;
        padding: 0.65rem 0.75rem calc(0.65rem + env(safe-area-inset-bottom, 0px));
        box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.08);
        background: color-mix(in srgb, var(--bg-page) 94%, transparent);
        backdrop-filter: blur(10px);
    }
}
</style>
@endpush

@section('content')
@php
    $createBackBtn = '<a href="' . e(route('admin.users')) . '" class="btn btn-outline btn-sm">'
        . '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">'
        . '<path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>'
        . '</svg> Retour</a>';
@endphp

@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Créer un utilisateur',
    'subtitle' => 'Créer un nouveau compte utilisateur',
    'actions' => $createBackBtn,
])

<div class="admin-page-header page-header md:hidden animate-fadeInUp">
    <div class="admin-mobile-page-head is-mobile-banner">
        <div class="admin-title-banner__text">
            <h1 class="page-title">Créer un utilisateur</h1>
            <p class="page-subtitle">Membre, caissier, admin ou responsable IT</p>
        </div>
        @include('admin.users.partials.create-mobile-banner-actions')
    </div>
</div>

<div class="admin-mobile-page admin-mobile-page--user-create space-y-4 sm:space-y-6">

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
        <form action="{{ route('admin.users.store') }}" method="POST" id="adminUserCreateForm">
            @csrf

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                <!-- Name -->
                <div class="form-group">
                    <label>Nom complet <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') form-control-error @enderror"
                           placeholder="Nom et prénom" required>
                    @error('name')
                        <p class="text-xs text-[var(--ui-stat-danger)] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') form-control-error @enderror"
                           placeholder="membre@exemple.com" required>
                    @error('email')
                        <p class="text-xs text-[var(--ui-stat-danger)] mt-1">{{ $message }}</p>
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
                        <p class="text-xs text-[var(--ui-stat-danger)] mt-1">{{ $message }}</p>
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
                        <p class="text-xs text-[var(--ui-stat-danger)] mt-1">{{ $message }}</p>
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
                    <input type="text"
                           name="parrain_code"
                           id="parrainCode"
                           value="{{ old('parrain_code') }}"
                           class="form-control @error('parrain_code') form-control-error @enderror"
                           placeholder="Ex. 51234567"
                           autocomplete="off">
                    <span class="help-text">Saisissez le <strong>code de parrain</strong>. Laissez vide si aucun parrain.</span>
                    <div id="parrainFeedback" class="parrain-feedback"></div>
                    @error('parrain_code')
                        <p class="text-xs text-[var(--ui-stat-danger)] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div class="form-group md:col-span-2">
                    @include('admin.users.partials.role-select', ['selectedRole' => old('role', 'user')])
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
        </form>
    </div>

    <div class="admin-user-create-actions max-w-2xl animate-fadeInUp delay-3">
        <button type="submit" form="adminUserCreateForm" class="btn btn-primary" id="submitBtn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Créer l'utilisateur
        </button>
        <a href="{{ route('admin.users') }}" class="btn btn-outline">
            Annuler
        </a>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('userRoleSelect');
    const roleInfoText = document.getElementById('roleInfoText');
    const packageGroup = document.getElementById('packageGroup');
    const sponsorGroup = document.getElementById('sponsorGroup');

    const infoMessages = {
        user: 'Un code de parrain unique sera généré automatiquement. Il pourra bénéficier du système MLM et des commissions.',
        cashier: 'Les caissiers sont des employés. Pas de code de parrainage, pas de commissions, pas de réseau MLM. Accès POS uniquement.',
        admin: 'Administrateur avec accès complet. Un code de parrain sera généré automatiquement.',
        it_manager: 'Accès Supervision (assistance membres, publications). Un code de parrain sera généré automatiquement.'
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

    if (roleSelect) {
        updateRoleDisplay(roleSelect.value);
        roleSelect.addEventListener('change', function () {
            updateRoleDisplay(roleSelect.value);
        });
    }

    const parrainInput = document.getElementById('parrainCode');
    const parrainFeedback = document.getElementById('parrainFeedback');
    const form = document.getElementById('adminUserCreateForm');
    let parrainValide = false;

    if (parrainInput) {
        let debounceTimer;

        parrainInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const code = this.value.trim();
            parrainValide = false;

            if (code === '') {
                parrainFeedback.className = 'parrain-feedback';
                parrainFeedback.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(function () {
                verifierCodeParrain(code);
            }, 500);
        });
    }

    function verifierCodeParrain(code) {
        parrainFeedback.className = 'parrain-feedback loading';
        parrainFeedback.innerHTML = '⏳ Vérification du code...';

        const url = '{{ route("admin.users.verify-sponsor") }}?code=' + encodeURIComponent(code);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            if (data.valid) {
                parrainValide = true;
                parrainFeedback.className = 'parrain-feedback success';
                parrainFeedback.innerHTML = '✓ Parrain trouvé : <strong>' +
                    escapeHtml(data.name) + '</strong> (Code: ' + escapeHtml(data.sponsor_id) + ')';
            } else {
                parrainFeedback.className = 'parrain-feedback error';
                parrainFeedback.innerHTML = '✗ ' + escapeHtml(data.message || 'Code invalide');
            }
        })
        .catch(function () {
            parrainFeedback.className = 'parrain-feedback error';
            parrainFeedback.innerHTML = '✗ Erreur de vérification. Réessayez.';
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            const code = parrainInput ? parrainInput.value.trim() : '';
            if (code !== '' && !parrainValide) {
                e.preventDefault();
                parrainFeedback.className = 'parrain-feedback error';
                parrainFeedback.innerHTML = '✗ Veuillez attendre la vérification du code parrain ou corriger le code.';
                if (parrainInput) {
                    parrainInput.focus();
                    parrainInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
});
</script>
@endpush
@endsection