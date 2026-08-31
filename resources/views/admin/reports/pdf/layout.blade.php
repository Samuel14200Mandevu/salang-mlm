{{-- resources/views/admin/reports/pdf/layout.blade.php --}}
@php
    $logoPath = public_path('images/salang_logo.png');
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoBase64 = 'data:image/png;base64,' . $logoData;
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Salang Group - Rapport')</title>
    <style>
        /* ===== STYLES GÉNÉRAUX DomPDF ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5px;
            color: #1a202c;
            line-height: 1.4;
            padding: 15px 20px;
        }

        /* ===== EN-TÊTE CORPORATE ===== */
        .company-header {
            width: 100%;
            border-bottom: 2.5px solid #8b0000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-cell {
            width: 75px;
            text-align: center;
        }
        .logo-cell img {
            max-height: 60px;
            width: auto;
        }
        .header-center {
            text-align: center;
            padding: 0 8px;
        }
        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #558b2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .header-text {
            font-size: 8.5px;
            font-weight: bold;
            color: #2d3748;
            line-height: 1.2;
        }
        .address {
            font-size: 8px;
            color: #4a5568;
            margin-top: 2px;
            line-height: 1.2;
        }
        .address .city {
            font-weight: bold;
            color: #0E2F76;
        }

        /* ===== TITRE ET META DU RAPPORT ===== */
        .report-meta-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .report-meta-table td {
            font-size: 8.5px;
            color: #718096;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
        }

        /* ===== TABLEAUX ===== */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table.data-table thead th {
            background: #0E2F76;
            color: #ffffff;
            padding: 6px 8px;
            text-align: left;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #0E2F76;
        }
        table.data-table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #edf2f7;
            border-right: 1px solid #edf2f7;
            font-size: 8.5px;
        }
        table.data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        /* ===== STATUT BADGE ===== */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-warning { background: #feebc8; color: #744210; }
        .badge-danger { background: #fed7d7; color: #742a2a; }
        .badge-info { background: #ebf8ff; color: #2c5282; }

        /* ===== RECAPITULATIF SUMMARY ===== */
        .summary-box {
            margin-top: 15px;
            padding: 8px 12px;
            background: #f7fafc;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
        .summary-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #0E2F76;
            margin-bottom: 6px;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e0;
            padding-bottom: 3px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 3px 0;
            font-size: 8.5px;
            border-bottom: 1px dashed #e2e8f0;
        }

        /* ===== PIED DE PAGE ET TAMPON ===== */
        .footer-container {
            margin-top: 20px;
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-left {
            font-size: 8px;
            color: #718096;
        }
        .stamp-box {
            border: 1.5px dashed #0E2F76;
            color: #0E2F76;
            padding: 4px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 8.5px;
            border-radius: 4px;
            text-transform: uppercase;
            background: #ffffff;
            display: inline-block;
        }
    </style>
</head>
<body>
    
    <!-- EN-TÊTE D'ENTREPRISE -->
    <div class="company-header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Salang Logo">
                    @endif
                </td>
                <td class="header-center">
                    <div class="company-title">SALANG GROUP SARL</div>
                    <div class="header-text">
                        N° IDN : 22-M7300-N63464Q &nbsp;|&nbsp; N° RCCM : CD/BKV/RCCM/20-B-00116<br>
                        N° IMPORT-EXPORT : 0024/CBX-21/I000439SK/Z<br>
                        Contact : +243 975 220 079 &nbsp;|&nbsp; Web : www.salanggroup.com &nbsp;|&nbsp; Email : support@salanggroup.com
                    </div>
                    <div class="address">
                        <span class="city">Kinshasa :</span> 4 AV, Ixoras 382, 7ème Rue Résidentielle, Petit Boulevard &nbsp;|&nbsp;
                        <span class="city">Bukavu :</span> N°4 Avenue FIZI, Nyawera, Ibanda<br>
                        <span class="city">Goma :</span> Rond-point Chikudu, Bâtiment KBS, 3ème Niveau
                    </div>
                </td>
                <td class="logo-cell">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Salang Logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>
    
    <!-- TITRE & INFOS GÉNÉRATION -->
    <table class="report-meta-table">
        <tr>
            <td>
                <div class="report-title">@yield('report_title', 'RAPPORT')</div>
            </td>
            <td style="text-align: right;">
                <strong>Date :</strong> {{ now()->format('d/m/Y à H:i') }}<br>
                <strong>Référence :</strong> RPT-{{ now()->format('Ymd') }}-{{ strtoupper(substr(md5(uniqid()), 0, 5)) }}
            </td>
        </tr>
    </table>
    
    <!-- CONTENU DU RAPPORT -->
    @yield('content')
    
    <!-- PIED DE PAGE -->
    <div class="footer-container">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <strong>Salang Group International SARL</strong> - Santé & Bien-être - Marketing de réseau relationnel<br>
                    Document officiel généré automatiquement. Règlement d'ordre intérieur appliqué.
                </td>
                <td style="text-align: right;">
                    <div class="stamp-box">
                        Document Validé<br>
                        <span style="font-size: 7px; font-weight: normal;">SALANG GROUP SARL</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
</body>
</html>