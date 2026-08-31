{{-- resources/views/admin/reports/pdf/users.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Salang Group - Rapport des Utilisateurs')
@section('report_title', 'RAPPORT DES UTILISATEURS')

@push('styles')
<style>
    /* ===== STYLES POUR LE RAPPORT ===== */
    .report-header {
        margin-bottom: 15px;
        padding: 10px 15px;
        background: linear-gradient(135deg, #0E2F76 0%, #1a4a8a 100%);
        color: #ffffff;
        border-radius: 6px;
    }
    .report-header h1 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 1px;
    }
    .report-header .subtitle {
        font-size: 9px;
        opacity: 0.8;
        margin-top: 3px;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8px;
        margin-bottom: 10px;
    }
    .data-table thead th {
        background-color: #0E2F76;
        color: #ffffff;
        padding: 6px 5px;
        text-align: center;
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #0E2F76;
        font-weight: 700;
    }
    .data-table tbody td {
        padding: 5px 5px;
        border: 1px solid #d1d5db;
        vertical-align: middle;
        font-size: 8px;
    }
    .data-table tbody tr:nth-child(even) {
        background-color: #f7fafc;
    }
    .data-table tbody tr:hover {
        background-color: #edf2f7;
    }
    
    .badge-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 6.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .badge-active {
        background-color: #c6f6d5;
        color: #276749;
    }
    .badge-inactive {
        background-color: #fed7d7;
        color: #9b2c2c;
    }
    
    .badge-kyc {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 6.5px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .badge-kyc-verified {
        background-color: #c6f6d5;
        color: #276749;
    }
    .badge-kyc-pending {
        background-color: #fefcbf;
        color: #975a16;
    }
    .badge-kyc-rejected {
        background-color: #fed7d7;
        color: #9b2c2c;
    }
    .badge-kyc-not_submitted {
        background-color: #e2e8f0;
        color: #4a5568;
    }
    
    .sponsor-code {
        font-family: 'Courier New', monospace;
        font-size: 7px;
        font-weight: 600;
        background: #ebf8ff;
        padding: 1px 6px;
        border-radius: 3px;
        border: 1px solid #bee3f8;
        color: #0E2F76;
    }
    
    .stats-box {
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 10px 15px;
        margin-top: 12px;
        font-size: 8.5px;
    }
    .stats-box .stat-item {
        display: inline-block;
        margin: 0 10px;
        padding: 4px 8px;
    }
    .stats-box .stat-item .number {
        font-size: 14px;
        font-weight: 700;
        display: block;
        text-align: center;
    }
    .stats-box .stat-item .label {
        font-size: 6.5px;
        text-transform: uppercase;
        color: #718096;
        letter-spacing: 0.3px;
    }
    .stats-box .stat-item .number.primary { color: #0E2F76; }
    .stats-box .stat-item .number.green { color: #276749; }
    .stats-box .stat-item .number.red { color: #9b2c2c; }
    .stats-box .stat-item .number.orange { color: #c05621; }
    .stats-box .stat-item .number.purple { color: #6b46c1; }
    
    .footer-report {
        margin-top: 12px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        text-align: center;
        font-size: 7px;
        color: #9ca3af;
    }
    .footer-report .company {
        font-size: 8px;
        font-weight: 600;
        color: #0E2F76;
    }
    
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: 700; }
    .text-muted { color: #718096; }
    
    .rank-badge {
        background: #ebf8ff;
        padding: 1px 6px;
        border-radius: 3px;
        font-size: 7px;
        color: #0E2F76;
        font-weight: 600;
    }
</style>
@endpush

@section('content')

<!-- ============================================================
     EN-TÊTE DU RAPPORT
     ============================================================ -->
<div class="report-header">
    <h1>RAPPORT DES UTILISATEURS</h1>
    <div class="subtitle">
        Liste complète des utilisateurs - {{ now()->format('d/m/Y à H:i') }}
        <span style="float: right;">Total: {{ $users->count() }} utilisateurs</span>
    </div>
</div>

<!-- ============================================================
     STATISTIQUES RAPIDES
     ============================================================ -->
<div class="stats-box">
    <div class="stat-item">
        <span class="number primary">{{ number_format($users->count()) }}</span>
        <span class="label">Total</span>
    </div>
    <div class="stat-item">
        <span class="number green">{{ number_format($users->where('is_active', true)->count()) }}</span>
        <span class="label">Actifs</span>
    </div>
    <div class="stat-item">
        <span class="number red">{{ number_format($users->where('is_active', false)->count()) }}</span>
        <span class="label">Inactifs</span>
    </div>
    <div class="stat-item">
        <span class="number purple">{{ number_format($users->where('kyc_status', 'verified')->count()) }}</span>
        <span class="label">KYC Vérifié</span>
    </div>
    <div class="stat-item">
        <span class="number orange">{{ number_format($users->whereNotNull('package_id')->count()) }}</span>
        <span class="label">Avec Package</span>
    </div>
</div>

<!-- ============================================================
     TABLEAU PRINCIPAL
     ============================================================ -->
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;">#</th>
            <th style="width: 14%; text-align: left;">Nom</th>
            <th style="width: 10%; text-align: center;">Code Membre</th>
            <th style="width: 10%; text-align: center;">Grade</th>
            <th style="width: 12%; text-align: left;">Parrain</th>
            <th style="width: 9%; text-align: center;">Code Parrain</th>
            <th style="width: 8%; text-align: center;">PV</th>
            <th style="width: 8%; text-align: center;">PV Équipe</th>
            <th style="width: 8%; text-align: center;">Package</th>
            <th style="width: 8%; text-align: center;">Statut</th>
            <th style="width: 9%; text-align: center;">KYC</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $index => $user)
            @php
                $rowColor = $index % 2 == 0 ? '#ffffff' : '#f7fafc';
            @endphp
            <tr style="background-color: {{ $rowColor }};">
                <td style="text-align: center; font-weight: 700; color: #0E2F76;">{{ $index + 1 }}</td>
                <td style="text-align: left; font-weight: 600;">{{ $user->name }}</td>
                <td style="text-align: center;">
                    <span class="sponsor-code">{{ $user->sponsor_id ?? '-' }}</span>
                </td>
                <td style="text-align: center;">
                    <span class="rank-badge">{{ $user->rank_name ?? ($user->rank ?? 'Distributeur') }}</span>
                    @if(isset($user->rank_level) && $user->rank_level)
                        <span style="font-size: 6px; color: #718096;">(Niv.{{ $user->rank_level }})</span>
                    @endif
                </td>
                <td style="text-align: left;">
                    @if(isset($user->parrain) && $user->parrain)
                        {{ $user->parrain->name }}
                    @elseif(isset($user->parrain_id) && $user->parrain_id)
                        <span style="color: #718096; font-size: 7px;">ID: {{ $user->parrain_id }}</span>
                    @else
                        <span style="color: #9ca3af;">-</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    @if(isset($user->parrain) && $user->parrain && isset($user->parrain->sponsor_id))
                        <span class="sponsor-code" style="font-size: 6.5px;">{{ $user->parrain->sponsor_id }}</span>
                    @elseif(isset($user->parrain_id) && $user->parrain_id)
                        <span style="color: #9ca3af; font-size: 7px;">ID: {{ $user->parrain_id }}</span>
                    @else
                        <span style="color: #9ca3af;">-</span>
                    @endif
                </td>
                <td style="text-align: center; font-weight: 600; color: #0E2F76;">
                    {{ number_format($user->pv_balance ?? 0, 0, ',', ' ') }}
                </td>
                <td style="text-align: center; font-weight: 600; color: #276749;">
                    {{ number_format($user->team_pv ?? 0, 0, ',', ' ') }}
                </td>
                <td style="text-align: center;">
                    @if(isset($user->package) && $user->package)
                        <span class="badge badge-kyc-verified" style="font-size: 6.5px; background: #bee3f8; color: #2a69ac;">{{ $user->package->name ?? 'Aucun' }}</span>
                    @elseif(isset($user->package_id) && $user->package_id)
                        <span class="badge badge-kyc-verified" style="font-size: 6.5px; background: #bee3f8; color: #2a69ac;">Package</span>
                    @else
                        <span style="color: #9ca3af; font-size: 7px;">Aucun</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="badge-status {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
                <td style="text-align: center;">
                    <span class="badge-kyc 
                        @if($user->kyc_status == 'verified') badge-kyc-verified
                        @elseif($user->kyc_status == 'pending') badge-kyc-pending
                        @elseif($user->kyc_status == 'rejected') badge-kyc-rejected
                        @else badge-kyc-not_submitted @endif">
                        {{ $user->kyc_status_label ?? ucfirst($user->kyc_status ?? 'Non soumis') }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="text-align: center; padding: 30px; color: #9ca3af; font-size: 9px;">
                    <div style="font-size: 24px; margin-bottom: 5px;">📋</div>
                    Aucun utilisateur trouvé
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- ============================================================
     STATISTIQUES DÉTAILLÉES
     ============================================================ -->
<div style="margin-top: 12px; padding: 10px 15px; background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
    <table style="width: 100%; border-collapse: collapse; font-size: 8px;">
        <tr>
            <td style="padding: 4px 8px; border-right: 1px solid #e2e8f0; text-align: center;">
                <span style="font-weight: 700; color: #0E2F76;">{{ number_format($users->count()) }}</span>
                <span style="color: #718096; display: block; font-size: 6px; text-transform: uppercase;">Total</span>
            </td>
            <td style="padding: 4px 8px; border-right: 1px solid #e2e8f0; text-align: center;">
                <span style="font-weight: 700; color: #276749;">{{ number_format($users->where('is_active', true)->count()) }}</span>
                <span style="color: #718096; display: block; font-size: 6px; text-transform: uppercase;">Actifs</span>
            </td>
            <td style="padding: 4px 8px; border-right: 1px solid #e2e8f0; text-align: center;">
                <span style="font-weight: 700; color: #9b2c2c;">{{ number_format($users->where('is_active', false)->count()) }}</span>
                <span style="color: #718096; display: block; font-size: 6px; text-transform: uppercase;">Inactifs</span>
            </td>
            <td style="padding: 4px 8px; border-right: 1px solid #e2e8f0; text-align: center;">
                <span style="font-weight: 700; color: #6b46c1;">{{ number_format($users->where('kyc_status', 'verified')->count()) }}</span>
                <span style="color: #718096; display: block; font-size: 6px; text-transform: uppercase;">KYC Vérifié</span>
            </td>
            <td style="padding: 4px 8px; border-right: 1px solid #e2e8f0; text-align: center;">
                <span style="font-weight: 700; color: #c05621;">{{ number_format($users->where('kyc_status', 'pending')->count()) }}</span>
                <span style="color: #718096; display: block; font-size: 6px; text-transform: uppercase;">KYC En attente</span>
            </td>
            <td style="padding: 4px 8px; border-right: 1px solid #e2e8f0; text-align: center;">
                <span style="font-weight: 700; color: #0E2F76;">{{ number_format($users->sum('pv_balance') ?? 0, 0, ',', ' ') }}</span>
                <span style="color: #718096; display: block; font-size: 6px; text-transform: uppercase;">PV Total</span>
            </td>
            <td style="padding: 4px 8px; text-align: center;">
                <span style="font-weight: 700; color: #0E2F76;">{{ number_format($users->sum('team_pv') ?? 0, 0, ',', ' ') }}</span>
                <span style="color: #718096; display: block; font-size: 6px; text-transform: uppercase;">PV Équipe Total</span>
            </td>
        </tr>
    </table>
</div>

<!-- ============================================================
     RÉPARTITION PAR GRADE
     ============================================================ -->
@php
    $rankDistribution = $users->groupBy(function($user) {
        return $user->rank_name ?? ($user->rank ?? 'Distributeur');
    })->map(function($group) {
        return $group->count();
    })->sortDesc();
@endphp

@if($rankDistribution->count() > 0)
    <div style="margin-top: 12px; padding: 10px 15px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px;">
        <div style="font-size: 9px; font-weight: 700; color: #0E2F76; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
            Répartition par Grade
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            @foreach($rankDistribution as $rank => $count)
                @php
                    $percentage = $users->count() > 0 ? round(($count / $users->count()) * 100, 1) : 0;
                @endphp
                <div style="flex: 1; min-width: 80px; text-align: center; padding: 4px 8px; background: #f7fafc; border-radius: 4px; border: 1px solid #e2e8f0;">
                    <div style="font-weight: 700; color: #0E2F76; font-size: 12px;">{{ number_format($count) }}</div>
                    <div style="font-size: 7px; color: #718096; text-transform: uppercase;">{{ $rank }}</div>
                    <div style="font-size: 6px; color: #9ca3af;">{{ $percentage }}%</div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- ============================================================
     RÉPARTITION PAR STATUT KYC
     ============================================================ -->
@php
    $kycDistribution = $users->groupBy('kyc_status')->map(function($group) {
        return $group->count();
    })->sortDesc();
@endphp

@if($kycDistribution->count() > 0)
    <div style="margin-top: 8px; padding: 10px 15px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px;">
        <div style="font-size: 9px; font-weight: 700; color: #0E2F76; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
            Répartition par Statut KYC
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            @foreach($kycDistribution as $status => $count)
                @php
                    $percentage = $users->count() > 0 ? round(($count / $users->count()) * 100, 1) : 0;
                    $label = match($status) {
                        'verified' => 'Vérifié',
                        'pending' => 'En attente',
                        'rejected' => 'Rejeté',
                        'not_submitted' => 'Non soumis',
                        default => ucfirst(str_replace('_', ' ', $status))
                    };
                    $color = match($status) {
                        'verified' => '#276749',
                        'pending' => '#975a16',
                        'rejected' => '#9b2c2c',
                        default => '#4a5568'
                    };
                @endphp
                <div style="flex: 1; min-width: 80px; text-align: center; padding: 4px 8px; background: #f7fafc; border-radius: 4px; border: 1px solid #e2e8f0;">
                    <div style="font-weight: 700; color: {{ $color }}; font-size: 12px;">{{ number_format($count) }}</div>
                    <div style="font-size: 7px; color: #718096; text-transform: uppercase;">{{ $label }}</div>
                    <div style="font-size: 6px; color: #9ca3af;">{{ $percentage }}%</div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- ============================================================
     PIED DE PAGE
     ============================================================ -->
<div class="footer-report">
    <div class="company">SALANG GROUP HEALTH CARE</div>
    <div style="margin-top: 3px;">
        382 AV Ixoras Limeté Résidentielle, Kinshasa RD Congo<br>
        +243 999 086 990 - 975 220 079 - support@salanggroup.com - www.salang-group.com
    </div>
    <div style="margin-top: 5px;">
        Rapport généré le {{ now()->format('d/m/Y à H:i:s') }} - Réf: RPT-{{ date('Ymd') }}-{{ strtoupper(substr(md5(time()), 0, 8)) }}
    </div>
    <div style="margin-top: 3px; font-size: 6px; color: #d1d5db;">
        Document confidentiel - Réservé à l'usage interne
    </div>
</div>

@endsection