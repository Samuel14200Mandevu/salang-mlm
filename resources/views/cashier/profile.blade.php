{{-- resources/views/cashier/profile.blade.php --}}
@extends('cashier.layouts.app')

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
        border: 1px solid rgba(179, 42, 42, 0.15);
        background: rgba(179, 42, 42, 0.03);
        transition: border-color 0.3s ease, background 0.3s ease;
    }
    .danger-zone:hover {
        border-color: rgba(179, 42, 42, 0.30);
        background: rgba(179, 42, 42, 0.05);
    }

    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon-primary { background: rgba(15, 43, 79, 0.10); color: var(--primary); }
    .stat-icon-warning { background: rgba(245, 158, 11, 0.10); color: #d97706; }
    .stat-icon-danger { background: rgba(179, 42, 42, 0.10); color: #b32a2a; }
    .stat-icon-info { background: rgba(59, 130, 246, 0.10); color: #2563eb; }

    .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }
    .badge-danger {
        background: rgba(179, 42, 42, 0.12);
        color: #b32a2a;
    }
    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
    }
    .badge-cashier {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
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
        background: var(--primary);
        color: white;
    }
    .avatar-info {
        background: #2563eb;
        color: white;
    }
    .avatar-ring {
        border: 3px solid var(--primary);
        box-shadow: 0 0 0 4px rgba(15, 43, 79, 0.10);
    }
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        background: var(--bg-primary);
        color: var(--text-primary);
        transition: border-color 0.2s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary);
    }
    .input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: var(--primary-hover, #091E3B);
    }
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-warning {
        background: #d97706;
        color: #FFFFFF;
    }
    .btn-warning:hover {
        background: #b45309;
    }

    .btn-danger {
        background: #b32a2a;
        color: #FFFFFF;
    }
    .btn-danger:hover {
        background: #8f2121;
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.15rem 0.5rem;
        font-size: 0.65rem;
    }

    .cursor-pointer { cursor: pointer; }
    .cursor-not-allowed { cursor: not-allowed; }
    .hidden { display: none; }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-md, 8px);
        padding: 1.75rem;
        max-width: 450px;
        width: 90%;
        border: 1px solid var(--border-color);
        transform: scale(0.95);
        transition: transform 0.25s ease;
    }
    .modal-overlay.active .modal-box {
        transform: scale(1);
    }
    .modal-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary);
        text-align: center;
        margin-bottom: 0.5rem;
    }
    .modal-text {
        font-size: 0.875rem;
        color: var(--text-secondary);
        text-align: center;
        margin-bottom: 1.25rem;
        line-height: 1.6;
    }
    .modal-text .text-danger {
        color: #b32a2a;
    }
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .modal-actions .btn {
        min-width: 100px;
        justify-content: center;
    }

    .alert-success {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md, 8px);
        background: rgba(34, 197, 94, 0.06);
        border: 1px solid rgba(34, 197, 94, 0.15);
        color: #16a34a;
        font-size: 0.875rem;
    }

    .alert-error {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md, 8px);
        background: rgba(179, 42, 42, 0.06);
        border: 1px solid rgba(179, 42, 42, 0.15);
        color: #b32a2a;
        font-size: 0.875rem;
    }

    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .avatar-xl { width: 4.5rem; height: 4.5rem; font-size: 1.5rem; }
        .btn { font-size: 0.75rem; padding: 0.35rem 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .input { font-size: 0.813rem; padding: 0.4rem 0.6rem; }
        .stat-icon { width: 2rem; height: 2rem; }
        .stat-icon svg { width: 1.25rem; height: 1.25rem; }
        .profile-grid { grid-template-columns: 1fr !important; }
        .modal-box { padding: 1.25rem; }
        .modal-actions { flex-direction: column; }
        .modal-actions .btn { width: 100%; }
    }

    @media (max-width: 480px) {
        .card { padding: 0.75rem; }
        .avatar-xl { width: 4rem; height: 4rem; font-size: 1.25rem; }
    }
</style>
@endpush

@section('title', 'Mon Profil - Caissier')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div>
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
            Mon Profil
        </h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Gérez vos informations personnelles de caissier
        </p>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert-success">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-grid grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- CARTE PROFIL --}}
        <div class="lg:col-span-1 space-y-3 sm:space-y-4">
            <div class="card">
                <div class="flex flex-col items-center">
                    {{-- Avatar --}}
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

                    {{-- Badge Caissier --}}
                    <span class="mt-2 badge badge-cashier">Caissier</span>

                    {{-- Stats --}}
                    <div class="mt-3 sm:mt-4 w-full grid grid-cols-2 gap-1.5 sm:gap-2">
                        <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-md text-center">
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">ID</p>
                            <p class="font-bold text-[var(--text-primary)] text-sm sm:text-base">#{{ $user->id }}</p>
                        </div>
                        <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-md text-center">
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Crée</p>
                            <p class="font-bold text-[var(--text-primary)] text-xs sm:text-sm">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-md text-center">
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Rôle</p>
                            <p class="font-bold text-[var(--primary)] text-xs sm:text-sm">Caissier</p>
                        </div>
                        <div class="p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-md text-center">
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Statut</p>
                            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                {{ $user->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions rapides --}}
                    <div class="mt-3 sm:mt-4 w-full flex flex-wrap gap-1.5 justify-center">
                        <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm text-xs sm:text-sm">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nouvelle vente
                        </a>
                        <a href="{{ route('cashier.dashboard') }}" class="btn btn-outline btn-sm text-xs sm:text-sm">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORMULAIRES --}}
        <div class="lg:col-span-2 space-y-3 sm:space-y-4">

            {{-- INFORMATIONS PERSONNELLES --}}
            <div class="card">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Informations Personnelles</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mettez à jour vos informations de caissier</p>
                    </div>
                </div>

                <form action="{{ route('cashier.profile.update') }}" method="POST">
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
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                            <input type="text" name="address" value="{{ old('address', $user->address) }}" class="input text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Ville</label>
                            <input type="text" name="city" value="{{ old('city', $user->city) }}" class="input text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Pays</label>
                            <input type="text" name="country" value="{{ old('country', $user->country) }}" class="input text-sm sm:text-base">
                        </div>
                    </div>

                    <div class="mt-3 sm:mt-4 flex justify-end">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            {{-- CHANGER LE MOT DE PASSE --}}
            <div class="card">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-warning">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Changer le mot de passe</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Sécurisez votre compte caissier</p>
                    </div>
                </div>

                <form action="{{ route('cashier.profile.update-password') }}" method="POST">
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
                        <button type="submit" class="btn btn-warning w-full sm:w-auto text-sm sm:text-base">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>

            {{-- ZONE DE DANGER --}}
            <div class="card danger-zone">
                <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                    <div class="stat-icon stat-icon-danger">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[#b32a2a]">Zone de danger</h3>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Actions irréversibles</p>
                    </div>
                </div>

                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mb-3 sm:mb-4">
                    Une fois votre compte caissier supprimé, toutes les données associées seront définitivement perdues.
                </p>

                <button onclick="openDeleteModal()" class="btn btn-danger text-sm sm:text-base">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer le compte
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL SUPPRESSION --}}
    <div id="deleteModal" class="modal-overlay" onclick="if(event.target === this) closeDeleteModal()">
        <div class="modal-box">
            <div class="text-center">
                <svg class="w-12 h-12 mx-auto text-[#b32a2a] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h2 class="modal-title">Supprimer le compte</h2>
                <p class="modal-text">
                    Cette action est <strong class="text-danger">irréversible</strong>.<br>
                    Veuillez entrer votre mot de passe pour confirmer.
                </p>
            </div>

            <form method="post" action="{{ route('cashier.profile.destroy') }}">
                @csrf @method('delete')

                <div class="mb-4">
                    <input type="password"
                           name="password"
                           id="deletePassword"
                           placeholder="Votre mot de passe"
                           class="input text-center text-sm sm:text-base"
                           required>
                    @error('password', 'userDeletion')
                        <p class="text-xs text-[#b32a2a] text-center mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-outline">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
//  MODAL SUPPRESSION
// ============================================================
function openDeleteModal() {
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(function() {
        document.getElementById('deletePassword').focus();
    }, 200);
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('deletePassword').value = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

// ============================================================
//  AVATAR UPLOAD
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar_input');

    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('avatar', this.files[0]);

                fetch('{{ route('cashier.profile.update-avatar') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(function() { alert('Erreur lors du téléchargement'); });
            }
        });
    }
});
</script>
@endpush
@endsection