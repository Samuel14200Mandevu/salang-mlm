@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- En-tête -->
    <div>
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Mon Profil</h1>
        <p class="text-[var(--text-secondary)]">Gérez vos informations personnelles</p>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="p-4 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded-xl border border-green-200 dark:border-green-800">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 rounded-xl border border-red-200 dark:border-red-800">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Grille principale -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Colonne gauche : Avatar -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Carte Avatar -->
            <div class="bg-[var(--bg-card)] rounded-xl p-6 border border-[var(--border-color)]">
                <div class="flex flex-col items-center">
                    <!-- Avatar -->
                    <div class="relative group">
                        <div class="w-40 h-40 rounded-full overflow-hidden bg-gradient-to-br from-primary-500/30 to-primary-600/30 border-4 border-primary-500 shadow-xl shadow-primary-500/20 transition-all duration-300 group-hover:scale-105">
                            @if($user->avatar)
                                <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                                     alt="Avatar" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-5xl font-bold text-primary-600">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <!-- Badge Grade -->
                        <div class="absolute -bottom-1 -right-1 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-lg shadow-primary-500/30">
                            🏆 {{ $user->rank_name ?? 'Distributor' }}
                        </div>
                        <!-- Hover overlay -->
                        <label for="avatar_input" class="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center cursor-pointer">
                            <span class="text-white text-sm font-semibold">📸 Changer</span>
                        </label>
                        <input type="file" id="avatar_input" name="avatar" accept="image/*" class="hidden">
                    </div>

                    <h3 class="mt-4 text-xl font-bold text-[var(--text-primary)]">{{ $user->name }}</h3>
                    <p class="text-sm text-[var(--text-secondary)]">{{ $user->email }}</p>
                    <p class="text-sm text-[var(--text-secondary)]">Sponsor: <strong class="text-primary-600">{{ $user->sponsor_id }}</strong></p>

                    <!-- Actions Avatar -->
                    <div class="mt-3 flex gap-2">
                        <label for="avatar_input" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg transition-all cursor-pointer hover:scale-105">
                            📸 Changer
                        </label>
                        @if($user->avatar)
                            <button id="removeAvatarBtn" class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-500 text-sm rounded-lg transition-all hover:scale-105">
                                🗑️ Supprimer
                            </button>
                        @endif
                    </div>

                    <!-- Informations rapides -->
                    <div class="mt-4 w-full grid grid-cols-2 gap-2">
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center border border-[var(--border-color)]">
                            <p class="text-xs text-[var(--text-secondary)]">MEMBER ID</p>
                            <p class="font-bold text-[var(--text-primary)]">#{{ $user->id }}</p>
                        </div>
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center border border-[var(--border-color)]">
                            <p class="text-xs text-[var(--text-secondary)]">JOINED</p>
                            <p class="font-bold text-[var(--text-primary)]">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center border border-[var(--border-color)]">
                            <p class="text-xs text-[var(--text-secondary)]">PACKAGE</p>
                            <p class="font-bold text-primary-600">{{ $user->package?->name ?? 'Starter' }}</p>
                        </div>
                        <div class="p-3 bg-[var(--bg-secondary)] rounded-lg text-center border border-[var(--border-color)]">
                            <p class="text-xs text-[var(--text-secondary)]">STATUS</p>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $user->is_active ? '✅ Active' : '❌ Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carte Mon Parrain -->
            <div class="bg-[var(--bg-card)] rounded-xl p-6 border border-[var(--border-color)]">
                <h4 class="text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider mb-3">🤝 Mon Parrain</h4>
                <div class="flex items-center gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                        {{ substr($user->sponsor?->name ?? 'N/A', 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-[var(--text-primary)]">{{ $user->sponsor?->name ?? 'Aucun parrain' }}</p>
                        <p class="text-xs text-[var(--text-secondary)]">{{ $user->sponsor?->email ?? '--' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne droite : Formulaires -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Formulaire Informations -->
            <div class="bg-[var(--bg-card)] rounded-xl p-6 border border-[var(--border-color)]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center text-xl">📋</div>
                    <div>
                        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Informations personnelles</h3>
                        <p class="text-xs text-[var(--text-secondary)]">Mettez à jour vos informations</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Nom complet</label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Email</label>
                            <input type="email" 
                                   value="{{ $user->email }}" 
                                   disabled 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] text-[var(--text-secondary)] cursor-not-allowed opacity-70">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Téléphone</label>
                            <input type="tel" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Pays</label>
                            <input type="text" 
                                   name="country" 
                                   value="{{ old('country', $user->country) }}" 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Ville</label>
                            <input type="text" 
                                   name="city" 
                                   value="{{ old('city', $user->city) }}" 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Code postal</label>
                            <input type="text" 
                                   name="zip" 
                                   value="{{ old('zip', $user->zip ?? '') }}" 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Adresse</label>
                            <textarea name="address" 
                                      rows="2" 
                                      class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">{{ old('address', $user->address) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-lg transition-all hover:scale-105 shadow-lg shadow-primary-500/30">
                            💾 Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Formulaire Mot de passe -->
            <div class="bg-[var(--bg-card)] rounded-xl p-6 border border-[var(--border-color)]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-yellow-500/20 flex items-center justify-center text-xl">🔒</div>
                    <div>
                        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Changer le mot de passe</h3>
                        <p class="text-xs text-[var(--text-secondary)]">Sécurisez votre compte</p>
                    </div>
                </div>

                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Mot de passe actuel</label>
                            <input type="password" 
                                   name="current_password" 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition"
                                   placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Nouveau mot de passe</label>
                            <input type="password" 
                                   name="password" 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition"
                                   placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1.5">Confirmer</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold rounded-lg transition-all hover:scale-105 shadow-lg shadow-yellow-500/30">
                            🔑 Mettre à jour
                        </button>
                    </div>
                </form>
            </div>

            <!-- Suppression du compte -->
            <div class="bg-[var(--bg-card)] rounded-xl p-6 border border-red-500/20 hover:border-red-500/40 transition-all">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center text-xl">⚠️</div>
                    <div>
                        <h3 class="text-lg font-semibold text-red-500">Zone de danger</h3>
                        <p class="text-xs text-[var(--text-secondary)]">Actions irréversibles</p>
                    </div>
                </div>
                
                <p class="text-sm text-[var(--text-secondary)] mb-4">
                    Une fois votre compte supprimé, toutes ses données seront définitivement perdues.
                </p>
                
                <button 
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                    class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all hover:scale-105 shadow-lg shadow-red-500/30"
                >
                    🗑️ Supprimer mon compte
                </button>
            </div>

        </div>
    </div>

    <!-- Modal de confirmation -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="text-center">
                <div class="text-5xl mb-4">⚠️</div>
                <h2 class="text-xl font-bold text-[var(--text-primary)]">
                    Supprimer définitivement mon compte
                </h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    Cette action est <strong class="text-red-500">irréversible</strong>.<br>
                    Veuillez entrer votre mot de passe pour confirmer.
                </p>
            </div>

            <div class="mt-6">
                <input type="password" 
                       name="password" 
                       placeholder="Votre mot de passe"
                       class="w-full px-4 py-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-input)] text-[var(--text-primary)] text-center focus:ring-2 focus:ring-red-500 focus:border-transparent transition"
                       autofocus>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-center" />
            </div>

            <div class="mt-6 flex justify-center gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-2.5 bg-[var(--bg-secondary)] hover:bg-[var(--bg-tertiary)] text-[var(--text-primary)] rounded-lg transition-all border border-[var(--border-color)]">
                    Annuler
                </button>
                <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all hover:scale-105 shadow-lg shadow-red-500/30">
                    ✅ Confirmer
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Scripts -->
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
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Erreur: ' + data.message);
                            }
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
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Erreur: ' + data.message);
                            }
                        })
                        .catch(() => alert('Erreur lors de la suppression'));
                    }
                });
            }
        });
    </script>
</div>
@endsection
