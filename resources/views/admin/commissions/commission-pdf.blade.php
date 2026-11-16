{{-- resources/views/admin/commissions/commission-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Commissions</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            padding: 0;
            margin: 0;
            background: #fff;
        }
        @include('partials.pdf._styles')
        @include('partials.pdf._letterhead_full_bleed')

        /* TITRE ET META */
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

        /* SECTIONS NUMÉROTÉES */
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

        /* SECTION MEMBRE / FILTRES */
        .member-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            border: 1px solid #000;
        }
        .member-info td {
            padding: 3px 6px;
            font-size: 10px;
            border: 1px solid #000;
        }
        .member-info .label {
            font-weight: bold;
            background-color: #f2f2f2;
            width: 15%;
        }
        .member-info .value {
            width: 35%;
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
            font-size: 10.5px;
            background-color: #f9f9f9;
        }
        .totals-grid .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            background-color: #f2f2f2;
        }
        .totals-grid .amount {
            font-weight: bold;
            font-size: 12px;
        }
        .direct-color { color: #4F46E5; }
        .indirect-color { color: #2563EB; }
        .leadership-color { color: #A65A0E; }
        .retail-color { color: #1C7E4A; }
        .sponsor-color { color: #B54708; }
        .cash-color { color: #065F9C; }

        /* TABLEAU DES COMMISSIONS */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 4px 0;
            font-size: 9px;
        }
        
        .data-table thead {
            display: table-header-group;
        }
        
        .data-table tr {
            page-break-inside: avoid;
        }
        
        .data-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 3px 4px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 2px 4px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 8.5px;
        }
        .data-table td.text-left { text-align: left; }
        .data-table td.text-right { text-align: right; }

        .data-table .total-row td {
            font-weight: bold;
            font-size: 9.5px;
            background-color: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-direct { background: #EEF2FF; color: #4F46E5; }
        .badge-indirect { background: #EFF6FF; color: #2563EB; }
        .badge-leadership { background: #FEF3E8; color: #A65A0E; }
        .badge-retail { background: #ECFDF5; color: #1C7E4A; }
        .badge-cash_pos { background: #E8F0F8; color: #065F9C; }
        .badge-sponsor { background: #FEF3E8; color: #B54708; }

        .badge-paid { background: #ECFDF3; color: #1C7E4A; }
        .badge-pending { background: #FFFAEB; color: #B54708; }
        .badge-cancelled { background: #FEF3F2; color: #B91C1C; }

        .beneficiaire-title {
            font-size: 10.5px;
            font-weight: bold;
            padding: 3px 8px;
            background: #f2f2f2;
            border-left: 3px solid #8b0000;
            margin: 6px 0 3px 0;
            page-break-after: avoid;
        }
        .beneficiaire-title .total-badge {
            float: right;
            font-weight: bold;
            color: #0E2F76;
        }

        .beneficiaire-section {
            margin-bottom: 6px;
        }

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

    <!-- CONTENU PRINCIPAL -->
    <div class="page-content page-content-padded">

        <!-- TITRE -->
        <div class="main-title">Rapport des Commissions</div>
        
        <table class="form-meta">
            <tr>
                <td style="text-align: left; width:50%;">Période : <strong>{{ $filters['period'] ?? 'Toutes' }}</strong></td>
                <td style="text-align: right; width:50%;">Date : <strong>{{ $date_generated }}</strong></td>
            </tr>
        </table>

        <!-- 1. INFORMATIONS GENERALES & FILTRES -->
        <div class="section-header">1. Informations Générales</div>
        
        <table class="member-info">
            <tr>
                <td class="label">Commissions</td>
                <td class="value"><strong>{{ $commissions->count() }} enregistrement(s)</strong></td>
                <td class="label">Statut Payé</td>
                <td class="value"><strong>${{ number_format($stats['total_paid'] ?? 0, 2) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Recherche</td>
                <td class="value"><strong>{{ $filters['search'] ?? 'Aucune' }}</strong></td>
                <td class="label">Statut En Attente</td>
                <td class="value"><strong>${{ number_format($stats['total_pending'] ?? 0, 2) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Type Filtré</td>
                <td class="value"><strong>{{ !empty($filters['type']) ? ucfirst($filters['type']) : 'Tous' }}</strong></td>
                <td class="label">Statut Annulé</td>
                <td class="value"><strong>${{ number_format($stats['total_cancelled'] ?? 0, 2) }}</strong></td>
            </tr>
        </table>

        <!-- 2. RÉSUMÉ DES GAINS -->
        <div class="section-header">2. Résumé des Gains</div>
        <table class="totals-grid">
            <tr>
                <td class="label" style="width:16%;">Direct</td>
                <td class="label" style="width:16%;">Indirect</td>
                <td class="label" style="width:16%;">Leadership</td>
                <td class="label" style="width:16%;">Retail</td>
                <td class="label" style="width:16%;">Sponsor</td>
                <td class="label" style="width:16%;">Cash POS</td>
            </tr>
            <tr>
                <td class="amount direct-color">${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                <td class="amount indirect-color">${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                <td class="amount leadership-color">${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                <td class="amount retail-color">${{ number_format($totals['retail'] ?? 0, 2) }}</td>
                <td class="amount sponsor-color">${{ number_format($totals['sponsor'] ?? 0, 2) }}</td>
                <td class="amount cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" style="background:#e8e8e8; font-weight:bold; font-size:11px; text-align:center;">
                    TOTAL GÉNÉRAL : ${{ number_format($totals['total'] ?? 0, 2) }}
                </td>
            </tr>
        </table>

        <!-- 3. DÉTAIL DES COMMISSIONS -->
        <div class="section-header">3. Détail des Commissions</div>
        
        @if($commissions->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:10%;">Période</th>
                    <th style="width:15%;">Utilisateur</th>
                    <th style="width:12%;">De</th>
                    <th style="width:10%;">Type</th>
                    <th style="width:10%;" class="text-right">Montant</th>
                    <th style="width:8%;">Taux</th>
                    <th style="width:10%;">Statut</th>
                    <th style="width:25%;">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commissions as $commission)
                <tr>
                    <td>{{ $commission->period ?? '-' }}</td>
                    <td class="text-left">{{ $commission->user?->name ?? 'N/A' }}</td>
                    <td class="text-left">{{ $commission->fromUser?->name ?? 'Système' }}</td>
                    <td>
                        <span class="badge badge-{{ $commission->type }}">
                            {{ ucfirst(str_replace('_', ' ', $commission->type)) }}
                        </span>
                    </td>
                    <td class="text-right">${{ number_format($commission->amount, 2) }}</td>
                    <td>{{ $commission->percentage ?? '-' }}%</td>
                    <td>
                        <span class="badge badge-{{ $commission->status }}">
                            {{ ucfirst($commission->status) }}
                        </span>
                    </td>
                    <td class="text-left" style="font-size:8px;">{{ Str::limit($commission->description ?? '', 40) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" style="text-align:right;">TOTAL :</td>
                    <td class="text-right" style="color:#0E2F76;">${{ number_format($totals['total'] ?? 0, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        @else
        <p style="text-align:center; color:#999; padding:15px 0;">
            Aucune commission trouvée
        </p>
        @endif

        @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])

    </div>

</body>
</html>