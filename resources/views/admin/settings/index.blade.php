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

.setting-card {
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
    cursor: pointer;
    text-decoration: none !important;
    display: block;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    background: var(--bg-card);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.setting-card:hover {
    border-color: var(--primary-blue);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.setting-card:active {
    transform: scale(0.98);
}

.setting-card .card-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.setting-card .card-icon-primary {
    background: var(--primary-blue-bg);
    color: var(--primary-blue);
}
.setting-card .card-icon-success {
    background: rgba(28, 126, 74, 0.08);
    color: #1C7E4A;
}
.setting-card .card-icon-info {
    background: rgba(6, 95, 156, 0.08);
    color: #065F9C;
}
.setting-card .card-icon-warning {
    background: rgba(181, 71, 8, 0.08);
    color: #B54708;
}

.setting-card h3 {
    font-size: 0.938rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.125rem;
}
.setting-card p {
    font-size: 0.813rem;
    color: var(--text-secondary);
    margin: 0;
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.maintenance-card {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    background: var(--bg-card);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.maintenance-card h3 {
    font-size: 0.938rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
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

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }
.delay-4 { animation-delay: 0.2s; }

@media (max-width: 640px) {
    .setting-card {
        padding: 0.875rem;
    }
    .setting-card .card-icon {
        width: 2.25rem;
        height: 2.25rem;
    }
    .setting-card .card-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }
    .setting-card h3 {
        font-size: 0.813rem;
    }
    .setting-card p {
        font-size: 0.7rem;
    }
    .card {
        padding: 0.875rem;
    }
    .maintenance-card {
        padding: 0.875rem;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Paramètres</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Configuration de la plateforme</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Settings Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">

        <!-- General Settings -->
        <a href="{{ route('admin.settings') }}" class="setting-card">
            <div class="flex items-center gap-3">
                <div class="card-icon card-icon-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3>Général</h3>
                    <p>Nom du site, fuseau horaire, langue</p>
                </div>
            </div>
        </a>

        <!-- Payment Settings -->
        <a href="{{ route('admin.settings.payment') }}" class="setting-card">
            <div class="flex items-center gap-3">
                <div class="card-icon card-icon-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3>Paiements</h3>
                    <p>Passerelles et frais</p>
                </div>
            </div>
        </a>

        <!-- Commission Settings -->
        <a href="{{ route('admin.settings.commission') }}" class="setting-card">
            <div class="flex items-center gap-3">
                <div class="card-icon card-icon-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3>Commissions</h3>
                    <p>Taux et règles</p>
                </div>
            </div>
        </a>
    </div>

    <!-- General Settings Form -->
    <div class="card animate-fadeInUp delay-2 p-3 sm:p-4">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 border-b border-[var(--border-color)] pb-2">
            Paramètres généraux
        </h3>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <!-- Site Name -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                        Nom du site <span class="required">*</span>
                    </label>
                    <input type="text" name="site_name"
                           value="{{ old('site_name', config('app.name')) }}"
                           class="form-control @error('site_name') form-control-error @enderror"
                           required>
                    @error('site_name')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Site URL -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                        URL du site <span class="required">*</span>
                    </label>
                    <input type="url" name="site_url"
                           value="{{ old('site_url', config('app.url')) }}"
                           class="form-control @error('site_url') form-control-error @enderror"
                           required>
                    @error('site_url')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Timezone -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                        Fuseau horaire <span class="required">*</span>
                    </label>
                    <select name="timezone" class="form-control" required>
                        <option value="UTC" {{ config('app.timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="Africa/Lagos" {{ config('app.timezone') == 'Africa/Lagos' ? 'selected' : '' }}>Africa/Lagos</option>
                        <option value="Africa/Kinshasa" {{ config('app.timezone') == 'Africa/Kinshasa' ? 'selected' : '' }}>Africa/Kinshasa</option>
                        <option value="Africa/Lubumbashi" {{ config('app.timezone') == 'Africa/Lubumbashi' ? 'selected' : '' }}>Africa/Lubumbashi</option>
                        <option value="Europe/Paris" {{ config('app.timezone') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                        <option value="Europe/London" {{ config('app.timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                        <option value="America/New_York" {{ config('app.timezone') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                        <option value="America/Los_Angeles" {{ config('app.timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                        <option value="Asia/Dubai" {{ config('app.timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                        <option value="Asia/Singapore" {{ config('app.timezone') == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore</option>
                    </select>
                    @error('timezone')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Locale -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                        Langue <span class="required">*</span>
                    </label>
                    <select name="locale" class="form-control" required>
                        <option value="en" {{ config('app.locale') == 'en' ? 'selected' : '' }}>English</option>
                        <option value="fr" {{ config('app.locale') == 'fr' ? 'selected' : '' }}>Français</option>
                    </select>
                    @error('locale')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    <!-- Maintenance -->
    <div class="maintenance-card animate-fadeInUp delay-3">
        <h3>Maintenance</h3>
        <div class="flex flex-wrap gap-2 sm:gap-3">
            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Vider le cache
                </button>
            </form>

            <form action="{{ route('admin.settings.optimize') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Optimiser l'application
                </button>
            </form>

            <form action="{{ route('admin.settings.toggle-maintenance') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Mode maintenance
                </button>
            </form>
        </div>

        <div class="mt-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                <span class="font-medium">Information :</span> Vider le cache supprime toutes les données mises en cache.
                Optimiser permet de mettre en cache les routes, les vues et la configuration pour de meilleures performances.
            </p>
        </div>
    </div>

    <!-- Environment Info -->
    <div class="maintenance-card animate-fadeInUp delay-4 border-l-4 border-[var(--primary-blue)]">
        <h3>Environnement</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 text-xs sm:text-sm">
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Environnement</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ config('app.env') }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Mode debug</span>
                <span class="font-semibold {{ config('app.debug') ? 'text-[#B91C1C]' : 'text-[#1C7E4A]' }}">
                    {{ config('app.debug') ? 'Activé' : 'Désactivé' }}
                </span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Laravel</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ app()->version() }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">PHP</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ phpversion() }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Fuseau horaire</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ config('app.timezone') }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-[var(--border-light)]">
                <span class="text-[var(--text-secondary)]">Langue</span>
                <span class="font-semibold text-[var(--text-primary)]">{{ config('app.locale') }}</span>
            </div>
        </div>
    </div>

</div>
@endsection