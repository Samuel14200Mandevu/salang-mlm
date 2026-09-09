@extends('cashier.layouts.app')

@push('styles')
<style>
    /* ============================================================
       STATISTIQUES PV – Sobres, sans dégradés ni ombres
       ============================================================ */
    .pv-stat-card {
        background: var(--bg-card, #F8F9FA);
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 8px;
        padding: 1.25rem;
    }

    .pv-stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--text-primary, #1A1A1E);
    }

    .pv-stat-card .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary, #7A7A82);
    }

    .pv-stat-card .stat-sub {
        font-size: 0.6rem;
        color: var(--text-tertiary, #7A7A82);
    }

    .stat-value-pv { color: #0F2B4F; }
    .stat-value-available { color: #1F7B4D; }
    .stat-value-allocated { color: #2563EB; }
    .stat-value-pending { color: #A65A0E; }

    /* ============================================================
       ALLOCATIONS
       ============================================================ */
    .allocation-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-light, #E8EAEE);
    }
    .allocation-item:last-child {
        border-bottom: none;
    }
    .allocation-item .info h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary, #1A1A1E);
        margin: 0 0 0.125rem;
    }
    .allocation-item .info p {
        font-size: 0.75rem;
        color: var(--text-secondary, #4A4A52);
        margin: 0;
    }

    .btn-approve {
        background: #1F7B4D;
        color: #fff;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .btn-approve:hover {
        background: #16633D;
    }

    .btn-reject {
        background: #B32A2A;
        color: #fff;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .btn-reject:hover {
        background: #8F2121;
    }

    /* ============================================================
       RÉSEAU
       ============================================================ */
    .network-member {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        transition: background 0.15s ease;
        cursor: pointer;
    }
    .network-member:hover {
        background: var(--bg-hover, #E8EAEE);
    }

    .network-member .avatar {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
        background: #0F2B4F;
        color: #fff;
        flex-shrink: 0;
    }

    .network-member .info .name {
        font-weight: 600;
        color: var(--text-primary, #1A1A1E);
        font-size: 0.875rem;
    }
    .network-member .info .detail {
        font-size: 0.7rem;
        color: var(--text-secondary, #4A4A52);
    }

    .network-member .pv-badge {
        margin-left: auto;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        background: rgba(37, 99, 235, 0.10);
        color: #2563EB;
    }

    /* ============================================================
       TABLE
       ============================================================ */
    .table-wrap {
        overflow-x: auto;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.813rem;
    }
    .table thead th {
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary, #7A7A82);
        border-bottom: 2px solid var(--border-color, #DCDEE3);
        background: var(--bg-secondary, #EEF0F3);
    }
    .table tbody td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-light, #E8EAEE);
        color: var(--text-secondary, #4A4A52);
        vertical-align: middle;
    }

    .badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .badge-success { background: #E6F4EC; color: #1F7B4D; border-color: #B8DFCC; }
    .badge-danger { background: #FDE8E8; color: #B32A2A; border-color: #F5C8C8; }
    .badge-info { background: #E8EDF5; color: #0F2B4F; border-color: #C8D4E3; }
    .badge-warning { background: #FEF1E6; color: #A65A0E; border-color: #FADCB8; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.813rem;
        transition: background 0.15s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }
    .btn-primary {
        background: #0F2B4F;
        color: #fff;
    }
    .btn-primary:hover {
        background: #091E3B;
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary, #1A1A1E);
        border: 1.5px solid var(--border-color, #DCDEE3);
    }
    .btn-outline:hover {
        background: var(--bg-hover, #E8EAEE);
        border-color: #0F2B4F;
        color: #0F2B4F;
    }

    .card {
        background: var(--bg-card, #FCFCFD);
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 8px;
        padding: 1.25rem;
    }

    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .pv-stat-card { padding: 0.75rem 1rem; }
        .pv-stat-card .stat-value { font-size: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
    }
</style>
@endpush

@section('title', 'Gestion des PV')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Gestion des PV</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Consultez votre solde et gérez les allocations</p>
        </div>
        <a href="{{ route('cashier.pv.distribute') }}" class="btn btn-primary btn-sm">Distribuer des PV</a>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="p-3 bg-[#E6F4EC] border border-[#B8DFCC] rounded text-[#1F7B4D] text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-[#FDE8E8] border border-[#F5C8C8] rounded text-[#B32A2A] text-sm">{{ session('error') }}</div>
    @endif

    {{-- STATISTIQUES --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="pv-stat-card">
            <p class="stat-label">PV Total</p>
            <p class="stat-value stat-value-pv">{{ number_format($balance?->total_pv ?? 0) }}</p>
            <p class="stat-sub">BV: {{ number_format($balance?->total_bv ?? 0) }}</p>
        </div>
        <div class="pv-stat-card">
            <p class="stat-label">Disponibles</p>
            <p class="stat-value stat-value-available">{{ number_format($balance?->available_pv ?? 0) }}</p>
            <p class="stat-sub">À distribuer</p>
        </div>
        <div class="pv-stat-card">
            <p class="stat-label">Alloués</p>
            <p class="stat-value stat-value-allocated">{{ number_format($balance?->allocated_pv ?? 0) }}</p>
            <p class="stat-sub">Déjà distribués</p>
        </div>
        <div class="pv-stat-card">
            <p class="stat-label">En attente</p>
            <p class="stat-value stat-value-pending">{{ number_format($balance?->pending_pv ?? 0) }}</p>
            <p class="stat-sub">En cours de validation</p>
        </div>
    </div>

    {{-- ALLOCATIONS + RÉSEAU --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Allocations --}}
        <div class="lg:col-span-2 card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">
                Allocations en attente
                <span class="text-xs text-[var(--text-secondary)] font-normal">({{ $pendingAllocations->count() }})</span>
            </h3>

            @if($pendingAllocations->isEmpty())
                <p class="text-[var(--text-tertiary)] text-sm py-4 text-center">Aucune allocation en attente</p>
            @else
                @foreach($pendingAllocations as $allocation)
                    <div class="allocation-item">
                        <div class="info">
                            <h4>{{ $allocation->user?->name ?? 'Membre' }}</h4>
                            <p>
                                {{ $allocation->pv_amount }} PV
                                @if($allocation->bv_amount) • {{ $allocation->bv_amount }} BV @endif
                                • {{ $allocation->created_at->diffForHumans() }}
                                @if($allocation->notes)
                                    <span class="text-xs text-[var(--text-tertiary)]">— {{ $allocation->notes }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="approveAllocation({{ $allocation->id }})" class="btn-approve">Approuver</button>
                            <button onclick="rejectAllocation({{ $allocation->id }})" class="btn-reject">Rejeter</button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Réseau --}}
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">
                Membres du réseau
                <span class="text-xs text-[var(--text-secondary)] font-normal">({{ count($networkMembers) }})</span>
            </h3>

            @if(empty($networkMembers))
                <p class="text-[var(--text-tertiary)] text-sm py-4 text-center">Aucun membre dans votre réseau</p>
            @else
                <div class="space-y-1 max-h-96 overflow-y-auto">
                    @foreach($networkMembers as $member)
                        <div class="network-member" onclick="selectNetworkMember({{ $member['id'] }})">
                            <div class="avatar">{{ substr($member['name'], 0, 1) }}</div>
                            <div class="info">
                                <div class="name">{{ $member['name'] }}</div>
                                <div class="detail">Niveau {{ $member['level'] }} • {{ $member['phone'] ?? 'N/A' }}</div>
                            </div>
                            <div class="pv-badge">{{ $member['pv_balance'] }} PV</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- HISTORIQUE --}}
    <div class="card">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Historique des transactions</h3>

        @if($transactions->isEmpty())
            <p class="text-[var(--text-tertiary)] text-sm py-4 text-center">Aucune transaction</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="text-right">PV</th>
                            <th>Source</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td class="text-xs text-[var(--text-tertiary)]">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'credit' => ['label' => 'Crédit', 'class' => 'badge-success'],
                                            'debit' => ['label' => 'Débit', 'class' => 'badge-danger'],
                                            'allocation' => ['label' => 'Allocation', 'class' => 'badge-info'],
                                            'bonus' => ['label' => 'Bonus', 'class' => 'badge-warning'],
                                        ];
                                        $type = $typeLabels[$transaction->type] ?? ['label' => ucfirst($transaction->type), 'class' => 'badge-info'];
                                    @endphp
                                    <span class="badge {{ $type['class'] }}">{{ $type['label'] }}</span>
                                </td>
                                <td class="text-right font-semibold {{ $transaction->type == 'credit' || $transaction->type == 'bonus' ? 'text-[#1F7B4D]' : 'text-[#B32A2A]' }}">
                                    {{ $transaction->type == 'credit' || $transaction->type == 'bonus' ? '+' : '-' }}
                                    {{ $transaction->pv_amount }} PV
                                    @if($transaction->bv_amount)
                                        <span class="text-xs text-[var(--text-tertiary)]">({{ $transaction->bv_amount }} BV)</span>
                                    @endif
                                </td>
                                <td><span class="text-xs text-[var(--text-secondary)]">{{ ucfirst(str_replace('_', ' ', $transaction->source)) }}</span></td>
                                <td class="text-xs text-[var(--text-secondary)] max-w-xs truncate">{{ $transaction->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="mt-3">{{ $transactions->links() }}</div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
function approveAllocation(id) {
    if (confirm('Approuver cette allocation de PV ?')) {
        fetch(`/cashier/pv/approve/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => { if (data.success) window.location.reload(); else alert('Erreur'); })
        .catch(() => alert('Erreur'));
    }
}

function rejectAllocation(id) {
    if (confirm('Rejeter cette allocation de PV ?')) {
        alert('Fonctionnalité en développement');
    }
}

function selectNetworkMember(id) {
    window.location.href = `{{ route('cashier.pv.distribute') }}?member_id=${id}`;
}
</script>
@endpush
@endsection