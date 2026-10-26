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
            padding: 28px 32px 32px 32px;
            font-family: DejaVu Sans, sans-serif;
            color: #000;
        }

        .page-content { width: 100%; }

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
        .header-center { text-align: center; }
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

        .main-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .form-meta {
            width: 100%;
            margin: 2px 0 6px 0;
            font-size: 12px;
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
            font-size: 16px;
            font-weight: bold;
            color: #8b0000;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 0.5px;
            padding: 0 8px;
        }

        .section-header {
            font-size: 14px;
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
            font-size: 10px;
        }
        .global-table thead { display: table-header-group; }
        .global-table tr { page-break-inside: avoid; }
        .global-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 4px 3px;
            border: 1px solid #000;
            text-align: center;
        }
        .global-table td {
            padding: 3px 3px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 9.5px;
        }
        .global-table td.text-left { text-align: left; }
        .global-table td.text-right { text-align: right; }
        .global-table .total-row td {
            font-weight: bold;
            font-size: 10.5px;
            background-color: #f9f9f9;
        }

        .sub { font-size: 8px; color: #444; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-package { background: rgba(34, 197, 94, 0.15); color: #16a34a; }
        .badge-product { background: rgba(37, 99, 235, 0.12); color: #2563eb; }
        .badge-membership { background: rgba(234, 148, 15, 0.15); color: #b45309; }

        @page {
            size: A4 landscape;
            margin: 1.5cm 1.5cm 1.8cm 1.5cm;
        }

        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>
<div class="page-content">

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

    <div class="main-title">Rapport Historique des Ventes POS</div>

    <table class="form-meta">
        <tr>
            <td class="meta-left">Période : <strong>{{ $periodLabel }}</strong></td>
            <td class="meta-period-highlight" rowspan="2">{{ $periodLabel }}</td>
            <td class="meta-right">Date : <strong>{{ $date_generated }}</strong></td>
        </tr>
        <tr>
            <td class="meta-left">Nombre de ventes : <strong>{{ $rows->count() }}</strong></td>
            <td class="meta-right">
                @if(!empty($search))
                    Recherche : <strong>{{ $search }}</strong>
                @else
                    &nbsp;
                @endif
            </td>
        </tr>
    </table>

    <div class="section-header">1. Détail des ventes POS</div>

    <table class="global-table">
        <thead>
            <tr>
                <th style="width:9%;">Date</th>
                <th style="width:10%;">N° commande</th>
                <th style="width:14%;">Client</th>
                <th style="width:18%;">Package / Produit</th>
                <th style="width:10%;">Code parrain</th>
                <th style="width:9%;">Prix</th>
                <th style="width:11%;">Commission</th>
                <th style="width:9%;">Payé</th>
                <th style="width:10%;">PV</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td style="color:#0E2F76; font-weight:bold;">#{{ $row['order_number'] }}</td>
                    <td class="text-left">
                        <strong>{{ $row['client_name'] }}</strong>
                        @if($row['client_phone'])
                            <div class="sub">{{ $row['client_phone'] }}</div>
                        @endif
                    </td>
                    <td class="text-left">
                        @if($row['item_kind'] === 'membership')
                            <span class="badge badge-membership">Adhésion</span>
                        @elseif($row['item_kind'] === 'package')
                            <span class="badge badge-package">Package</span>
                        @elseif($row['item_kind'] === 'product')
                            <span class="badge badge-product">Produit</span>
                        @endif
                        <strong>{{ $row['item_label'] }}</strong>
                        @if(!empty($row['item_extra']))
                            <div class="sub">{{ $row['item_extra'] }}</div>
                        @endif
                    </td>
                    <td>
                        @if($row['sponsor_code'])
                            <strong style="color:#0E2F76;">{{ $row['sponsor_code'] }}</strong>
                            @if($row['sponsor_name'])
                                <div class="sub">{{ $row['sponsor_name'] }}</div>
                            @endif
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
                    <td class="text-right" style="color:#0E2F76; font-weight:bold;">{{ number_format($row['pv'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Aucune vente POS pour cette période</td>
                </tr>
            @endforelse
        </tbody>
        @if($rows->isNotEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">TOTAL GÉNÉRAL :</td>
                    <td class="text-right" style="color:#0E2F76;">${{ number_format($totals['price'], 2) }}</td>
                    <td class="text-right" style="color:#0E2F76;">${{ number_format($totals['commission'], 2) }}</td>
                    <td class="text-right" style="color:#0E2F76;">${{ number_format($totals['paid'], 2) }}</td>
                    <td class="text-right" style="color:#0E2F76;">{{ number_format($totals['pv'], 0, ',', ' ') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

</div>
</body>
</html>
