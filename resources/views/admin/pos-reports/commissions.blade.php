{{-- resources/views/admin/pos-reports/commissions.blade.php --}}
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

.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease;
}
.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.stat-card .number {
    font-size: 1.25rem;
    font-weight: 700;
}
.stat-card .label {
    font-size: 0.625rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.stat-card .icon {
    width: 2rem;
    height: 2rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.icon-green { background: rgba(28, 126, 74, 0.08); color: #1C7E4A; }
.icon-blue { background: rgba(10, 42, 108, 0.08); color: #0A2A6C; }
.icon-purple { background: rgba(10, 42, 108, 0.08); color: #0A2A6C; }
.icon-orange { background: rgba(181, 71, 8, 0.08); color: #B54708; }

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
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

.badge {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.6rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-pending {
    background: rgba(181, 71, 8, 0.12);
    color: #B54708;
    border-color: rgba(181, 71, 8, 0.15);
}
.badge-paid {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.badge-approved {
    background: rgba(6, 95, 156, 0.12);
    color: #065F9C;
    border-color: rgba(6, 95, 156, 0.15);
}
.badge-rejected {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}
.badge-cash {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border: 1px solid rgba(28, 126, 74, 0.2);
}

.payment-badge {
    display: inline-block;
    padding: 0.1rem 0.4rem;
    border-radius: 9999px;
    font-size: 0.5rem;
    font-weight: 700;
    text-transform: uppercase;
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border: 1px solid rgba(28, 126, 74, 0.2);
}

.filter-section {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.filter-section select,
.filter-section input {
    padding: 0.375rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.813rem;
    flex: 1;
    min-width: 120px;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.filter-section select:focus,
.filter-section input:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}
.filter-section .btn-filter {
    padding: 0.375rem 1.25rem;
    background: var(--primary-blue);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    cursor: pointer;
    transition: background 0.15s ease;
}
.filter-section .btn-filter:hover {
    background: var(--primary-blue-dark);
}
.filter-section .btn-reset {
    padding: 0.375rem 1.25rem;
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.filter-section .btn-reset:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.813rem; }
.table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    border-bottom: 2px solid var(--border-color);
}
.table tbody td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-primary);
    vertical-align: middle;
}
.table tbody tr:hover td {
    background: var(--bg-hover);
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
    .stat-card .number { font-size: 1.125rem; }
    .filter-section { flex-direction: column; }
    .filter-section select, .filter-section input, .filter-section button { width: 100%; }
    .table thead th, .table tbody td { padding: 0.3rem 0.4rem; font-size: 0.7rem; }
    .card { padding: 0.875rem; }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Commissions POS</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Suivi des commissions en espèces versées aux parrains
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.pos-reports.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('admin.pos-reports.export') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Total commissions</p>
                    <p class="number text-[#1C7E4A]">${{ number_format($commissionStats['total_amount'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-green">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Payées</p>
                    <p class="number text-[var(--primary-blue)]">${{ number_format($commissionStats['paid_amount'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">En attente</p>
                    <p class="number text-[#B54708]">${{ number_format($commissionStats['pending_amount'] ?? 0, 2) }}</p>
                </div>
                <div class="icon icon-orange">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fadeInUp delay-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="label">Membres bénéficiaires</p>
                    <p class="number text-[var(--primary-blue)]">{{ number_format($commissionStats['total_members'] ?? 0) }}</p>
                </div>
                <div class="icon icon-purple">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card animate-fadeInUp delay-2 p-3 sm:p-4">
        <form method="GET" action="{{ route('admin.pos-reports.commissions') }}" class="filter-section">
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Date début" class="flex-1">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Date fin" class="flex-1">
            <select name="user_id" class="flex-1">
                <option value="">Tous les membres</option>
                @foreach($members ?? [] as $member)
                    <option value="{{ $member->id }}" {{ request('user_id') == $member->id ? 'selected' : '' }}>
                        {{ $member->name }} ({{ $member->sponsor_id ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
            <select name="status" class="flex-1">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvée</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payée</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetée</option>
            </select>
            <button type="submit" class="btn-filter">Filtrer</button>
            <a href="{{ route('admin.pos-reports.commissions') }}" class="btn-reset">Réinitialiser</a>
        </form>
    </div>

    <!-- Commissions List -->
    <div class="card animate-fadeInUp delay-3 p-3 sm:p-4">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Parrain</th>
                        <th>Client</th>
                        <th>%</th>
                        <th class="text-right">Montant CASH</th>
                        <th>Statut</th>
                        <th class="hidden md:table-cell">Période</th>
                        <th class="hidden md:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions ?? [] as $commission)
                        <tr>
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $commission->id }}</td>
                            <td>
                                <div class="font-medium text-sm">{{ $commission->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">Code: {{ $commission->user?->sponsor_id ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="text-sm">{{ $commission->fromUser?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">{{ $commission->order?->order_number ?? '' }}</div>
                            </td>
                            <td class="font-medium">{{ $commission->percentage ?? 0 }}%</td>
                            <td class="text-right">
                                <span class="font-bold text-[#1C7E4A]">${{ number_format($commission->amount, 2) }}</span>
                                <span class="payment-badge ml-1">CASH</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($commission->status) {
                                        'pending' => 'badge-pending',
                                        'approved' => 'badge-approved',
                                        'paid' => 'badge-paid',
                                        'rejected' => 'badge-rejected',
                                        default => 'badge-warning'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ $commission->status_label }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $commission->period ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-[var(--text-secondary)] text-sm">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base font-medium">Aucune commission POS</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Aucune commission trouvée pour cette période</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($commissions) && $commissions->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection