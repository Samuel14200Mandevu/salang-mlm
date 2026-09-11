{{-- resources/views/admin/commissions/commission-global-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport Global des Commissions</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11px; 
            color: #000; 
            padding: 10px 18px 45px 18px;
            line-height: 1.3;
        }

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
        }
        .address .city {
            font-weight: bold;
            color: #0E2F76;
        }

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

        .direct-color { color: #4F46E5; }
        .indirect-color { color: #2563EB; }
        .leadership-color { color: #A65A0E; }
        .cash-color { color: #16a34a; }
        .sponsor-color { color: #B54708; }
        .retail-color { color: #1C7E4A; }

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

    <div class="footer-container">
        <div class="footer-left">
            <strong>Salang Group International SARL</strong> — Service Administratif &amp; Réseau<br>
            Site web : www.salanggroup.com &nbsp;|&nbsp; E-mail : support@salanggroup.com
        </div>
        <div class="footer-line"></div>
        <div class="footer-right">Généré le {{ $date_generated }}</div>
    </div>

    <div class="page-content">

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

        <div class="main-title">Rapport Global des Commissions</div>
        
        <table class="form-meta">
            <tr>
                <td style="text-align: left; width:50%;">Période : <strong>{{ $period }}</strong></td>
                <td style="text-align: right; width:50%;">Date : <strong>{{ $date_generated }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: left;">Bénéficiaires : <strong>{{ $beneficiaires->count() }}</strong></td>
                <td style="text-align: right;">Commissions : <strong>{{ $commissions->count() }}</strong></td>
            </tr>
            @if(!empty($filters['search']) || !empty($filters['type']) || !empty($filters['status']))
            <tr>
                <td colspan="2" style="text-align: center; font-size: 9px; color: #666;">
                    Filtres : 
                    @if($filters['search']) Recherche: "{{ $filters['search'] }}" @endif
                    @if($filters['type']) | Type: {{ ucfirst($filters['type']) }} @endif
                    @if($filters['status']) | Statut: {{ ucfirst($filters['status']) }} @endif
                </td>
            </tr>
            @endif
        </table>

        <div class="section-header">1. Détail Global par Membre</div>
        
        <table class="global-table">
            <thead>
                <tr>
                    <th style="width:4%;">N°</th>
                    <th style="width:9%;">Code Membre</th>
                    <th style="width:18%;">Membre</th>
                    <th style="width:9%;">Code Parrain</th>
                    <th style="width:6%;">Grade</th>
                    <th style="width:8%;">PV</th>
                    <th style="width:8%;">Direct</th>
                    <th style="width:8%;">Indirect</th>
                    <th style="width:8%;">Leadership</th>
                    <th style="width:8%;">Sponsor</th>
                    <th style="width:7%;">Retail</th>
                    <th style="width:7%;">Cash</th>
                    <th style="width:8%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $numero = 1; @endphp
                @foreach($beneficiaires as $userId => $beneficiaire)
                    @php
                        $d = $beneficiaire['details'];
                        $directTotal = collect($d)->where('type', 'direct')->sum('amount');
                        $indirectTotal = collect($d)->where('type', 'indirect')->sum('amount');
                        $leadershipTotal = collect($d)->where('type', 'leadership')->sum('amount');
                        $sponsorTotal = collect($d)->where('type', 'sponsor')->sum('amount');
                        $retailTotal = collect($d)->where('type', 'retail')->sum('amount');
                        $cashTotal = collect($d)->where('type', 'cash_pos')->sum('amount');
                    @endphp
                    <tr>
                        <td>{{ $numero++ }}</td>
                        <td class="text-center" style="color:#0E2F76; font-weight:bold;">{{ $beneficiaire['code'] }}</td>
                        <td class="text-left"><strong>{{ $beneficiaire['name'] }}</strong></td>
                        <td class="text-center" style="color:#000; font-weight:bold;">{{ $beneficiaire['parrain_code'] }}</td>
                        <td>{{ $beneficiaire['rank_level'] }}</td>
                        <td class="text-right" style="color:#0E2F76; font-weight:bold;">{{ number_format($beneficiaire['pv'], 1, ',', ' ') }}</td>
                        <td class="text-right direct-color">${{ number_format($directTotal, 2) }}</td>
                        <td class="text-right indirect-color">${{ number_format($indirectTotal, 2) }}</td>
                        <td class="text-right leadership-color">${{ number_format($leadershipTotal, 2) }}</td>
                        <td class="text-right sponsor-color">${{ number_format($sponsorTotal, 2) }}</td>
                        <td class="text-right retail-color">${{ number_format($retailTotal, 2) }}</td>
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
                    <td class="text-right sponsor-color">${{ number_format($totals['sponsor'] ?? 0, 2) }}</td>
                    <td class="text-right retail-color">${{ number_format($totals['retail'] ?? 0, 2) }}</td>
                    <td class="text-right cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
                    <td class="text-right" style="color:#0E2F76;">${{ number_format($totals['total'] ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>

    </div>

</body>
</html>