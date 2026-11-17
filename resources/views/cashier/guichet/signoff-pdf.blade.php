{{-- resources/views/cashier/guichet/signoff-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement de bonus - Salang Group</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif, Arial;
            font-size: 13px;
            color: #000;
            padding: 0;
            margin: 0;
            background: #fff;
        }

        @include('partials.pdf._styles')
        @include('partials.pdf._letterhead_full_bleed')

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
        .form-meta td.meta-left { text-align: left; width: 50%; }
        .form-meta td.meta-right { text-align: right; width: 50%; }

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
            font-size: 10.5px;
            padding: 5px 3px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 7px 3px;
            border-bottom: 1.5px dotted #000;
            border-left: 1px solid #ccc;
            border-right: 1px solid #ccc;
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
            height: 28px;
        }
        .data-table td.text-left { text-align: left; padding-left: 6px; }
        .data-table td.sig-cell {
            min-height: 28px;
            border-bottom: 1px solid #000;
        }

        .data-table thead tr.page-top-gap td {
            border: none !important;
            padding: 0 !important;
            height: 18px;
            font-size: 0;
            line-height: 0;
        }

        .total-row td {
            font-weight: bold;
            border-top: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
            font-size: 12.5px;
            background-color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 30px 0;
            color: #666;
            font-size: 13.5px;
        }

        .page-content { background: #fff; }

        @page {
            size: A4 landscape;
            margin: 0 0 12mm 0;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            @page {
                size: A4 landscape;
                margin: 0 0 12mm 0;
            }
        }
    </style>
</head>
<body>

@include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 280])

<div class="page-content page-content-padded">

    @php
        $docTitle = (($phase ?? 'direct') === 'il')
            ? 'Paiement de bonus Indirect & Leadership'
            : 'Paiement de bonus Direct';
    @endphp
    <div class="main-title">{{ $docTitle }}</div>
    <div class="main-title-rule"></div>
    <div class="main-subtitle">
        Journée du {{ $reportDate->format('d/m/Y') }}@if(!empty($period)) · Période {{ $period }}@endif
    </div>

    <table class="form-meta">
        <tr>
            <td class="meta-left">Journée : <strong>{{ $reportDate->format('d/m/Y') }}</strong></td>
            <td class="meta-right">Généré le : <strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong></td>
        </tr>
        <tr>
            <td class="meta-left">Membres : <strong>{{ count($rows) }}</strong></td>
            <td class="meta-right">Total bonus : <strong>${{ number_format($totalAll, 2) }}</strong></td>
        </tr>
    </table>

    <div class="section-header">1. Total / Retiré / Reste / Signature</div>

    @if(count($rows) > 0)
    <table class="data-table">
        <thead>
            <tr class="page-top-gap"><td colspan="7">&nbsp;</td></tr>
            <tr>
                <th style="width:5%;">N°</th>
                <th style="width:28%;">Membre</th>
                <th style="width:12%;">Total</th>
                <th style="width:12%;">Retiré</th>
                <th style="width:12%;">Reste</th>
                <th style="width:12%;">Date</th>
                <th style="width:19%;">Signature</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="text-left">
                    <strong>{{ $row->user?->sponsor_id ?? '—' }}</strong>
                    — {{ $row->user?->name ?? 'N/A' }}
                </td>
                <td>${{ number_format((float) $row->total, 2) }}</td>
                <td class="sig-cell">&nbsp;</td>
                <td class="sig-cell">&nbsp;</td>
                <td>{{ $reportDate->format('d/m/Y') }}</td>
                <td class="sig-cell">&nbsp;</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align:right;">TOTAUX</td>
                <td>${{ number_format($totalAll, 2) }}</td>
                <td class="sig-cell">&nbsp;</td>
                <td class="sig-cell">&nbsp;</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">
        <p>Aucun montant à remettre pour le {{ $reportDate->format('d/m/Y') }}@if(!empty($period)) · période {{ $period }}@endif.</p>
    </div>
    @endif

    @include('partials.pdf.footer', ['pdfFooterLabel' => 'PAIEMENT DE BONUS – SALANG GROUP SARL'])

</div>
</body>
</html>
