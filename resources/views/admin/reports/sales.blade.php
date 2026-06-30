@extends('admin.layouts.app')

@push('styles')
<style>
    .sales-row:hover { background: var(--bg-hover); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📈 Rapport des ventes</h1>
            <p class="text-[var(--text-secondary)] mt-1">Analyse détaillée des commandes</p>
        </div>
        <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm">
            ← Retour
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Total commandes</p>
            <p class="text-2xl font-bold text-primary-500">{{ $stats['total_orders'] ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Chiffre d'affaires</p>
            <p class="text-2xl font-bold text-green-500">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Panier moyen</p>
            <p class="text-2xl font-bold text-blue-500">${{ number_format($stats['avg_order_value'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-yellow-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">TVA totale</p>
            <p class="text-2xl font-bold text-yellow-500">${{ number_format($stats['total_tax'] ?? 0, 2) }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-5">
            <p class="text-sm text-[var(--text-secondary)]">Livraison</p>
            <p class="text-2xl font-bold text-purple-500">${{ number_format($stats['total_shipping'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>N° commande</th>
                        <th>Client</th>
                        <th class="hidden md:table-cell">Articles</th>
                        <th class="text-right">Sous-total</th>
                        <th class="text-right hidden sm:table-cell">TVA</th>
                        <th class="text-right">Total</th>
                        <th>Statut</th>
                        <th class="text-right">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
                        <tr class="sales-row">
                            <td class="font-mono text-sm">#{{ $order->order_number }}</td>
                            <td>{{ $order->user?->name ?? 'N/A' }}</td>
                            <td class="hidden md:table-cell">{{ $order->items->count() }}</td>
                            <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
                            <td class="text-right hidden sm:table-cell">${{ number_format($order->tax, 2) }}</td>
                            <td class="text-right font-bold text-primary-500">${{ number_format($order->total, 2) }}</td>
                            <td>
                                <span class="badge {{ $order->status == 'completed' ? 'badge-success' : ($order->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="text-right text-sm text-[var(--text-secondary)]">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                Aucune commande
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($orders) && $orders->hasPages())
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection