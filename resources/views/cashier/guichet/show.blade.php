@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
<style>
    .guichet-thermal {
        display: none;
        width: 280px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        color: #000;
        background: #fff;
        padding: 12px;
    }
    @media print {
        body * { visibility: hidden !important; }
        .guichet-thermal, .guichet-thermal * { visibility: visible !important; }
        .guichet-thermal {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>
@endpush

@section('title', 'Retrait guichet #'.$withdrawal->id)

@section('content')
@php
    $member = $withdrawal->user;
    $memberLabel = $member
        ? trim(($member->sponsor_id ? $member->sponsor_id.' : ' : '').$member->name)
        : 'N/A';
    $statusMap = [
        'pending' => ['label' => 'En attente', 'class' => 'badge-warning'],
        'completed' => ['label' => 'Remis', 'class' => 'badge-success'],
        'rejected' => ['label' => 'Rejeté', 'class' => 'badge-danger'],
        'cancelled' => ['label' => 'Annulé', 'class' => 'badge-secondary'],
    ];
    $status = $statusMap[$withdrawal->status] ?? ['label' => ucfirst((string) $withdrawal->status), 'class' => 'badge-info'];
@endphp
<div class="space-y-4 sm:space-y-6">

    {{-- BANDEAU MOBILE --}}
    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Retrait #{{ $withdrawal->id }}</h1>
                <p class="page-subtitle">{{ $memberLabel }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <a href="{{ route('cashier.guichet.index') }}" class="btn btn-outline btn-sm btn-round-icon" title="Retour" aria-label="Retour">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <button type="button" onclick="printGuichetReceipt()" class="btn btn-primary btn-sm btn-round-icon" title="Imprimer" aria-label="Imprimer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- EN-TÊTE DESKTOP --}}
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Retrait guichet #{{ $withdrawal->id }}</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">{{ $memberLabel }}</p>
        </div>
        <div class="flex gap-2 flex-wrap header-actions">
            <a href="{{ route('cashier.guichet.index') }}" class="btn btn-outline btn-sm">Retour</a>
            <button type="button" onclick="printGuichetReceipt()" class="btn btn-primary btn-sm">
                Imprimer reçu thermique
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- STATS MONTANT --}}
    <div class="commission-stats">
        <div class="commission-stat-card">
            <div class="icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">${{ number_format($withdrawal->amount, 2) }}</div>
            <div class="label">À remettre</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number">$0.00</div>
            <div class="label">Frais</div>
        </div>
        <div class="commission-stat-card">
            <div class="icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="number" style="font-size:1rem;padding-top:0.35rem;">
                <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
            </div>
            <div class="label">Statut</div>
        </div>
    </div>

    {{-- DÉTAIL --}}
    <div class="card">
        <div class="detail-row">
            <span class="label">Membre</span>
            <span class="value">{{ $memberLabel }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Téléphone</span>
            <span class="value">{{ $member->phone ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Demandé le</span>
            <span class="value">{{ $withdrawal->created_at?->format('d/m/Y H:i') }}</span>
        </div>
        @if($withdrawal->receipt_number)
            <div class="detail-row">
                <span class="label">N° reçu</span>
                <span class="value">{{ $withdrawal->receipt_number }}</span>
            </div>
        @endif
        @if($withdrawal->completed_at)
            <div class="detail-row">
                <span class="label">Remis le</span>
                <span class="value">{{ $withdrawal->completed_at->format('d/m/Y H:i') }}</span>
            </div>
        @endif
        @if($withdrawal->cashier)
            <div class="detail-row">
                <span class="label">Caissier</span>
                <span class="value">{{ $withdrawal->cashier->name }}</span>
            </div>
        @endif

        <div class="flex flex-wrap gap-2 mt-4">
            @if($withdrawal->status === 'pending')
                <form method="POST"
                      action="{{ route('cashier.guichet.fulfill', $withdrawal->id) }}"
                      id="guichetFulfillForm"
                      class="guichet-fulfill-form">
                    @csrf
                    <button type="submit" class="btn btn-primary">Confirmer remise + préparer reçu</button>
                </form>
                <form method="POST"
                      action="{{ route('cashier.guichet.cancel', $withdrawal->id) }}"
                      id="guichetCancelForm"
                      class="guichet-cancel-form">
                    @csrf
                    <button type="submit" class="btn btn-danger">Annuler le retrait</button>
                </form>
            @endif
            <button type="button" class="btn btn-outline md:hidden" onclick="printGuichetReceipt()">Imprimer reçu</button>
        </div>
    </div>
</div>

<div class="guichet-thermal" id="guichetThermalTicket">
    <div style="text-align:center;font-weight:700;margin-bottom:8px;">SALANG GROUP</div>
    <div style="text-align:center;margin-bottom:8px;">REÇU RETRAIT GUICHET</div>
    <div>--------------------------------</div>
    <div>N° {{ $withdrawal->receipt_number ?: ('GUICH-'.$withdrawal->id) }}</div>
    <div>Date : {{ ($withdrawal->completed_at ?? now())->format('d/m/Y H:i') }}</div>
    <div>--------------------------------</div>
    <div>Membre : {{ $member->name ?? '—' }}</div>
    <div>ID : {{ $member->sponsor_id ?? '—' }}</div>
    <div>--------------------------------</div>
    <div style="font-size:16px;font-weight:700;margin:8px 0;">
        Montant : ${{ number_format($withdrawal->amount, 2) }}
    </div>
    <div>Frais : $0.00</div>
    <div>Mode : Espèces (guichet)</div>
    <div>--------------------------------</div>
    <div>Caissier : {{ $withdrawal->cashier->name ?? auth()->user()->name }}</div>
    <div style="text-align:center;margin-top:12px;">Merci — Salang Health Care</div>
</div>

@push('scripts')
<script>
function printGuichetReceipt() {
    window.print();
}

document.addEventListener('DOMContentLoaded', function () {
    var memberName = @json($member->name ?? 'ce membre');
    var amountLabel = @json('$' . number_format((float) $withdrawal->amount, 2));

    var fulfillForm = document.getElementById('guichetFulfillForm');
    if (fulfillForm && typeof window.showConfirmDialog === 'function') {
        fulfillForm.addEventListener('submit', function (e) {
            e.preventDefault();
            window.showConfirmDialog({
                type: 'primary',
                title: 'Confirmer la remise',
                message: 'Vous remettez ' + amountLabel + ' en espèces à ' + memberName + '. Continuer et préparer le reçu ?',
                confirmText: 'Confirmer remise',
                onConfirm: function () {
                    fulfillForm.submit();
                },
                form: fulfillForm
            });
        });
    }

    var cancelForm = document.getElementById('guichetCancelForm');
    if (cancelForm && typeof window.showConfirmDialog === 'function') {
        cancelForm.addEventListener('submit', function (e) {
            e.preventDefault();
            window.showConfirmDialog({
                type: 'danger',
                title: 'Annuler le retrait',
                message: 'Annuler ce retrait guichet ? ' + amountLabel + ' sera recrédité au portefeuille de ' + memberName + ' et le membre sera notifié.',
                confirmText: 'Annuler le retrait',
                onConfirm: function () {
                    cancelForm.submit();
                },
                form: cancelForm
            });
        });
    }

@if(session('print_receipt'))
    setTimeout(printGuichetReceipt, 400);
@endif
});
</script>
@endpush
@endsection
