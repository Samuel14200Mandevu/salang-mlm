{{-- resources/views/admin/reports/pdf/withdrawals.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Retraits')
@section('report_title', 'RAPPORT DES RETRAITS')

@section('content')

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-label">En attente</div>
        <div class="stat-value yellow">${{ number_format($stats['pending'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Payés</div>
        <div class="stat-value green">${{ number_format($stats['completed'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Échoués</div>
        <div class="stat-value red">${{ number_format($stats['failed'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Total</div>
        <div class="stat-value purple">${{ number_format($stats['total'] ?? 0, 2) }}</div>
    </div>
</div>

<!-- Tableau -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Utilisateur</th>
            <th>Email</th>
            <th>Montant</th>
            <th>Méthode</th>
            <th>Statut</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($withdrawals ?? [] as $withdrawal)
            <tr>
                <td>#{{ $withdrawal->id }}</td>
                <td>{{ $withdrawal->user?->name ?? 'N/A' }}</td>
                <td>{{ $withdrawal->user?->email ?? 'N/A' }}</td>
                <td><strong>${{ number_format($withdrawal->amount, 2) }}</strong></td>
                <td>
                    <span class="badge badge-info">{{ ucfirst($withdrawal->method) }}</span>
                </td>
                <td>
                    <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : 'badge-danger') }}">
                        {{ ucfirst($withdrawal->status) }}
                    </span>
                </td>
                <td>{{ $withdrawal->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#999; padding:20px;">
                    Aucun retrait trouvé
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Récapitulatif par méthode -->
<div style="margin-top:15px; padding:10px; background:#f9fafb; border-radius:4px; border:1px solid #e5e7eb;">
    <h4 style="font-size:10px; color:#333; margin-bottom:5px;">Récapitulatif par méthode</h4>
    @php
        $methods = [];
        foreach ($withdrawals ?? [] as $w) {
            $methods[$w->method] = ($methods[$w->method] ?? 0) + $w->amount;
        }
    @endphp
    @foreach($methods as $method => $amount)
        <div style="display:flex; justify-content:space-between; font-size:9px; padding:2px 0; border-bottom:1px solid #f3f4f6;">
            <span style="text-transform:capitalize;">{{ $method }}</span>
            <span style="font-weight:600;">${{ number_format($amount, 2) }}</span>
        </div>
    @endforeach
</div>

@endsection