@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
@endpush

@section('title', 'Détail bonus — '.$member->name)

@section('content')
@php
    $monthNames = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];
    $formatPeriod = function (?string $period) use ($monthNames): string {
        if (! $period || ! preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
            return (string) $period;
        }

        return ($monthNames[(int) $m[2]] ?? $period).' '.$m[1];
    };
    $periodLabel = $formatPeriod($period);
    $isPending = ($summary['pending_amount'] ?? 0) > 0;
    $typeLabels = [
        'direct' => 'Direct',
        'indirect' => 'Indirect',
        'leadership' => 'Leadership',
    ];
@endphp
<div class="space-y-4 sm:space-y-6">

    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">{{ $member->name }}</h1>
                <p class="page-subtitle">{{ $member->sponsor_id }} · {{ $periodLabel }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <a href="{{ route('cashier.offline-bonus.pay', ['period' => $period]) }}"
                   class="btn btn-outline btn-sm btn-round-icon" title="Retour" aria-label="Retour">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">{{ $member->name }}</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                {{ $member->sponsor_id }}
                · Parrain {{ $member->parrain?->sponsor_id ?? '—' }} {{ $member->parrain?->name ? '('.$member->parrain->name.')' : '' }}
                · {{ $periodLabel }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap header-actions">
            <a href="{{ route('cashier.offline-bonus.pay', ['period' => $period]) }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour paiement
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <div class="commission-stats">
        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="number">{{ $summary['line_count'] }}</div>
            <div class="label">Lignes</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($summary['pending_amount'], 2) }}</div>
            <div class="label">À payer</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($summary['paid_amount'], 2) }}</div>
            <div class="label">Payé</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($summary['total_amount'], 2) }}</div>
            <div class="label">Total</div>
        </div>
    </div>

    @if($isPending)
        <form method="POST"
              action="{{ route('cashier.offline-bonus.pay.member', $member->id) }}"
              id="offlinePayMemberForm">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            <button type="submit" class="btn btn-primary w-full md:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Envoyer au portefeuille — ${{ number_format($summary['pending_amount'], 2) }}
            </button>
        </form>
    @endif

    <div class="card">
        <div class="card-title" style="font-size:1rem;font-weight:700;margin-bottom:1rem;">
            Détail des bonus
            <span class="text-sm font-medium text-[var(--text-tertiary)]">({{ $summary['line_count'] }})</span>
        </div>
        <div class="table-wrap">
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>De</th>
                        <th>Gén.</th>
                        <th class="col-amount">%</th>
                        <th class="col-amount">PV</th>
                        <th class="col-amount">Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $commission)
                        <tr>
                            <td>{{ $typeLabels[$commission->type] ?? ucfirst($commission->type) }}</td>
                            <td>
                                @if($commission->from_user_id && $commission->from_user_id !== $commission->user_id)
                                    <span class="commission-member">{{ $commission->fromUser?->sponsor_id ?? '—' }}</span>
                                    <div class="text-xs text-[var(--text-tertiary)]">{{ $commission->fromUser?->name ?? '' }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $commission->generation ?? 0 }}</td>
                            <td class="col-amount">{{ number_format((float) $commission->percentage, 2) }}%</td>
                            <td class="col-amount">{{ number_format((float) ($commission->pv_used ?? 0), 1) }}</td>
                            <td class="col-amount">${{ number_format((float) $commission->amount, 2) }}</td>
                            <td>
                                @if(in_array($commission->status, ['pending', 'approved'], true))
                                    <span class="badge badge-warning">En attente</span>
                                @elseif($commission->status === 'paid')
                                    <span class="badge badge-success">Payé</span>
                                @else
                                    <span class="badge">{{ $commission->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td colspan="5" style="text-align:right;">Total</td>
                        <td class="col-amount">${{ number_format($summary['total_amount'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('offlinePayMemberForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const el = this;
        if (typeof window.showConfirmDialog !== 'function') {
            el.submit();
            return;
        }
        window.showConfirmDialog({
            type: 'primary',
            title: 'Envoyer au portefeuille',
            message: 'Créditer ${{ number_format($summary['pending_amount'], 2) }} à {{ $member->name }} pour {{ $periodLabel }} ?',
            confirmText: 'Envoyer',
            onConfirm: function () { el.submit(); },
            form: el
        });
    });
});
</script>
@endpush
