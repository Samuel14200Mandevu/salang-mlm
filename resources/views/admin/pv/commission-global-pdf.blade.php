{{-- resources/views/admin/pv/commission-global-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Global des Commissions - {{ $period }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            padding: 28px 32px 32px 32px;
        }

        .page-content {
            width: 100%;
        }

        /* EN-TÊTE */
        .report-header {
            width: 100%;
            border-bottom: 2.5px solid #8b0000;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
            padding: 2px 4px;
        }
        .logo-cell {
            width: 70px;
            text-align: center;
        }
        .logo-cell img {
            max-height: 50px;
            width: auto;
        }
        .header-center {
            text-align: center;
        }
        .company-title {
            font-size: 19px;
            font-weight: bold;
            color: #558b2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .header-text {
            font-size: 10px;
            line-height: 1.2;
            font-weight: bold;
        }
        .address {
            font-size: 9.5px;
            color: #000;
            margin-top: 1px;
            line-height: 1.2;
            font-weight: normal;
        }
        .address .city {
            font-weight: bold;
            color: #0E2F76;
        }

        /* TITRE ET META */
        .main-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .main-subtitle {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: capitalize;
            margin-bottom: 6px;
        }
        .form-meta {
            width: 100%;
            margin: 2px 0 6px 0;
            font-size: 13.5px;
            font-weight: bold;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .form-meta td {
            padding: 2px 0;
            white-space: nowrap;
            vertical-align: middle;
        }
        .form-meta td.meta-left {
            text-align: left;
            width: 33%;
        }
        .form-meta td.meta-right {
            text-align: right;
            width: 33%;
        }
        .form-meta td.meta-period-highlight {
            text-align: center;
            width: 34%;
            font-size: 20px;
            font-weight: bold;
            color: #8b0000;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 0.5px;
            padding: 0 8px;
        }

        /* SECTIONS NUMÉROTÉES */
        .section-header {
            font-size: 15px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 2px;
            margin: 6px 0 4px 0;
            text-transform: uppercase;
            clear: both;
        }

        /* TABLEAU DES TOTAUX */
        .totals-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 6px 0;
        }
        .totals-grid td {
            padding: 4px 8px;
            border: 1px solid #000;
            text-align: center;
            font-size: 13.5px;
            background-color: #f9f9f9;
        }
        .totals-grid .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            background-color: #f2f2f2;
        }
        .totals-grid .amount {
            font-weight: bold;
            font-size: 15px;
        }
        .direct-color, .td-direct, .th-direct { color: #312E81; font-weight: bold; }
        .indirect-color, .td-indirect, .th-indirect { color: #1E3A8A; font-weight: bold; }
        .leadership-color, .td-leadership, .th-leadership { color: #9A3412; font-weight: bold; }
        .cash-color, .td-cash, .th-cash { color: #14532D; font-weight: bold; }
        .th-direct, .th-indirect, .th-leadership, .th-cash {
            background-color: #f2f2f2 !important;
        }

        /* TABLEAU GLOBAL */
        .global-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 4px 0;
            font-size: 12px;
        }
        
        .global-table thead {
            display: table-header-group;
        }
        
        .global-table tr {
            page-break-inside: avoid;
        }
        
        .global-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 4px 5px;
            border: 1px solid #000;
            text-align: center;
        }
        .global-table td {
            padding: 3px 5px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 11.5px;
        }
        .global-table td.text-left { text-align: left; }
        .global-table td.text-right { text-align: right; }

        .global-table .total-row td {
            font-weight: bold;
            font-size: 12.5px;
            background-color: #f9f9f9;
        }

        /* LIGNES DE SÉPARATION PAR UTILISATEUR */
        .user-header-row td {
            background-color: #0E2F76;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
            padding: 4px 8px;
            text-align: left;
        }
        .user-total-row td {
            background-color: #f9f9f9;
            font-weight: bold;
            font-size: 12px;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-direct { background: rgba(79, 70, 229, 0.15); color: #4F46E5; }
        .badge-indirect { background: rgba(37, 99, 235, 0.15); color: #2563EB; }
        .badge-leadership { background: rgba(166, 90, 14, 0.15); color: #A65A0E; }
        .badge-cash { background: rgba(22, 163, 74, 0.15); color: #16a34a; }

        @page {
            size: A4 landscape;
            margin: 1.8cm 1.8cm 2cm 1.8cm;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <!-- CONTENU PRINCIPAL -->
    <div class="page-content">

        <!-- EN-TÊTE -->
        <div class="report-header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @php
                            $logoPath = public_path('images/salang_logo.png');
                            $logoBase64 = '';
                            if(file_exists($logoPath)) {
                                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                            }
                        @endphp
                        @if(!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" alt="Salang Logo" loading="lazy">
                        @endif
                    </td>
                    <td class="header-center">
                        <div class="company-title">SALANG GROUP SARL</div>
                        <div class="header-text">
                            N° IDN:22-M7300-N63464Q &nbsp; N° RCCM:CD/BKV/RCCM/20-B-00116<br>
                            Contact : +243 975 220 079 &nbsp; Web:www.salanggroup.com &nbsp; Email:support@salanggroup.com
                        </div>
                        <div class="address">
                            <span class="city">Kinshasa :</span> 4 AV, Ixoras 382, 7eme Rue Resid. &nbsp;|&nbsp;
                            <span class="city">Bukavu :</span> N°4 Av. FIZI, Q. Nyawera, C/Ibanda &nbsp;|&nbsp;
                            <span class="city">Goma :</span> Rondpoint Chikudu, Bat. KBS 3e N.
                        </div>
                    </td>
                    <td class="logo-cell">
                        @if(!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" alt="Salang Logo" loading="lazy">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- TITRE -->
        @php
            $periodLabel = $period;
            if ($period === 'all') {
                $periodLabel = 'Toutes les périodes';
            } elseif (preg_match('/^(\d{4})-(\d{2})$/', (string) $period, $m)) {
                $moisFr = [
                    1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
                    5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
                    9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
                ];
                $moisNum = (int) $m[2];
                $periodLabel = ($moisFr[$moisNum] ?? $m[2]) . ' ' . $m[1];
            }
        @endphp
        <div class="main-title">Rapport Global des Commissions</div>

        <table class="form-meta">
            <tr>
                <td class="meta-left">Période : <strong>{{ $periodLabel }}</strong></td>
                <td class="meta-period-highlight" rowspan="2">{{ $periodLabel }}</td>
                <td class="meta-right">Date : <strong>{{ $date_generated }}</strong></td>
            </tr>
            <tr>
                <td class="meta-left">Nombre de bénéficiaires : <strong>{{ $beneficiaires->count() }}</strong></td>
                <td class="meta-right">Nombre de commissions : <strong>{{ $commissions->count() }}</strong></td>
            </tr>
        </table>

        <!-- 1. TABLEAU GLOBAL DES COMMISSIONS -->
        <div class="section-header">1. Détail Global par Membre</div>
        
        <table class="global-table">
            <thead>
                <tr>
                    <th style="width:4%;">N°</th>
                    <th style="width:8%;">Code Membre</th>
                    <th style="width:24%;">Membre</th>
                    <th style="width:8%;">Code Parrain</th>
                    <th style="width:5%;">Grade</th>
                    <th style="width:7%;">PV</th>
                    <th class="th-direct" style="width:8%;">Direct</th>
                    <th class="th-indirect" style="width:8%;">Indirect</th>
                    <th class="th-leadership" style="width:9%;">Leadership</th>
                    <th class="th-cash" style="width:8%;">Cash POS</th>
                    <th style="width:11%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $numero = 1;
                    $totalPV = 0;
                @endphp
                @foreach($beneficiaires as $userId => $beneficiaire)
                    @php
                        $userCommissions = $beneficiaire['details'];
                        $directTotal = collect($userCommissions)->where('type', 'direct')->sum('amount');
                        $indirectTotal = collect($userCommissions)->where('type', 'indirect')->sum('amount');
                        $leadershipTotal = collect($userCommissions)->where('type', 'leadership')->sum('amount');
                        $cashTotal = collect($userCommissions)->where('type', 'cash_pos')->sum('amount');
                        $userRank = $beneficiaire['rank_level'] ?? 'N/A';
                        $userPV = (float) ($beneficiaire['pv'] ?? 0);
                        $totalPV += $userPV;
                        $userCode = $beneficiaire['code'] ?? 'N/A';
                        $parrainCode = $beneficiaire['parrain_code'] ?? 'N/A';
                    @endphp
                    <tr>
                        <td>{{ $numero++ }}</td>
                        <td class="text-center" style="color:#0E2F76; font-weight:bold;">{{ $userCode }}</td>
                        <td class="text-left"><strong>{{ $beneficiaire['name'] }}</strong></td>
                        <td class="text-center" style="color:#000; font-weight:bold;">{{ $parrainCode }}</td>
                        <td>{{ $userRank }}</td>
                        <td class="text-right" style="color:#0E2F76; font-weight:bold;">{{ number_format($userPV, 1, ',', ' ') }}</td>
                        <td class="text-right td-direct">${{ number_format($directTotal, 2) }}</td>
                        <td class="text-right td-indirect">${{ number_format($indirectTotal, 2) }}</td>
                        <td class="text-right td-leadership">${{ number_format($leadershipTotal, 2) }}</td>
                        <td class="text-right td-cash">${{ number_format($cashTotal, 2) }}</td>
                        <td class="text-right" style="color:#0E2F76; font-weight:bold;">${{ number_format($beneficiaire['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">TOTAL GÉNÉRAL :</td>
                    <td class="text-right" style="color:#0E2F76;">{{ number_format($totalPV, 1, ',', ' ') }}</td>
                    <td class="text-right td-direct">${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                    <td class="text-right td-indirect">${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                    <td class="text-right td-leadership">${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                    <td class="text-right td-cash">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
                    <td class="text-right" style="color:#0E2F76;">${{ number_format($totals['total'] ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- 2. RÉCAPITULATIF PAR TYPE DE BONUS -->
        <div class="section-header">2. Récapitulatif par Type de Bonus</div>
        
        <table class="global-table">
            <thead>
                <tr>
                    <th style="width:25%;">Type de Bonus</th>
                    <th style="width:15%;">Nombre</th>
                    <th style="width:20%;">Montant Total</th>
                    <th style="width:20%;">Pourcentage</th>
                    <th style="width:20%;">Moyenne</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalGeneral = $totals['total'] ?? 1;
                @endphp
                @foreach(['direct', 'indirect', 'leadership', 'cash_pos'] as $type)
                    @php
                        $typeCommissions = $commissions->where('type', $type);
                        $count = $typeCommissions->count();
                        $total = $typeCommissions->sum('amount');
                        $percentage = $totalGeneral > 0 ? ($total / $totalGeneral) * 100 : 0;
                        $average = $count > 0 ? $total / $count : 0;
                    @endphp
                    <tr>
                        <td class="text-left">
                            <span class="badge badge-{{ $type }}">
                                {{ ucfirst($type) }}
                            </span>
                        </td>
                        <td>{{ $count }}</td>
                        <td class="text-right" style="font-weight:bold;">${{ number_format($total, 2) }}</td>
                        <td>{{ number_format($percentage, 1) }}%</td>
                        <td class="text-right">${{ number_format($average, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="text-left"><strong>TOTAL GÉNÉRAL</strong></td>
                    <td><strong>{{ $commissions->count() }}</strong></td>
                    <td class="text-right" style="color:#0E2F76;"><strong>${{ number_format($totals['total'] ?? 0, 2) }}</strong></td>
                    <td><strong>100%</strong></td>
                    <td class="text-right"><strong>${{ number_format($commissions->count() > 0 ? $totals['total'] / $commissions->count() : 0, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

    </div>

</body>
</html>