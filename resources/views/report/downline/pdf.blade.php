<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport détaillé — {{ $period }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
            padding: 22px 26px 28px;
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
            font-size: 17px;
            font-weight: bold;
            color: #558b2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .header-text {
            font-size: 9px;
            line-height: 1.25;
            font-weight: bold;
        }
        .address {
            font-size: 8.5px;
            color: #000;
            margin-top: 1px;
            line-height: 1.25;
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
            margin: 6px 0 2px;
        }

        .form-meta {
            width: 100%;
            margin: 2px 0 8px;
            font-size: 11px;
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
            letter-spacing: 0.4px;
            padding: 0 8px;
        }

        .section-header {
            font-size: 13px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 2px;
            margin: 8px 0 5px;
            text-transform: uppercase;
            clear: both;
        }

        .member-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
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
            width: 16%;
        }
        .member-info .value { width: 34%; }

        .totals-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px;
        }
        .totals-grid td {
            padding: 4px 8px;
            border: 1px solid #000;
            text-align: center;
            font-size: 11px;
            background-color: #f9f9f9;
        }
        .totals-grid .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            background-color: #f2f2f2;
        }
        .totals-grid .amount {
            font-weight: bold;
            font-size: 13px;
            color: #0E2F76;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 6px;
            font-size: 8.5px;
        }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 3px 3px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 2px 3px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }
        .data-table td.text-left { text-align: left; }
        .data-table td.text-right { text-align: right; }
        .data-table tbody tr:nth-child(even) td { background: #fafafa; }

        .empty {
            text-align: center;
            padding: 18px;
            border: 1px solid #000;
            color: #555;
            font-size: 11px;
        }

        .footer {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #8b0000;
            font-size: 8px;
            color: #444;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $shortPeriod = $period;
    if (preg_match('/^(\d{4})-(\d{2})$/', (string) $period, $m)) {
        $moisFr = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];
        $shortPeriod = ($moisFr[(int) $m[2]] ?? $m[2]).' '.$m[1];
    }
    $gradeLabel = \App\Support\MlmRank::label((int) ($user->rank_level ?? 0));
@endphp

<div class="page-content">
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if (! empty($logoBase64))
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
                    @if (! empty($logoBase64))
                        <img src="{{ $logoBase64 }}" alt="Salang Logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="main-title">Rapport Détaillé de PV</div>

    <table class="form-meta">
        <tr>
            <td class="meta-left">Période : <strong>{{ $shortPeriod }}</strong></td>
            <td class="meta-period-highlight" rowspan="2">{{ $shortPeriod }}</td>
            <td class="meta-right">Date : <strong>{{ $date_generated }}</strong></td>
        </tr>
        <tr>
            <td class="meta-left">Filleuls actifs : <strong>{{ number_format($rowsCount) }}</strong></td>
            <td class="meta-right"></td>
        </tr>
    </table>

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
            <td class="value"><strong>{{ $gradeLabel }}</strong></td>
            <td class="label">Niveau</td>
            <td class="value"><strong>{{ (int) ($user->rank_level ?? 0) }}</strong></td>
        </tr>
        <tr>
            <td class="label">Téléphone</td>
            <td class="value"><strong>{{ $user->phone ?? 'N/A' }}</strong></td>
            <td class="label">Email</td>
            <td class="value"><strong>{{ $user->email ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Parrain</td>
            <td class="value"><strong>{{ $user->parrain?->name ?? 'Aucun' }}</strong></td>
            <td class="label">Code parrain</td>
            <td class="value"><strong>{{ $user->parrain?->sponsor_id ?? '—' }}</strong></td>
        </tr>
    </table>

    <div class="section-header">2. Résumé de la Période</div>
    <table class="totals-grid">
        <tr>
            <td class="label" style="width:25%;">PV mensuel</td>
            <td class="label" style="width:25%;">PV mensuel groupe</td>
            <td class="label" style="width:25%;">PV personnel cumulé</td>
            <td class="label" style="width:25%;">PV cumulé réseau</td>
        </tr>
        <tr>
            <td class="amount">{{ number_format($memberStats['monthly_pv'], 1, ',', ' ') }}</td>
            <td class="amount">{{ number_format($memberStats['group_pv'], 1, ',', ' ') }}</td>
            <td class="amount">{{ number_format($memberStats['cumulative_pv'], 1, ',', ' ') }}</td>
            <td class="amount">{{ number_format($memberStats['cumulative_network_pv'], 1, ',', ' ') }}</td>
        </tr>
    </table>

    <div class="section-header">3. Détail sur les filleuls</div>

    @if ($rowsCount === 0)
        <div class="empty">Aucun filleul n’a enregistré de PV sur cette période.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%;">N°</th>
                    <th style="width:7%;">Code</th>
                    <th style="width:12%;">Nom &amp; Post-Nom</th>
                    <th style="width:9%;">Grade</th>
                    <th style="width:5%;">Gén.</th>
                    <th style="width:8%;">PV mensuel</th>
                    <th style="width:8%;">PV mens groupe</th>
                    <th style="width:10%;">PV pers. cumulé</th>
                    <th style="width:10%;">PV cumulé réseau</th>
                    <th style="width:8%;">Code parrain</th>
                    <th style="width:9%;">Nom &amp; Post-Nom</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $row['code'] }}</strong></td>
                        <td class="text-left">{{ $row['name'] }}</td>
                        <td>{{ $row['grade'] }}</td>
                        <td>{{ $row['generation'] }}</td>
                        <td class="text-right">{{ number_format($row['monthly_pv'], 1, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($row['group_pv'], 1, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($row['cumulative_pv'], 1, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($row['cumulative_network_pv'] ?? 0, 1, ',', ' ') }}</td>
                        <td>{{ $row['sponsor_code'] }}</td>
                        <td class="text-left">{{ $row['sponsor_name'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Salang Group SARL — Rapport confidentiel · {{ $user->sponsor_id }} · {{ $periodLabel }} · Généré le {{ $date_generated }}
    </div>
</div>
</body>
</html>
