{{-- resources/views/admin/reports/pdf/user_network.blade.php --}}
@extends('admin.reports.pdf.layout')

@section('title', 'Réseau de ' . $user->name)
@section('report_title', 'RAPPORT RÉSEAU - ' . strtoupper($user->name))

@push('styles')
<style>
    /* ===== STYLES SPÉCIFIQUES POUR LE PDF ===== */
    body {
        font-size: 12px;
    }
    .summary-box {
        margin-top: 0;
        margin-bottom: 12px;
        padding: 10px 12px;
        background-color: #f7fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        font-size: 12px;
    }
    .summary-title {
        font-size: 14px;
        font-weight: 700;
        color: #0E2F76;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #0E2F76;
        padding-bottom: 4px;
        margin-top: 15px;
        margin-bottom: 8px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-bottom: 10px;
    }
    .data-table thead th {
        background-color: #0E2F76;
        color: #ffffff;
        padding: 6px 8px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: 1px solid #0E2F76;
    }
    .data-table tbody td {
        padding: 5px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
        font-size: 11px;
    }
    .data-table tbody tr:nth-child(even) {
        background-color: #f7fafc;
    }
    .data-table tbody tr:hover {
        background-color: #edf2f7;
    }
    .badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .badge-success {
        background-color: #c6f6d5;
        color: #276749;
    }
    .badge-danger {
        background-color: #fed7d7;
        color: #9b2c2c;
    }
    .badge-warning {
        background-color: #fefcbf;
        color: #975a16;
    }
    .badge-info {
        background-color: #bee3f8;
        color: #2a69ac;
    }
    .badge-primary {
        background-color: #0E2F76;
        color: #ffffff;
    }
    .badge-secondary {
        background-color: #e2e8f0;
        color: #4a5568;
    }
    .badge-sponsor {
        background-color: transparent;
        color: #0E2F76;
        font-family: 'Courier New', monospace;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 3px;
        letter-spacing: 0.5px;
        border: 1px solid #0E2F76;
    }
    .ancestors-line {
        margin-bottom: 8px;
        font-size: 11px;
        color: #4a5568;
        background-color: #f7fafc;
        padding: 6px 10px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    .root-node {
        padding: 8px 14px;
        background-color: #ebf8ff;
        border: 2px solid #0E2F76;
        border-radius: 6px;
        margin-bottom: 10px;
        display: inline-block;
        font-size: 12px;
    }
    .root-node strong {
        color: #0E2F76;
        font-size: 13px;
    }
    .stat-box {
        padding: 8px 12px;
        text-align: center;
    }
    .stat-box .number {
        font-size: 20px;
        font-weight: 700;
        display: block;
    }
    .stat-box .label {
        font-size: 9px;
        font-weight: 600;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .stat-box .number.primary { color: #0E2F76; }
    .stat-box .number.green { color: #276749; }
    .stat-box .number.red { color: #9b2c2c; }
    .stat-box .number.orange { color: #c05621; }
    .stat-box .number.purple { color: #6b46c1; }
    .generation-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        text-align: center;
        min-width: 55px;
    }
    .gen-direct {
        background: #0E2F76;
        color: #ffffff;
    }
    .gen-level {
        background: #e2e8f0;
        color: #4a5568;
    }
    .tree-prefix {
        color: #a0aec0;
        font-size: 11px;
    }
    .filleuls-count {
        color: #0E2F76;
        font-size: 10px;
        font-weight: 400;
        display: inline-block;
        margin-left: 2px;
    }
    .code-membre {
        font-family: 'Courier New', monospace;
        font-size: 10px;
        color: #0E2F76;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 3px;
        border: 1px solid #bee3f8;
        display: inline-block;
        background-color: #ffffff;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .font-bold { font-weight: 700; }
    .mt-1 { margin-top: 5px; }
    .mt-2 { margin-top: 10px; }
    .mb-1 { margin-bottom: 5px; }
    .mb-2 { margin-bottom: 10px; }
    .no-data {
        text-align: center;
        color: #a0aec0;
        padding: 20px;
        border: 1px dashed #cbd5e1;
        border-radius: 4px;
        font-size: 11px;
    }
    .summary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    .summary-table td {
        padding: 4px 8px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
    }
    .summary-table tr:last-child td {
        border-bottom: none;
    }
    .rank-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
        width: 100%;
    }
    .rank-bar .fill {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, #0E2F76, #2563eb);
    }
    .kyc-bar .fill {
        height: 100%;
        border-radius: 3px;
    }
    .kyc-bar .fill.verified { background: #48bb78; }
    .kyc-bar .fill.pending { background: #ecc94b; }
    .kyc-bar .fill.rejected { background: #fc8181; }
    .kyc-bar .fill.not_submitted { background: #a0aec0; }
    .sponsor-code {
        background: transparent;
        color: #0E2F76;
        padding: 2px 10px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        display: inline-block;
        border: 1px solid #0E2F76;
    }
    .member-name {
        font-weight: 600;
        font-size: 12px;
        display: inline-block;
    }
    .statut-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .statut-actif {
        background-color: #c6f6d5;
        color: #276749;
    }
    .statut-inactif {
        background-color: #fed7d7;
        color: #9b2c2c;
    }
    .member-row td {
        padding: 4px 6px;
        vertical-align: middle;
    }
    .member-row .col-num {
        text-align: center;
        font-size: 10px;
        color: #718096;
        white-space: nowrap;
        width: 30px;
    }
    .member-row .col-name {
        font-size: 11px;
        white-space: nowrap;
    }
    .member-row .col-code {
        text-align: center;
        white-space: nowrap;
    }
    .member-row .col-gen {
        text-align: center;
        white-space: nowrap;
    }
    .member-row .col-statut {
        text-align: center;
        white-space: nowrap;
    }
    .member-row .col-grade {
        white-space: nowrap;
        font-size: 10px;
    }
    .member-row .col-pv {
        text-align: right;
        font-weight: bold;
        color: #0E2F76;
        white-space: nowrap;
    }
    .filleuls-badge {
        display: inline-block;
        color: #0E2F76;
        font-size: 9px;
        font-weight: 400;
        margin-left: 3px;
    }
</style>
@endpush

@section('content')

<!-- ============================================================
     CARTE D'INFORMATIONS UTILISATEUR
     ============================================================ -->
<div class="summary-box" style="margin-top: 0; margin-bottom: 12px; padding: 12px 16px;">
    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <tr>
            <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                <span style="color: #718096; font-weight: bold;">Membre :</span> 
                <span style="color: #0E2F76; font-weight: bold; font-size: 13px;">{{ $user->name }}</span><br>
                <span style="color: #718096; font-weight: bold;">Code Membre :</span> 
                <span class="sponsor-code">{{ $user->sponsor_id ?? 'N/A' }}</span><br>
                <span style="color: #718096; font-weight: bold;">Email :</span> {{ $user->email }}<br>
                <span style="color: #718096; font-weight: bold;">Grade :</span> {{ $user->rank?->name ?? 'Distributeur' }}
                <span style="color: #718096; font-weight: bold; margin-left: 10px;">KYC :</span> 
                <span class="badge 
                    @if($user->kyc_status == 'verified') badge-success
                    @elseif($user->kyc_status == 'pending') badge-warning
                    @elseif($user->kyc_status == 'rejected') badge-danger
                    @else badge-secondary @endif">
                    {{ ucfirst(str_replace('_', ' ', $user->kyc_status ?? 'Non soumis')) }}
                </span>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                <span style="color: #718096; font-weight: bold;">Package :</span> 
                <span class="badge badge-info">{{ $user->package?->name ?? 'Aucun' }}</span><br>
                <span style="color: #718096; font-weight: bold;">PV Personnel :</span> 
                <strong style="color: #0E2F76; font-size: 13px;">{{ number_format($user->pv_balance ?? 0, 0, ',', ' ') }}</strong><br>
                <span style="color: #718096; font-weight: bold;">PV Équipe :</span> 
                <strong style="color: #0E2F76; font-size: 13px;">{{ number_format($user->team_pv ?? 0, 0, ',', ' ') }}</strong><br>
                <span style="color: #718096; font-weight: bold;">Gains Totaux :</span> 
                <strong style="color: #276749; font-size: 13px;">${{ number_format($user->total_earnings ?? 0, 2) }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border: none; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                <span style="color: #718096; font-weight: bold;">Statut :</span>
                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                    {{ $user->is_active ? 'ACTIF' : 'INACTIF' }}
                </span>
                <span style="color: #718096; font-weight: bold; margin-left: 15px;">Date d'inscription :</span>
                {{ $user->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                <span style="color: #718096; font-weight: bold; margin-left: 15px;">ID :</span>
                #{{ $user->id }}
                <span style="color: #718096; font-weight: bold; margin-left: 15px;">Parrain :</span>
                {{ $user->parrain?->name ?? 'Aucun' }}
                @if($user->parrain)
                    <span class="code-membre">({{ $user->parrain->sponsor_id ?? 'N/A' }})</span>
                @endif
            </td>
        </tr>
    </table>
</div>

<!-- ============================================================
     STATISTIQUES DU RÉSEAU
     ============================================================ -->
<div class="summary-title">Statistiques du Réseau</div>
<table class="data-table" style="margin-bottom: 12px;">
    <tbody>
        <tr>
            <td style="width: 16%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number primary">{{ number_format($stats['total_descendants'] ?? 0) }}</span>
                    <span class="label">Total Descendants</span>
                </span>
            </td>
            <td style="width: 16%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number green">{{ number_format($stats['direct_children'] ?? 0) }}</span>
                    <span class="label">Filleuls Directs</span>
                </span>
            </td>
            <td style="width: 16%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number green">{{ number_format($stats['active_descendants'] ?? 0) }}</span>
                    <span class="label">Actifs</span>
                </span>
            </td>
            <td style="width: 16%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number red">{{ number_format($stats['inactive_descendants'] ?? 0) }}</span>
                    <span class="label">Inactifs</span>
                </span>
            </td>
            <td style="width: 18%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number orange">{{ number_format($stats['with_package'] ?? 0) }}</span>
                    <span class="label">Avec Package</span>
                </span>
            </td>
            <td style="width: 18%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number purple">{{ number_format($stats['max_depth'] ?? 0) }}</span>
                    <span class="label">Profondeur Max</span>
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center; background-color: #ebf8ff;">
                <span class="stat-box">
                    <span class="number primary">{{ number_format($stats['total_pv'] ?? 0, 0, ',', ' ') }}</span>
                    <span class="label">PV Total Réseau</span>
                </span>
            </td>
            <td colspan="3" style="text-align: center; background-color: #ebf8ff;">
                <span class="stat-box">
                    <span class="number primary">{{ number_format($stats['total_team_pv'] ?? 0, 0, ',', ' ') }}</span>
                    <span class="label">PV Cumulé Réseau</span>
                </span>
            </td>
        </tr>
    </tbody>
</table>

<!-- ============================================================
     ARBRE GÉNÉALOGIQUE
     ============================================================ -->
<div class="summary-title">Arbre Généalogique</div>

{{-- Ancêtres --}}
@if(!empty($networkData['ancestors']))
    <div class="ancestors-line">
        <strong>Lignée ascendante :</strong>
        @foreach(array_reverse($networkData['ancestors']) as $index => $ancestor)
            <span style="font-weight: 600;">{{ $ancestor['user']['name'] }}</span>
            <span class="code-membre" style="font-size: 9px;">({{ $ancestor['user']['sponsor_id'] ?? 'N/A' }})</span>
            <span style="font-size: 10px; color: #718096;">[{{ $ancestor['relationship'] }}]</span>
            @if(!$loop->last) 
                <span style="color: #a0aec0; margin: 0 3px;">&#8594;</span> 
            @endif
        @endforeach
    </div>
@endif

{{-- Membre Racine --}}
<div class="root-node">
    <strong>&#9733; {{ $user->name }}</strong>
    <span class="sponsor-code" style="font-size: 10px; margin-left: 6px;">{{ $user->sponsor_id ?? 'N/A' }}</span>
    <span style="color: #4a5568; margin-left: 8px;">({{ $user->rank?->name ?? 'Distributeur' }})</span>
    <span style="color: #4a5568; margin-left: 8px;">| PV: {{ number_format($user->pv_balance ?? 0) }}</span>
    @if($user->package?->name)
        <span style="color: #4a5568; margin-left: 8px;">| Package: {{ $user->package->name }}</span>
    @endif
    <span class="badge badge-primary" style="margin-left: 8px;">RACINE</span>
</div>

{{-- Descendants --}}
@if(!empty($networkData['children']))
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 30%;">Membre du réseau</th>
                <th style="width: 12%; text-align: center;">Code Membre</th>
                <th style="width: 12%; text-align: center;">Génération</th>
                <th style="width: 10%; text-align: center;">Statut</th>
                <th style="width: 15%;">Grade</th>
                <th style="width: 16%; text-align: right;">PV</th>
            </tr>
        </thead>
        <tbody>
            @php
                $counter = 0;
                
                if (!function_exists('renderPdfTreeRows')) {
                    function renderPdfTreeRows($nodes, $depth = 1, &$counter) {
                        $html = '';

                        foreach ($nodes as $node) {
                            $counter++;
                            $userNode = $node['user'];
                            $hasChildren = !empty($node['children']);
                            
                            // Style pour la Gen 1 (Direct)
                            if ($depth === 1) {
                                $rowStyle = 'background-color: #ebf8ff;';
                                $genBadge = '<span class="generation-badge gen-direct">Gen 1</span>';
                            } else {
                                $rowStyle = 'background-color: ' . ($depth % 2 == 0 ? '#f7fafc' : '#ffffff') . ';';
                                $genBadge = '<span class="generation-badge gen-level">Gen ' . $depth . '</span>';
                            }
                            
                            $paddingLeft = ($depth - 1) * 16; 
                            
                            // Préfixe d'arbre
                            $treePrefix = ($depth > 1) 
                                ? '<span class="tree-prefix">&#9495;&#9472;&#9472; </span>' 
                                : '';
                            
                            // Badge statut
                            $isActive = $userNode['is_active'] ?? true;
                            $statusBadge = $isActive 
                                ? '<span class="statut-badge statut-actif">Actif</span>' 
                                : '<span class="statut-badge statut-inactif">Inactif</span>';
                            
                            // Package
                            $packageBadge = '';
                            if (!empty($userNode['package']) && $userNode['package'] !== 'Aucun') {
                                $packageBadge = ' <span class="badge badge-info" style="font-size: 8px;">' . e($userNode['package']) . '</span>';
                            }
                            
                            // Compteur de filleuls
                            $filleulsCount = !empty($node['children']) 
                                ? ' <span class="filleuls-badge">(' . count($node['children']) . ' filleuls)</span>' 
                                : '';

                            $rankLabel = e($userNode['rank'] ?? ($userNode['rank_name'] ?? 'Distributeur'));
                            $sponsorCode = $userNode['sponsor_id'] ?? 'N/A';
                            $pvValue = $userNode['pv_balance'] ?? ($userNode['pv'] ?? 0);

                            // Construction de la ligne
                            $html .= '<tr class="member-row" style="' . $rowStyle . '">';
                            $html .= '  <td class="col-num">' . $counter . '</td>';
                            $html .= '  <td class="col-name" style="padding-left: ' . ($paddingLeft + 6) . 'px;">' . $treePrefix . '<span class="member-name">' . e($userNode['name']) . '</span>' . $filleulsCount . $packageBadge . '</td>';
                            $html .= '  <td class="col-code"><span class="code-membre" style="font-size: 10px;">' . $sponsorCode . '</span></td>';
                            $html .= '  <td class="col-gen">' . $genBadge . '</td>';
                            $html .= '  <td class="col-statut">' . $statusBadge . '</td>';
                            $html .= '  <td class="col-grade">' . $rankLabel . '</td>';
                            $html .= '  <td class="col-pv">' . number_format($pvValue) . '</td>';
                            $html .= '</tr>';

                            // Rendu récursif des enfants (filleuls)
                            if (!empty($node['children'])) {
                                $html .= renderPdfTreeRows($node['children'], $depth + 1, $counter);
                            }
                        }
                        return $html;
                    }
                }
            @endphp
            {!! renderPdfTreeRows($networkData['children'], 1, $counter) !!}
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="background-color: #f7fafc; text-align: center; font-size: 10px; color: #718096; border-top: 2px solid #0E2F76; padding: 6px;">
                    Total des membres affichés : <strong style="color: #0E2F76; font-size: 12px;">{{ $counter }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>
@else
    <div class="no-data">
        <strong>Aucun descendant enregistré dans ce réseau.</strong>
    </div>
@endif

<!-- ============================================================
     DISTRIBUTION DES GRADES
     ============================================================ -->
@if(!empty($stats['rank_distribution']))
    <div class="summary-title">Distribution des Grades</div>
    <div class="summary-box" style="margin-top: 4px; padding: 8px 12px;">
        <table class="summary-table">
            @foreach($stats['rank_distribution'] as $rank => $count)
                @php
                    $percentage = ($stats['total_descendants'] ?? 0) > 0 
                        ? round(($count / $stats['total_descendants']) * 100, 1) 
                        : 0;
                @endphp
                <tr>
                    <td style="width: 25%; font-size: 12px;"><strong>{{ $rank }}</strong></td>
                    <td style="width: 40%;">
                        <div class="rank-bar">
                            <div class="fill" style="width: {{ $percentage }}%;"></div>
                        </div>
                    </td>
                    <td style="width: 15%; text-align: center; font-weight: bold; color: #0E2F76; font-size: 12px;">
                        {{ number_format($count) }}
                    </td>
                    <td style="width: 20%; text-align: right; color: #718096; font-size: 10px;">
                        {{ $percentage }}%
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" style="border-top: 2px solid #0E2F76; padding-top: 8px; text-align: center; font-size: 10px; color: #718096;">
                    Total des descendants : <strong style="color: #0E2F76; font-size: 12px;">{{ number_format($stats['total_descendants'] ?? 0) }}</strong>
                </td>
            </tr>
        </table>
    </div>
@endif

<!-- ============================================================
     DISTRIBUTION KYC
     ============================================================ -->
@if(!empty($stats['kyc_distribution']))
    <div class="summary-title">Statut KYC</div>
    <div class="summary-box" style="margin-top: 4px; padding: 8px 12px;">
        <table class="summary-table">
            @foreach($stats['kyc_distribution'] as $status => $count)
                @php
                    $percentage = ($stats['total_descendants'] ?? 0) > 0 
                        ? round(($count / $stats['total_descendants']) * 100, 1) 
                        : 0;
                    $colorClass = match($status) {
                        'verified' => 'verified',
                        'pending' => 'pending',
                        'rejected' => 'rejected',
                        default => 'not_submitted'
                    };
                    $label = match($status) {
                        'verified' => 'Vérifié',
                        'pending' => 'En attente',
                        'rejected' => 'Rejeté',
                        'not_submitted' => 'Non soumis',
                        default => ucfirst(str_replace('_', ' ', $status))
                    };
                @endphp
                <tr>
                    <td style="width: 25%; font-size: 12px;"><strong>{{ $label }}</strong></td>
                    <td style="width: 40%;">
                        <div class="rank-bar kyc-bar">
                            <div class="fill {{ $colorClass }}" style="width: {{ $percentage }}%;"></div>
                        </div>
                    </td>
                    <td style="width: 15%; text-align: center; font-weight: bold; color: #0E2F76; font-size: 12px;">
                        {{ number_format($count) }}
                    </td>
                    <td style="width: 20%; text-align: right; color: #718096; font-size: 10px;">
                        {{ $percentage }}%
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endif

<!-- ============================================================
     RÉCAPITULATIF DES PARRAINAGES
     ============================================================ -->
<div class="summary-title" style="margin-top: 12px;">Récapitulatif des Parrainages</div>
<table class="data-table" style="margin-bottom: 5px;">
    <tbody>
        <tr>
            <td style="width: 20%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number primary">{{ number_format($stats['total_descendants'] ?? 0) }}</span>
                    <span class="label">Total Descendants</span>
                </span>
            </td>
            <td style="width: 20%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number green">{{ number_format($stats['direct_children'] ?? 0) }}</span>
                    <span class="label">Filleuls Directs</span>
                </span>
            </td>
            <td style="width: 20%; text-align: center; background-color: #f7fafc;">
                <span class="stat-box">
                    <span class="number purple">{{ number_format($stats['max_depth'] ?? 0) }}</span>
                    <span class="label">Profondeur Max</span>
                </span>
            </td>
            <td style="width: 20%; text-align: center; background-color: #ebf8ff;">
                <span class="stat-box">
                    <span class="number orange">{{ number_format($user->total_sponsors ?? 0) }}</span>
                    <span class="label">Parrainages Directs</span>
                </span>
            </td>
            <td style="width: 20%; text-align: center; background-color: #ebf8ff;">
                <span class="stat-box">
                    <span class="number primary">{{ number_format($stats['with_package'] ?? 0) }}</span>
                    <span class="label">Avec Package</span>
                </span>
            </td>
        </tr>
    </tbody>
</table>

@endsection