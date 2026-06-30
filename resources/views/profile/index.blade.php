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
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">👤 Mon Profil</h1>
        <p class="text-[var(--text-secondary)] mt-1">Gérez vos informations personnelles</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 animate-fadeIn">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 animate-fadeIn">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Avatar -->
        <div class="lg:col-span-1 space-y-4">
            <div class="card animate-fadeInLeft">
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
                            <span class="text-white text-sm font-semibold">📸 Changer</span>
                        </label>
                        <input type="file" id="avatar_input" name="avatar" accept="image/*" class="hidden">
                    </div>

                    <h3 class="mt-4 text-xl font-bold text-[var(--text-primary)]">{{ $user->name }}</h3>
                    <p class="text-sm text-[var(--text-secondary)]">{{ $user->email }}</p>
                    <p class="text-sm text-[var(--text-secondary)]">
                        Sponsor: <strong class="text-primary-500">{{ $user->sponsor?->name ?? 'Aucun' }}</strong>
                    </p>

                    <div class="mt-3 flex gap-2">
                        <label for="avatar_input" class="btn btn-primary btn-sm cursor-pointer">
                            📸 Changer
                        </label>
                        @if($user->avatar)
                            <button id="removeAvatarBtn" class="btn btn-danger btn-sm">
                                🗑️ Supprimer
                            </button>
                        @endif
                    </div>

                    <div class="mt-4 w-full grid grid-cols-2 gap-2">
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                            <p class="text-xs text-[var(--text-secondary)]">ID</p>
                            <p class="font-bold text-[var(--text-primary)]">#{{ $user->id }}</p>
                        </div>
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                            <p class="text-xs text-[var(--text-secondary)]">Inscrit</p>
                            <p class="font-bold text-[var(--text-primary)]">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                            <p class="text-xs text-[var(--text-secondary)]">Package</p>
                            <p class="font-bold text-primary-500">{{ $user->package?->name ?? 'Starter' }}</p>
                        </div>
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center">
                            <p class="text-xs text-[var(--text-secondary)]">Statut</p>
                            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $user->is_active ? '✅ Actif' : '❌ Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parrain -->
            <div class="card animate-fadeInLeft delay-2">
                <h4 class="text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider mb-3">🤝 Mon Parrain</h4>
                <div class="flex items-center gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <div class="avatar avatar-md avatar-info">
                        {{ substr($user->sponsor?->name ?? 'N/A', 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-[var(--text-primary)]">{{ $user->sponsor?->name ?? 'Aucun parrain' }}</p>
                        <p class="text-xs text-[var(--text-secondary)]">{{ $user->sponsor?->email ?? '--' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaires -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Informations -->
            <div class="card animate-fadeInRight">
                <div class="flex items-center gap-3 mb-4">
                    <div class="stat-icon stat-icon-primary">📋</div>
                    <div>
                        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Informations personnelles</h3>
                        <p class="text-xs text-[var(--text-secondary)]">Mettez à jour vos informations</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Nom complet</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Email</label>
                            <input type="email" value="{{ $user->email }}" class="input opacity-70 cursor-not-allowed" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Pays</label>
                            <input type="text" name="country" value="{{ old('country', $user->country) }}" class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Ville</label>
                            <input type="text" name="city" value="{{ old('city', $user->city) }}" class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Code postal</label>
                            <input type="text" name="zip" value="{{ old('zip', $user->zip) }}" class="input">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                            <textarea name="address" rows="2" class="input">{{ old('address', $user->address) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            💾 Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Mot de passe -->
            <div class="card animate-fadeInRight delay-2">
                <div class="flex items-center gap-3 mb-4">
                    <div class="stat-icon stat-icon-warning">🔒</div>
                    <div>
                        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Changer le mot de passe</h3>
                        <p class="text-xs text-[var(--text-secondary)]">Sécurisez votre compte</p>
                    </div>
                </div>

                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="input" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Nouveau mot de passe</label>
                            <input type="password" name="password" class="input" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Confirmer</label>
                            <input type="password" name="password_confirmation" class="input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="btn btn-warning">
                            🔑 Mettre à jour
                        </button>
                    </div>
                </form>
            </div>

            <!-- Zone de danger -->
            <div class="card danger-zone animate-fadeInRight delay-3">
                <div class="flex items-center gap-3 mb-4">
                    <div class="stat-icon stat-icon-danger">⚠️</div>
                    <div>
                        <h3 class="text-lg font-semibold text-red-500">Zone de danger</h3>
                        <p class="text-xs text-[var(--text-secondary)]">Actions irréversibles</p>
                    </div>
                </div>

                <p class="text-sm text-[var(--text-secondary)] mb-4">
                    Une fois votre compte supprimé, toutes ses données seront définitivement perdues.
                </p>

                <button x-data="" 
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        class="btn btn-danger">
                    🗑️ Supprimer mon compte
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf @method('delete')

            <div class="text-center">
                <div class="text-5xl mb-4">⚠️</div>
                <h2 class="text-xl font-bold text-[var(--text-primary)]">Supprimer définitivement mon compte</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    Cette action est <strong class="text-red-500">irréversible</strong>.<br>
                    Veuillez entrer votre mot de passe pour confirmer.
                </p>
            </div>

            <div class="mt-6">
                <input type="password" 
                       name="password" 
                       placeholder="Votre mot de passe"
                       class="input text-center"
                       autofocus>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-center" />
            </div>

            <div class="mt-6 flex justify-center gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="btn btn-outline">
                    Annuler
                </button>
                <button type="submit" class="btn btn-danger">
                    ✅ Confirmer
                </button>
            </div>
        </form>
    </x-modal>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar_input');
    const removeBtn = document.getElementById('removeAvatarBtn');

    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('avatar', this.files[0]);

                fetch('{{ route('profile.update-avatar') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Erreur: ' + data.message);
                })
                .catch(() => alert('Erreur lors de l\'upload'));
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Erreur: ' + data.message);
                })
                .catch(() => alert('Erreur lors de la suppression'));
            }
        });
    }
});
</script>
@endpush
@endsection