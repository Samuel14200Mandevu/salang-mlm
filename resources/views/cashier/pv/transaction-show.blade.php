@extends('cashier.layouts.app')

@push('styles')
<style>
    .card {
        background: var(--bg-card, #FCFCFD);
        border: 1px solid var(--border-color, #DCDEE3);
        border-radius: 8px;
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
        font-size: 0.813rem;
        transition: background 0.15s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
    .btn-outline {
        background: transparent;
        color: var(--text-primary, #1A1A1E);
        border: 1.5px solid var(--border-color, #DCDEE3);
    }
    .btn-outline:hover {
        background: var(--bg-hover, #E8EAEE);
        border-color: #184f94;
        color: #184f94;
    }
    .badge {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(31, 123, 77, 0.12); color: #1F7B4D; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #B32A2A; }
    .badge-info { background: rgba(37, 99, 235, 0.12); color: #2563EB; }
    .badge-warning { background: rgba(166, 90, 14, 0.12); color: #A65A0E; }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem 1.25rem;
    }
    @media (min-width: 768px) {
        .detail-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    .detail-item .label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary, #7A7A82);
        margin-bottom: 0.2rem;
    }
    .detail-item .value {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary, #1A1A1E);
    }
    .detail-item .sub {
        font-size: 0.7rem;
        color: var(--text-secondary, #4A4A52);
        margin-top: 0.1rem;
    }
    .network-member {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.25rem;
        border-bottom: 1px solid var(--border-light, #E8EAEE);
    }
    .network-member:last-child { border-bottom: none; }
    .network-member .avatar {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
        background: #184f94;
        color: #fff;
        flex-shrink: 0;
    }
    .network-member .info { flex: 1; min-width: 0; }
    .network-member .name {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-primary, #1A1A1E);
    }
    .network-member .detail {
        font-size: 0.7rem;
        color: var(--text-secondary, #4A4A52);
    }
    .network-member .pv-badge {
        font-size: 0.7rem;
        font-weight: 600;
        color: #2563EB;
        background: rgba(37, 99, 235, 0.08);
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        white-space: nowrap;
    }
    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .detail-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('title', 'Détail transaction PV')

@section('content')
@php
    $pvQuery = !empty($viewingMember) ? ['member_id' => $subject->id] : [];
    $typeLabels = [
        'credit' => ['label' => 'Crédit', 'class' => 'badge-success'],
        'debit' => ['label' => 'Débit', 'class' => 'badge-danger'],
        'allocation' => ['label' => 'Allocation', 'class' => 'badge-info'],
        'bonus' => ['label' => 'Bonus', 'class' => 'badge-warning'],
    ];
    $type = $typeLabels[$transaction->type] ?? ['label' => ucfirst($transaction->type), 'class' => 'badge-info'];
    $isCredit = in_array($transaction->type, ['credit', 'bonus'], true);
@endphp

<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Détail de la transaction</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $transaction->created_at->format('d/m/Y H:i') }}
                · {{ $subject->name }}
            </p>
        </div>
        <a href="{{ route('cashier.pv.transactions', $pvQuery) }}" class="btn btn-outline btn-sm">Retour historique</a>
    </div>

    {{-- Résumé transaction --}}
    <div class="card space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge {{ $type['class'] }}">{{ $type['label'] }}</span>
            @php
                $sourceLabel = match ($transaction->source) {
                    'migration' => 'Import initial (reprise d’ancien solde)',
                    'distribution' => 'Distribution',
                    'sale' => 'Vente',
                    default => ucfirst(str_replace('_', ' ', (string) $transaction->source)),
                };
            @endphp
            <span class="text-xs text-[var(--text-secondary)]">{{ $sourceLabel }}</span>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="label">PV</div>
                <div class="value {{ $isCredit ? 'text-[var(--ui-stat-success)]' : 'text-[var(--ui-stat-danger)]' }}">
                    {{ $isCredit ? '+' : '-' }}{{ number_format((float) $transaction->pv_amount) }} PV
                </div>
                @if($transaction->bv_amount)
                    <div class="sub">{{ number_format((float) $transaction->bv_amount) }} BV</div>
                @endif
            </div>
            <div class="detail-item">
                <div class="label">Date</div>
                <div class="value">{{ $transaction->created_at->format('d/m/Y') }}</div>
                <div class="sub">{{ $transaction->created_at->format('H:i:s') }}</div>
            </div>
            @if($allocation)
                <div class="detail-item">
                    <div class="label">Statut allocation</div>
                    <div class="value">{{ ucfirst($allocation->status) }}</div>
                    @if($allocation->approved_at)
                        <div class="sub">Approuvée le {{ $allocation->approved_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
            @endif
            @if($networkDepth)
                <div class="detail-item">
                    <div class="label">Niveau sous le distributeur</div>
                    <div class="value">Niveau {{ $networkDepth }}</div>
                </div>
            @endif
        </div>

        @if($transaction->description)
            <p class="text-sm text-[var(--text-secondary)] border-t border-[var(--border-light,#E8EAEE)] pt-3">
                {{ $transaction->description }}
            </p>
        @endif
        @if($allocation?->notes)
            <p class="text-xs text-[var(--text-tertiary)]">Note : {{ $allocation->notes }}</p>
        @endif
    </div>

    {{-- Attribution --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Distributeur</h3>
            @if($distributor)
                <p class="font-semibold text-[var(--text-primary)]">{{ $distributor->name }}</p>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">
                    Code {{ $distributor->sponsor_id ?? 'N/A' }}
                    @if($distributor->phone) · {{ $distributor->phone }} @endif
                </p>
            @else
                <p class="text-sm text-[var(--text-tertiary)]">Non renseigné pour cette transaction</p>
            @endif
        </div>

        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Membre attributaire (PV reçus)</h3>
            @if($attributedMember)
                <p class="font-semibold text-[var(--text-primary)]">{{ $attributedMember->name }}</p>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">
                    Code {{ $attributedMember->sponsor_id ?? 'N/A' }}
                    @if($attributedMember->phone) · {{ $attributedMember->phone }} @endif
                </p>
                <p class="text-xs text-[var(--text-tertiary)] mt-1">
                    PV personnels : {{ number_format((float) ($attributedMember->pv_balance ?? 0)) }}
                </p>
            @else
                <p class="text-sm text-[var(--text-tertiary)]">Aucun membre attributaire lié (ex. migration / crédit portefeuille)</p>
            @endif
        </div>
    </div>

    {{-- Réseau du membre attributaire --}}
    <div class="card">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-1">
            Réseau du membre attributaire
            @if($attributedMember)
                <span class="text-xs text-[var(--text-secondary)] font-normal">
                    — {{ $attributedMember->name }} (aperçu niveaux 1–2 · {{ count($networkMembers) }})
                </span>
            @endif
        </h3>
        <p class="text-xs text-[var(--text-tertiary)] mb-3">
            Filleuls du membre auquel les PV ont été attribués.
        </p>

        @if(!$attributedMember)
            <p class="text-sm text-[var(--text-tertiary)] py-4 text-center">
                Pas de réseau à afficher pour cette transaction.
            </p>
        @elseif(empty($networkMembers))
            <p class="text-sm text-[var(--text-tertiary)] py-4 text-center">
                Ce membre n’a pas encore de filleuls (niveaux 1–2).
            </p>
        @else
            <div class="max-h-96 overflow-y-auto">
                @foreach($networkMembers as $member)
                    <div class="network-member">
                        <div class="avatar">{{ substr($member['name'], 0, 1) }}</div>
                        <div class="info">
                            <div class="name">{{ $member['name'] }}</div>
                            <div class="detail">Niveau {{ $member['level'] }} · {{ $member['sponsor_id'] ?? 'N/A' }}</div>
                        </div>
                        <div class="pv-badge">{{ number_format($member['pv_balance']) }} PV</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
