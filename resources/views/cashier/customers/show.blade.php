@extends('cashier.layouts.app')

@section('title', 'Client POS')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)] tracking-tight">
                {{ $customer->name }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Client POS · {{ $customer->sponsor_id ?? '—' }}
            </p>
        </div>
        <a href="{{ route('cashier.customers') }}" class="btn btn-outline btn-sm">
            Retour aux clients
        </a>
    </div>

    <div class="md:hidden admin-page-header page-header animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">{{ $customer->name }}</h1>
                <p class="page-subtitle">Client POS</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
        <div class="lg:col-span-1 space-y-3">
            <div class="card p-4 space-y-3">
                <h2 class="text-sm font-semibold text-[var(--text-primary)]">Coordonnées</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="text-[var(--text-secondary)]">Téléphone</span>
                        <span class="font-medium">{{ $customer->phone ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-[var(--text-secondary)]">Email</span>
                        <span class="font-medium text-right break-all">{{ $customer->email ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-[var(--text-secondary)]">Code client</span>
                        <span class="font-mono text-xs">{{ $customer->sponsor_id ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-[var(--text-secondary)]">Statut</span>
                        <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $customer->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-[var(--text-secondary)]">Inscrit</span>
                        <span>{{ $customer->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="card p-4 space-y-2">
                <h2 class="text-sm font-semibold text-[var(--text-primary)]">Parrain</h2>
                @if($customer->parrain)
                    <p class="text-sm font-medium">
                        @if($customer->parrain->sponsor_id){{ $customer->parrain->sponsor_id }} : @endif
                        {{ $customer->parrain->name }}
                    </p>
                    @if($customer->parrain->phone)
                        <p class="text-xs text-[var(--text-secondary)]">{{ $customer->parrain->phone }}</p>
                    @endif
                @else
                    <p class="text-sm text-[var(--text-tertiary)]">Aucun parrain enregistré</p>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div class="card p-3 text-center">
                    <p class="text-[10px] uppercase tracking-wide text-[var(--text-tertiary)]">Commandes</p>
                    <p class="text-lg font-bold">{{ $stats['total_orders'] }}</p>
                </div>
                <div class="card p-3 text-center">
                    <p class="text-[10px] uppercase tracking-wide text-[var(--text-tertiary)]">Complétées</p>
                    <p class="text-lg font-bold">{{ $stats['completed_orders'] }}</p>
                </div>
                <div class="card p-3 text-center">
                    <p class="text-[10px] uppercase tracking-wide text-[var(--text-tertiary)]">Total</p>
                    <p class="text-lg font-bold">${{ number_format((float) $stats['total_spent'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 card p-3 sm:p-4">
            <h2 class="text-sm font-semibold text-[var(--text-primary)] mb-3">Dernières commandes</h2>
            <div class="table-wrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="cursor-pointer" onclick="window.location='{{ route('cashier.orders.show', $order->id) }}'">
                                <td class="font-mono text-xs">{{ $order->order_number }}</td>
                                <td class="text-xs text-[var(--text-secondary)]">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="font-medium">${{ number_format((float) $order->total, 2) }}</td>
                                <td>
                                    <span class="badge {{ $order->status === 'completed' ? 'badge-success' : ($order->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-[var(--text-secondary)] text-sm">
                                    Aucune commande pour ce client
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
