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

.info-row {
    display: flex;
    flex-direction: column;
    padding: 0.625rem 0;
    border-bottom: 1px solid var(--border-light);
}
.info-row:last-child {
    border-bottom: none;
}
.info-row .label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-tertiary);
}
.info-row .value {
    font-size: 0.938rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-top: 0.125rem;
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
.badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); border-color: var(--border-color); }
.badge-gold { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-purple { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }

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

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
}

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-info {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-info:hover {
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

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
}
.table-striped tbody tr:nth-child(even) { background: var(--bg-secondary); }

.code-display {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 0.625rem 1rem;
    text-align: center;
    font-family: monospace;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--primary-blue);
    letter-spacing: 1.5px;
    border: 2px dashed var(--border-color);
    margin: 0.75rem 0;
    word-break: break-all;
}

.package-summary {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid var(--border-color);
}
.package-summary:hover {
    border-color: var(--primary-blue);
}
.package-summary .package-icon svg {
    width: 2rem;
    height: 2rem;
    color: var(--primary-blue);
}
.package-summary .package-name {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
}
.package-summary .package-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-blue);
}
.package-summary .package-detail {
    font-size: 0.8rem;
    color: var(--text-secondary);
}
.package-summary .package-stat {
    text-align: center;
    padding: 0.375rem 0.5rem;
    background: var(--bg-card);
    border-radius: 6px;
    border: 1px solid var(--border-color);
}
.package-summary .package-stat .stat-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary);
}
.package-summary .package-stat .stat-label {
    font-size: 0.625rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
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
    .info-row .value { font-size: 0.85rem; }
    .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
    .badge { font-size: 0.55rem; padding: 0.1rem 0.4rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
    .card { padding: 0.875rem; }
    .code-display { font-size: 1rem; padding: 0.5rem 0.75rem; }
    .package-summary .package-price { font-size: 1rem; }
    .package-summary .package-icon svg { width: 1.5rem; height: 1.5rem; }
    .package-summary .package-stat .stat-value { font-size: 1rem; }
    .package-stats-grid { grid-template-columns: 1fr 1fr !important; }
    .grid-cols-2 { grid-template-columns: 1fr 1fr !important; }
}

@media (max-width: 480px) {
    .package-stats-grid { grid-template-columns: 1fr !important; }
    .grid-cols-2 { grid-template-columns: 1fr !important; }
    .package-summary .package-price { font-size: 0.938rem; }
    .flex-wrap.gap-3 { flex-direction: column; }
    .flex-wrap.gap-3 .btn { width: 100%; }
}
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Activation de {{ $user->name }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                ID: #{{ $user->id }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.activations.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
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

    <!-- User Information -->
    <div class="card mb-4 animate-fadeInUp delay-1">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Nom</p>
                <p class="font-semibold text-sm">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Email</p>
                <p class="font-semibold text-sm">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Code de parrainage</p>
                <p class="font-mono text-[var(--primary-blue)] text-sm">{{ $user->sponsor_id }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Statut</p>
                <span class="badge badge-danger">Inactif</span>
                @if($user->activation_code)
                    <span class="text-xs text-[var(--text-tertiary)] block mt-1">
                        Code: <span class="font-mono text-[var(--primary-blue)]">{{ $user->activation_code }}</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Package associé au code d'activation -->
    <div class="card mb-4 border-l-4 border-[var(--primary-blue)] animate-fadeInUp delay-2">
        <h2 class="text-base font-semibold mb-3">Package d'activation</h2>

        @if($user->activation_package_id)
            @php
                $activationPackage = App\Models\Package::find($user->activation_package_id);
            @endphp
            @if($activationPackage)
                <div class="package-summary">
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <span class="package-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="package-name">{{ $activationPackage->name }}</h3>
                                    <p class="package-price">${{ number_format($activationPackage->price, 2) }}</p>
                                </div>
                            </div>

                            <div class="package-stats-grid mt-3 grid grid-cols-2 md:grid-cols-4 gap-2">
                                <div class="package-stat">
                                    <p class="stat-value">{{ $activationPackage->pv_value }}</p>
                                    <p class="stat-label">PV</p>
                                </div>
                                <div class="package-stat">
                                    <p class="stat-value">{{ $activationPackage->bv_value }}</p>
                                    <p class="stat-label">BV</p>
                                </div>
                                <div class="package-stat">
                                    <p class="stat-value">{{ $activationPackage->commission_rate ?? 30 }}%</p>
                                    <p class="stat-label">Commission</p>
                                </div>
                                <div class="package-stat">
                                    <p class="stat-value text-[#1C7E4A]">Actif</p>
                                    <p class="stat-label">Statut</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-center sm:text-right">
                            <span class="badge badge-gold text-xs">Package sélectionné</span>
                            <p class="text-xs text-[var(--text-tertiary)] mt-1">
                                Valable jusqu'au {{ \Carbon\Carbon::parse($user->activation_code_expires_at)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-3">
                    <p class="text-[var(--text-secondary)] text-sm">Package non trouvé</p>
                    <p class="text-xs text-[var(--text-tertiary)]">Le package associé a été supprimé</p>
                </div>
            @endif
        @else
            <div class="text-center py-3">
                <p class="text-[var(--text-secondary)] text-sm">Aucun package associé</p>
                <p class="text-xs text-[var(--text-tertiary)]">Générez un code d'activation pour associer un package</p>
            </div>
        @endif
    </div>

    <!-- Commissions disponibles -->
    <div class="card mb-4 border-l-4 border-[#B54708] animate-fadeInUp delay-3">
        <h2 class="text-base font-semibold mb-3">Commissions disponibles</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-[var(--text-secondary)]">En attente</p>
                <p class="text-xl sm:text-2xl font-bold text-[#B54708]">${{ number_format($totalCommissions ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Payées</p>
                <p class="text-xl sm:text-2xl font-bold text-[#1C7E4A]">${{ number_format($paidCommissions ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Total gagné</p>
                <p class="text-xl sm:text-2xl font-bold text-[var(--primary-blue)]">${{ number_format($totalEarnings ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-secondary)]">Code d'activation</p>
                @if($user->activation_code)
                    <span class="badge badge-info font-mono text-xs">{{ $user->activation_code }}</span>
                @else
                    <span class="text-[var(--text-tertiary)] text-sm">Aucun code</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Historique des commissions -->
    <div class="card animate-fadeInUp delay-4">
        <h2 class="text-base font-semibold mb-3">Dernières commissions</h2>
        @if($commissions->count() > 0)
            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commissions as $commission)
                            <tr>
                                <td class="text-xs">#{{ $commission->id }}</td>
                                <td class="text-sm">{{ ucfirst($commission->type) }}</td>
                                <td class="text-[#1C7E4A] font-semibold text-sm">${{ number_format($commission->amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($commission->status) }}
                                    </span>
                                </td>
                                <td class="text-xs text-[var(--text-secondary)]">{{ $commission->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-[var(--text-secondary)] text-sm py-3 text-center">Aucune commission</p>
        @endif
    </div>

    <!-- Actions -->
    <div class="mt-4 flex gap-3 flex-wrap">
        <form action="{{ route('admin.activations.activate', $user->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Activer manuellement
            </button>
        </form>

        @if($user->activation_code)
            <form action="{{ route('admin.activations.send-code', $user->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Renvoyer le code
                </button>
            </form>
        @endif

        @if(!$user->activation_code)
            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Générer un code
            </a>
        @endif
    </div>
</div>
@endsection