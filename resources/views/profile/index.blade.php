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
    .profile-avatar-container .avatar-overlay span {
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .danger-zone {
        border: 1px solid rgba(239,68,68,0.2);
        background: rgba(239,68,68,0.03);
        transition: all 0.3s ease;
    }
    .danger-zone:hover {
        border-color: rgba(239,68,68,0.4);
        background: rgba(239,68,68,0.06);
    }
    
    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon-primary { background: rgba(90, 182, 56, 0.12); color: var(--primary-500); }
    .stat-icon-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .stat-icon-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .stat-icon-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
    }
    .avatar-sm { width: 2rem; height: 2rem; font-size: 0.75rem; }
    .avatar-md { width: 2.5rem; height: 2.5rem; font-size: 0.875rem; }
    .avatar-lg { width: 3.5rem; height: 3.5rem; font-size: 1.25rem; }
    .avatar-xl { width: 6rem; height: 6rem; font-size: 2rem; }
    .avatar-gradient {
        background: var(--gradient-primary);
        color: white;
    }
    .avatar-info {
        background: var(--gradient-info);
        color: white;
    }
    .avatar-ring {
        border: 3px solid var(--primary-500);
        box-shadow: 0 0 0 4px rgba(90, 182, 56, 0.15);
    }
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
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
    .input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
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
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
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
    .btn-warning {
        background: var(--gradient-warning);
        color: white;
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    }
    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
        box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
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
    .cursor-pointer {
        cursor: pointer;
    }
    .cursor-not-allowed {
        cursor: not-allowed;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .animate-fadeInLeft { animation: fadeInLeft 0.6s ease forwards; }
    .animate-fadeInRight { animation: fadeInRight 0.6s ease forwards; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    
    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .avatar-xl { width: 4.5rem; height: 4.5rem; font-size: 1.5rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.875rem; }
        .input { font-size: 0.813rem; padding: 0.5rem 0.75rem; }
        .stat-icon { width: 2rem; height: 2rem; }
        .profile-grid { grid-template-columns: 1fr !important; }
    }
    
    @media (max-width: 480px) {
        .card { padding: 0.75rem; }
        .avatar-xl { width: 4rem; height: 4rem; font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Mon Profil</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gérez vos informations personnelles</p>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
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

    <div class="profile-grid grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Avatar Section -->
        <div class="lg:col-span-1 space-y-3 sm:space-y-4">
            <div class="card animate-fadeInLeft">
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
                        <input type="file" id="avatar_input" name="avatar" accept="image/*" class="hidden">
                    </div>

                    <h3 class="mt-3 sm:mt-4 text-lg sm:text-xl font-bold text-[var(--text-primary)]">{{ $user->name }}</h3>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">{{ $user->email }}</p>
                    
                    <!-- Parrain Information -->
                    @php
                        $parrain = App\Models\User::find($user->parrain_id);
                    @endphp
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

                    <!-- Stats -->
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

            <!-- Parrain Card -->
            <div class="card animate-fadeInLeft delay-2">
                <h4 class="text-xs sm:text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider mb-2 sm:mb-3">
                    Mon Parrain
                </h4>
                @php
                    $parrain = App\Models\User::find($user->parrain_id);
                @endphp
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
                @php
                    $filleulsCount = App\Models\User::where('parrain_id', Auth::user()->id)->count();
                @endphp
                <div class="grid grid-cols-2 gap-2">
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
            <div class="card animate-fadeInRight">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
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
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nom complet</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input text-sm sm:text-base" required>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Email</label>
                            <input type="email" value="{{ $user->email }}" class="input text-sm sm:text-base opacity-70 cursor-not-allowed" disabled>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
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
                            <input type="text" name="zip" value="{{ old('zip', $user->zip ?? '') }}" class="input text-sm sm:text-base">
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

            <!-- Change Password -->
            <div class="card animate-fadeInRight delay-2">
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

            <!-- Danger Zone -->
            <div class="card danger-zone animate-fadeInRight delay-3">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-danger">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-red-500">Zone de danger</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Actions irréversibles</p>
                    </div>
                </div>

                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mb-3 sm:mb-4">
                    Une fois votre compte supprimé, toutes les données associées seront définitivement perdues.
                </p>

                <button x-data="" 
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        class="btn btn-danger text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer le compte
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4 sm:p-6">
            @csrf @method('delete')

            <div class="text-center">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-red-500 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h2 class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">Supprimer le compte</h2>
                <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-[var(--text-secondary)]">
                    Cette action est <strong class="text-red-500">irréversible</strong>.<br>
                    Veuillez entrer votre mot de passe pour confirmer.
                </p>
            </div>

            <div class="mt-4 sm:mt-6">
                <input type="password" 
                       name="password" 
                       placeholder="Votre mot de passe"
                       class="input text-center text-sm sm:text-base"
                       autofocus>
                @error('password', 'userDeletion')
                    <p class="text-xs text-red-500 text-center mt-2">{{ $message }}</p>
                @enderror
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