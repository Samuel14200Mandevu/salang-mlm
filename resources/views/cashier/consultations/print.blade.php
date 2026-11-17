{{-- resources/views/cashier/consultations/print.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Consultation #{{ $consultation->id ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 15.5px;
            color: #000;
            padding: 0;
            margin: 0;
            background: #fff;
        }

        @include('partials.pdf._styles')

        /* En-tête pleine largeur — un peu plus haute */
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
        .page-body {
            padding: 6px 26px 130px 26px;
        }
        .page-bottom-fixed {
            position: fixed;
            left: 26px;
            right: 26px;
            bottom: 6mm;
            width: auto;
        }
        .salang-pdf-footer {
            margin-top: 8px;
        }

        .title-row {
            width: 100%;
            margin: 4px 0 6px 0;
            font-size: 16px;
            border-collapse: collapse;
        }
        .title-row td {
            vertical-align: middle;
            font-weight: bold;
            color: #000;
        }
        .main-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #000;
            text-decoration: underline;
            text-transform: uppercase;
        }

        /* Infos patient */
        .patient-block {
            width: 82%;
            margin: 0 auto;
            line-height: 1.55;
            font-size: 16.5px;
        }

        .divider-line {
            width: 82%;
            margin: 6px auto 6px auto;
            border-bottom: 2px solid #000;
        }

        .section-header {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #000;
            text-decoration: underline;
            text-transform: none;
            margin: 4px 0 6px 0;
            font-style: normal;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .products-table th {
            border: 1.5px solid #000;
            padding: 4px 7px;
            font-size: 15px;
            font-weight: bold;
            text-align: left;
            background-color: #fcfcfc;
            color: #000;
        }
        .products-table th:nth-child(2),
        .products-table th:nth-child(3),
        .products-table td:nth-child(2),
        .products-table td:nth-child(3) {
            text-align: center;
        }
        .products-table td {
            border: 1.5px solid #000;
            padding: 3px 7px;
            font-size: 14.5px;
            height: 38px;
            vertical-align: middle;
        }
        .products-table td.total-cell {
            font-weight: bold;
            font-size: 15.5px;
            text-align: center;
        }
        .products-table td.total-cell-span {
            font-weight: bold;
            font-size: 15.5px;
            text-align: center;
        }

        .disclaimer {
            text-align: center;
            font-style: italic;
            font-weight: bold;
            font-size: 14px;
            margin: 14px 0 6px 0;
        }

        .advice-title {
            text-align: center;
            font-size: 17.5px;
            font-weight: bold;
            font-style: italic;
            color: #C5A04D;
            text-transform: none;
            text-decoration: underline;
            width: 72%;
            margin: 6px auto 6px auto;
        }
        .advice-list {
            width: 82%;
            margin: 0 auto;
            padding-left: 18px;
            font-size: 15px;
            line-height: 1.45;
        }
        .advice-list li {
            margin-bottom: 3px;
            list-style-type: square;
        }

        .footer-container {
            margin-top: 0;
            position: relative;
            width: 100%;
            min-height: 56px;
        }
        .footer-left {
            width: 60%;
            font-size: 13px;
            line-height: 1.35;
        }
        .footer-left .company-name {
            font-weight: bold;
            font-size: 14px;
        }
        .stamp-box {
            position: absolute;
            right: 10px;
            top: -8px;
            border: 1.5px solid #2b6cb0;
            color: #2b6cb0;
            padding: 6px 12px;
            text-align: center;
            font-weight: bold;
            font-size: 14.5px;
            border-radius: 4px;
            background: #fff;
        }

        .no-print { display: block; }
        .no-print .btn-print-action {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            background: #0A254E;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
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
            margin-left: 8px;
        }

        @page {
            size: A4 portrait;
            margin: 0 0 8mm 0;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page {
                margin: 0 0 8mm 0;
            }
        }
    </style>
</head>
<body>

@include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])

<div class="page-body">
<table class="title-row">
    <tr>
        <td style="width: 25%;">Code ID: {{ $consultation->code_id ?? '..........' }}</td>
        <td class="main-title" style="width: 50%;">Fiche de consultation</td>
        <td style="width: 25%; text-align: right;">Numéro: {{ $consultation->numero ?? '..........' }}</td>
    </tr>
</table>

<div class="patient-block">
    Noms : {{ $consultation->nom_complet ?? '' }}<br>
    Genre : {{ $consultation->genre_label ?? '' }} &nbsp;&nbsp;&nbsp;
    Age : {{ $consultation->age ?? '' }} &nbsp;&nbsp;&nbsp;
    Poids : {{ $consultation->poids ?? '' }} &nbsp;&nbsp;&nbsp;
    Tail : {{ $consultation->taille ?? '' }} cm<br>
    Date de l'examen : {{ isset($consultation->date_examen) ? $consultation->date_examen->format('d/m/Y') : '' }}
</div>

<div class="divider-line"></div>

<div class="section-header">Produits recommandés</div>

@php
    $products = $consultation->recommended_products ?? [];
    $prixCeragemTotal = ($consultation->seances_ceragem ?? 0) * ($consultation->prix_ceragem ?? 0);
    $prixDetoxTotal = ($consultation->seances_detox ?? 0) * ($consultation->prix_detox ?? 0);
    $totalServices = $prixCeragemTotal + $prixDetoxTotal;
    $productsTotal = \App\Services\ConsultationSaleService::productsTotalRange(is_array($products) ? $products : []);
    $totalProductsLabel = abs($productsTotal['min'] - $productsTotal['max']) < 0.001
        ? '$'.number_format($productsTotal['max'], 2)
        : '$'.number_format($productsTotal['min'], 2).' - $'.number_format($productsTotal['max'], 2);
    // Services à part : colonne Observation = total services uniquement (pas produits + services).
    $totalServicesLabel = '$'.number_format($totalServices, 2);
@endphp

<table class="products-table">
    <thead>
        <tr>
            <th style="width: 30%;">Produits</th>
            <th style="width: 12%;">Posologie</th>
            <th style="width: 24%;">Prix</th>
            <th style="width: 34%;">Observation</th>
        </tr>
    </thead>
    <tbody>
        @php $productRowCount = max(9, count(is_array($products) ? $products : [])); @endphp
        @for ($i = 0; $i < $productRowCount; $i++)
        <tr>
            <td>{{ !empty($products[$i]) ? \App\Services\ConsultationSaleService::productDisplayName($products[$i]) : '' }}</td>
            <td>{{ $products[$i]['posologie'] ?? '' }}</td>
            <td>{{ !empty($products[$i]) ? \App\Services\ConsultationSaleService::priceLabel($products[$i]) : '' }}</td>
            <td>
                @if ($i === 1)
                    <strong>Séances ceragem</strong> : {{ $consultation->seances_ceragem ?? '0' }}
                @elseif ($i === 2)
                    <strong>Prix /s:</strong> ${{ number_format($consultation->prix_ceragem ?? 0, 2) }}
                @elseif ($i === 5)
                    <strong>Séances detox</strong> : {{ $consultation->seances_detox ?? '0' }}
                @elseif ($i === 6)
                    <strong>Prix /s:</strong> ${{ number_format($consultation->prix_detox ?? 0, 2) }}
                @else
                    {{ $products[$i]['observation'] ?? '' }}
                @endif
            </td>
        </tr>
        @endfor
        <tr>
            <td colspan="2" class="total-cell-span">Total</td>
            <td class="total-cell">{{ $totalProductsLabel }}</td>
            <td class="total-cell">{{ $totalServicesLabel }}</td>
        </tr>
    </tbody>
</table>

<div class="disclaimer">
    Résultats de test à prendre comme référence et non comme conclusion de diagnostic.
</div>

<div class="advice-title">Conseils Hygiéno-diététiques</div>
<ul class="advice-list">
    <li>Buvez plus au moins 1litres d'eau par jour.</li>
    <li>Consommez le sucre sel et cholesterol et/ou graisses et ses derivés avec moderation.</li>
    <li>Plus des legumes et fruits à votre régime alimentaire .</li>
    <li>Evitez les stress et reposez vous de fois .</li>
</ul>

</div>

<div class="page-bottom-fixed">
    <div class="footer-container">
        <div class="footer-left">
            <div class="company-name">Salang Group International sarl</div>
            Confirmé par le SALANG Company<br>
            Ixoras 382, 7eme Rue Residentielle, petit Boulevard
        </div>

        <div class="stamp-box">
            Demande approuvée<br><br>
            SALANGGROUP SARL
        </div>
    </div>

    @include('partials.pdf.footer', ['pdfFooterLabel' => 'FICHE DE CONSULTATION OFFICIELLE – SALANG GROUP SARL'])
</div>

@if(empty($isPdf))
<div class="no-print" style="text-align:center; margin-top:18px;">
    <button type="button" class="btn-print-action" onclick="window.print()">Imprimer</button>
    <button type="button" class="btn-print-back" onclick="window.close()">Fermer</button>
</div>
@endif

</body>
</html>
