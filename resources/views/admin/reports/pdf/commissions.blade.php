{{-- resources/views/admin/reports/pdf/commissions.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Commissions')
@section('report_title', 'RAPPORT DES COMMISSIONS')

@section('content')


<!-- Tableau -->
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Utilisateur</th>
            <th>De</th>
            <th>Type</th>
            <th>Montant</th>
            <th>%</th>
            <th>Statut</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($commissions ?? [] as $index => $commission)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $commission->user?->name ?? 'N/A' }}</td>
                <td>{{ $commission->fromUser?->name ?? 'Système' }}</td>
                <td>
                    <span class="badge badge-info">{{ ucfirst($commission->type) }}</span>
                </td>
                <td><strong>${{ number_format($commission->amount, 2) }}</strong></td>
                <td>{{ $commission->percentage }}%</td>
                <td>
                    <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                        {{ ucfirst($commission->status) }}
                    </span>
                </td>
                <td>{{ $commission->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#999; padding:20px;">
                    Aucune commission trouvée
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Récapitulatif par type -->
<div style="margin-top:15px; padding:10px; background:#f9fafb; border-radius:4px; border:1px solid #e5e7eb;">
    <h4 style="font-size:10px; color:#333; margin-bottom:5px;">Récapitulatif par type</h4>
    @php
        $types = [];
        foreach ($commissions ?? [] as $c) {
            $types[$c->type] = ($types[$c->type] ?? 0) + $c->amount;
        }
    @endphp
    @foreach($types as $type => $amount)
        <div style="display:flex; justify-content:space-between; font-size:9px; padding:2px 0; border-bottom:1px solid #f3f4f6;">
            <span style="text-transform:capitalize;">{{ $type }}</span>
            <span style="font-weight:600;">${{ number_format($amount, 2) }}</span>
        </div>
    @endforeach
</div>

@endsection