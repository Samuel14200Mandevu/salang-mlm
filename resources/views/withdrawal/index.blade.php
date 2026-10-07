@extends('layouts.app')

@section('title', 'Retraits')

@section('content')
@php
    $methodLabels = [
        'crypto' => 'Crypto',
        'mobile_money' => 'Mobile Money',
        'bank' => 'Banque',
    ];
    $statusLabels = [
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'completed' => 'Terminé',
        'rejected' => 'Refusé',
        'cancelled' => 'Annulé',
        'failed' => 'Échec',
    ];
    $withdrawTotal = $withdrawals instanceof \Illuminate\Pagination\AbstractPaginator
        ? $withdrawals->total()
        : ($withdrawals->count() ?? 0);
@endphp

<div class="member-withdraw member-withdraw--fintech">
    {{-- Mobile : style Binance / fintech --}}
    <div class="member-fintech-shell">
        @include('withdrawal.partials.fintech-hero')

        <div class="member-fintech-panel member-withdraw-form-panel member-fintech-animate member-fintech-animate--d3">
            @include('withdrawal.partials.withdraw-form', [
                'formId' => 'withdrawFormMobile',
                'variant' => 'fintech',
            ])
        </div>

        @include('withdrawal.partials.fintech-history')
    </div>

    {{-- Desktop --}}
    <div class="member-withdraw-classic space-y-4 sm:space-y-6">
        <div class="member-page-intro animate-fadeInUp">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Retrait</h1>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Transférez vos gains vers crypto, mobile money ou banque</p>
                </div>
                <a href="{{ route('wallet.index') }}" class="btn btn-outline text-sm">← Portefeuille</a>
            </div>
        </div>

        <div class="stats-grid grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
            <div class="card-stats animate-fadeInUp delay-1 border-l-4 border-orange-500">
                <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Solde disponible</p>
                <p class="text-2xl sm:text-3xl font-bold text-orange-600">${{ number_format($balance ?? 0, 2) }}</p>
            </div>
            <div class="card-stats animate-fadeInUp delay-2 border-l-4 border-amber-500">
                <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Retraits en cours</p>
                <p class="text-2xl sm:text-3xl font-bold text-yellow-500">${{ number_format($pendingWithdrawals ?? 0, 2) }}</p>
            </div>
            <div class="card-stats animate-fadeInUp delay-3 border-l-4 border-blue-500">
                <p class="text-[10px] sm:text-sm text-[var(--text-secondary)]">Total retiré</p>
                <p class="text-2xl sm:text-3xl font-bold text-blue-500">${{ number_format($stats['total_withdrawn'] ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="card animate-fadeInLeft delay-2 p-4 md:p-6 member-withdraw-form-panel">
                @include('withdrawal.partials.withdraw-form', [
                    'formId' => 'withdrawFormDesktop',
                    'variant' => 'classic',
                ])
            </div>

            <div class="card animate-fadeInRight delay-3 p-4 md:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-[var(--text-primary)]">Historique</h3>
                    <span class="badge badge-neutral text-xs">{{ $withdrawTotal }} demandes</span>
                </div>

                <div class="table-wrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Méthode</th>
                                <th class="text-right">Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($withdrawals ?? [] as $withdrawal)
                                <tr>
                                    <td class="text-[var(--text-secondary)] text-sm whitespace-nowrap">
                                        {{ $withdrawal->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-sm">{{ $methodLabels[$withdrawal->method] ?? $withdrawal->method }}</td>
                                    <td class="text-right font-semibold text-sm text-red-500">
                                        −${{ number_format($withdrawal->amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $withdrawal->status === 'completed' ? 'badge-success' : ($withdrawal->status === 'pending' ? 'badge-warning' : 'badge-danger') }} text-xs">
                                            {{ $statusLabels[$withdrawal->status] ?? $withdrawal->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-[var(--text-secondary)]">Aucune demande de retrait</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($withdrawals instanceof \Illuminate\Pagination\AbstractPaginator && $withdrawals->hasPages())
                    <div class="mt-4">
                        <x-salang-pagination :paginator="$withdrawals" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
