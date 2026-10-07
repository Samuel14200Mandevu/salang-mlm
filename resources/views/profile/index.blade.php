@extends('layouts.app')

@section('title', 'Profil')



@section('content')
@php
    $parrain = $user->parrain_id ? App\Models\User::find($user->parrain_id) : null;
    $filleulsCount = App\Models\User::where('parrain_id', $user->id)->count();
    $openEditMobile = $errors->any() || request()->boolean('edit');
@endphp
<div class="profile-page profile-page--account space-y-4 sm:space-y-6 {{ $openEditMobile ? 'profile-page--edit' : '' }}" id="profilePageRoot">

    <input type="file" id="avatar_input" name="avatar" accept="image/*" class="hidden" aria-hidden="true" tabindex="-1">

    <div class="member-page-intro profile-page-intro-desktop animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Profil</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gérez vos informations personnelles</p>
    </div>

    @if(session('success'))
        <div class="profile-flash profile-flash--mobile p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="profile-flash profile-flash--mobile p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="profile-flash profile-flash--mobile p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('profile.partials.account-mobile-hub')

    <div class="profile-edit-shell">
        <div class="profile-subpage profile-subpage--profile-edit md:hidden">
            @include('profile.partials.account-subpage-header', [
                'title' => 'Informations personnelles',
                'sub' => 'Profil · coordonnées · sécurité',
                'backUrl' => route('profile.index'),
            ])
        </div>

        <div class="profile-subpage-body profile-edit-shell__body">
    <div class="profile-desktop-shell profile-grid grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6" id="profileEditPanel">

        <div class="profile-sidebar-col lg:col-span-1 space-y-3 sm:space-y-4">
            <div class="card profile-avatar-card animate-fadeInLeft">
                <div class="flex flex-col items-center">
                    <!-- Avatar -->
                    <div class="profile-avatar-container">
                        <div class="avatar avatar-xl avatar-gradient avatar-ring">
                            @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
                                <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="Avatar">
                            @else
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            @endif
                        </div>
                        <label for="avatar_input" class="avatar-overlay">
                            <span>Changer</span>
                        </label>
                    </div>

                    <h3 class="mt-3 sm:mt-4 text-lg sm:text-xl font-bold text-[var(--text-primary)]">{{ $user->name }}</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">{{ $user->email }}</p>
                    
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                        Parrain: 
                        <strong class="text-primary-500">{{ $parrain?->name ?? 'Aucun' }}</strong>
                    </p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                        Code de parrain: <span class="font-mono text-primary-500 font-semibold">{{ $user->sponsor_id ?? 'N/A' }}</span>
                    </p>

                    <div class="mt-2 sm:mt-3 flex flex-wrap gap-1.5 sm:gap-2">
                        <label for="avatar_input" class="btn btn-primary btn-sm cursor-pointer text-xs sm:text-sm">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Changer
                        </label>
                        @if($user->avatar)
                            <button id="removeAvatarBtn" class="btn btn-danger btn-sm text-xs sm:text-sm">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Supprimer
                            </button>
                        @endif
                    </div>

                    <div class="profile-avatar-stats mt-3 sm:mt-4 w-full grid grid-cols-2 gap-1.5 sm:gap-2">
                        <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">ID</p>
                            <p class="font-bold text-[var(--text-primary)] text-sm sm:text-base">#{{ $user->id }}</p>
                        </div>
                        <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Inscrit</p>
                            <p class="font-bold text-[var(--text-primary)] text-xs sm:text-sm">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Package</p>
                            <p class="font-bold text-primary-500 text-xs sm:text-sm">{{ $user->package?->name ?? 'Starter' }}</p>
                        </div>
                        <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Statut</p>
                            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                {{ $user->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parrain Card -->
            <div class="card animate-fadeInLeft delay-2">
                <h4 class="text-xs sm:text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider mb-2 sm:mb-3">
                    Mon Parrain
                </h4>
                <div class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <div class="avatar avatar-md avatar-info">
                        {{ $parrain ? strtoupper(substr($parrain->name, 0, 1)) : 'N/A' }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base truncate">
                            {{ $parrain?->name ?? 'Aucun parrain' }}
                        </p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">
                            {{ $parrain?->email ?? '--' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filleuls Summary -->
            <div class="card animate-fadeInLeft delay-3">
                <h4 class="text-xs sm:text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider mb-2 sm:mb-3">
                    Mon Réseau
                </h4>
                <div class="profile-stats stats-grid grid grid-cols-2 gap-2">
                    <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Filleuls</p>
                        <p class="font-bold text-primary-500 text-lg sm:text-xl">{{ $filleulsCount }}</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mon Code</p>
                        <p class="font-bold text-primary-500 text-xs sm:text-sm font-mono truncate">{{ Auth::user()->sponsor_id }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forms -->
        <div class="lg:col-span-2 space-y-3 sm:space-y-4">
            
            <!-- Personal Information -->
            <div class="card animate-fadeInRight profile-personal-info-card">
                <div class="hidden md:flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Informations Personnelles</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mettez à jour vos informations</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        <!-- Nom -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nom complet</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input text-sm sm:text-base" required>
                        </div>
                        
                        <!-- Email (non modifiable) -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Email</label>
                            <input type="email" value="{{ $user->email }}" class="input text-sm sm:text-base opacity-70 cursor-not-allowed" disabled>
                        </div>
                        
                        <!-- Téléphone -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="input text-sm sm:text-base">
                        </div>
                        
                        <!-- Pays -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Pays</label>
                            <input type="text" name="country" value="{{ old('country', $user->country) }}" class="input text-sm sm:text-base">
                        </div>
                        
                        <!-- Ville -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Ville</label>
                            <input type="text" name="city" value="{{ old('city', $user->city) }}" class="input text-sm sm:text-base">
                        </div>
                        
                        <!-- Adresse -->
                        <div class="md:col-span-2">
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                            <textarea name="address" rows="2" class="input text-sm sm:text-base">{{ old('address', $user->address) }}</textarea>
                        </div>
                        
                        <!-- Date de naissance -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Date de naissance</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}" class="input text-sm sm:text-base">
                        </div>
                        
                        <!-- Sexe -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Sexe</label>
                            <select name="gender" class="input text-sm sm:text-base">
                                <option value="">Sélectionner</option>
                                <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Masculin</option>
                                <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Féminin</option>
                            </select>
                        </div>
                        
                        <!-- Profession -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Profession</label>
                            <input type="text" name="profession" value="{{ old('profession', $user->profession) }}" class="input text-sm sm:text-base">
                        </div>
                        
                        <!-- Numéro d'identité -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Numéro d'identité</label>
                            <input type="text" name="identity_number" value="{{ old('identity_number', $user->identity_number) }}" class="input text-sm sm:text-base">
                        </div>
                    </div>

                    <div class="mt-3 sm:mt-4 flex justify-end">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Coordonnées Bancaires -->
            <div class="card animate-fadeInRight delay-2">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-purple">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Coordonnées Bancaires</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Pour le paiement des bonus</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nom de la banque</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}" class="input text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Numéro de compte</label>
                            <input type="text" name="account_number" value="{{ old('account_number', $user->account_number) }}" class="input text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Titulaire du compte</label>
                            <input type="text" name="account_holder" value="{{ old('account_holder', $user->account_holder) }}" class="input text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Mobile Money</label>
                            <input type="text" name="mobile_money" value="{{ old('mobile_money', $user->mobile_money) }}" class="input text-sm sm:text-base">
                        </div>
                    </div>

                    <div class="mt-3 sm:mt-4 flex justify-end">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card animate-fadeInRight delay-4">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-warning">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Changer le mot de passe</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Sécurisez votre compte</p>
                    </div>
                </div>

                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="input text-sm sm:text-base" placeholder="Entrez votre mot de passe actuel" required>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nouveau mot de passe</label>
                            <input type="password" name="password" class="input text-sm sm:text-base" placeholder="Entrez un nouveau mot de passe" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" class="input text-sm sm:text-base" placeholder="Confirmez le nouveau mot de passe" required>
                        </div>
                    </div>

                    <div class="mt-3 sm:mt-4 flex justify-end">
                        <button type="submit" class="btn btn-warning w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var profileRoot = document.getElementById('profilePageRoot');

    function setProfileEditMode(on) {
        if (!profileRoot) {
            return;
        }
        profileRoot.classList.toggle('profile-page--edit', on);
        if (on) {
            var panel = document.getElementById('profileEditPanel');
            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    document.querySelectorAll('[data-profile-open-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setProfileEditMode(true);
        });
    });

    document.querySelectorAll('[data-profile-close-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setProfileEditMode(false);
        });
    });

    if (window.location.hash === '#edit') {
        setProfileEditMode(true);
    }

    var avatarInput = document.getElementById('avatar_input');
    var removeBtn = document.getElementById('removeAvatarBtn');

    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                var formData = new FormData();
                formData.append('avatar', this.files[0]);

                fetch('{{ route('profile.update-avatar') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(function() { alert('Upload error'); });
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (confirm('Delete your profile picture?')) {
                fetch('{{ route('profile.delete-avatar') }}', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(function() { alert('Delete error'); });
            }
        });
    }
});
</script>
@endpush
@endsection