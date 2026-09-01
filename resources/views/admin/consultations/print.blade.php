{{-- resources/views/admin/consultations/print.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Consultation #{{ $consultation->id ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Times New Roman', Times, serif, Arial; 
            font-size: 14px; 
            color: #000; 
            padding: 25px 35px;
            background: #fff;
        }

        /* EN-TÊTE */
        .report-header {
            width: 100%;
            border-bottom: 2.5px solid #8b0000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-cell {
            width: 110px;
            text-align: center;
        }
        .logo-cell img {
            max-height: 85px;
            width: auto;
        }
        .header-center {
            text-align: center;
        }
        .company-title {
            font-size: 22px;
            font-weight: bold;
            color: #558b2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .header-text {
            font-size: 11px;
            line-height: 1.4;
            font-weight: bold;
        }
        .address {
            font-size: 10.5px;
            color: #000;
            margin-top: 4px;
            line-height: 1.4;
            font-weight: normal;
        }
        .address .city {
            font-weight: bold;
            color: #0E2F76;
        }

        /* TITRES & INFOS PATIENT */
        .title-row {
            width: 100%;
            margin-bottom: 15px;
            font-size: 15px;
        }
        .main-title {
            text-align: center;
            font-size: 24px;
            font-weight: normal;
            text-decoration: underline;
        }
        
        .patient-block {
            width: 80%;
            margin: 0 auto;
            line-height: 2;
            font-size: 15px;
        }

        /* SÉPARATION */
        .divider-line {
            width: 80%;
            margin: 10px auto 20px auto;
            border-bottom: 3.5px solid #000;
        }

        /* TABLEAU */
        .section-header {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 15px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .products-table th {
            border: 2px solid #000;
            padding: 6px 10px;
            font-size: 15px;
            font-weight: bold;
            text-align: left;
            background-color: #fcfcfc;
        }
        .products-table td {
            border: 2px solid #000;
            padding: 6px 10px;
            font-size: 14.5px;
            height: 38px;
            vertical-align: middle;
        }
        .products-table td.total-cell {
            font-weight: bold;
            font-size: 15.5px;
        }

        .disclaimer {
            text-align: center;
            font-style: italic;
            font-weight: bold;
            font-size: 12.5px;
            margin: 18px 0 12px 0;
        }

        /* CONSEILS */
        .advice-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            width: 55%;
            margin: 15px auto 12px auto;
        }

        .advice-list {
            margin-left: 40px;
            font-size: 13.5px;
            line-height: 1.7;
        }
        .advice-list li {
            margin-bottom: 4px;
            list-style-type: square;
        }

        /* FOOTER & TAMPON */
        .footer-container {
            margin-top: 30px;
            position: relative;
            width: 100%;
        }
        
        .stamp-box {
            position: absolute;
            right: 15px;
            top: -20px;
            border: 2px solid #2b6cb0;
            color: #2b6cb0;
            padding: 10px 18px;
            transform: rotate(-5deg);
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border-radius: 4px;
            background: #fff;
        }

        .footer-left {
            width: 60%;
            font-size: 11px;
            line-height: 1.4;
        }
        .footer-left .company-name {
            font-weight: bold;
            font-size: 12px;
        }

        .footer-line {
            border-bottom: 1px solid #cbd5e0;
            margin-top: 15px;
        }

        /* MEDIA PRINT & MASQUAGE URL */
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }

        @page {
            margin-top: 0.5cm;
            margin-bottom: 0.5cm;
        }
        @page :header { display: none; }
        @page :footer { display: none; }
    </style>
</head>
<body>

<!-- EN-TÊTE -->
<div class="report-header">
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ asset('images/salang_logo.png') }}" alt="Salang Logo">
            </td>
            <td class="header-center">
                <div class="company-title">SALANG GROUP SARL</div>
                <div class="header-text">
                    N° IDN:22-M7300-N63464Q &nbsp; N° RCCM:CD/BKV/RCCM/20-B-00116<br>
                    NUMERO IMPORT-EXPORT:0024/CBX-21/I000439SK/Z<br>
                    Contact : +243 975 220 079 &nbsp; Web:www.salanggroup.com &nbsp; Email:support@salanggroup.com
                </div>
                <div class="address">
                    <span class="city">Kinshasa :</span> 4 AV, Ixoras 382, 7ème Rue Résidentielle, petit Boulevard &nbsp;|&nbsp;<br>
                    <span class="city">Bukavu :</span> N°4 Avenue FIZI, Quartier Nyawera, Commune d'Ibanda Bukavu, RD Congo<br>
                    <span class="city">Goma :</span> Rondpoint Chikudu, Batiment KBS (Kitumaini Serge Balezi) au 3ème Niveau, C/Goma
                </div>
            </td>
            <td class="logo-cell">
                <img src="{{ asset('images/salang_logo.png') }}" alt="Salang Logo">
            </td>
        </tr>
    </table>
</div>

<!-- CODE ID, TITRE, NUMÉRO -->
<table class="title-row">
    <tr>
        <td style="width: 25%;">Code ID: {{ $consultation->code_id ?? '..........' }}</td>
        <td class="main-title" style="width: 50%;">Fiche de consultation</td>
        <td style="width: 25%; text-align: right;">Numéro: {{ $consultation->numero ?? '..........' }}</td>
    </tr>
</table>

<!-- INFOS PATIENT -->
<div class="patient-block">
    Noms : {{ $consultation->nom_complet ?? '' }}<br>
    Genre : {{ $consultation->genre_label ?? '' }} &nbsp;&nbsp;&nbsp; 
    Age : {{ $consultation->age ?? '' }} &nbsp;&nbsp;&nbsp; 
    Poids : {{ $consultation->poids ?? '' }} &nbsp;&nbsp;&nbsp; 
    Taille : {{ $consultation->taille ?? '' }} cm<br>
    Date de l'examen : {{ isset($consultation->date_examen) ? $consultation->date_examen->format('d/m/Y') : '' }}
</div>

<!-- BARRE NOIRE -->
<div class="divider-line"></div>

<!-- TABLEAU PRODUITS & SERVICES -->
<div class="section-header">Produits recommandés</div>

@php
    $products = $consultation->recommended_products ?? [];
    $totalProduits = 0;
    
    // Calcul des totaux
    $prixCeragemTotal = ($consultation->seances_ceragem ?? 0) * ($consultation->prix_ceragem ?? 0);
    $prixDetoxTotal = ($consultation->seances_detox ?? 0) * ($consultation->prix_detox ?? 0);
    $totalServices = $prixCeragemTotal + $prixDetoxTotal;
    
    foreach($products as $p) {
        $totalProduits += floatval($p['prix'] ?? 0);
    }
    
    $totalGeneral = $totalProduits + $totalServices;
@endphp

<table class="products-table">
    <thead>
        <tr>
            <th style="width: 35%;">Produits</th>
            <th style="width: 20%;">Posologie</th>
            <th style="width: 15%;">Prix</th>
            <th style="width: 30%;">Observation</th>
        </tr>
    </thead>
    <tbody>
        {{-- Ligne 1 --}}
        <tr>
            <td>{{ $products[0]['produit'] ?? '' }}</td>
            <td>{{ $products[0]['posologie'] ?? '' }}</td>
            <td>{{ isset($products[0]['prix']) ? '$'.number_format($products[0]['prix'], 2) : '' }}</td>
            <td>{{ $products[0]['observation'] ?? '' }}</td>
        </tr>
        {{-- Ligne 2 : Séances ceragem --}}
        <tr>
            <td>{{ $products[1]['produit'] ?? '' }}</td>
            <td>{{ $products[1]['posologie'] ?? '' }}</td>
            <td>{{ isset($products[1]['prix']) ? '$'.number_format($products[1]['prix'], 2) : '' }}</td>
            <td><strong>Séances ceragem</strong> : {{ $consultation->seances_ceragem ?? '0' }}</td>
        </tr>
        {{-- Ligne 3 : Prix /s ceragem --}}
        <tr>
            <td>{{ $products[2]['produit'] ?? '' }}</td>
            <td>{{ $products[2]['posologie'] ?? '' }}</td>
            <td>{{ isset($products[2]['prix']) ? '$'.number_format($products[2]['prix'], 2) : '' }}</td>
            <td><strong>Prix /s:</strong> ${{ number_format($consultation->prix_ceragem ?? 0, 2) }}</td>
        </tr>
        {{-- Ligne 4 --}}
        <tr>
            <td>{{ $products[3]['produit'] ?? '' }}</td>
            <td>{{ $products[3]['posologie'] ?? '' }}</td>
            <td>{{ isset($products[3]['prix']) ? '$'.number_format($products[3]['prix'], 2) : '' }}</td>
            <td>{{ $products[3]['observation'] ?? '' }}</td>
        </tr>
        {{-- Ligne 5 --}}
        <tr>
            <td>{{ $products[4]['produit'] ?? '' }}</td>
            <td>{{ $products[4]['posologie'] ?? '' }}</td>
            <td>{{ isset($products[4]['prix']) ? '$'.number_format($products[4]['prix'], 2) : '' }}</td>
            <td>{{ $products[4]['observation'] ?? '' }}</td>
        </tr>
        {{-- Ligne 6 : Séances detox --}}
        <tr>
            <td>{{ $products[5]['produit'] ?? '' }}</td>
            <td>{{ $products[5]['posologie'] ?? '' }}</td>
            <td>{{ isset($products[5]['prix']) ? '$'.number_format($products[5]['prix'], 2) : '' }}</td>
            <td><strong>Séances detox</strong> : {{ $consultation->seances_detox ?? '0' }}</td>
        </tr>
        {{-- Ligne 7 : Prix /s detox --}}
        <tr>
            <td>{{ $products[6]['produit'] ?? '' }}</td>
            <td>{{ $products[6]['posologie'] ?? '' }}</td>
            <td>{{ isset($products[6]['prix']) ? '$'.number_format($products[6]['prix'], 2) : '' }}</td>
            <td><strong>Prix /s:</strong> ${{ number_format($consultation->prix_detox ?? 0, 2) }}</td>
        </tr>
        {{-- Ligne 8 --}}
        <tr>
            <td>{{ $products[7]['produit'] ?? '' }}</td>
            <td>{{ $products[7]['posologie'] ?? '' }}</td>
            <td>{{ isset($products[7]['prix']) ? '$'.number_format($products[7]['prix'], 2) : '' }}</td>
            <td>{{ $products[7]['observation'] ?? '' }}</td>
        </tr>
        {{-- Ligne Totaux séparés --}}
        <tr>
            <td class="total-cell">Total</td>
            <td></td>
            <td style="font-size: 15px;"><strong>${{ number_format($totalProduits, 2) }}</strong></td>
            <td style="font-size: 15px;"><strong>${{ number_format($totalGeneral, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

<div class="disclaimer">
    Résultats de test à prendre comme référence et non comme conclusion de diagnostic.
</div>

<!-- CONSEILS -->
<div class="advice-title">Conseils Hygiéno-diététiques</div>
<ul class="advice-list">
    <li>Buvez au moins 1,5 litre d'eau par jour.</li>
    <li>Consommez le sucre, le sel, le cholestérol et/ou les graisses avec modération.</li>
    <li>Privilégiez les légumes et fruits dans votre régime alimentaire.</li>
    <li>Évitez le stress et reposez-vous régulièrement.</li>
</ul>

<!-- PIED DE PAGE ET TAMPON -->
<div class="footer-container">
    <div class="footer-left">
        <div class="company-name">Salang Group International sarl</div>
        Confirmé par le SALANG Company<br>
        Ixoras 382, 7ème Rue Résidentielle, petit Boulevard
    </div>

    <div class="stamp-box">
        Demande approuvée<br><br>
        SALANGGROUP SARL
    </div>
</div>

<div class="footer-line"></div>

<!-- BOUTONS D'ACTION (ÉCRAN SEULEMENT) -->
<div style="text-align:center; margin-top:20px;" class="no-print">
    <button onclick="window.print()" style="padding:10px 24px; background:#0E2F76; color:#fff; border:none; border-radius:4px; font-size:14px; cursor:pointer;">Imprimer</button>
    <button onclick="window.close()" style="padding:10px 24px; background:#ccc; border:none; border-radius:4px; font-size:14px; cursor:pointer; margin-left:10px;">Fermer</button>
</div>

</body>
</html>