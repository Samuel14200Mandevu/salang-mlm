@extends('cashier.layouts.app')

@push('styles')
<style>
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
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
    .btn-primary { background: var(--primary, #1E5DAD); color: #fff; }
    .btn-primary:hover { background: var(--primary-hover, #134178); color: #fff; }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary, #1E5DAD);
        color: var(--primary, #1E5DAD);
    }
    .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

    .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .badge-source-pos { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-source-mlm { background: rgba(59, 130, 246, 0.12); color: #2563eb; }

    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.55rem 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.875rem;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-row .label { color: var(--text-secondary); }
    .detail-row .value {
        font-weight: 650;
        color: var(--text-primary);
        text-align: right;
    }

    .table-wrap { overflow-x: auto; }
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
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
    }
    .table tbody td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }
    .table tbody tr.similar-row { cursor: pointer; }
    .table tbody tr.similar-row:hover td { background: var(--bg-secondary); }

    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
    }
</style>
@endpush

@section('title', 'Détail commission #'.$commission->id)

@section('content')
@php
    $statusMap = [
        'pending' => ['label' => 'En attente', 'class' => 'badge-warning'],
        'approved' => ['label' => 'Approuvée', 'class' => 'badge-info'],
        'paid' => ['label' => 'Payée', 'class' => 'badge-success'],
        'rejected' => ['label' => 'Rejetée', 'class' => 'badge-danger'],
        'cancelled' => ['label' => 'Annulée', 'class' => 'badge-danger'],
    ];
    $status = $statusMap[$commission->status] ?? ['label' => ucfirst((string) $commission->status), 'class' => 'badge-info'];
    $memberLabel = $member
        ? trim(($member->sponsor_id ? $member->sponsor_id.' : ' : '').$member->name)
        : 'N/A';
    $fromLabel = $commission->fromUser
        ? trim(($commission->fromUser->sponsor_id ? $commission->fromUser->sponsor_id.' : ' : '').$commission->fromUser->name)
        : null;
@endphp

<div class="space-y-4 sm:space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Commission #{{ $commission->id }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $typeLabel }} · {{ $commission->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($member)
                <a href="{{ route('cashier.members.show', $member->id) }}" class="btn btn-outline btn-sm">Fiche membre</a>
            @endif
            <a href="{{ route('cashier.commissions') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
        <div class="lg:col-span-2 card space-y-0">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">Informations</h3>

            <div class="detail-row">
                <span class="label">Membre</span>
                <span class="value">{{ $memberLabel }}</span>
            </div>
            @if($fromLabel)
                <div class="detail-row">
                    <span class="label">De</span>
                    <span class="value">{{ $fromLabel }}</span>
                </div>
            @endif
            <div class="detail-row">
                <span class="label">Type</span>
                <span class="value">{{ $typeLabel }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Source</span>
                <span class="value">
                    <span class="badge {{ ($commission->source ?? '') === 'pos' ? 'badge-source-pos' : 'badge-source-mlm' }}">
                        {{ strtoupper($commission->source ?? 'N/A') }}
                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="label">Pourcentage</span>
                <span class="value">{{ $commission->percentage !== null ? number_format((float) $commission->percentage, 2).' %' : '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Statut</span>
                <span class="value"><span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span></span>
            </div>
            <div class="detail-row">
                <span class="label">Période</span>
                <span class="value">{{ $commission->period ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Date</span>
                <span class="value">{{ $commission->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($commission->paid_at)
                <div class="detail-row">
                    <span class="label">Payée le</span>
                    <span class="value">{{ $commission->paid_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
            @if(!empty($commission->description))
                <div class="detail-row" style="flex-direction:column; align-items:flex-start; gap:0.35rem;">
                    <span class="label">Description</span>
                    <span class="value" style="text-align:left; font-weight:500;">{{ $commission->description }}</span>
                </div>
            @endif
        </div>

        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Résumé</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Commission membre</span>
                    <span class="font-semibold text-[var(--ui-stat-success)]">${{ number_format($commissionAmount, 2) }}</span>
                </div>
                @if($saleTotal > 0)
                    <div class="flex justify-between">
                        <span class="text-[var(--text-secondary)]">Vente liée</span>
                        <span class="font-semibold text-[var(--text-primary)]">${{ number_format($saleTotal, 2) }}</span>
                    </div>
                    <div class="border-t border-[var(--border-color)] pt-3 mt-2 flex justify-between items-baseline gap-2">
                        <span class="text-sm text-[var(--text-secondary)]">
                            Vente ${{ number_format($saleTotal, 2) }} − commission ${{ number_format($commissionAmount, 2) }} :
                        </span>
                        <span class="font-bold text-[var(--primary)]">${{ number_format($companyShare ?? 0, 2) }}</span>
                    </div>
                @endif
                <div class="border-t border-[var(--border-color)] pt-3 mt-3 flex justify-between">
                    <span class="text-[var(--text-secondary)]">Total payé (membre)</span>
                    <span class="font-semibold">${{ number_format((float) ($memberPaidTotal ?? 0), 2) }}</span>
                </div>
            </div>

            @if($order)
                <div class="mt-4 pt-4 border-t border-[var(--border-color)] space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[var(--text-secondary)]">Commande</span>
                        <a href="{{ route('cashier.orders.show', ['id' => $order->id, 'from' => 'history']) }}"
                           class="font-mono text-xs text-[var(--primary)] hover:underline">
                            #{{ $order->order_number }}
                        </a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[var(--text-secondary)]">Client</span>
                        <span class="font-medium">{{ $order->user?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[var(--text-secondary)]">Date commande</span>
                        <span class="font-medium text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($similarCommissions->isNotEmpty())
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">
                Autres commissions du membre
            </h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($similarCommissions as $item)
                            @php
                                $itemType = $item->type === 'cash_pos'
                                    ? 'POS'
                                    : ($item->type_label ?? ucfirst(str_replace('_', ' ', (string) $item->type)));
                                $itemStatus = $statusMap[$item->status] ?? ['label' => ucfirst((string) $item->status), 'class' => 'badge-info'];
                                $itemUrl = route('cashier.commissions.show', $item->id);
                            @endphp
                            <tr class="similar-row"
                                role="link"
                                tabindex="0"
                                data-href="{{ $itemUrl }}"
                                onclick="window.location.href=this.dataset.href"
                                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href=this.dataset.href}">
                                <td class="font-mono text-xs">#{{ $item->id }}</td>
                                <td>{{ $itemType }}</td>
                                <td>
                                    <span class="badge {{ ($item->source ?? '') === 'pos' ? 'badge-source-pos' : 'badge-source-mlm' }}">
                                        {{ strtoupper($item->source ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="font-semibold text-[var(--text-primary)]">${{ number_format((float) $item->amount, 2) }}</td>
                                <td><span class="badge {{ $itemStatus['class'] }}">{{ $itemStatus['label'] }}</span></td>
                                <td class="text-xs text-[var(--text-tertiary)]">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
