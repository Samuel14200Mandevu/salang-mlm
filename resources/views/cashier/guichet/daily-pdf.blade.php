{{-- Rapport fin de paiement bonus : Direct (1re date) + I&L (2e date) --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport fin de paiement — Salang Group</title>
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
            width: 42%;
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
            margin: 0 0 4px 0;
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
        .form-meta td.meta-left { text-align: left; width: 55%; }
        .form-meta td.meta-right { text-align: right; width: 45%; }

        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px 0;
            font-size: 11px;
        }
        .summary-box th, .summary-box td {
            border: 1px solid #000;
            padding: 4px 5px;
            text-align: center;
        }
        .summary-box th {
            background: #f3f3f3;
            text-transform: uppercase;
            font-size: 10px;
        }
        .summary-box td.num { font-weight: bold; font-variant-numeric: tabular-nums; }
        .summary-box td.label { text-align: left; font-weight: bold; }

        .section-header {
            font-size: 11.5px;
            font-weight: bold;
            color: #0A254E;
            border-bottom: 1.5px solid #C5A04D;
            padding-bottom: 1px;
            margin: 8px 0 3px 0;
            text-transform: uppercase;
            clear: both;
            line-height: 1.2;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .data-table th {
            background-color: #fff;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 4px 2px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 3px 2px;
            border-bottom: 1.5px dotted #000;
            border-left: 1px solid #ccc;
            border-right: 1px solid #ccc;
            text-align: center;
            vertical-align: middle;
            font-size: 11.5px;
        }
        .data-table td.text-left { text-align: left; padding-left: 4px; }
        .data-table thead tr.page-top-gap td {
            border: none !important;
            padding: 0 !important;
            height: 14px;
            font-size: 0;
            line-height: 0;
        }
        .total-row td {
            font-weight: bold;
            border-top: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
            font-size: 12px;
            background-color: #fff;
        }
        .amount-positive { color: #000; font-weight: bold; }
        .empty-state {
            text-align: center;
            padding: 16px 0;
            color: #666;
            font-size: 12.5px;
        }
        .page-content { background: #fff; }
        .note {
            font-size: 10px;
            color: #444;
            margin: 2px 0 6px 0;
            font-style: italic;
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

@include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 300])

@php
    $totals = $totals ?? [];
    $directDateLabel = !empty($directDate)
        ? \Carbon\Carbon::createFromFormat('Y-m-d', $directDate)->format('d/m/Y')
        : '—';
    $ilDateLabel = !empty($ilDate)
        ? \Carbon\Carbon::createFromFormat('Y-m-d', $ilDate)->format('d/m/Y')
        : '—';
@endphp

<div class="page-content page-content-padded">

    <div class="main-title">Rapport de fin de paiement des bonus</div>
    <div class="main-title-rule"></div>
    <div class="main-subtitle">Période {{ $period }} — Direct (1ʳᵉ date) + Indirect &amp; Leadership (2ᵉ date)</div>

    <table class="form-meta">
        <tr>
            <td class="meta-left">Période : <strong>{{ $period }}</strong> · Membres : <strong>{{ $totals['members'] ?? 0 }}</strong></td>
            <td class="meta-right">Généré le : <strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong></td>
        </tr>
        <tr>
            <td class="meta-left">1ʳᵉ date (Direct) : <strong>{{ $directDateLabel }}</strong></td>
            <td class="meta-right">2ᵉ date (I&amp;L) : <strong>{{ $ilDateLabel }}</strong></td>
        </tr>
    </table>

    <table class="summary-box">
        <thead>
            <tr>
                <th style="width:22%;">Phase</th>
                <th style="width:26%;">Total bonus</th>
                <th style="width:26%;">Retiré (remis)</th>
                <th style="width:26%;">Reste</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label">1. Direct</td>
                <td class="num">${{ number_format($totals['direct_total'] ?? 0, 2) }}</td>
                <td class="num">${{ number_format($totals['direct_retire'] ?? 0, 2) }}</td>
                <td class="num">${{ number_format($totals['direct_reste'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="label">2. Indirect + Leadership</td>
                <td class="num">${{ number_format($totals['il_total'] ?? 0, 2) }}</td>
                <td class="num">${{ number_format($totals['il_retire'] ?? 0, 2) }}</td>
                <td class="num">${{ number_format($totals['il_reste'] ?? 0, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">TOTAL GÉNÉRAL</td>
                <td class="num">${{ number_format($totals['grand_total'] ?? 0, 2) }}</td>
                <td class="num">${{ number_format($totals['grand_retire'] ?? 0, 2) }}</td>
                <td class="num">${{ number_format($totals['grand_reste'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-header">1. Première date — Bonus Direct (détail remis)</div>
    <p class="note">Imputation : les retraits couvrent d’abord le Direct, puis l’Indirect &amp; Leadership.</p>
    @if(($directLines ?? collect())->count() > 0)
    <table class="data-table">
        <thead>
            <tr class="page-top-gap"><td colspan="7">&nbsp;</td></tr>
            <tr>
                <th style="width:6%;">N°</th>
                <th style="width:12%;">Date</th>
                <th style="width:8%;">Heure</th>
                <th style="width:14%;">Code</th>
                <th style="width:28%;">Membre</th>
                <th style="width:14%;">Montant</th>
                <th style="width:18%;">N° reçu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($directLines as $line)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $line->date_label }}</td>
                <td>{{ $line->time }}</td>
                <td><strong>{{ $line->user?->sponsor_id ?? '—' }}</strong></td>
                <td class="text-left">{{ $line->user?->name ?? 'N/A' }}</td>
                <td><span class="amount-positive">${{ number_format((float) $line->amount, 2) }}</span></td>
                <td>{{ $line->receipt }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align:right;">TOTAL DIRECT REMIS</td>
                <td colspan="2" style="text-align:left;color:#0A254E;">
                    ${{ number_format($totals['direct_retire'] ?? 0, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">Aucun Direct remis pour cette période.</div>
    @endif

    <div class="section-header">2. Deuxième date — Indirect &amp; Leadership (détail remis)</div>
    @if(($ilLines ?? collect())->count() > 0)
    <table class="data-table">
        <thead>
            <tr class="page-top-gap"><td colspan="7">&nbsp;</td></tr>
            <tr>
                <th style="width:6%;">N°</th>
                <th style="width:12%;">Date</th>
                <th style="width:8%;">Heure</th>
                <th style="width:14%;">Code</th>
                <th style="width:28%;">Membre</th>
                <th style="width:14%;">Montant</th>
                <th style="width:18%;">N° reçu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ilLines as $line)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $line->date_label }}</td>
                <td>{{ $line->time }}</td>
                <td><strong>{{ $line->user?->sponsor_id ?? '—' }}</strong></td>
                <td class="text-left">{{ $line->user?->name ?? 'N/A' }}</td>
                <td><span class="amount-positive">${{ number_format((float) $line->amount, 2) }}</span></td>
                <td>{{ $line->receipt }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align:right;">TOTAL I&amp;L REMIS</td>
                <td colspan="2" style="text-align:left;color:#0A254E;">
                    ${{ number_format($totals['il_retire'] ?? 0, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">Aucun Indirect / Leadership remis pour cette période.</div>
    @endif

    <div class="section-header">3. Synthèse par membre — Total / Retiré / Reste</div>
    @if(($memberRows ?? collect())->count() > 0)
    <table class="data-table">
        <thead>
            <tr class="page-top-gap"><td colspan="9">&nbsp;</td></tr>
            <tr>
                <th style="width:5%;">N°</th>
                <th style="width:22%;">Membre</th>
                <th style="width:9%;">Dir. tot.</th>
                <th style="width:9%;">Dir. ret.</th>
                <th style="width:9%;">Dir. reste</th>
                <th style="width:9%;">I&amp;L tot.</th>
                <th style="width:9%;">I&amp;L ret.</th>
                <th style="width:9%;">I&amp;L reste</th>
                <th style="width:9%;">Reste</th>
            </tr>
        </thead>
        <tbody>
            @foreach($memberRows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="text-left">
                    <strong>{{ $row->user?->sponsor_id ?? '—' }}</strong>
                    {{ $row->user?->name ?? '' }}
                </td>
                <td>${{ number_format((float) $row->direct_total, 2) }}</td>
                <td>${{ number_format((float) $row->direct_retire, 2) }}</td>
                <td>${{ number_format((float) $row->direct_reste, 2) }}</td>
                <td>${{ number_format((float) $row->il_total, 2) }}</td>
                <td>${{ number_format((float) $row->il_retire, 2) }}</td>
                <td>${{ number_format((float) $row->il_reste, 2) }}</td>
                <td><strong>${{ number_format((float) $row->reste, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align:right;">TOTAUX</td>
                <td>${{ number_format($totals['direct_total'] ?? 0, 2) }}</td>
                <td>${{ number_format($totals['direct_retire'] ?? 0, 2) }}</td>
                <td>${{ number_format($totals['direct_reste'] ?? 0, 2) }}</td>
                <td>${{ number_format($totals['il_total'] ?? 0, 2) }}</td>
                <td>${{ number_format($totals['il_retire'] ?? 0, 2) }}</td>
                <td>${{ number_format($totals['il_reste'] ?? 0, 2) }}</td>
                <td style="color:#0A254E;">${{ number_format($totals['grand_reste'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">Aucun bonus payé au portefeuille pour la période {{ $period }}.</div>
    @endif

    @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT FIN DE PAIEMENT – SALANG GROUP SARL'])

</div>
</body>
</html>
