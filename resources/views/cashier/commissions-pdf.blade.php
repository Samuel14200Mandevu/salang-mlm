<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des commissions - {{ $period }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            font-size: 10px; 
            color: #1a1a1a; 
            padding: 15px;
            background: #fff;
        }

        .report-header {
            width: 100%;
            border-bottom: 3px solid #0E2F76;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        
        .report-header table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-header table td {
            border: none;
            padding: 5px 10px;
            vertical-align: middle;
            text-align: center;
        }
        
        .report-header .logo-cell {
            width: 200px;  /* Augmenté pour accueillir un plus grand logo */
            text-align: center;
        }
        
        .report-header .logo-cell img {
            max-height: 100px;  /* Augmenté de 55px à 80px */
            width: auto;
            display: inline-block;
        }
        
        .report-header .header-center {
            text-align: center;
        }
        .report-header .header-center h1 {
            font-size: 18px;
            font-weight: 900;
            color: #0E2F76;
            letter-spacing: 2px;
            margin: 0;
        }
        .report-header .header-center .sub {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        .report-header .header-center .period {
            font-size: 13px;
            font-weight: 700;
            color: #0E2F76;
            margin-top: 4px;
        }
        .report-header .header-center .date {
            font-size: 9px;
            color: #888;
            margin-top: 2px;
        }
        
        .report-summary {
            display: flex;
            justify-content: space-around;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .report-summary .item {
            text-align: center;
            padding: 4px 8px;
        }
        .report-summary .item .number {
            font-size: 14px;
            font-weight: 700;
            color: #0E2F76;
        }
        .report-summary .item .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 5px;
        }
        table th {
            background: #0E2F76;
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 6.5px;
            padding: 5px 4px;
            border: 1px solid #0E2F76;
            text-align: center;
        }
        table td {
            padding: 4px 3px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        table td.text-left {
            text-align: left;
        }
        table td.text-right {
            text-align: right;
        }
        table tr:nth-child(even) {
            background: #f9fafb;
        }
        table tr:hover {
            background: #f0f7ff;
        }
        
        .total-row td {
            font-weight: 700;
            background: #e8f0fe !important;
            border-top: 2px solid #0E2F76;
        }
        
        .report-footer {
            margin-top: 12px;
            text-align: center;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .report-footer .signature {
            margin-top: 4px;
            font-size: 8px;
            color: #666;
        }
        
        .amount-positive {
            color: #22c55e;
            font-weight: 700;
        }
        .amount-zero {
            color: #999;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px 0;
            color: #999;
        }
        .empty-state p {
            font-size: 13px;
        }
        
        @media print {
            body { padding: 8px; }
            .report-header { border-bottom-color: #000; }
            .report-header .header-center h1 { color: #000; }
            .report-header .header-center .period { color: #000; }
            table th { background: #000; border-color: #000; }
            .total-row td { background: #e8f0fe !important; }
            .report-summary .item .number { color: #000; }
        }
        
        @media (max-width: 600px) {
            .report-header table td {
                display: block;
                width: 100% !important;
                text-align: center !important;
                padding: 5px 0;
            }
            .report-header .logo-cell {
                width: 100% !important;
            }
            .report-header .logo-cell img {
                max-height: 80px;  /* Augmenté pour mobile aussi */
            }
            
            table {
                font-size: 6px;
            }
            table th, table td {
                padding: 3px 2px;
            }
        }
    </style>
</head>
<body>
<!--  EN-TÊTE -->
<div class="report-header">
    <table>
        <tr>
            <td class="logo-cell" style="text-align: right; width: auto; padding-right: 5px;">
                <img src="{{ $logoBase64 }}" alt="Salang Group" style="max-height: 50px; width: auto;">
            </td>
            <td class="header-center" style="padding: 0 5px; text-align: center;">
                <h1>SALANG GROUP SARL</h1>
                <div class="sub">E-COMMERCE &amp; MLM</div>
                <div style="margin-top:2px; font-size:12px;">
                <div class="signature">
                N° ID.NAT: 22-7300-N634640 | N° RCCM: CD/BKVIRCM/20-8-001165001<br>
                Rond Point CHIKUDU, Batiment KBS au 3eme Niveau<br>
                Tel: +243 975 220 079 | Email: support@salanggroup.com
                <div class="period">RAPPORT DES COMMISSIONS - {{ $period }}</div>
                <div class="date">Généré le {{ $date->format('d/m/Y H:i') }}</div>
            </td>
            <td class="logo-cell" style="text-align: left; width: auto; padding-left: 5px;">
                <img src="{{ $logoBase64 }}" alt="Salang Group" style="max-height: 50px; width: auto;">
            </td>
        </tr>
    </table>
</div>

<!-- TABLEAU DES COMMISSIONS -->
@if($members->count() > 0)
<table>
    <thead>
        <tr>
            <th style="width:8%;">Période</th>
            <th style="width:10%;">Code Parrain</th>
            <th style="width:15%;">Nom</th>
            <th style="width:5%;">Niv.</th>
            <th style="width:7%;">PV</th>
            <th style="width:10%;">Sponsor</th>
            <th style="width:10%;">Direct</th>
            <th style="width:10%;">Indirect</th>
            <th style="width:10%;">Leadership</th>
            <th style="width:10%;">CASH POS</th>
            <th style="width:10%;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($members as $member)
        @php
            $user = $member['user'];
            $totalMember = $member['sponsor'] + $member['direct'] + $member['indirect'] + $member['leadership'] + $member['cash_pos'];
        @endphp
        <tr>
            <td>{{ $member['period'] ?? $period }}</td>
            <td>{{ $user?->sponsor_id ?? 'N/A' }}</td>
            <td class="text-left">{{ $user?->name ?? 'N/A' }}</td>
            <td>{{ $user?->rank_level ?? '-' }}</td>
            <td>{{ $member['monthly_pv'] ?? 0 }}</td>
            <td>
                @if(($member['sponsor'] ?? 0) > 0)
                    <span class="amount-positive">${{ number_format($member['sponsor'], 2) }}</span>
                @else
                    <span class="amount-zero">-</span>
                @endif
            </td>
            <td>
                @if(($member['direct'] ?? 0) > 0)
                    <span class="amount-positive">${{ number_format($member['direct'], 2) }}</span>
                @else
                    <span class="amount-zero">-</span>
                @endif
            </td>
            <td>
                @if(($member['indirect'] ?? 0) > 0)
                    <span class="amount-positive">${{ number_format($member['indirect'], 2) }}</span>
                @else
                    <span class="amount-zero">-</span>
                @endif
            </td>
            <td>
                @if(($member['leadership'] ?? 0) > 0)
                    <span class="amount-positive">${{ number_format($member['leadership'], 2) }}</span>
                @else
                    <span class="amount-zero">-</span>
                @endif
            </td>
            <td>
                @if(($member['cash_pos'] ?? 0) > 0)
                    <span class="amount-positive">${{ number_format($member['cash_pos'], 2) }}</span>
                @else
                    <span class="amount-zero">-</span>
                @endif
            </td>
            <td class="amount-positive">${{ number_format($totalMember, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5" style="text-align:right; font-weight:700; font-size:9px;">TOTAUX</td>
            <td style="font-weight:700; text-align:center;">${{ number_format($totals['sponsor'], 2) }}</td>
            <td style="font-weight:700; text-align:center;">${{ number_format($totals['direct'], 2) }}</td>
            <td style="font-weight:700; text-align:center;">${{ number_format($totals['indirect'], 2) }}</td>
            <td style="font-weight:700; text-align:center;">${{ number_format($totals['leadership'], 2) }}</td>
            <td style="font-weight:700; text-align:center;">${{ number_format($totals['cash_pos'], 2) }}</td>
            <td style="font-weight:700; text-align:center; color:#0E2F76; font-size:10px;">${{ number_format($totals['grand_total'], 2) }}</td>
        </tr>
    </tfoot>
</table>
@else
<div class="empty-state">
    <p>Aucune commission trouvée pour la période {{ $period }}</p>
    <p style="font-size:10px; color:#aaa; margin-top:4px;">Aucune donnée à afficher</p>
</div>
@endif

<!-- PIED DE PAGE -->
<div class="report-footer">
    <div>Ce rapport est généré automatiquement par le système Salang Group</div>
</div>

</body>
</html>