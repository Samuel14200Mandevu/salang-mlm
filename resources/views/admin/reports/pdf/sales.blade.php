{{-- resources/views/admin/reports/pdf/sales.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Ventes')
@section('report_title', 'RAPPORT DES VENTES')

@section('content')

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-label">Commandes</div>
        <div class="stat-value">{{ $stats['total_orders'] ?? 0 }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Chiffre d'affaires</div>
        <div class="stat-value green">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Panier moyen</div>
        <div class="stat-value purple">${{ number_format($stats['avg_order_value'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">TVA</div>
        <div class="stat-value yellow">${{ number_format($stats['total_tax'] ?? 0, 2) }}</div>
    </div>
</div>

<!-- Tableau -->
<table>
    <thead>
        <tr>
            <th>N° commande</th>
            <th>Client</th>
            <th>Articles</th>
            <th>Sous-total</th>
            <th>TVA</th>
            <th>Total</th>
            <th>Statut</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders ?? [] as $order)
            <tr>
                <td>#{{ $order->order_number }}</td>
                <td>{{ $order->user?->name ?? 'N/A' }}</td>
                <td>{{ $order->items->count() }}</td>
                <td>${{ number_format($order->subtotal, 2) }}</td>
                <td>${{ number_format($order->tax, 2) }}</td>
                <td><strong>${{ number_format($order->total, 2) }}</strong></td>
                <td>
                    <span class="badge {{ $order->status == 'completed' ? 'badge-success' : ($order->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#999; padding:20px;">
                    Aucune commande trouvée
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Récapitulatif par package -->
<div style="margin-top:15px; padding:10px; background:#f9fafb; border-radius:4px; border:1px solid #e5e7eb;">
    <h4 style="font-size:10px; color:#333; margin-bottom:5px;">Récapitulatif par package</h4>
    @php
        $packages = [];
        foreach ($orders ?? [] as $order) {
            foreach ($order->items as $item) {
                if ($item->package) {
                    $key = $item->package->name;
                    $packages[$key] = ($packages[$key] ?? 0) + $item->total;
                }
            }
        }
    @endphp
    @foreach($packages as $name => $amount)
        <div style="display:flex; justify-content:space-between; font-size:9px; padding:2px 0; border-bottom:1px solid #f3f4f6;">
            <span>{{ $name }}</span>
            <span style="font-weight:600;">${{ number_format($amount, 2) }}</span>
        </div>
    @endforeach
</div>

@endsection