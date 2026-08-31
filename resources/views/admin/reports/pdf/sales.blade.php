{{-- resources/views/admin/reports/pdf/sales.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Ventes')
@section('report_title', 'RAPPORT DES VENTES')

@section('content')

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 14%;">N° Commande</th>
            <th style="width: 22%;">Client</th>
            <th style="width: 10%; text-align: center;">Articles</th>
            <th style="width: 13%;">Sous-total</th>
            <th style="width: 11%;">TVA</th>
            <th style="width: 13%;">Total</th>
            <th style="width: 7%;">Statut</th>
            <th style="width: 10%;">Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders ?? [] as $order)
            <tr>
                <td><strong>#{{ $order->order_number }}</strong></td>
                <td>{{ $order->user?->name ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ $order->items->count() }}</td>
                <td>${{ number_format($order->subtotal, 2) }}</td>
                <td>${{ number_format($order->tax, 2) }}</td>
                <td style="color: #0E2F76;"><strong>${{ number_format($order->total, 2) }}</strong></td>
                <td>
                    <span class="badge {{ $order->status == 'completed' ? 'badge-success' : ($order->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                        {{ $order->status == 'completed' ? 'Complété' : ucfirst($order->status) }}
                    </span>
                </td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #a0aec0; padding: 15px;">
                    Aucune commande trouvée.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Récapitulatif par package -->
@php
    $packages = [];
    $totalSales = 0;
    foreach ($orders ?? [] as $order) {
        foreach ($order->items as $item) {
            if ($item->package) {
                $key = $item->package->name;
                $packages[$key] = ($packages[$key] ?? 0) + $item->total;
                $totalSales += $item->total;
            }
        }
    }
@endphp

@if(!empty($packages))
    <div class="summary-box">
        <div class="summary-title">Récapitulatif des Ventes par Package</div>
        <table class="summary-table">
            @foreach($packages as $name => $amount)
                <tr>
                    <td><strong>{{ $name }}</strong></td>
                    <td style="text-align: right; font-weight: bold; color: #0E2F76;">${{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="text-transform: uppercase; font-weight: bold; padding-top: 4px;">TOTAL DES VENTES</td>
                <td style="text-align: right; font-weight: bold; color: #8b0000; font-size: 9.5px; padding-top: 4px;">${{ number_format($totalSales, 2) }}</td>
            </tr>
        </table>
    </div>
@endif

@endsection