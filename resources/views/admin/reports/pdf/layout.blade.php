{{-- resources/views/admin/reports/pdf/layout.blade.php --}}
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
            padding: 0;
            margin: 0;
            background: #fff;
        }
        @include('partials.pdf._styles')
        @include('partials.pdf._letterhead_full_bleed')

        @page {
            size: A4 portrait;
            margin: 0 0 12mm 0;
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

    </style>
    @stack('styles')
</head>
<body>

    @include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])

    <div class="page-content-padded">
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
    
    @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])
    </div>

</body>
</html>