{{-- resources/views/cashier/guichet/daily-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Retraits Guichet - Salang Group</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif, Arial;
            font-size: 14px;
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

        /* Marge haut pages 2+ (DomPDF) */
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

        .page-content {
            background: #fff;
        }

        @page {
            size: A4 portrait;
            margin: 0 0 12mm 0;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            @page {
                size: A4 portrait;
                margin: 0 0 12mm 0;
            }
        }
    </style>
</head>
<body>

@include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])

<div class="page-content page-content-padded">

    <div class="main-title">Rapport des Retraits Guichet</div>
    <div class="main-title-rule"></div>
    <div class="main-subtitle">Journée du {{ $reportDate->format('d/m/Y') }}</div>

    <table class="form-meta">
        <tr>
            <td class="meta-left">Journée : <strong>{{ $reportDate->format('d/m/Y') }}</strong></td>
            <td class="meta-right">Généré le : <strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong></td>
        </tr>
        <tr>
            <td class="meta-left">Nombre de retraits : <strong>{{ count($withdrawals) }}</strong></td>
            <td class="meta-right">Total remis : <strong>${{ number_format($totalAmount, 2) }}</strong></td>
        </tr>
    </table>

    <div class="section-header">1. Détail des retraits remis en espèces</div>

    @if(count($withdrawals) > 0)
    <table class="data-table">
        <thead>
            <tr class="page-top-gap"><td colspan="7">&nbsp;</td></tr>
            <tr>
                <th style="width:7%;">#</th>
                <th style="width:10%;">Heure</th>
                <th style="width:13%;">Code ID</th>
                <th style="width:28%;">Nom Complet</th>
                <th style="width:14%;">Montant</th>
                <th style="width:16%;">N° Reçu</th>
                <th style="width:12%;">Caissier</th>
            </tr>
        </thead>
        <tbody>
            @foreach($withdrawals as $w)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ ($w->completed_at ?? $w->created_at)?->format('H:i') ?? '—' }}</td>
                <td><strong>{{ $w->user?->sponsor_id ?? 'N/A' }}</strong></td>
                <td class="text-left"><strong>{{ $w->user?->name ?? 'N/A' }}</strong></td>
                <td>
                    @if((float) $w->amount > 0)
                        <span class="amount-positive">${{ number_format((float) $w->amount, 2) }}</span>
                    @else
                        <span class="amount-zero">-</span>
                    @endif
                </td>
                <td>{{ $w->receipt_number ?: '—' }}</td>
                <td>{{ $w->cashier?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align:right; font-weight:bold;">NOMBRE : {{ count($withdrawals) }}</td>
                <td colspan="3" style="text-align:left;">&nbsp;</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="text-align:right; font-weight:bold; color:#0A254E;">TOTAL GÉNÉRAL :</td>
                <td style="color:#0A254E; font-size:14px;"><strong>${{ number_format($totalAmount, 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">
        <p>Aucun retrait guichet remis pour le {{ $reportDate->format('d/m/Y') }}</p>
    </div>
    @endif

    @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])

</div>
</body>
</html>
