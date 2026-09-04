{{-- resources/views/cashier/payslip-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Paie - {{ $member->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Times New Roman', Times, serif, Arial; 
            font-size: 13px; 
            color: #000; 
            padding: 15px 20px 50px 20px; /* Espace réservé pour le footer en bas */
            background: #fff;
        }

        /* PIED DE PAGE PERMANENT */
        .footer-container {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            width: 100%;
            text-align: center;
            background-color: #fff;
            padding-top: 4px;
        }
        .footer-left {
            font-size: 10.5px;
            line-height: 1.3;
            text-align: center;
            color: #000;
        }
        .footer-line {
            border-bottom: 1.5px solid #cbd5e0;
            margin-top: 4px;
        }

        /* EN-TÊTE */
        .report-header {
            width: 100%;
            border-bottom: 2.5px solid #8b0000;
            padding-bottom: 6px;
            margin-bottom: 10px;
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
            width: 75px;
            text-align: center;
        }
        .logo-cell img {
            max-height: 52px;
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
            margin-bottom: 2px;
        }
        .header-text {
            font-size: 9px;
            line-height: 1.25;
            font-weight: bold;
        }
        .address {
            font-size: 8.5px;
            color: #000;
            margin-top: 2px;
            line-height: 1.25;
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
            margin-bottom: 8px;
        }
        .form-meta {
            width: 100%;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: bold;
            border-collapse: collapse;
        }
        .form-meta td {
            padding: 2px 0;
        }

        /* SECTIONS NUMÉROTÉES */
        .section-header {
            font-size: 13.5px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 2px;
            margin: 10px 0 6px 0;
            text-transform: uppercase;
        }

        /* GRILLES / CHAMPS */
        .form-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .form-grid td {
            padding: 4px 4px;
            vertical-align: middle;
            font-size: 12.5px;
        }
        .field-label {
            font-weight: bold;
            white-space: nowrap;
        }
        .field-line {
            border-bottom: 1.5px dotted #000;
            padding-left: 5px;
            padding-bottom: 1px;
        }

        /* TABLEAU DYNAMIQUE DES COMMISSIONS */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 10px;
            page-break-inside: auto; /* Permet la pagination si très long */
        }
        .data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .data-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10.5px;
            padding: 5px 3px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 4px 3px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
        }
        .data-table td.text-left { text-align: left; }

        .total-row td {
            font-weight: bold;
            font-size: 12.5px;
            background-color: #f9f9f9;
        }

        /* SIGNATURES (Ne se séparent jamais) */
        .signatures-container {
            page-break-inside: avoid;
            margin-top: 10px;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 8px;
            font-size: 12.5px;
        }
        .signature-box {
            border: 1.5px solid #000;
            min-height: 60px;
            margin-top: 4px;
            padding: 6px 8px;
            font-size: 11.5px;
            color: #555;
        }

        @media print {
            body { padding: 10px 15px 45px 15px; }
            @page { 
                size: A4 portrait;
                margin: 0.5cm 0.6cm; 
            }
        }
    </style>
</head>
<body>

    <!-- PIED DE PAGE FIXE EN BAS DE LA PAJE (Positioned fixed) -->
    <div class="footer-container">
        <div class="footer-left">
            <strong>Salang Group International SARL</strong> — Service Administratif &amp; Réseau<br>
            Site web : www.salanggroup.com &nbsp;|&nbsp; E-mail : support@salanggroup.com
        </div>
        <div class="footer-line"></div>
    </div>

    <!-- CONTENU PRINCIPAL -->
    <div class="page-content">

        <!-- EN-TÊTE -->
        <div class="report-header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @if(!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" alt="Salang Logo">
                        @endif
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
                        @if(!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" alt="Salang Logo">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- TITRE ET META -->
        <div class="main-title">Fiche de Paie des Commissions</div>
        <table class="form-meta">
            <tr>
                <td style="text-align: left;">Période : <strong>{{ $period }}</strong></td>
                <td style="text-align: right;">Date : <strong>{{ $date->format('d/m/Y') }}</strong></td>
            </tr>
        </table>

        <!-- SECTION 1 : INFORMATIONS BÉNÉFICIAIRE -->
        <div class="section-header">1. Informations du Membre</div>
        <table class="form-grid">
            <tr>
                <td class="field-label" style="width:16%;">Nom complet :</td>
                <td class="field-line" style="width:34%;"><strong>{{ $member->name }}</strong></td>
                <td class="field-label" style="width:16%; text-align:right; padding-right:4px;">Code Membre :</td>
                <td class="field-line" style="width:34%;"><strong>{{ $member->sponsor_id }}</strong></td>
            </tr>
            <tr>
                <td class="field-label">Grade :</td>
                <td class="field-line"><strong>{{ $member->rank ?? 'Distributeur' }}</strong></td>
                <td class="field-label" style="text-align:right; padding-right:4px;">Package :</td>
                <td class="field-line"><strong>{{ $member->package?->name ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="field-label">PV Mensuel :</td>
                <td class="field-line"><strong>{{ number_format($monthlyPv, 0) }} PV</strong></td>
                <td class="field-label" style="text-align:right; padding-right:4px;">PV Réseau :</td>
                <td class="field-line"><strong>{{ number_format($teamPv, 0) }} PV</strong></td>
            </tr>
            <tr>
                <td class="field-label">Parrain :</td>
                <td class="field-line"><strong>{{ $member->parrain?->name ?? 'Aucun' }}</strong></td>
                <td class="field-label" style="text-align:right; padding-right:4px;">Téléphone :</td>
                <td class="field-line"><strong>{{ $member->phone ?? 'N/A' }}</strong></td>
            </tr>
        </table>

        <!-- SECTION 2 : RÉSUMÉ DES GAINS -->
        <div class="section-header">2. Résumé des Gains</div>
        <table class="form-grid">
            <tr>
                <td class="field-label" style="width:20%;">Sponsor Bonus :</td>
                <td class="field-line" style="width:30%;"><strong>${{ number_format($totals['sponsor'] ?? 0, 2) }}</strong></td>
                <td class="field-label" style="width:20%; text-align:right; padding-right:4px;">Direct Bonus :</td>
                <td class="field-line" style="width:30%;"><strong>${{ number_format($totals['direct'] ?? 0, 2) }}</strong></td>
            </tr>
            <tr>
                <td class="field-label">Indirect Bonus :</td>
                <td class="field-line"><strong>${{ number_format($totals['indirect'] ?? 0, 2) }}</strong></td>
                <td class="field-label" style="text-align:right; padding-right:4px;">Leadership Bonus :</td>
                <td class="field-line"><strong>${{ number_format($totals['leadership'] ?? 0, 2) }}</strong></td>
            </tr>
            <tr>
                <td class="field-label">CASH POS Bonus :</td>
                <td class="field-line"><strong>${{ number_format($totals['cash_pos'] ?? 0, 2) }}</strong></td>
                <td class="field-label" style="text-align:right; padding-right:4px;">TOTAL GÉNÉRAL :</td>
                <td class="field-line"><strong>${{ number_format($totalCommissions, 2) }}</strong></td>
            </tr>
        </table>

        <!-- SECTION 3 : DÉTAIL DYNAMIQUE -->
        <div class="section-header">3. Détail des Transactions</div>
        @if(isset($commissionDetails) && $commissionDetails->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:25%; text-align:left;">Personne</th>
                    <th style="width:11%;">Type</th>
                    <th style="width:12.8%;">Sponsor</th>
                    <th style="width:12.8%;">Direct</th>
                    <th style="width:12.8%;">Indirect</th>
                    <th style="width:12.8%;">Leadership</th>
                    <th style="width:12.8%;">CASH POS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commissionDetails as $detail)
                <tr>
                    <td class="text-left">
                        <strong>{{ $detail['user']?->name ?? 'N/A' }}</strong>
                        <br><span style="font-size: 9.5px; color: #444;">Code: {{ $detail['user']?->sponsor_id ?? 'N/A' }}</span>
                    </td>
                    <td>
                        @if($detail['user_type'] == 'member')
                            Membre
                        @elseif($detail['user_type'] == 'client')
                            Client POS
                        @else
                            —
                        @endif
                    </td>
                    <td>${{ number_format($detail['sponsor'] ?? 0, 2) }}</td>
                    <td>${{ number_format($detail['direct'] ?? 0, 2) }}</td>
                    <td>${{ number_format($detail['indirect'] ?? 0, 2) }}</td>
                    <td>${{ number_format($detail['leadership'] ?? 0, 2) }}</td>
                    <td>${{ number_format($detail['cash_pos'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="text-left">TOTAUX :</td>
                    <td>—</td>
                    <td>${{ number_format($totals['sponsor'] ?? 0, 2) }}</td>
                    <td>${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                    <td>${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                    <td>${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                    <td>${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="6" style="text-align:right; font-weight:bold; color:#0E2F76;">TOTAL NET À PAYER :</td>
                    <td style="color:#0E2F76; font-size:13px;"><strong>${{ number_format($totalCommissions, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
        @else
        <div style="text-align:center; padding: 12px 0; font-size: 12px; color: #555;">
            Aucune commission enregistrée pour la période {{ $period }}.
        </div>
        @endif

        <!-- SECTION 4 : SIGNATURES ET APPROBATION -->
        <div class="signatures-container">
            <div class="section-header">4. Signatures & Approbation</div>
            <table class="signatures-table">
                <tr>
                    <td>
                        <strong>Signature du Bénéficiaire :</strong>
                        <div class="signature-box">
                            <span>Lu et approuvé</span>
                        </div>
                    </td>
                    <td>
                        <strong>Signature &amp; Cachet de la Caisse :</strong>
                        <div class="signature-box">
                            <span>Validé et payé</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>