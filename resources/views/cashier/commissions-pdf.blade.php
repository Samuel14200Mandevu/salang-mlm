{{-- resources/views/cashier/commissions-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Commissions - Salang Group</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Times New Roman', Times, serif, Arial; 
            font-size: 14px; 
            color: #000; 
            padding: 16px 25px;
            background: #fff;
        }

        /* EN-TÊTE (IDENTIQUE AU FORMULAIRE D'ADHÉSION) */
        .report-header {
            width: 100%;
            border-bottom: 2.5px solid #8b0000;
            padding-bottom: 6px;
            margin-bottom: 8px;
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
            width: 80px;
            text-align: center;
        }
        .logo-cell img {
            max-height: 55px;
            width: auto;
        }
        .header-center {
            text-align: center;
        }
        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #558b2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .header-text {
            font-size: 9px;
            line-height: 1.3;
            font-weight: bold;
        }
        .address {
            font-size: 8.5px;
            color: #000;
            margin-top: 2px;
            line-height: 1.3;
            font-weight: normal;
        }
        .address .city {
            font-weight: bold;
            color: #0E2F76;
        }

        /* TITRE */
        .main-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 6px;
        }
        .form-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            padding: 0 3px;
        }

        /* SECTIONS DE TITRE */
        .section-header {
            font-size: 14.5px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 2px;
            margin: 10px 0 6px 0;
            text-transform: uppercase;
        }

        /* TABLEAU DES DONNÉES */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table th {
            background-color: #fff;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            padding: 5px 3px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 4px 3px;
            border-bottom: 1.5px dotted #000;
            border-left: 1px solid #ccc;
            border-right: 1px solid #ccc;
            text-align: center;
            vertical-align: middle;
            font-size: 12.5px;
        }
        .data-table td.text-left { text-align: left; }
        
        .total-row td {
            font-weight: bold;
            border-top: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
            font-size: 13px;
            background-color: #fff;
        }

        .amount-positive {
            color: #000;
            font-weight: bold;
        }
        .amount-zero {
            color: #666;
        }

        .empty-state {
            text-align: center;
            padding: 30px 0;
            color: #666;
            font-size: 13.5px;
        }

        /* PIED DE PAGE (IDENTIQUE AU FORMULAIRE D'ADHÉSION) */
        .footer-container {
            margin-top: 16px;
            width: 100%;
        }
        .footer-left {
            font-size: 11px;
            line-height: 1.5;
            text-align: center;
        }
        .footer-line {
            border-bottom: 1.5px solid #cbd5e0;
            margin-top: 6px;
        }

        .page-content {
            max-height: 100vh;
            overflow: hidden;
            background: #fff;
        }

        @media print {
            body { padding: 10px 20px; }
            .no-print { display: none !important; }
            @page { 
                size: A4 portrait;
                margin: 0.6cm 0.8cm; 
            }
        }
    </style>
</head>
<body>

<!-- PAGE CONTENT -->
<div class="page-content">

    <!-- EN-TÊTE -->
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/salang_logo.png'))) }}" alt="Salang Logo">
                </td>
                <td class="header-center">
                    <div class="company-title">SALANG GROUP SARL</div>
                    <div class="header-text">
                        N° IDN:22-M7300-N63464Q &nbsp; N° RCCM:CD/BKV/RCCM/20-B-00116 &nbsp; N° IMPORT-EXPORT:0024/CBX-21/I000439SK/Z<br>
                        Contact : +243 975 220 079 &nbsp; Web:www.salanggroup.com &nbsp; Email:support@salanggroup.com
                    </div>
                    <div class="address">
                        <span class="city">Kinshasa :</span> 4 AV, Ixoras 382, 7eme Rue Resid. &nbsp;|&nbsp;
                        <span class="city">Bukavu :</span> N°4 Av. FIZI, Q. Nyawera, C/Ibanda &nbsp;|&nbsp;
                        <span class="city">Goma :</span> Rondpoint Chikudu, Bat. KBS 3e N.
                    </div>
                </td>
                <td class="logo-cell">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/salang_logo.png'))) }}" alt="Salang Logo">
                </td>
            </tr>
        </table>
    </div>

    <!-- TITRE + META -->
    <div class="main-title">Rapport des Commissions</div>
    <div class="form-meta">
        <div>Période : <strong>{{ $period }}</strong></div>
        <div>Généré le : <strong>{{ $date->format('d/m/Y') }}</strong></div>
    </div>

    <!-- SECTION : COMMISSIONS -->
    <div class="section-header">Détails des Commissions par Membre</div>
    
    @if(count($members) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:8%;">Période</th>
                <th style="width:11%;">Code ID</th>
                <th style="width:20%;">Nom Complet</th>
                <th style="width:5%;">Niv.</th>
                <th style="width:6%;">PV</th>
                <th style="width:9%;">Sponsor</th>
                <th style="width:9%;">Direct</th>
                <th style="width:9%;">Indirect</th>
                <th style="width:11%;">Leadership</th>
                <th style="width:12%;">Cash POS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($members as $member)
            @php
                $user = $member['user'];
            @endphp
            <tr>
                <td>{{ $member['period'] ?? $period }}</td>
                <td><strong>{{ $user?->sponsor_id ?? 'N/A' }}</strong></td>
                <td class="text-left"><strong>{{ $user?->name ?? 'N/A' }}</strong></td>
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
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align:right; font-weight:bold;">TOTAUX :</td>
                <td>${{ number_format($totals['sponsor'], 2) }}</td>
                <td>${{ number_format($totals['direct'], 2) }}</td>
                <td>${{ number_format($totals['indirect'], 2) }}</td>
                <td>${{ number_format($totals['leadership'], 2) }}</td>
                <td>${{ number_format($totals['cash_pos'], 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="9" style="text-align:right; font-weight:bold; color:#0E2F76;">TOTAL GÉNÉRAL :</td>
                <td style="color:#0E2F76; font-size:14px;"><strong>${{ number_format($totals['grand_total'], 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">
        <p>Aucune commission enregistrée pour la période {{ $period }}</p>
    </div>
    @endif

    <!-- BAS DE PAGE (IDENTIQUE AU FORMULAIRE D'ADHÉSION) -->
    <div class="footer-container">
        <div class="footer-left">
            <strong>Salang Group International SARL</strong> — Service Administratif &amp; Réseau<br>
            Site web : www.salanggroup.com &nbsp;|&nbsp; E-mail : support@salanggroup.com
        </div>
        <div class="footer-line"></div>
    </div>

</div>
</body>
</html>