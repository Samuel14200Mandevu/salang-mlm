@extends('layouts.app')

@section('title', 'Détail du retrait')

@section('content')
@php
    $methodLabels = [
        'guichet' => 'Guichet',
        'crypto' => 'Crypto',
        'mobile_money' => 'Mobile Money',
        'bank' => 'Banque',
    ];
    $status = (string) $withdrawal->status;
    $method = $methodLabels[$withdrawal->method] ?? ucfirst((string) $withdrawal->method);
    $dateSource = $withdrawal->completed_at ?? $withdrawal->processed_at ?? $withdrawal->created_at;
    $dateLabel = $dateSource?->locale(app()->getLocale())->translatedFormat('d M Y, H:i') ?? '—';
    $reference = $withdrawal->receipt_number
        ?: ('#WD-'.str_pad((string) $withdrawal->id, 6, '0', STR_PAD_LEFT));

    // Un écran par statut (style mockup payaiment.webp)
    $screen = match ($status) {
        'completed' => [
            'variant' => 'success',
            'title' => 'Paiement réussi !',
            'subtitle' => 'Votre retrait a été traité avec succès !',
            'button' => 'Terminé',
            'icon' => 'check',
        ],
        'approved' => [
            'variant' => 'approved',
            'title' => 'Retrait approuvé !',
            'subtitle' => 'Votre demande a été approuvée. Le transfert sera finalisé sous peu.',
            'button' => 'Terminé',
            'icon' => 'check',
        ],
        'processing' => [
            'variant' => 'processing',
            'title' => 'Paiement en cours',
            'subtitle' => 'Votre retrait est en cours de traitement.',
            'button' => 'Terminé',
            'icon' => 'clock',
        ],
        'pending' => [
            'variant' => 'pending',
            'title' => 'Retrait en attente',
            'subtitle' => $withdrawal->method === 'guichet'
                ? 'Présentez-vous au guichet pour retirer vos espèces.'
                : 'Votre demande est enregistrée et en attente de validation.',
            'button' => 'Terminé',
            'icon' => 'clock',
        ],
        'rejected' => [
            'variant' => 'failed',
            'title' => 'Paiement échoué !',
            'subtitle' => $withdrawal->notes
                ?: 'Votre demande de retrait a été refusée. Veuillez choisir une autre méthode.',
            'button' => 'Retour',
            'icon' => 'x',
        ],
        'cancelled' => [
            'variant' => 'failed',
            'title' => 'Paiement échoué !',
            'subtitle' => $withdrawal->notes
                ?: 'Ce retrait a été annulé. Le montant a été recrédité sur votre portefeuille.',
            'button' => 'Retour',
            'icon' => 'x',
        ],
        'failed' => [
            'variant' => 'failed',
            'title' => 'Paiement échoué !',
            'subtitle' => $withdrawal->notes
                ?: 'Veuillez choisir une autre méthode de paiement.',
            'button' => 'Retour',
            'icon' => 'x',
        ],
        default => [
            'variant' => 'pending',
            'title' => 'Détail du retrait',
            'subtitle' => 'Statut : '.ucfirst($status),
            'button' => 'Retour',
            'icon' => 'clock',
        ],
    };
@endphp

<div class="member-withdraw member-withdraw--fintech member-withdraw--result">
    <div class="member-pay-result-page">
        <div class="member-pay-result member-pay-result--{{ $screen['variant'] }} member-pay-result--page">
            <a href="{{ route('withdrawal.index') }}" class="member-pay-result__close" aria-label="Fermer">
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

            {{-- Toujours les détails de CETTE opération --}}
            <div class="member-pay-result__details">
                <h2 class="member-pay-result__details-title">Détails de la transaction</h2>
                <dl class="member-pay-result__rows">
                    <div class="member-pay-result__row">
                        <dt>Montant</dt>
                        <dd>${{ number_format((float) $withdrawal->amount, 2) }}</dd>
                    </div>
                    @if((float) $withdrawal->fee > 0)
                        <div class="member-pay-result__row">
                            <dt>Frais</dt>
                            <dd>${{ number_format((float) $withdrawal->fee, 2) }}</dd>
                        </div>
                        <div class="member-pay-result__row">
                            <dt>Net</dt>
                            <dd>${{ number_format((float) $withdrawal->net_amount, 2) }}</dd>
                        </div>
                    @endif
                    <div class="member-pay-result__row">
                        <dt>Méthode</dt>
                        <dd>{{ $method }}</dd>
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
                        <dd>{{ match ($status) {
                            'pending' => 'En attente',
                            'approved' => 'Approuvé',
                            'processing' => 'En cours',
                            'completed' => 'Terminé',
                            'rejected' => 'Refusé',
                            'cancelled' => 'Annulé',
                            'failed' => 'Échec',
                            default => ucfirst($status),
                        } }}</dd>
                    </div>
                </dl>
            </div>

            <a href="{{ route('withdrawal.index') }}" class="member-pay-result__btn">
                {{ $screen['button'] }}
            </a>
        </div>
    </div>
</div>
@endsection
