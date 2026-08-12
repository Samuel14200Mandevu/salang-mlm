{{-- resources/views/admin/reports/pdf/users.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Utilisateurs')
@section('report_title', 'RAPPORT DES UTILISATEURS')

@section('content')

<!-- Tableau -->
<table>
    <thead>
        <tr>
            <th style="width:5%;">ID</th>
            <th style="width:15%;">Nom complet</th>
            <th style="width:10%;">Code</th>
            <th style="width:12%;">Grade & Niveau</th>
            <th style="width:12%;">Parrain</th>
            <th style="width:10%;">Code Parrain</th>
            <th style="width:8%;">PV Perso</th>
            <th style="width:8%;">PV Cumulé</th>
            <th style="width:8%;">Statut</th>
            <th style="width:7%;">KYC</th>
            <th style="width:10%;">Inscrit</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users ?? [] as $user)
            <tr>
                <td style="text-align:center;">#{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td style="text-align:center;">{{ $user->sponsor_id ?? '-' }}</td>
                <td>
                    {{ $user->rank_name ?? ($user->rank ?? 'Distributeur') }}
                    @if($user->rank_level)
                        <span style="font-size:7px; color:#6b7280;">(Niv. {{ $user->rank_level }})</span>
                    @endif
                </td>
                <td>
                    @if($user->parrain)
                        {{ $user->parrain->name }}
                    @elseif($user->parrain_id)
                        ID: {{ $user->parrain_id }}
                    @else
                        -
                    @endif
                </td>
                <td style="text-align:center;">{{ $user->parrain->sponsor_id ?? ($user->parrain_id ?? '-') }}</td>
                <td style="text-align:center;">{{ number_format($user->pv_balance ?? 0) }}</td>
                <td style="text-align:center;">{{ number_format($user->team_pv ?? 0) }}</td>
                <td style="text-align:center;">
                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
                <td style="text-align:center;">
                    <span class="badge 
                        @if($user->kyc_status == 'verified') badge-success
                        @elseif($user->kyc_status == 'pending') badge-warning
                        @elseif($user->kyc_status == 'rejected') badge-danger
                        @else badge-secondary @endif">
                        {{ $user->kyc_status_label ?? ucfirst($user->kyc_status ?? 'Non soumis') }}
                    </span>
                </td>
                <td style="text-align:center;">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="text-align:center; color:#999; padding:20px;">
                    Aucun utilisateur trouvé
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Récapitulatif -->
<div style="margin-top:10px; font-size:8px; color:#888; text-align:center; border-top:1px solid #e5e7eb; padding-top:8px;">
    @php
        $count = 0;
        if (isset($users)) {
            if (method_exists($users, 'total')) {
                $count = $users->total();
            } elseif (is_countable($users)) {
                $count = count($users);
            }
        }
    @endphp
    Total: {{ $count }} utilisateurs
    
    @if(isset($stats))
        | Actifs: {{ $stats['active'] ?? 0 }} | Inactifs: {{ $stats['inactive'] ?? 0 }}
        @if(isset($stats['members']) && isset($stats['clients']))
            | Membres: {{ $stats['members'] ?? 0 }} | Clients: {{ $stats['clients'] ?? 0 }}
        @endif
        @if(isset($stats['withPackage']) && isset($stats['withoutPackage']))
            | Avec package: {{ $stats['withPackage'] }} | Sans package: {{ $stats['withoutPackage'] }}
        @endif
        | PV Total: {{ number_format($stats['totalPv'] ?? 0, 0) }}
        | PV Cumulé Total: {{ number_format($stats['totalTeamPv'] ?? 0, 0) }}
    @endif
</div>

@endsection