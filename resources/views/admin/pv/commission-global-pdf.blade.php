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
            font-family: 'Times New Roman', Times, serif, Arial; 
            font-size: 11px; 
            color: #000; 
            padding: 10px 18px 45px 18px;
            background: #fff;
            line-height: 1.3;
        }

        /* PIED DE PAGE PERMANENT */
        .footer-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            background-color: #fff;
            padding: 4px 20px 6px 20px;
        }
        .footer-left {
            font-size: 9px;
            line-height: 1.3;
            text-align: center;
            color: #000;
        }
        .footer-line {
            border-bottom: 1.5px solid #cbd5e0;
            margin-top: 3px;
        }
        .footer-right {
            font-size: 8px;
            color: #666;
            text-align: right;
            margin-top: 1px;
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
            width: 65px;
            text-align: center;
        }
        .logo-cell img {
            max-height: 45px;
            width: auto;
        }
        .header-center {
            text-align: center;
        }
        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #558b2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .header-text {
            font-size: 7.5px;
            line-height: 1.2;
            font-weight: bold;
        }
        .address {
            font-size: 7px;
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
            font-size: 17px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .form-meta {
            width: 100%;
            margin-bottom: 4px;
            font-size: 10.5px;
            font-weight: bold;
            border-collapse: collapse;
        }
        .form-meta td {
            padding: 2px 0;
        }

        /* SECTIONS NUMÉROTÉES */
        .section-header {
            font-size: 11.5px;
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
            font-size: 10.5px;
            background-color: #f9f9f9;
        }
        .totals-grid .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            background-color: #f2f2f2;
        }
        .totals-grid .amount {
            font-weight: bold;
            font-size: 12px;
        }
        .direct-color { color: #4F46E5; }
        .indirect-color { color: #2563EB; }
        .leadership-color { color: #A65A0E; }
        .cash-color { color: #16a34a; }

        /* TABLEAU GLOBAL */
        .global-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 4px 0;
            font-size: 9px;
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
            font-size: 7.5px;
            padding: 3px 4px;
            border: 1px solid #000;
            text-align: center;
        }
        .global-table td {
            padding: 2px 4px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 8.5px;
        }
        .global-table td.text-left { text-align: left; }
        .global-table td.text-right { text-align: right; }

        .global-table .total-row td {
            font-weight: bold;
            font-size: 9.5px;
            background-color: #f9f9f9;
        }

        /* LIGNES DE SÉPARATION PAR UTILISATEUR */
        .user-header-row td {
            background-color: #0E2F76;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            padding: 4px 8px;
            text-align: left;
        }
        .user-total-row td {
            background-color: #f9f9f9;
            font-weight: bold;
            font-size: 9px;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-direct { background: rgba(79, 70, 229, 0.15); color: #4F46E5; }
        .badge-indirect { background: rgba(37, 99, 235, 0.15); color: #2563EB; }
        .badge-leadership { background: rgba(166, 90, 14, 0.15); color: #A65A0E; }
        .badge-cash { background: rgba(22, 163, 74, 0.15); color: #16a34a; }

        @media print {
            body { padding: 8px 15px 40px 15px; }
            @page { 
                size: A4 portrait;
                margin: 0.4cm 0.5cm; 
            }
        }
    </style>
</head>
<body>

    <!-- PIED DE PAGE FIXE -->
    <div class="footer-container">
        <div class="footer-left">
            <strong>Salang Group International SARL</strong> — Service Administratif &amp; Réseau<br>
            Site web : www.salanggroup.com &nbsp;|&nbsp; E-mail : support@salanggroup.com
        </div>
        <div class="footer-line"></div>
        <div class="footer-right">Généré le {{ $date_generated }}</div>
    </div>

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
                            <img src="{{ $logoBase64 }}" alt="Salang Logo">
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
                            <img src="{{ $logoBase64 }}" alt="Salang Logo">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- TITRE -->
        <div class="main-title">Rapport Global des Commissions</div>
        
        <table class="form-meta">
            <tr>
                <td style="text-align: left; width:50%;">Période : <strong>{{ $period }}</strong></td>
                <td style="text-align: right; width:50%;">Date : <strong>{{ $date_generated }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: left;">Nombre de bénéficiaires : <strong>{{ $beneficiaires->count() }}</strong></td>
                <td style="text-align: right;">Nombre de commissions : <strong>{{ $commissions->count() }}</strong></td>
            </tr>
        </table>

        <!-- 1. TABLEAU GLOBAL DES COMMISSIONS -->
        <div class="section-header">1. Détail Global par Membre</div>
        
        <table class="global-table">
            <thead>
                <tr>
                    <th style="width:4%;">N°</th>
                    <th style="width:9%;">Code Membre</th>
                    <th style="width:20%;">Membre</th>
                    <th style="width:9%;">Code Parrain</th>
                    <th style="width:6%;">Grade</th>
                    <th style="width:8%;">PV</th>
                    <th style="width:9%;">Direct</th>
                    <th style="width:9%;">Indirect</th>
                    <th style="width:9%;">Leadership</th>
                    <th style="width:8%;">Cash POS</th>
                    <th style="width:9%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $numero = 1; @endphp
                @foreach($beneficiaires as $userId => $beneficiaire)
                    @php
                        $userCommissions = $beneficiaire['details'];
                        $directTotal = collect($userCommissions)->where('type', 'direct')->sum('amount');
                        $indirectTotal = collect($userCommissions)->where('type', 'indirect')->sum('amount');
                        $leadershipTotal = collect($userCommissions)->where('type', 'leadership')->sum('amount');
                        $cashTotal = collect($userCommissions)->where('type', 'cash_pos')->sum('amount');
                        $userRank = $beneficiaire['rank_level'] ?? 'N/A';
                        $userPV = $beneficiaire['pv'] ?? 0;
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
                        <td class="text-right direct-color">${{ number_format($directTotal, 2) }}</td>
                        <td class="text-right indirect-color">${{ number_format($indirectTotal, 2) }}</td>
                        <td class="text-right leadership-color">${{ number_format($leadershipTotal, 2) }}</td>
                        <td class="text-right cash-color">${{ number_format($cashTotal, 2) }}</td>
                        <td class="text-right" style="color:#0E2F76; font-weight:bold;">${{ number_format($beneficiaire['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" style="text-align:right;">TOTAL GÉNÉRAL :</td>
                    <td class="text-right direct-color">${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                    <td class="text-right indirect-color">${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                    <td class="text-right leadership-color">${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                    <td class="text-right cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
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