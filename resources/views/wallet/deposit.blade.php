@extends('layouts.app')

@section('title', 'Dépôt')

@section('content')
@php
    $statusLabels = [
        'pending' => 'En attente',
        'completed' => 'Terminé',
        'failed' => 'Échec',
        'cancelled' => 'Annulé',
    ];
@endphp

<div class="member-deposit member-deposit--fintech">
    <div class="member-fintech-shell">
        @include('wallet.partials.fintech-deposit-hero')

        <div class="member-fintech-panel member-withdraw-form-panel member-fintech-animate member-fintech-animate--d3">
            @include('wallet.partials.deposit-form', [
                'formId' => 'depositFormMobile',
                'variant' => 'fintech',
            ])
        </div>

        @include('wallet.partials.fintech-deposit-history')
    </div>

    <div class="member-deposit-classic space-y-4 sm:space-y-6">
        <div class="animate-fadeInUp">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Faire un dépôt</h1>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Alimentez votre portefeuille USD</p>
                </div>
                <a href="{{ route('wallet.index') }}" class="btn btn-outline text-sm">← Portefeuille</a>
            </div>
        </div>

        <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-green-500 p-3 sm:p-4">
            <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Solde disponible</p>
            <p class="text-2xl sm:text-3xl font-bold text-green-600">${{ number_format($balance ?? 0, 2) }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-0.5 sm:mt-1">Frais de dépôt : 0 %</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="card animate-fadeInLeft delay-2 p-4 md:p-6 member-withdraw-form-panel">
                @include('wallet.partials.deposit-form', [
                    'formId' => 'depositFormDesktop',
                    'variant' => 'classic',
                ])
            </div>

            <div class="card animate-fadeInRight delay-3 p-4 md:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-[var(--text-primary)]">Historique des dépôts</h3>
                    <span class="badge badge-neutral text-xs">{{ ($deposits ?? collect())->count() }} dépôts</span>
                </div>

                <div class="space-y-2 sm:space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar">
                    @forelse($deposits ?? [] as $deposit)
                        <div class="flex flex-wrap items-center justify-between p-3 bg-[var(--bg-secondary)] rounded-lg gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-green-600 text-sm">+${{ number_format($deposit->amount, 2) }}</p>
                                <p class="text-xs text-[var(--text-secondary)]">
                                    {{ $deposit->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <span class="badge {{ $deposit->status == 'completed' ? 'badge-success' : 'badge-warning' }} text-xs">
                                {{ $statusLabels[$deposit->status] ?? ucfirst($deposit->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-center text-[var(--text-secondary)] py-8 text-sm">Aucun dépôt effectué</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
