{{-- resources/views/admin/pv/commission-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Commissions - {{ $period }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            padding: 0;
            margin: 0;
            background: #fff;
        }

        @include('partials.pdf._styles')
        @include('partials.pdf._letterhead_full_bleed')

        .page-content {
            /* pas de width:100% : sinon les marges latérales débordent sous DomPDF */
            width: auto;
        }

        /* TITRE ET META — même structure que rapport global */
        .main-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #0A254E;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0;
            line-height: 1.2;
            text-decoration: none;
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
            margin: 0 0 6px 0;
            line-height: 1.2;
        }
        .form-meta {
            width: 100%;
            margin: 0 0 6px 0;
            font-size: 11px;
            font-weight: bold;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .form-meta td {
            padding: 1px 0;
            white-space: normal;
            vertical-align: middle;
            overflow: hidden;
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
            margin: 8px 0 4px 0;
            text-transform: uppercase;
            clear: both;
            line-height: 1.2;
        }

        /* SECTION MEMBRE */
        .member-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            border: 1px solid #000;
        }
        .member-info td {
            padding: 3px 6px;
            font-size: 11px;
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
        .direct-color { color: #312E81; font-weight: bold; }
        .indirect-color { color: #1E3A8A; font-weight: bold; }
        .leadership-color { color: #9A3412; font-weight: bold; }
        .cash-color { color: #14532D; font-weight: bold; }

        /* TABLEAU DES COMMISSIONS */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 4px 0;
            font-size: 10px;
        }
        .data-table thead {
            display: table-header-group;
        }
        /* Marge haut pages 2+ (DomPDF ignore @page top sur tableaux) */
        .data-table thead tr.page-top-gap td {
            height: 12mm;
            border: none !important;
            background: #fff !important;
            padding: 0 !important;
            font-size: 1px;
            line-height: 0;
            color: transparent;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 3px 4px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 2px 4px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 9.5px;
        }
        .data-table td.text-left { text-align: left; }
        .data-table td.text-right { text-align: right; }
        .data-table .total-row td {
            font-weight: bold;
            font-size: 10.5px;
            background-color: #f9f9f9;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-direct { background: #f2f2f2; color: #312E81; }
        .badge-indirect { background: #f2f2f2; color: #1E3A8A; }
        .badge-leadership { background: #f2f2f2; color: #9A3412; }
        .badge-cash { background: #f2f2f2; color: #14532D; }

        /* EN-TÊTE / SOUS-TOTAL DE GROUPE */
        .bonus-group-header {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            background-color: #f2f2f2;
        }
        .bonus-group-direct .bonus-group-header,
        .bonus-group-direct .bonus-group-total { color: #312E81; }
        .bonus-group-indirect .bonus-group-header,
        .bonus-group-indirect .bonus-group-total { color: #1E3A8A; }
        .bonus-group-leadership .bonus-group-header,
        .bonus-group-leadership .bonus-group-total { color: #9A3412; }
        .bonus-group-cash_pos .bonus-group-header,
        .bonus-group-cash_pos .bonus-group-total,
        .bonus-group-cash .bonus-group-header,
        .bonus-group-cash .bonus-group-total { color: #14532D; }

        .bonus-group-total {
            font-weight: bold;
            font-size: 9.5px;
            background-color: #f9f9f9;
            border-top: 2px solid #000;
        }

        .beneficiaire-title {
            font-size: 12px;
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
            @page { margin: 0 0 12mm 0; }
        }
    </style>
</head>
<body>

    @include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])

    <div class="page-content page-content-padded">

        <!-- TITRE + META (aligné rapport global) -->
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

        <div class="main-title">Rapport des Commissions</div>
        <div class="main-title-rule"></div>
        <div class="main-subtitle">{{ $periodLabel }}</div>

        <table class="form-meta">
            <tr>
                <td class="meta-left">Période : <strong>{{ $periodLabel }}</strong></td>
                <td class="meta-right">Date : <strong>{{ \Illuminate\Support\Str::of($date_generated ?? now()->format('d/m/Y'))->before(' ') }}</strong></td>
            </tr>
            <tr>
                <td class="meta-left">Nombre de bénéficiaires : <strong>{{ is_countable($beneficiaires) ? count($beneficiaires) : 0 }}</strong></td>
                <td class="meta-right">Nombre de commissions : <strong>{{ $commissionsCount ?? 0 }}</strong></td>
            </tr>
        </table>

        <!-- 1. INFORMATIONS DU MEMBRE -->
        @if(isset($user) && $user)
        <div class="section-header">1. Informations du Membre</div>
        <table class="member-info">
            <tr>
                <td class="label">Nom complet</td>
                <td class="value"><strong>{{ $user->name }}</strong></td>
                <td class="label">Code Membre</td>
                <td class="value"><strong>{{ $user->sponsor_id ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Grade</td>
                <td class="value"><strong>{{ $user->rank ?? 'Distributeur' }}</strong></td>
                <td class="label">Niveau</td>
                <td class="value"><strong>{{ $user->rank_level ?? 1 }}</strong></td>
            </tr>
            <tr>
                <td class="label">Package</td>
                <td class="value"><strong>{{ $user->package?->name ?? 'Aucun' }}</strong></td>
                <td class="label">Téléphone</td>
                <td class="value"><strong>{{ $user->phone ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="value"><strong>{{ $user->email ?? 'N/A' }}</strong></td>
                <td class="label">Statut</td>
                <td class="value"><strong>{{ $user->is_active ? 'Actif' : 'Inactif' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Parrain</td>
                <td class="value"><strong>{{ $user->parrain?->name ?? 'Aucun' }}</strong></td>
                <td class="label">Date d'inscription</td>
                <td class="value"><strong>{{ $user->created_at?->format('d/m/Y') ?? 'N/A' }}</strong></td>
            </tr>
        </table>
        @endif

        @if(isset($periodPV) && $periodPV > 0)
        <div style="margin: 4px 0; padding: 4px 8px; background: #f9f9f9; border: 1px solid #ddd; text-align: center; font-size: 12px;">
            <strong>PV pour la période {{ $periodLabel }} :</strong> {{ number_format($periodPV, 1) }} PV
        </div>
        @endif

        <!-- 2. RÉSUMÉ DES GAINS -->
        <div class="section-header">{{ isset($user) && $user ? '2' : '1' }}. Résumé des Gains</div>
        <table class="totals-grid">
            <tr>
                <td class="label" style="width:16%;">Type</td>
                <td class="label" style="width:17%;">Direct</td>
                <td class="label" style="width:17%;">Indirect</td>
                <td class="label" style="width:17%;">Leadership</td>
                <td class="label" style="width:17%;">Cash POS</td>
                <td class="label" style="width:16%;">Total</td>
            </tr>
            <tr>
                <td class="label">Montant</td>
                <td class="amount direct-color">${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                <td class="amount indirect-color">${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                <td class="amount leadership-color">${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                <td class="amount cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
                <td class="amount" style="color:#0E2F76;">${{ number_format($totals['total'] ?? 0, 2) }}</td>
            </tr>
        </table>

        <!-- 3. DÉTAIL PAR BÉNÉFICIAIRE -->
        <div class="section-header">{{ isset($user) && $user ? '3' : '2' }}. Détail des Commissions</div>

        @foreach($beneficiaires as $beneficiaire)
            <div class="beneficiaire-section">
                <div class="beneficiaire-title">
                    {{ $beneficiaire['name'] }}
                    <span class="total-badge">${{ number_format($beneficiaire['total'], 2) }}</span>
                </div>

                <table class="data-table">
                    <thead>
                        <tr class="page-top-gap">
                            <td colspan="7">&nbsp;</td>
                        </tr>
                        <tr>
                            <th style="width:11%;">Code</th>
                            <th style="width:14%;">De</th>
                            <th style="width:11%;">Montant</th>
                            <th style="width:7%;">Taux</th>
                            <th style="width:8%;">PV</th>
                            <th style="width:7%;">Gén.</th>
                            <th style="width:42%;">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($bonusOrder ?? ['direct', 'indirect', 'leadership', 'cash_pos']) as $type)
                            @php
                                $groupedDetails = $beneficiaire['details_by_type'][$type] ?? [];
                                $typeTotal = $beneficiaire['totals_by_type'][$type] ?? 0;
                                $typeLabel = ($bonusLabels[$type] ?? null) ?: ucfirst(str_replace('_', ' ', $type));
                                $typeColor = match ($type) {
                                    'direct' => '#312E81',
                                    'indirect' => '#1E3A8A',
                                    'leadership' => '#9A3412',
                                    'cash_pos' => '#14532D',
                                    default => '#0E2F76',
                                };
                            @endphp
                            @if(count($groupedDetails) > 0)
                                <tr class="bonus-group-{{ $type }}">
                                    <td colspan="7" class="bonus-group-header">
                                        {{ $typeLabel }}
                                    </td>
                                </tr>

                                @foreach($groupedDetails as $detail)
                                <tr>
                                    <td class="text-left" style="font-weight:600;">{{ $detail['from_user_code'] ?? '—' }}</td>
                                    <td class="text-left">{{ $detail['from_user'] }}</td>
                                    <td class="text-right" style="color: {{ $typeColor }}; font-weight: bold;">${{ number_format($detail['amount'], 2) }}</td>
                                    <td>{{ $detail['percentage'] }}%</td>
                                    <td class="text-right">{{ number_format((float) ($detail['pv_used'] ?? 0), 1) }}</td>
                                    <td>{{ $detail['generation'] }}</td>
                                    <td class="text-left" style="font-size:8px;">{{ $detail['description'] }}</td>
                                </tr>
                                @endforeach

                                <tr class="bonus-group-{{ $type }}">
                                    <td colspan="6" class="bonus-group-total" style="text-align: right;">
                                        Sous-total {{ $typeLabel }}
                                    </td>
                                    <td class="bonus-group-total" style="text-align: right; font-weight: bold; color: {{ $typeColor }};">
                                        ${{ number_format($typeTotal, 2) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;">TOTAL {{ strtoupper($beneficiaire['name']) }} :</td>
                            <td style="text-align:right; color:#0E2F76; font-size:11px;">
                                <strong>${{ number_format($beneficiaire['total'], 2) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

        @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])

    </div>

</body>
</html>
