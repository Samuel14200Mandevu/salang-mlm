@extends('cashier.layouts.app')

@section('title', 'Commissions de ' . $member->name)

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Commissions de {{ $member->name }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                ID: {{ $member->id }} • Code: {{ $member->sponsor_id ?? 'N/A' }}
            </p>
        </div>
        <a href="{{ route('cashier.members.show', $member->id) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    {{-- STATISTIQUES --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--primary-navy)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--primary-navy)]">${{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Payé</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-success)]">${{ number_format($stats['paid'] ?? 0, 2) }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-accent)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Transactions</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-accent)]">{{ $commissions->total() ?? 0 }}</p>
        </div>

        <div class="card-stats">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-[var(--ui-stat-warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">PV</span>
            </div>
            <p class="text-lg sm:text-xl font-bold text-[var(--ui-stat-warning)]">{{ $member->pv_balance ?? 0 }}</p>
        </div>
    </div>

    {{-- LISTE DES COMMISSIONS --}}
    <div class="card p-3 sm:p-4">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm">
                Liste des commissions
                <span class="text-xs text-[var(--text-secondary)] font-normal ml-1">
                    ({{ $commissions->total() ?? 0 }})
                </span>
            </h3>
            <span class="badge-status badge-status-paid">Payée</span>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th class="hidden md:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions ?? [] as $commission)
                        @php
                            $typeClass = 'type-badge-' . $commission->type;
                            if (!in_array($commission->type, ['sponsor', 'direct', 'indirect', 'leadership', 'cash_pos'])) {
                                $typeClass = 'type-badge-default';
                            }
                            $typeLabel = $commission->type_label ?? ucfirst(str_replace('_', ' ', $commission->type));
                            if ($commission->type == 'cash_pos') { $typeLabel = 'POS'; }
                            $detailUrl = route('cashier.commissions.show', $commission->id);
                        @endphp
                        <tr class="commission-row cursor-pointer"
                            role="link"
                            tabindex="0"
                            data-href="{{ $detailUrl }}"
                            onclick="window.location.href=this.dataset.href"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href=this.dataset.href}">
                            <td class="font-mono text-xs">#{{ $commission->id }}</td>
                            <td>
                                <span class="type-badge {{ $typeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td>
                                @if($commission->source == 'pos')
                                    <span class="badge-status badge-active">POS</span>
                                @elseif($commission->source == 'mlm')
                                    <span class="badge-status" style="background: rgba(59,130,246,0.12); color:#3b82f6;">MLM</span>
                                @elseif($commission->source == 'membership')
                                    <span class="badge-status" style="background: rgba(139,92,246,0.12); color:#7C3AED;">ADHÉSION</span>
                                @elseif($commission->source == 'manual')
                                    <span class="badge-status" style="background: rgba(245,158,11,0.12); color:#A65A0E;">MANUEL</span>
                                @else
                                    <span class="badge-status" style="background: var(--bg-secondary); color:var(--text-secondary);">—</span>
                                @endif
                            </td>
                            <td class="font-bold 
                                @if($commission->amount > 0)
                                    text-[var(--ui-stat-success)]
                                @else
                                    text-[var(--ui-stat-danger)]
                                @endif
                                text-sm">
                                @if($commission->amount > 0 && in_array($commission->type, ['sponsor', 'direct', 'indirect', 'leadership', 'cash_pos']))
                                    +${{ number_format($commission->amount, 2) }}
                                @else
                                    ${{ number_format($commission->amount, 2) }}
                                @endif
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
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucune commission</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Ce membre n'a pas encore de commissions</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($commissions) && $commissions->hasPages())
            <div class="mt-3 sm:mt-4">
                <x-salang-pagination :paginator="$commissions" />
            </div>
        @endif
    </div>
</div>
@endsection