{{-- resources/views/admin/reports/pdf/withdrawals.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Retraits')
@section('report_title', 'RAPPORT DES RETRAITS')

@section('content')

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 8%;">ID</th>
            <th style="width: 24%;">Utilisateur</th>
            <th style="width: 25%;">Email</th>
            <th style="width: 15%;">Montant</th>
            <th style="width: 10%;">Méthode</th>
            <th style="width: 8%;">Statut</th>
            <th style="width: 10%;">Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($withdrawals ?? [] as $withdrawal)
            <tr>
                <td>#{{ $withdrawal->id }}</td>
                <td><strong>{{ $withdrawal->user?->name ?? 'N/A' }}</strong></td>
                <td>{{ $withdrawal->user?->email ?? 'N/A' }}</td>
                <td style="color: #c53030;"><strong>${{ number_format($withdrawal->amount, 2) }}</strong></td>
                <td>
                    <span class="badge badge-info">{{ ucfirst($withdrawal->method) }}</span>
                </td>
                <td>
                    <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : 'badge-danger') }}">
                        {{ $withdrawal->status == 'completed' ? 'Validé' : ucfirst($withdrawal->status) }}
                    </span>
                </td>
                <td>{{ $withdrawal->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #a0aec0; padding: 15px;">
                    Aucune demande de retrait trouvée.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Récapitulatif par méthode -->
@php
    $methods = [];
    $totalWithdrawals = 0;
    foreach ($withdrawals ?? [] as $w) {
        $methods[$w->method] = ($methods[$w->method] ?? 0) + $w->amount;
        $totalWithdrawals += $w->amount;
    }
@endphp

@if(!empty($methods))
    <div class="summary-box">
        <div class="summary-title">Récapitulatif par Mode de Paiement</div>
        <table class="summary-table">
            @foreach($methods as $method => $amount)
                <tr>
                    <td style="text-transform: capitalize;"><strong>{{ $method }}</strong></td>
                    <td style="text-align: right; font-weight: bold; color: #0E2F76;">${{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="text-transform: uppercase; font-weight: bold; padding-top: 4px;">CUMUL DES RETRAITS</td>
                <td style="text-align: right; font-weight: bold; color: #8b0000; font-size: 9.5px; padding-top: 4px;">${{ number_format($totalWithdrawals, 2) }}</td>
            </tr>
        </table>
    </div>
@endif

@endsection