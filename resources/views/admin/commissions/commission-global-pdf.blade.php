{{-- resources/views/admin/commissions/commission-global-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport Global des Commissions</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            padding: 0;
            margin: 0;
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

        .main-title {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .form-meta {
            width: 100%;
            margin-bottom: 4px;
            font-size: 10.5px;
            font-weight: bold;
            border-collapse: collapse;
        }
        .form-meta td {
            padding: 2px 0;
        }

        .section-header {
            font-size: 11.5px;
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
            font-size: 9px;
        }
        
        .global-table thead {
            display: table-header-group;
        }
        
        .global-table tr {
            page-break-inside: avoid;
        }
        
        .global-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 3px 4px;
            border: 1px solid #000;
            text-align: center;
        }
        .global-table td {
            padding: 2px 4px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 8.5px;
        }
        .global-table td.text-left { text-align: left; }
        .global-table td.text-right { text-align: right; }

        .global-table .total-row td {
            font-weight: bold;
            font-size: 9.5px;
            background-color: #f9f9f9;
        }

        .direct-color { color: #4F46E5; }
        .indirect-color { color: #2563EB; }
        .leadership-color { color: #A65A0E; }
        .cash-color { color: #16a34a; }
        .sponsor-color { color: #B54708; }
        .retail-color { color: #1C7E4A; }

        @page {
            size: A4 portrait;
            margin: 0 0 12mm 0;
        }
        @media print {
            body { padding: 0; margin: 0; }
            @page {
                size: A4 portrait;
                margin: 0 0 12mm 0;
            }
        }
    </style>
</head>
<body>

    @include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])

    <div class="page-content">

        <div class="main-title">Rapport Global des Commissions</div>
        
        <table class="form-meta">
            <tr>
                <td style="text-align: left; width:50%;">Période : <strong>{{ $period }}</strong></td>
                <td style="text-align: right; width:50%;">Date : <strong>{{ $date_generated }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: left;">Bénéficiaires : <strong>{{ $beneficiaires->count() }}</strong></td>
                <td style="text-align: right;">Commissions : <strong>{{ $commissionsCount ?? $beneficiaires->count() }}</strong></td>
            </tr>
            @if(!empty($filters['search']) || !empty($filters['type']) || !empty($filters['status']))
            <tr>
                <td colspan="2" style="text-align: center; font-size: 9px; color: #666;">
                    Filtres : 
                    @if($filters['search']) Recherche: "{{ $filters['search'] }}" @endif
                    @if($filters['type']) | Type: {{ ucfirst($filters['type']) }} @endif
                    @if($filters['status']) | Statut: {{ ucfirst($filters['status']) }} @endif
                </td>
            </tr>
            @endif
        </table>

        <div class="section-header">1. Détail Global par Membre</div>
        
        <table class="global-table">
            <thead>
                <tr>
                    <th style="width:4%;">N°</th>
                    <th style="width:9%;">Code Membre</th>
                    <th style="width:18%;">Membre</th>
                    <th style="width:9%;">Code Parrain</th>
                    <th style="width:6%;">Grade</th>
                    <th style="width:8%;">PV</th>
                    <th style="width:8%;">Direct</th>
                    <th style="width:8%;">Indirect</th>
                    <th style="width:8%;">Leadership</th>
                    <th style="width:8%;">Sponsor</th>
                    <th style="width:7%;">Retail</th>
                    <th style="width:7%;">Cash</th>
                    <th style="width:8%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $numero = 1; @endphp
                @foreach($beneficiaires as $userId => $beneficiaire)
                    <tr>
                        <td>{{ $numero++ }}</td>
                        <td class="text-center" style="color:#0E2F76; font-weight:bold;">{{ $beneficiaire['code'] }}</td>
                        <td class="text-left"><strong>{{ $beneficiaire['name'] }}</strong></td>
                        <td class="text-center" style="color:#000; font-weight:bold;">{{ $beneficiaire['parrain_code'] }}</td>
                        <td>{{ $beneficiaire['rank_level'] }}</td>
                        <td class="text-right" style="color:#0E2F76; font-weight:bold;">{{ number_format($beneficiaire['pv'], 1, ',', ' ') }}</td>
                        <td class="text-right direct-color">${{ number_format($beneficiaire['direct'] ?? 0, 2) }}</td>
                        <td class="text-right indirect-color">${{ number_format($beneficiaire['indirect'] ?? 0, 2) }}</td>
                        <td class="text-right leadership-color">${{ number_format($beneficiaire['leadership'] ?? 0, 2) }}</td>
                        <td class="text-right sponsor-color">${{ number_format($beneficiaire['sponsor'] ?? 0, 2) }}</td>
                        <td class="text-right retail-color">${{ number_format($beneficiaire['retail'] ?? 0, 2) }}</td>
                        <td class="text-right cash-color">${{ number_format($beneficiaire['cash_pos'] ?? 0, 2) }}</td>
                        <td class="text-right" style="color:#0E2F76; font-weight:bold;">${{ number_format($beneficiaire['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" style="text-align:right;">TOTAL GÉNÉRAL :</td>
                    <td class="text-right direct-color">${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                    <td class="text-right indirect-color">${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                    <td class="text-right leadership-color">${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                    <td class="text-right sponsor-color">${{ number_format($totals['sponsor'] ?? 0, 2) }}</td>
                    <td class="text-right retail-color">${{ number_format($totals['retail'] ?? 0, 2) }}</td>
                    <td class="text-right cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
                    <td class="text-right" style="color:#0E2F76;">${{ number_format($totals['total'] ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])

    </div>

</body>
</html>