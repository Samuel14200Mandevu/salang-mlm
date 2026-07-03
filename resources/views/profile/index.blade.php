@extends('layouts.app')

@push('styles')
<style>
    .profile-avatar-container {
        position: relative;
        display: inline-block;
    }
    .profile-avatar-container .avatar-overlay {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        cursor: pointer;
    }
    .profile-avatar-container:hover .avatar-overlay {
        opacity: 1;
    }
    .danger-zone {
        border: 1px solid rgba(239,68,68,0.2);
        background: rgba(239,68,68,0.03);
    }
    .danger-zone:hover {
        border-color: rgba(239,68,68,0.4);
    }
    
    @media (max-width: 640px) {
        .card { padding: 0.75rem; }
        .avatar-xl { width: 4rem; height: 4rem; font-size: 1.2rem; }
        .text-5xl { font-size: 2.5rem; }
        .text-2xl { font-size: 1.25rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Profil</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gerez vos informations personnelles</p>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Avatar -->
        <div class="lg:col-span-1 space-y-3 sm:space-y-4">
            <div class="card animate-fadeInLeft p-3 sm:p-4 md:p-6">
                <div class="flex flex-col items-center">
                    <div class="profile-avatar-container">
                        <div class="avatar avatar-xl avatar-gradient avatar-ring">
                            @if($user->avatar)
                                <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="Avatar">
                            @else
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            @endif
                        </div>
                        <label for="avatar_input" class="avatar-overlay">
                            <span class="text-white text-xs sm:text-sm font-semibold">Changer</span>
                        </label>
                        <input type="file" id="avatar_input" name="avatar" accept="image/*" class="hidden">
                    </div>

                    <h3 class="mt-3 sm:mt-4 text-lg sm:text-xl font-bold text-[var(--text-primary)]">{{ $user->name }}</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">{{ $user->email }}</p>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                        Sponsor: <strong class="text-primary-500">{{ $user->sponsor?->name ?? 'Aucun' }}</strong>
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

                    <div class="mt-3 sm:mt-4 w-full grid grid-cols-2 gap-1.5 sm:gap-2">
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

            <!-- Parrain -->
            <div class="card animate-fadeInLeft delay-2 p-3 sm:p-4 md:p-6">
                <h4 class="text-xs sm:text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider mb-2 sm:mb-3">Mon Parrain</h4>
                <div class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <div class="avatar avatar-md avatar-info">
                        {{ substr($user->sponsor?->name ?? 'N/A', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base truncate">{{ $user->sponsor?->name ?? 'Aucun parrain' }}</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">{{ $user->sponsor?->email ?? '--' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaires -->
        <div class="lg:col-span-2 space-y-3 sm:space-y-4">
            <!-- Informations -->
            <div class="card animate-fadeInRight p-3 sm:p-4 md:p-6">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Informations personnelles</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mettez a jour vos informations</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nom complet</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input text-sm sm:text-base" required>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Email</label>
                            <input type="email" value="{{ $user->email }}" class="input text-sm sm:text-base opacity-70 cursor-not-allowed" disabled>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Telephone</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="input text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Pays</label>
                            <input type="text" name="country" value="{{ old('country', $user->country) }}" class="input text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Ville</label>
                            <input type="text" name="city" value="{{ old('city', $user->city) }}" class="input text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Code postal</label>
                            <input type="text" name="zip" value="{{ old('zip', $user->zip) }}" class="input text-sm sm:text-base">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                            <textarea name="address" rows="2" class="input text-sm sm:text-base">{{ old('address', $user->address) }}</textarea>
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

            <!-- Mot de passe -->
            <div class="card animate-fadeInRight delay-2 p-3 sm:p-4 md:p-6">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-warning">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Changer le mot de passe</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Securisez votre compte</p>
                    </div>
                </div>

                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="input text-sm sm:text-base" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nouveau mot de passe</label>
                            <input type="password" name="password" class="input text-sm sm:text-base" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Confirmer</label>
                            <input type="password" name="password_confirmation" class="input text-sm sm:text-base" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="mt-3 sm:mt-4 flex justify-end">
                        <button type="submit" class="btn btn-warning w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Mettre a jour
                        </button>
                    </div>
                </form>
            </div>

            <!-- Zone de danger -->
            <div class="card danger-zone animate-fadeInRight delay-3 p-3 sm:p-4 md:p-6">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-danger">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-red-500">Zone de danger</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Actions irreversibles</p>
                    </div>
                </div>

                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mb-3 sm:mb-4">
                    Une fois votre compte supprime, toutes ses donnees seront definitivement perdues.
                </p>

                <button x-data="" 
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        class="btn btn-danger text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer mon compte
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4 sm:p-6">
            @csrf @method('delete')

            <div class="text-center">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-red-500 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h2 class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">Supprimer definitivement mon compte</h2>
                <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-[var(--text-secondary)]">
                    Cette action est <strong class="text-red-500">irreversible</strong>.<br>
                    Veuillez entrer votre mot de passe pour confirmer.
                </p>
            </div>

            <div class="mt-4 sm:mt-6">
                <input type="password" 
                       name="password" 
                       placeholder="Votre mot de passe"
                       class="input text-center text-sm sm:text-base"
                       autofocus>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-center" />
            </div>

            <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-center gap-2 sm:gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Annuler
                </button>
                <button type="submit" class="btn btn-danger w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Confirmer
                </button>
            </div>
        </form>
    </x-modal>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
                    if (data.success) location.reload();
                    else alert('Erreur: ' + data.message);
                })
                ['catch'](function() { alert('Erreur lors de l\'upload'); });
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (confirm('Voulez-vous vraiment supprimer votre photo de profil ?')) {
                fetch('{{ route('profile.delete-avatar') }}', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) location.reload();
                    else alert('Erreur: ' + data.message);
                })
                ['catch'](function() { alert('Erreur lors de la suppression'); });
            }
        });
    }
});
</script>
@endpush
@endsection