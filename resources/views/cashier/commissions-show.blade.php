@extends('cashier.layouts.app')

@push('styles')
<style>
    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }
    .detail-card .detail-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-secondary);
        font-weight: 600;
    }
    .detail-card .detail-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-top: 0.25rem;
    }
    .detail-card .detail-value.text-success { color: #1F7B4D; }
    .detail-card .detail-value.text-warning { color: #A65A0E; }
    .detail-card .detail-value.text-info { color: #0A2A6C; }
    .detail-card .detail-value.text-danger { color: #B32A2A; }

    .member-avatar {
        width: 4rem;
        height: 4rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
        background: var(--primary-navy);
        color: white;
        flex-shrink: 0;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--primary-navy);
        color: #FFFFFF;
    }
    .btn-primary:hover {
        background: #091E3B;
        color: #FFFFFF;
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary-navy);
        color: var(--primary-navy);
    }
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }
    .btn-success {
        background: #1F7B4D;
        color: white;
    }
    .btn-success:hover {
        background: #16633E;
        color: white;
    }
    .btn-pdf {
        background: #B32A2A;
        color: white;
    }
    .btn-pdf:hover {
        background: #8F2121;
        color: white;
    }

    .badge-status {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-active { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; }
    .badge-inactive { background: rgba(179, 42, 42, 0.12); color: #B32A2A; }
    .badge-pending { background: rgba(245, 158, 11, 0.12); color: #A65A0E; }

    .commission-row {
        transition: background 0.1s ease;
    }
    .commission-row:hover {
        background: var(--bg-hover);
    }

    .type-badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
    }
    .type-badge-sponsor { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; }
    .type-badge-direct { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
    .type-badge-indirect { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .type-badge-leadership { background: rgba(245, 158, 11, 0.12); color: #A65A0E; }
    .type-badge-cash_pos { background: rgba(34, 197, 94, 0.12); color: #1F7B4D; }
    .type-badge-default { background: var(--bg-secondary); color: var(--text-secondary); }

    .table-wrap { overflow-x: auto; }
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.75rem;
    }
    .table thead th {
        padding: 0.4rem 0.6rem;
        text-align: left;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.4rem 0.6rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.25rem;
    }

    .tab-nav {
        display: flex;
        gap: 0.25rem;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 1rem;
        overflow-x: auto;
    }
    .tab-nav a {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-decoration: none;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .tab-nav a:hover {
        color: var(--text-primary);
    }
    .tab-nav a.active {
        color: var(--primary-navy);
        border-bottom-color: var(--primary-navy);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .stat-item {
        text-align: center;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: 6px;
    }
    .stat-item .number {
        font-size: 1.1rem;
        font-weight: 800;
    }
    .stat-item .label {
        font-size: 0.55rem;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 600;
    }

    @media (max-width: 640px) {
        .member-avatar { width: 3rem; height: 3rem; font-size: 1rem; }
        .detail-card { padding: 0.75rem; }
        .card { padding: 0.75rem; }
        .stats-grid { grid-template-columns: 1fr 1fr; }
        .table thead th, .table tbody td { padding: 0.3rem 0.4rem; font-size: 0.65rem; }
        .tab-nav a { padding: 0.4rem 0.6rem; font-size: 0.65rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
    }
</style>
@endpush

@section('title', $member->name)

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE AVEC ACTIONS --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-center gap-4">
            <div class="member-avatar">
                {{ substr($member->name, 0, 2) }}
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                    {{ $member->name }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 text-sm text-[var(--text-secondary)]">
                    <span>Code: <strong>{{ $member->sponsor_id }}</strong></span>
                    <span>•</span>
                    <span>ID: {{ $member->id }}</span>
                    <span>•</span>
                    <span>
                        Grade: <strong>{{ $member->rank ?? 'Distributeur' }}</strong>
                    </span>
                    <span>•</span>
                    <span>
                        Statut:
                        @if($member->is_active)
                            <span class="badge-status badge-active">Actif</span>
                        @else
                            <span class="badge-status badge-inactive">Inactif</span>
                        @endif
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-[var(--text-secondary)] mt-1">
                    <span> {{ $member->email ?? 'N/A' }}</span>
                    <span>•</span>
                    <span> {{ $member->phone ?? 'N/A' }}</span>
                    @if($member->package)
                        <span>•</span>
                        <span> {{ $member->package->name }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cashier.members.pay-slip', $member->id) }}?period={{ date('Y-m') }}" 
               class="btn btn-pdf btn-sm" title="Fiche de paie">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Fiche de paie
            </a>
            <a href="{{ route('cashier.members.adhesion-pdf', $member->id) }}" 
               class="btn btn-outline btn-sm" target="_blank">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Adhésion PDF
            </a>
            <a href="{{ route('cashier.members') }}" class="btn btn-outline btn-sm">
                ← Retour
            </a>
        </div>
      
    </div>

    {{-- STATISTIQUES RAPIDES --}}
    <div class="stats-grid">
        <div class="stat-item">
            <div class="number text-success">${{ number_format($stats['paid_commissions'] ?? 0, 2) }}</div>
            <div class="label">Commissions payées</div>
        </div>
        <div class="stat-item">
            <div class="number text-warning">${{ number_format($stats['pending_commissions'] ?? 0, 2) }}</div>
            <div class="label">En attente</div>
        </div>
        <div class="stat-item">
            <div class="number text-info">{{ $stats['total_downlines'] ?? 0 }}</div>
            <div class="label">Filleuls</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $member->pv_balance ?? 0 }}</div>
            <div class="label">PV Total</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $member->monthly_pv ?? 0 }}</div>
            <div class="label">PV Mensuel</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $member->team_pv ?? 0 }}</div>
            <div class="label">PV Réseau</div>
        </div>
    </div>

    {{-- TABS --}}
    <div class="tab-nav">
        <a href="#commissions" class="active" onclick="switchTab(event, 'commissions')">Commissions</a>
        <a href="#downlines" onclick="switchTab(event, 'downlines')">Filleuls ({{ $downlines->total() }})</a>
        <a href="#orders" onclick="switchTab(event, 'orders')">Commandes</a>
    </div>

    {{-- TAB COMMISSIONS --}}
    <div id="tab-commissions" class="tab-content">
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm">
                    Commissions POS & MLM
                    <span class="text-xs text-[var(--text-secondary)] font-normal ml-1">
                        ({{ $commissions->total() }})
                    </span>
                </h3>
                <div class="flex gap-2">
                    <a href="{{ route('cashier.members.pay-slip', $member->id) }}?period={{ date('Y-m') }}" 
                       class="btn btn-pdf btn-sm">
                        Fiche de paie
                    </a>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th class="text-right">Montant</th>
                            <th>Statut</th>
                            <th class="hidden md:table-cell">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $commission)
                            <tr class="commission-row">
                                <td class="font-mono text-xs">#{{ $commission->id }}</td>
                                <td>
                                    @php
                                        $typeClass = 'type-badge-' . $commission->type;
                                        if (!in_array($commission->type, ['sponsor', 'direct', 'indirect', 'leadership', 'cash_pos'])) {
                                            $typeClass = 'type-badge-default';
                                        }
                                        $typeLabel = $commission->type_label ?? ucfirst(str_replace('_', ' ', $commission->type));
                                        if ($commission->type == 'cash_pos') { $typeLabel = 'CASH POS'; }
                                    @endphp
                                    <span class="type-badge {{ $typeClass }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td>
                                    @if($commission->source == 'pos')
                                        <span class="badge-status badge-active">POS</span>
                                    @elseif($commission->source == 'mlm')
                                        <span class="badge-status" style="background: rgba(59,130,246,0.12); color:#3b82f6;">MLM</span>
                                    @else
                                        <span class="badge-status" style="background: var(--bg-secondary); color:var(--text-secondary);">—</span>
                                    @endif
                                </td>
                                <td class="text-right font-bold text-success text-sm">
                                    ${{ number_format($commission->amount, 2) }}
                                </td>
                                <td>
                                    @if($commission->status == 'paid')
                                        <span class="badge-status badge-active">Payée</span>
                                    @elseif($commission->status == 'pending')
                                        <span class="badge-status badge-pending">En attente</span>
                                    @elseif($commission->status == 'approved')
                                        <span class="badge-status" style="background:rgba(59,130,246,0.12); color:#3b82f6;">Approuvée</span>
                                    @else
                                        <span class="badge-status badge-inactive">{{ ucfirst($commission->status) }}</span>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell text-xs text-[var(--text-secondary)]">
                                    {{ $commission->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                    <p>Aucune commission pour ce membre.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($commissions->hasPages())
                <div class="mt-3">{{ $commissions->links() }}</div>
            @endif
        </div>
    </div>

    {{-- TAB DOWNLINES --}}
    <div id="tab-downlines" class="tab-content" style="display:none;">
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">
                Filleuls directs ({{ $downlines->total() }})
            </h3>

            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Grade</th>
                            <th>PV</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($downlines as $downline)
                            <tr>
                                <td class="font-mono text-xs">{{ $downline->sponsor_id }}</td>
                                <td>
                                    <a href="{{ route('cashier.members.show', $downline->id) }}" class="text-[var(--primary-navy)] hover:underline">
                                        {{ $downline->name }}
                                    </a>
                                </td>
                                <td>{{ $downline->rank ?? 'Distributeur' }}</td>
                                <td>{{ $downline->pv_balance ?? 0 }}</td>
                                <td>
                                    @if($downline->is_active)
                                        <span class="badge-status badge-active">Actif</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactif</span>
                                    @endif
                                </td>
                                <td class="text-xs text-[var(--text-secondary)]">
                                    {{ $downline->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                    <p>Aucun filleul direct.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($downlines->hasPages())
                <div class="mt-3">{{ $downlines->links() }}</div>
            @endif
        </div>
    </div>

    {{-- TAB ORDERS --}}
    <div id="tab-orders" class="tab-content" style="display:none;">
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">
                Commandes du membre
            </h3>

            @php
                $orders = $member->orders()->orderBy('created_at', 'desc')->paginate(10);
            @endphp

            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Source</th>
                            <th class="text-right">Total</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-mono text-xs">
                                    <a href="{{ route('cashier.orders.show', $order->id) }}" class="text-[var(--primary-navy)] hover:underline">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    @if($order->source == 'pos')
                                        <span class="badge-status badge-active">POS</span>
                                    @else
                                        <span class="badge-status" style="background:rgba(59,130,246,0.12); color:#3b82f6;">{{ strtoupper($order->source) }}</span>
                                    @endif
                                </td>
                                <td class="text-right font-bold">${{ number_format($order->total, 2) }}</td>
                                <td>
                                    @if($order->status == 'completed')
                                        <span class="badge-status badge-active">Complétée</span>
                                    @elseif($order->status == 'pending')
                                        <span class="badge-status badge-pending">En attente</span>
                                    @elseif($order->status == 'cancelled')
                                        <span class="badge-status badge-inactive">Annulée</span>
                                    @else
                                        <span class="badge-status">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-xs text-[var(--text-secondary)]">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-[var(--text-secondary)]">
                                    <p>Aucune commande.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="mt-3">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
function switchTab(event, tabName) {
    event.preventDefault();

    document.querySelectorAll('.tab-content').forEach(el => {
        el.style.display = 'none';
    });

    document.querySelectorAll('.tab-nav a').forEach(el => {
        el.classList.remove('active');
    });

    document.getElementById('tab-' + tabName).style.display = 'block';
    event.currentTarget.classList.add('active');
}

// Activer le premier tab par défaut
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-content').forEach((el, index) => {
        el.style.display = index === 0 ? 'block' : 'none';
    });
});
</script>
@endpush
@endsection