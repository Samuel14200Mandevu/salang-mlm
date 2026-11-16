{{-- resources/views/cashier/history-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Historique des Ventes POS - {{ $periodLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            padding: 0;
            margin: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #111;
            background: #fff;
        }

        .page-content {
            display: block;
            margin: 18px 40px 12px 40px;
            padding: 0;
        }

        @include('partials.pdf._styles')

        .salang-letterhead {
            width: 100%;
            margin: 0 0 4px 0;
            padding: 0;
        }
        .salang-letterhead__img {
            width: 100%;
            max-width: 100%;
            height: 145px;
            min-height: 145px;
            display: block;
        }

        .salang-pdf-footer {
            margin-top: 8px;
        }

        .main-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #0A254E;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0;
            line-height: 1.2;
        }
        .main-title-rule {
            width: 36%;
            margin: 2px auto 3px auto;
            border-bottom: 1.5px solid #C5A04D;
        }
        .main-subtitle {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #C5A04D;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0 0 3px 0;
            line-height: 1.2;
        }
        .form-meta {
            width: 100%;
            margin: 0 0 4px 0;
            font-size: 11px;
            font-weight: bold;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .form-meta td {
            padding: 1px 0;
            white-space: nowrap;
            vertical-align: middle;
        }
        .form-meta td.meta-left {
            text-align: left;
            width: 50%;
        }
        .form-meta td.meta-right {
            text-align: right;
            width: 50%;
        }

        .section-header {
            font-size: 11.5px;
            font-weight: bold;
            color: #0A254E;
            border-bottom: 1.5px solid #C5A04D;
            padding-bottom: 1px;
            margin: 4px 0 2px 0;
            text-transform: uppercase;
            clear: both;
            line-height: 1.2;
        }

        .global-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1px 0 3px 0;
            font-size: 10.5px;
        }
        .global-table thead { display: table-header-group; }
        .global-table thead tr.page-top-gap td {
            height: 12mm;
            border: none !important;
            background: #fff !important;
            padding: 0 !important;
            font-size: 1px;
            line-height: 0;
            color: transparent;
        }
        .global-table tfoot { display: table-row-group; }
        .global-table tr { page-break-inside: avoid; }
        .global-table tr.total-row {
            page-break-before: avoid;
            page-break-inside: avoid;
        }
        .global-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 2px 3px;
            border: 1px solid #000;
            text-align: center;
            line-height: 1.15;
        }
        .global-table td {
            padding: 1.5px 3px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            line-height: 1.15;
        }
        .global-table td.text-left { text-align: left; }
        .global-table td.text-right { text-align: right; }
        .global-table .total-row td {
            font-weight: bold;
            font-size: 10.5px;
            background-color: #f9f9f9;
        }

        .sub { font-size: 8px; color: #444; }
        .empty-state {
            text-align: center;
            padding: 24px 0;
            color: #666;
            font-size: 12px;
        }

        @page {
            size: A4 landscape;
            margin: 0 0 12mm 0;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            @page { margin: 0 0 12mm 0; }
        }
    </style>
</head>
<body>

@include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])

<div class="page-content">

    <div class="main-title">Rapport Historique des Ventes POS</div>
    <div class="main-title-rule"></div>
    <div class="main-subtitle">{{ $periodLabel }}</div>

    <table class="form-meta">
        <tr>
            <td class="meta-left">Période : <strong>{{ $periodLabel }}</strong></td>
            <td class="meta-right">Date : <strong>{{ $date_generated }}</strong></td>
        </tr>
        <tr>
            <td class="meta-left">Nombre de ventes : <strong>{{ $rows->count() }}</strong></td>
            <td class="meta-right">
                @if(!empty($search))
                    Recherche : <strong>{{ $search }}</strong>
                @else
                    Total remis : <strong>${{ number_format($totals['paid'] ?? 0, 2) }}</strong>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-header">1. Détail des ventes POS</div>

    @if($rows->isNotEmpty())
    <table class="global-table">
        <thead>
            <tr class="page-top-gap">
                <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
                <th style="width:12%;">Date</th>
                <th style="width:14%;">N° commande</th>
                <th style="width:18%;">Client</th>
                <th style="width:18%;">Membre</th>
                <th style="width:10%;">Prix</th>
                <th style="width:12%;">Commission</th>
                <th style="width:10%;">Payé</th>
                <th style="width:6%;">PV</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td style="color:#0A254E; font-weight:bold;">#{{ $row['order_number'] }}</td>
                    <td class="text-left">
                        <strong>{{ $row['client_name'] }}</strong>
                    </td>
                    <td class="text-left">
                        @if($row['sponsor_code'] || $row['sponsor_name'])
                            <strong>
                                @if($row['sponsor_code']){{ $row['sponsor_code'] }} : @endif{{ $row['sponsor_name'] }}
                            </strong>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right">${{ number_format($row['price'], 2) }}</td>
                    <td class="text-right">
                        @if($row['commission'] > 0)
                            ${{ number_format($row['commission'], 2) }}
                            @if($row['commission_detail'])
                                <div class="sub">{{ $row['commission_detail'] }}</div>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right" style="color:#16a34a; font-weight:bold;">${{ number_format($row['paid'], 2) }}</td>
                    <td class="text-right" style="color:#0A254E; font-weight:bold;">{{ number_format($row['pv'], 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align:right; color:#0A254E;">TOTAL GÉNÉRAL :</td>
                <td class="text-right" style="color:#0A254E;">${{ number_format($totals['price'], 2) }}</td>
                <td class="text-right" style="color:#0A254E;">${{ number_format($totals['commission'], 2) }}</td>
                <td class="text-right" style="color:#0A254E;">${{ number_format($totals['paid'], 2) }}</td>
                <td class="text-right" style="color:#0A254E;">{{ number_format($totals['pv'], 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">
        <p>Aucune vente POS pour cette période</p>
    </div>
    @endif

    @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])

</div>
</body>
</html>
