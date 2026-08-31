{{-- resources/views/admin/reports/pdf/commissions.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Commissions')
@section('report_title', 'RAPPORT DES COMMISSIONS')

@section('content')

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 20%;">Bénéficiaire</th>
            <th style="width: 20%;">Source</th>
            <th style="width: 12%;">Type</th>
            <th style="width: 13%;">Montant</th>
            <th style="width: 8%;">%</th>
            <th style="width: 10%;">Statut</th>
            <th style="width: 12%;">Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($commissions ?? [] as $index => $commission)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><strong>{{ $commission->user?->name ?? 'N/A' }}</strong></td>
                <td>{{ $commission->fromUser?->name ?? 'Système' }}</td>
                <td>
                    <span class="badge badge-info">{{ ucfirst($commission->type) }}</span>
                </td>
                <td style="color: #276749;"><strong>${{ number_format($commission->amount, 2) }}</strong></td>
                <td>{{ $commission->percentage }}%</td>
                <td>
                    <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                        {{ $commission->status == 'paid' ? 'Payé' : ucfirst($commission->status) }}
                    </span>
                </td>
                <td>{{ $commission->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #a0aec0; padding: 15px;">
                    Aucune commission trouvée dans cette période.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Récapitulatif par type -->
@php
    $types = [];
    $totalAmount = 0;
    foreach ($commissions ?? [] as $c) {
        $types[$c->type] = ($types[$c->type] ?? 0) + $c->amount;
        $totalAmount += $c->amount;
    }
@endphp

@if(!empty($types))
    <div class="summary-box">
        <div class="summary-title">Récapitulatif par Type de Commission</div>
        <table class="summary-table">
            @foreach($types as $type => $amount)
                <tr>
                    <td style="text-transform: capitalize;"><strong>{{ $type }}</strong></td>
                    <td style="text-align: right; font-weight: bold; color: #0E2F76;">${{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="text-transform: uppercase; font-weight: bold; padding-top: 4px;">TOTAL GENERAL</td>
                <td style="text-align: right; font-weight: bold; color: #8b0000; font-size: 9.5px; padding-top: 4px;">${{ number_format($totalAmount, 2) }}</td>
            </tr>
        </table>
    </div>
@endif

@endsection