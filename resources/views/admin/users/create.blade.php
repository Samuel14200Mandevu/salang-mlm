@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">➕ Ajouter un utilisateur</h1>
        <p class="text-[var(--text-secondary)] mt-1">Créez un nouveau compte utilisateur</p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input @error('name') input-error @enderror" required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input @error('email') input-error @enderror" required>
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Mot de passe *</label>
                    <input type="password" name="password" class="input @error('password') input-error @enderror" required>
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Confirmer *</label>
                    <input type="password" name="password_confirmation" class="input" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="input">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Pays</label>
                    <input type="text" name="country" value="{{ old('country') }}" class="input">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Package</label>
                    <select name="package_id" class="input">
                        <option value="">Aucun</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} (${{ number_format($package->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Parrain (Sponsor)</label>
                    <select name="sponsor_id" class="input">
                        <option value="">Aucun</option>
                        @foreach($sponsors as $sponsor)
                            <option value="{{ $sponsor->id }}" {{ old('sponsor_id') == $sponsor->id ? 'selected' : '' }}>
                                {{ $sponsor->name }} ({{ $sponsor->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Rôle</label>
                    <select name="is_admin" class="input">
                        <option value="0" {{ old('is_admin') == 0 ? 'selected' : '' }}>Utilisateur</option>
                        <option value="1" {{ old('is_admin') == 1 ? 'selected' : '' }}>Administrateur</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Statut</label>
                    <select name="is_active" class="input">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                    <textarea name="address" rows="2" class="input">{{ old('address') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer l'utilisateur
                </button>
                <a href="{{ route('admin.users') }}" class="btn btn-outline">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection