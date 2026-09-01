{{-- resources/views/admin/cashiers/show.blade.php --}}
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

.info-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.info-card:hover {
    border-color: var(--primary-blue);
}

.info-card .label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.info-card .value {
    font-size: 0.938rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-top: 0.25rem;
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-info { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }
.badge-cashier { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }

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

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
}

.btn-danger {
    background: #B91C1C;
    color: white;
    border-color: #B91C1C;
}
.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
}

.btn-warning {
    background: #B54708;
    color: white;
    border-color: #B54708;
}
.btn-warning:hover {
    background: #92400E;
    border-color: #92400E;
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

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }

@media (max-width: 640px) {
    .info-card {
        padding: 0.875rem;
    }
    .info-card .value {
        font-size: 0.875rem;
    }
    .btn {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    .grid-cols-1.sm\:grid-cols-2 {
        grid-template-columns: 1fr;
    }
    .grid-cols-1.sm\:grid-cols-3 {
        grid-template-columns: 1fr;
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
                Détails du caissier
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Informations détaillées de <strong>{{ $cashier->name }}</strong>
            </p>
        </div>
        <div class="flex gap-1.5 sm:gap-2 flex-wrap">
            <a href="{{ route('admin.users.edit', $cashier->id) }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
            <a href="{{ route('admin.cashiers.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Informations principales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">

        <!-- Informations personnelles -->
        <div class="info-card md:col-span-2">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                <svg class="inline-block w-5 h-5 mr-2 text-[var(--primary-blue)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Informations personnelles
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <p class="label">Nom complet</p>
                    <p class="value">{{ $cashier->name }}</p>
                </div>
                <div>
                    <p class="label">Email</p>
                    <p class="value">{{ $cashier->email }}</p>
                </div>
                <div>
                    <p class="label">Téléphone</p>
                    <p class="value">{{ $cashier->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="label">Statut</p>
                    <p class="value">
                        @if($cashier->is_active)
                            <span class="badge badge-success">Actif</span>
                        @else
                            <span class="badge badge-danger">Inactif</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="label">Rôle</p>
                    <p class="value">
                        <span class="badge badge-cashier">Caissier</span>
                    </p>
                </div>
                <div>
                    <p class="label">Date d'inscription</p>
                    <p class="value">{{ $cashier->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Rôle et accès -->
        <div class="info-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                <svg class="inline-block w-5 h-5 mr-2 text-[var(--primary-blue)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                </svg>
                Rôle et accès
            </h3>

            <div class="space-y-3">
                <div>
                    <p class="label">Rôle</p>
                    <p class="value">
                        <span class="badge badge-cashier">Caissier</span>
                        <span class="text-xs text-[var(--text-tertiary)] block mt-1">
                            Employé de la société — Accès POS uniquement
                        </span>
                    </p>
                </div>
                <div>
                    <p class="label">Accès</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span class="badge badge-info">Point de Vente</span>
                        <span class="badge badge-info">Ventes au guichet</span>
                        <span class="badge badge-info">Gestion clients</span>
                        <span class="badge badge-info">Traitement paiements</span>
                    </div>
                </div>
                <div>
                    <p class="label">Type de compte</p>
                    <p class="value text-sm text-[var(--text-secondary)]">
                        <span class="text-[#1C7E4A]">
                            <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </span>
                        Employé de la société
                    </p>
                    <p class="text-xs text-[var(--text-tertiary)] mt-1">
                        <strong>Note :</strong> Les caissiers n'ont pas de code de parrainage et ne font pas partie du réseau MLM.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Adresse -->
    <div class="info-card animate-fadeInUp delay-2">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
            <svg class="inline-block w-5 h-5 mr-2 text-[var(--primary-blue)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Adresse
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div>
                <p class="label">Pays</p>
                <p class="value">{{ $cashier->country ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="label">Ville</p>
                <p class="value">{{ $cashier->city ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="label">Adresse</p>
                <p class="value">{{ $cashier->address ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="info-card animate-fadeInUp delay-3">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
            <svg class="inline-block w-5 h-5 mr-2 text-[var(--primary-blue)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            </svg>
            Actions
        </h3>

        <div class="flex flex-wrap gap-2 sm:gap-3">
            <a href="{{ route('admin.users.edit', $cashier->id) }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>

            <form action="{{ route('admin.users.toggle-status', $cashier->id) }}" method="POST" class="inline">
                @csrf
                @method('POST')
                <button type="submit" class="btn {{ $cashier->is_active ? 'btn-warning' : 'btn-success' }} btn-sm"
                        onclick="return confirm('Confirmer le changement de statut ?')">
                    @if($cashier->is_active)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0a9 9 0 01-12.728 0m12.728 0L12 12m0 0l-6.364 6.364M12 12l6.364-6.364"/>
                        </svg>
                        Désactiver
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Activer
                    @endif
                </button>
            </form>

            @if(auth()->id() != $cashier->id)
                <form action="{{ route('admin.users.destroy', $cashier->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Supprimer définitivement ce caissier ?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer
                    </button>
                </form>
            @endif
        </div>
    </div>

</div>
@endsection