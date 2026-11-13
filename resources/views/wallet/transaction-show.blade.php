@extends('layouts.app')

@section('title', 'Détail de l’opération')

@section('content')
@php
    $status = (string) $transaction->status;
    $type = (string) $transaction->type;
    $isGuichetRefund = $transaction->isGuichetFundReturn();
    $typeLabel = $transaction->type_label;
    $amount = (float) $transaction->amount;
    $dateSource = $transaction->completed_at ?? $transaction->created_at;
    $dateLabel = $dateSource?->locale(app()->getLocale())->translatedFormat('d M Y, H:i') ?? '—';
    $reference = $transaction->reference
        ?: ($transaction->transaction_id
            ?: ('#TX-'.str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT)));

    $isFailed = in_array($status, ['failed', 'cancelled'], true) || $isGuichetRefund;
    $isPending = in_array($status, ['pending', 'processing'], true);
    $isSuccess = ! $isFailed && ! $isPending;

    if ($isGuichetRefund) {
        $screen = [
            'variant' => 'failed',
            'title' => 'Paiement échoué !',
            'subtitle' => 'Ce retrait a été annulé. Le montant a été recrédité sur votre portefeuille.',
            'button' => 'Retour',
            'icon' => 'x',
        ];
    } elseif ($isFailed) {
        $screen = [
            'variant' => 'failed',
            'title' => 'Paiement échoué !',
            'subtitle' => $transaction->description
                ?: 'Veuillez choisir une autre méthode de paiement.',
            'button' => 'Retour',
            'icon' => 'x',
        ];
    } elseif ($isPending) {
        $screen = [
            'variant' => $status === 'processing' ? 'processing' : 'pending',
            'title' => $type === 'withdrawal' ? 'Retrait en attente' : 'Paiement en cours',
            'subtitle' => $transaction->description
                ?: 'Cette opération est encore en cours de traitement.',
            'button' => 'Terminé',
            'icon' => 'clock',
        ];
    } else {
        $screen = match ($type) {
            'deposit' => [
                'variant' => 'success',
                'title' => 'Paiement réussi !',
                'subtitle' => 'Votre dépôt a été traité avec succès !',
                'button' => 'Terminé',
                'icon' => 'check',
            ],
            'withdrawal' => [
                'variant' => 'success',
                'title' => 'Paiement réussi !',
                'subtitle' => 'Votre retrait a été traité avec succès !',
                'button' => 'Terminé',
                'icon' => 'check',
            ],
            'commission' => [
                'variant' => 'success',
                'title' => 'Commission reçue !',
                'subtitle' => 'Votre commission a été créditée avec succès.',
                'button' => 'Terminé',
                'icon' => 'check',
            ],
            'refund' => [
                'variant' => 'approved',
                'title' => 'Remboursement effectué',
                'subtitle' => 'Le montant a été recrédité sur votre portefeuille.',
                'button' => 'Terminé',
                'icon' => 'check',
            ],
            default => [
                'variant' => 'success',
                'title' => 'Paiement réussi !',
                'subtitle' => 'Votre opération a été traitée avec succès !',
                'button' => 'Terminé',
                'icon' => 'check',
            ],
        };
    }
@endphp

<div class="member-wallet member-wallet--fintech member-wallet--tx-result">
    <div class="member-pay-result-page">
        <div class="member-pay-result member-pay-result--{{ $screen['variant'] }} member-pay-result--page">
            <a href="{{ route('wallet.index') }}" class="member-pay-result__close" aria-label="Fermer">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </a>

            <div class="member-pay-result__icon" aria-hidden="true">
                @if($screen['icon'] === 'check')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                @elseif($screen['icon'] === 'x')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>

            <h1 class="member-pay-result__title">{{ $screen['title'] }}</h1>
            <p class="member-pay-result__sub">{{ $screen['subtitle'] }}</p>

            <div class="member-pay-result__details">
                <h2 class="member-pay-result__details-title">Détails de la transaction</h2>
                <dl class="member-pay-result__rows">
                    <div class="member-pay-result__row">
                        <dt>Montant</dt>
                        <dd>{{ $amount > 0 ? '+' : '' }}${{ number_format($amount, 2) }}</dd>
                    </div>
                    <div class="member-pay-result__row">
                        <dt>Type</dt>
                        <dd>{{ $typeLabel }}</dd>
                    </div>
                    <div class="member-pay-result__row">
                        <dt>Date</dt>
                        <dd>{{ $dateLabel }}</dd>
                    </div>
                    <div class="member-pay-result__row">
                        <dt>Référence</dt>
                        <dd>{{ $reference }}</dd>
                    </div>
                    <div class="member-pay-result__row">
                        <dt>Statut</dt>
                        <dd>{{ $transaction->status_label }}</dd>
                    </div>
                </dl>
            </div>

            <a href="{{ route('wallet.index') }}" class="member-pay-result__btn">
                {{ $screen['button'] }}
            </a>
        </div>
    </div>
</div>
@endsection
