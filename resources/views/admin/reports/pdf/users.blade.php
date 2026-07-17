{{-- resources/views/admin/reports/pdf/users.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Utilisateurs')
@section('report_title', 'RAPPORT DES UTILISATEURS')

@section('content')

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-label">Total</div>
        <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Actifs</div>
        <div class="stat-value green">{{ number_format($stats['active'] ?? 0) }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Inactifs</div>
        <div class="stat-value red">{{ number_format($stats['inactive'] ?? 0) }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">PV Moyen</div>
        <div class="stat-value purple">{{ number_format($stats['avg_pv'] ?? 0) }}</div>
    </div>
</div>

<!-- Tableau -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Grade</th>
            <th>Package</th>
            <th>PV</th>
            <th>Statut</th>
            <th>Inscrit</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users ?? [] as $user)
            <tr>
                <td>#{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->rank?->name ?? 'Distributeur' }}</td>
                <td>{{ $user->package?->name ?? 'Aucun' }}</td>
                <td>{{ number_format($user->pv_balance ?? 0) }}</td>
                <td>
                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
                <td>{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#999; padding:20px;">
                    Aucun utilisateur trouvé
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Récapitulatif -->
<div style="margin-top:10px; font-size:8px; color:#888; text-align:center; border-top:1px solid #e5e7eb; padding-top:8px;">
    Total: {{ $users->total() ?? 0 }} utilisateurs
</div>

@endsection