@extends('cashier.layouts.app')

@push('styles')
@include('cashier.partials.commission-ui-styles')
<style>
    /* Ticket reçu : masqué à l’écran, visible uniquement à l’impression */
    .guichet-print-root {
        display: none;
    }
    .guichet-ticket {
        width: 100%;
        max-width: 360px;
        margin: 0 auto;
        background: #fff;
        color: #1a1a1a;
        font-family: 'Courier New', monospace;
        font-size: 11px;
        padding: 12px 14px 16px;
    }
    .guichet-ticket .header {
        text-align: center;
        border-bottom: 2px dashed #ccc;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    .guichet-ticket .store-logo {
        margin: 0 auto 4px;
        max-width: 70px;
    }
    .guichet-ticket .store-logo img {
        width: 100%;
        height: auto;
        display: block;
    }
    .guichet-ticket .store-name {
        color: #0E2F76;
        font-size: 16px;
        font-weight: 900;
        letter-spacing: 2px;
    }
    .guichet-ticket .store-sub {
        font-size: 7px;
        color: #666;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .guichet-ticket .store-contact {
        font-size: 7px;
        color: #555;
        line-height: 1.5;
        margin-top: 4px;
    }
    .guichet-ticket .store-address {
        font-size: 6.5px;
        color: #666;
        line-height: 1.4;
        margin-top: 3px;
        background: #f8fafc;
        padding: 4px 6px;
        border-radius: 3px;
    }
    .guichet-ticket .store-reg {
        font-size: 6px;
        color: #888;
        line-height: 1.4;
        margin-top: 3px;
        border-top: 1px dotted #e5e7eb;
        padding-top: 4px;
    }
    .guichet-ticket .separator-dashed { border-top: 1px dashed #ddd; margin: 5px 0; }
    .guichet-ticket .doc-title {
        font-size: 8px;
        font-weight: 700;
        color: #0E2F76;
        letter-spacing: 1px;
    }
    .guichet-ticket .doc-place {
        font-size: 7px;
        color: #888;
        margin-top: 2px;
    }
    .guichet-ticket .ticket-number {
        font-size: 13px;
        font-weight: 700;
        color: #0E2F76;
        margin-top: 5px;
        letter-spacing: 1px;
    }
    .guichet-ticket .ticket-date {
        font-size: 8px;
        color: #888;
        margin-top: 2px;
    }
    .guichet-ticket .source-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 7px;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 3px;
        background: #dbeafe;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    .guichet-ticket .status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 4px;
    }
    .guichet-ticket .status-badge.is-paid { background: #dcfce7; color: #16a34a; }
    .guichet-ticket .status-badge.is-pending { background: #fef3c7; color: #d97706; }
    .guichet-ticket .info {
        margin-bottom: 8px;
        border-bottom: 1px dashed #ddd;
        padding-bottom: 6px;
    }
    .guichet-ticket .info .row,
    .guichet-ticket .total .row,
    .guichet-ticket .payment-info .row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 2px 0;
        font-size: 9px;
    }
    .guichet-ticket .label { color: #888; flex-shrink: 0; }
    .guichet-ticket .value { font-weight: 600; color: #1a1a1a; text-align: right; }
    .guichet-ticket .amount-box {
        background: #f0f7ff;
        border: 1px solid #bfdbfe;
        border-left: 3px solid #0E2F76;
        border-radius: 4px;
        padding: 8px 10px;
        margin: 8px 0;
        text-align: center;
    }
    .guichet-ticket .amount-box .label {
        font-size: 8px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .guichet-ticket .amount-box .value {
        font-size: 18px;
        font-weight: 900;
        color: #0E2F76;
        margin-top: 2px;
        text-align: center;
    }
    .guichet-ticket .total {
        border-top: 2px dashed #ccc;
        padding-top: 6px;
        margin-top: 6px;
    }
    .guichet-ticket .total .row .label { color: #666; }
    .guichet-ticket .total-row {
        font-size: 14px !important;
        font-weight: 900;
        border-top: 2px double #1a1a1a;
        padding-top: 4px !important;
        margin-top: 3px;
    }
    .guichet-ticket .total-row .label { color: #1a1a1a; }
    .guichet-ticket .total-row .value { color: #0E2F76; }
    .guichet-ticket .payment-info {
        font-size: 8px;
        padding: 6px 8px;
        background: #f8fafc;
        border-radius: 4px;
        margin: 6px 0;
        border: 1px solid #e5e7eb;
    }
    .guichet-ticket .payment-info .value.paid { color: #22c55e; }
    .guichet-ticket .footer {
        text-align: center;
        font-size: 7px;
        color: #999;
        margin-top: 8px;
        border-top: 1px dashed #ddd;
        padding-top: 6px;
        line-height: 1.5;
    }
    .guichet-ticket .thank-you {
        font-size: 10px;
        font-weight: 700;
        color: #0E2F76;
        letter-spacing: 1px;
    }
    .guichet-ticket .barcode {
        font-size: 14px;
        letter-spacing: 2px;
        color: #333;
        font-weight: 700;
        margin: 3px 0;
    }

    .guichet-detail-card {
        padding: 0;
        overflow: hidden;
    }
    .guichet-detail-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1.1rem;
        border-bottom: 1px solid var(--border-light);
        background: color-mix(in srgb, var(--bg-secondary) 70%, transparent);
    }
    .guichet-detail-card__head h2 {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .guichet-detail-card__body {
        padding: 0.25rem 1.1rem 0.5rem;
    }
    .guichet-detail-card .detail-row {
        padding: 0.7rem 0;
    }
    .guichet-detail-card .detail-row .value {
        font-weight: 600;
        text-align: right;
        word-break: break-word;
    }
    .guichet-detail-actions {
        margin: 0;
        padding: 1rem 1.1rem 1.15rem;
        border-top: 1px solid var(--border-light);
        background: color-mix(in srgb, var(--bg-secondary) 45%, var(--bg-card));
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .guichet-detail-actions__title {
        margin: 0;
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .guichet-detail-actions__field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.35rem;
    }
    .guichet-detail-actions__field .form-control {
        width: 100%;
        max-width: 16rem;
        padding: 0.55rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.875rem;
    }
    .guichet-detail-actions__field .help-text {
        margin: 0.35rem 0 0;
        font-size: 0.6875rem;
        color: var(--text-tertiary);
        line-height: 1.35;
    }
    .guichet-detail-actions__buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .guichet-detail-actions__buttons .btn {
        min-height: 2.5rem;
    }
    .guichet-detail-actions__buttons .btn-primary {
        flex: 1 1 14rem;
    }
    .guichet-detail-actions__buttons .btn-danger,
    .guichet-detail-actions__buttons .btn-outline {
        flex: 0 0 auto;
    }
    @media (max-width: 640px) {
        .guichet-detail-actions__field .form-control {
            max-width: none;
        }
        .guichet-detail-actions__buttons .btn {
            flex: 1 1 100%;
            justify-content: center;
        }
    }

    @media print {
        body * { visibility: hidden !important; }
        .guichet-print-root,
        .guichet-print-root * { visibility: visible !important; }
        .guichet-print-root {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: #fff;
            padding: 0;
            margin: 0;
        }
        .guichet-ticket {
            max-width: 100%;
            box-shadow: none;
            border: none;
            padding: 8px 10px 12px;
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
                Imprimer le reçu
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
            <div class="number">${{ number_format((float) ($withdrawal->fee ?? 0), 2) }}</div>
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
    @php
        $bonusPhaseLabel = null;
        if (is_string($withdrawal->notes) && preg_match('/\[bonus_phase=(direct|il)\]/', $withdrawal->notes, $phaseMatch)) {
            $bonusPhaseLabel = $phaseMatch[1] === 'il' ? 'Indirect & Leadership' : 'Direct';
        }
        $defaultPaidOn = old('paid_on');
        if (! $defaultPaidOn && is_string($withdrawal->notes) && preg_match('/\[paid_on=(\d{4}-\d{2}-\d{2})\]/', $withdrawal->notes, $paidOnMatch)) {
            $defaultPaidOn = $paidOnMatch[1];
        }
        $defaultPaidOn = $defaultPaidOn ?: now()->toDateString();
    @endphp
    <div class="card guichet-detail-card">
        <div class="guichet-detail-card__head">
            <h2>Détails du retrait</h2>
            <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
        </div>

        <div class="guichet-detail-card__body">
            <div class="detail-row">
                <span class="label">Membre</span>
                <span class="value">{{ $memberLabel }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Téléphone</span>
                <span class="value">{{ $member->phone ?? '—' }}</span>
            </div>
            @if($bonusPhaseLabel)
                <div class="detail-row">
                    <span class="label">Type de paiement</span>
                    <span class="value">{{ $bonusPhaseLabel }}</span>
                </div>
            @endif
            <div class="detail-row">
                <span class="label">Demandé le</span>
                <span class="value">{{ $withdrawal->created_at?->format('d/m/Y H:i') }}</span>
            </div>
            @if($withdrawal->receipt_number)
                <div class="detail-row">
                    <span class="label">N° reçu</span>
                    <span class="value font-mono">{{ $withdrawal->receipt_number }}</span>
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
        </div>

        @if($withdrawal->status === 'pending')
            <div class="guichet-detail-actions">
                <p class="guichet-detail-actions__title">Valider la remise</p>

                <form method="POST"
                      action="{{ route('cashier.guichet.fulfill', $withdrawal->id) }}"
                      id="guichetFulfillForm"
                      class="guichet-fulfill-form space-y-3">
                    @csrf
                    <div class="guichet-detail-actions__field">
                        <label for="guichetFulfillPaidOn">Date du paiement</label>
                        <input type="date"
                               id="guichetFulfillPaidOn"
                               name="paid_on"
                               class="form-control"
                               value="{{ $defaultPaidOn }}"
                               max="{{ now()->toDateString() }}"
                               min="{{ now()->subDays(90)->toDateString() }}"
                               required>
                        <p class="help-text">Pour un paiement déjà remis au guichet (saisie manuelle). Par défaut : aujourd’hui.</p>
                    </div>

                    <div class="guichet-detail-actions__buttons">
                        <button type="submit" class="btn btn-primary">
                            Confirmer remise + préparer reçu
                        </button>
                        <button type="submit"
                                form="guichetCancelForm"
                                class="btn btn-danger">
                            Annuler le retrait
                        </button>
                        <button type="button"
                                class="btn btn-outline md:hidden"
                                onclick="printGuichetReceipt()">
                            Imprimer le reçu
                        </button>
                    </div>
                </form>

                <form method="POST"
                      action="{{ route('cashier.guichet.cancel', $withdrawal->id) }}"
                      id="guichetCancelForm"
                      class="guichet-cancel-form hidden">
                    @csrf
                </form>
            </div>
        @endif
    </div>
</div>

{{-- Reçu stylé (même page, impression uniquement) --}}
<div class="guichet-print-root" id="guichetPrintRoot" aria-hidden="true">
    @include('cashier.guichet.partials.receipt-ticket', ['withdrawal' => $withdrawal])
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

});
</script>
@endpush
@endsection
