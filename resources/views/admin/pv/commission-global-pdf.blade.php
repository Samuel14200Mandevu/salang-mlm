{{-- resources/views/admin/pv/commission-global-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Global des Commissions - {{ $period }}</title>
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
            margin: 22px 56px 12px 56px;
            padding: 0;
        }

        @include('partials.pdf._styles')

        /* En-tête = même structure que fiche de consultation */
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

        /* TITRE ET META */
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

        /* SECTIONS NUMÉROTÉES */
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

        /* TABLEAU DES TOTAUX */
        .totals-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 6px 0;
        }
        .totals-grid td {
            padding: 4px 8px;
            border: 1px solid #000;
            text-align: center;
            font-size: 13.5px;
            background-color: #f9f9f9;
        }
        .totals-grid .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            background-color: #f2f2f2;
        }
        .totals-grid .amount {
            font-weight: bold;
            font-size: 15px;
        }
        .direct-color, .td-direct, .th-direct { color: #312E81; font-weight: bold; }
        .indirect-color, .td-indirect, .th-indirect { color: #1E3A8A; font-weight: bold; }
        .leadership-color, .td-leadership, .th-leadership { color: #9A3412; font-weight: bold; }
        .cash-color, .td-cash, .th-cash { color: #14532D; font-weight: bold; }
        .th-direct, .th-indirect, .th-leadership, .th-cash {
            background-color: #f2f2f2 !important;
        }

        /* TABLEAU GLOBAL */
        .global-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1px 0 3px 0;
            font-size: 10.5px;
        }

        .global-table thead {
            display: table-header-group;
        }

        /* Marge haut pages 2+ : DomPDF ignore @page top sur tableaux continués */
        .global-table thead tr.page-top-gap td {
            height: 12mm;
            border: none !important;
            background: #fff !important;
            padding: 0 !important;
            font-size: 1px;
            line-height: 0;
            color: transparent;
        }

        /* Empêche la répétition du total sur chaque page imprimée */
        .global-table tfoot {
            display: table-row-group;
        }

        .global-table tr {
            page-break-inside: avoid;
        }

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

        /* LIGNES DE SÉPARATION PAR UTILISATEUR */
        .user-header-row td {
            background-color: #0A254E;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
            padding: 4px 8px;
            text-align: left;
        }
        .user-total-row td {
            background-color: #f9f9f9;
            font-weight: bold;
            font-size: 12px;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-direct { background: rgba(79, 70, 229, 0.15); color: #4F46E5; }
        .badge-indirect { background: rgba(37, 99, 235, 0.15); color: #2563EB; }
        .badge-leadership { background: rgba(166, 90, 14, 0.15); color: #A65A0E; }
        .badge-cash { background: rgba(22, 163, 74, 0.15); color: #16a34a; }

        @page {
            size: A4 landscape;
            /* haut 0 : en-tête page 1 comme fiche ; marge haut pages 2+ via thead.page-top-gap */
            margin: 0 0 12mm 0;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 0 0 12mm 0;
            }
        }

        @if(!empty($autoPrint))
        .no-print {
            position: fixed;
            top: 12px;
            right: 12px;
            z-index: 50;
            display: flex;
            gap: 8px;
        }
        .no-print .btn-print-action {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            background: #0A254E;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .no-print .btn-print-back {
            padding: 10px 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #fff;
            color: #333;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        @endif
    </style>
</head>
<body>
    @if(!empty($autoPrint))
        <div class="no-print">
            <button type="button" class="btn-print-action" onclick="window.print()">Imprimer</button>
            @if(!empty($printBackUrl))
                <a href="{{ $printBackUrl }}" class="btn-print-back">Retour</a>
            @endif
        </div>
    @endif

    @include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])

    <!-- CONTENU PRINCIPAL -->
    <div class="page-content">

        <!-- TITRE -->
        @php
            $periodLabel = $period;
            if ($period === 'all') {
                $periodLabel = 'Toutes les périodes';
            } elseif (preg_match('/^(\d{4})-(\d{2})$/', (string) $period, $m)) {
                $moisFr = [
                    1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
                    5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
                    9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
                ];
                $moisNum = (int) $m[2];
                $periodLabel = ($moisFr[$moisNum] ?? $m[2]) . ' ' . $m[1];
            }
        @endphp
        <div class="main-title">Rapport Global des Commissions</div>
        <div class="main-title-rule"></div>
        <div class="main-subtitle">{{ $periodLabel }}</div>

        <table class="form-meta">
            <tr>
                <td class="meta-left">Période : <strong>{{ $periodLabel }}</strong></td>
                <td class="meta-right">Date : <strong>{{ $date_generated }}</strong></td>
            </tr>
            <tr>
                <td class="meta-left">Nombre de bénéficiaires : <strong>{{ $beneficiaires->count() }}</strong></td>
                <td class="meta-right">Nombre de commissions : <strong>{{ $commissionsCount }}</strong></td>
            </tr>
        </table>

        <!-- 1. TABLEAU GLOBAL DES COMMISSIONS -->
        <div class="section-header">1. Détail Global par Membre</div>

        <table class="global-table">
            <thead>
                <tr class="page-top-gap">
                    <td colspan="11">&nbsp;</td>
                </tr>
                <tr>
                    <th style="width:4%;">N°</th>
                    <th style="width:8%;">Code Membre</th>
                    <th style="width:24%;">Membre</th>
                    <th style="width:8%;">Code Parrain</th>
                    <th style="width:5%;">Grade</th>
                    <th style="width:7%;">PV</th>
                    <th class="th-direct" style="width:8%;">Direct</th>
                    <th class="th-indirect" style="width:8%;">Indirect</th>
                    <th class="th-leadership" style="width:9%;">Leadership</th>
                    <th class="th-cash" style="width:8%;">Cash POS</th>
                    <th style="width:11%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $numero = 1;
                    $totalPV = 0;
                @endphp
                @foreach($beneficiaires as $userId => $beneficiaire)
                    @php
                        $userPV = (float) ($beneficiaire['pv'] ?? 0);
                        $totalPV += $userPV;
                    @endphp
                    <tr>
                        <td>{{ $numero++ }}</td>
                        <td class="text-center" style="color:#0E2F76; font-weight:bold;">{{ $beneficiaire['code'] ?? 'N/A' }}</td>
                        <td class="text-left"><strong>{{ $beneficiaire['name'] }}</strong></td>
                        <td class="text-center" style="color:#000; font-weight:bold;">{{ $beneficiaire['parrain_code'] ?? 'N/A' }}</td>
                        <td>{{ $beneficiaire['rank_level'] ?? 'N/A' }}</td>
                        <td class="text-right" style="color:#0E2F76; font-weight:bold;">{{ number_format($userPV, 1, ',', ' ') }}</td>
                        <td class="text-right td-direct">${{ number_format($beneficiaire['direct'] ?? 0, 2) }}</td>
                        <td class="text-right td-indirect">${{ number_format($beneficiaire['indirect'] ?? 0, 2) }}</td>
                        <td class="text-right td-leadership">${{ number_format($beneficiaire['leadership'] ?? 0, 2) }}</td>
                        <td class="text-right td-cash">${{ number_format($beneficiaire['cash_pos'] ?? 0, 2) }}</td>
                        <td class="text-right" style="color:#0E2F76; font-weight:bold;">${{ number_format($beneficiaire['total'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">TOTAL GÉNÉRAL :</td>
                    <td class="text-right" style="color:#0E2F76;">{{ number_format($totalPV, 1, ',', ' ') }}</td>
                    <td class="text-right td-direct">${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                    <td class="text-right td-indirect">${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                    <td class="text-right td-leadership">${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                    <td class="text-right td-cash">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
                    <td class="text-right" style="color:#0E2F76;">${{ number_format($totals['total'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- 2. RÉCAPITULATIF PAR TYPE DE BONUS -->
        <div class="section-header">2. Récapitulatif par Type de Bonus</div>

        <table class="global-table">
            <thead>
                <tr>
                    <th style="width:25%;">Type de Bonus</th>
                    <th style="width:15%;">Nombre</th>
                    <th style="width:20%;">Montant Total</th>
                    <th style="width:20%;">Pourcentage</th>
                    <th style="width:20%;">Moyenne</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['direct', 'indirect', 'leadership', 'cash_pos'] as $type)
                    @php
                        $stat = $typeStats[$type] ?? ['count' => 0, 'total' => 0, 'percentage' => 0, 'average' => 0];
                    @endphp
                    <tr>
                        <td class="text-left">
                            <span class="badge badge-{{ $type === 'cash_pos' ? 'cash' : $type }}">
                                {{ $type === 'cash_pos' ? 'Cash POS' : ucfirst($type) }}
                            </span>
                        </td>
                        <td>{{ $stat['count'] }}</td>
                        <td class="text-right" style="font-weight:bold;">${{ number_format($stat['total'], 2) }}</td>
                        <td>{{ number_format($stat['percentage'], 1) }}%</td>
                        <td class="text-right">${{ number_format($stat['average'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="text-left"><strong>TOTAL GÉNÉRAL</strong></td>
                    <td><strong>{{ $commissionsCount }}</strong></td>
                    <td class="text-right" style="color:#0E2F76;"><strong>${{ number_format($totals['total'] ?? 0, 2) }}</strong></td>
                    <td><strong>100%</strong></td>
                    <td class="text-right"><strong>${{ number_format($commissionsCount > 0 ? $totals['total'] / $commissionsCount : 0, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])

    </div>

    @if(!empty($autoPrint))
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 400);
            });
        </script>
    @endif

</body>
</html>
