<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport global suivi registre</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
        }
        .page-content {
            display: block;
            margin: 14px 28px 10px 28px;
            padding: 0;
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
            margin: 0 0 6px 0;
        }
        .form-meta {
            width: 100%;
            margin: 0 0 8px 0;
            font-size: 10.5px;
            font-weight: bold;
            border-collapse: collapse;
        }
        .form-meta td { padding: 1px 0; }
        .form-meta td.meta-left { text-align: left; width: 50%; }
        .form-meta td.meta-right { text-align: right; width: 50%; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .data-table th {
            background: #0A254E;
            color: #fff;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            padding: 5px 4px;
            text-align: left;
            border: 1px solid #0A254E;
        }
        .data-table th.num,
        .data-table td.num {
            text-align: center;
        }
        .data-table td {
            padding: 4px;
            border: 1px solid #d6dbe3;
            font-size: 10px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td { background: #fafbfc; }
        .data-table .code {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 9.5px;
            color: #475467;
        }
        .data-table tfoot td {
            background: #eef2f7;
            font-weight: bold;
            border-top: 1.5px solid #0A254E;
        }
    </style>
</head>
<body>
@if (!empty($letterheadBase64))
    <div class="salang-letterhead">
        <img class="salang-letterhead__img" src="{{ $letterheadBase64 }}" alt="Salang Group">
    </div>
@elseif (!empty($logoBase64))
    <div class="salang-lh-fallback" style="padding: 12px 28px 8px;">
        <img src="{{ $logoBase64 }}" alt="Salang" style="height: 42px; margin-bottom: 4px;">
        <div class="salang-lh-fallback__title">Salang Group</div>
    </div>
@endif

<div class="page-content">
    <h1 class="main-title">Rapport global — Suivi registre</h1>
    <div class="main-title-rule"></div>
    <p class="main-subtitle">Invités des 30 derniers jours</p>

    <table class="form-meta">
        <tr>
            <td class="meta-left">
                Période : {{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}
                @if ($memberCode !== '')
                    · Filtre code : {{ $memberCode }}
                @endif
            </td>
            <td class="meta-right">Généré le {{ $generatedAt->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="num">N°</th>
                <th style="width: 34%;">Membre</th>
                <th style="width: 14%;">Code</th>
                <th style="width: 8%;" class="num">Total</th>
                <th style="width: 10%;" class="num">Form.</th>
                <th style="width: 10%;" class="num">Adh.</th>
                <th style="width: 10%;" class="num">Cons.</th>
                <th style="width: 10%;" class="num">Achat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $index => $row)
                <tr>
                    <td class="num">{{ $index + 1 }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="code">{{ $row['code'] }}</td>
                    <td class="num">{{ $row['invites'] }}</td>
                    <td class="num">{{ $row['formation'] }}</td>
                    <td class="num">{{ $row['adhesion'] }}</td>
                    <td class="num">{{ $row['consultation'] }}</td>
                    <td class="num">{{ $row['purchase'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Totaux</td>
                <td class="num">{{ $totals['invites'] }}</td>
                <td class="num">{{ $totals['formation'] }}</td>
                <td class="num">{{ $totals['adhesion'] }}</td>
                <td class="num">{{ $totals['consultation'] }}</td>
                <td class="num">{{ $totals['purchase'] }}</td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
