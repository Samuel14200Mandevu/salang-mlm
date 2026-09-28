<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport de Caisse - {{ $report->report_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif, Arial;
            font-size: 12.5px;
            color: #000;
            padding: 16px 25px;
            background: #fff;
        }

        /* ============================================================
           EN-TÊTE (identique au formulaire d'adhésion)
           ============================================================ */
        .report-header {
            width: 100%;
            border-bottom: 2.5px solid #8b0000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 2px 4px; }
        .logo-cell { width: 80px; text-align: center; }
        .logo-cell img { max-height: 55px; width: auto; }
        .header-center { text-align: center; }
        .company-title {
            font-size: 20px; font-weight: bold; color: #558b2f;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;
        }
        .header-text { font-size: 9px; line-height: 1.3; font-weight: bold; }
        .address { font-size: 8.5px; margin-top: 2px; line-height: 1.3; }
        .address .city { font-weight: bold; color: #0E2F76; }

        /* ============================================================
           TITRE
           ============================================================ */
        .main-title {
            text-align: center; font-size: 20px; font-weight: bold;
            color: #0E2F76; text-transform: uppercase; text-decoration: underline;
            margin-bottom: 6px;
        }
        .form-meta {
            display: flex; justify-content: space-between;
            font-size: 13px; font-weight: bold;
            margin-bottom: 8px; padding: 0 3px;
        }
        .status-center {
            text-align: center;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block; padding: 3px 12px;
            border-radius: 4px; font-size: 10px; font-weight: bold;
            text-transform: uppercase;
        }
        .status-approved { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .status-submitted { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
        .status-draft { background: #e5e7eb; color: #374151; border: 1px solid #6b7280; }
        .status-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #dc2626; }

        /* ============================================================
           SECTIONS
           ============================================================ */
        .section-header {
            font-size: 13.5px; font-weight: bold; color: #558b2f;
            border-bottom: 2px solid #8b0000; padding-bottom: 2px;
            margin: 12px 0 6px 0; text-transform: uppercase;
        }

        /* ============================================================
           TABLEAUX
           ============================================================ */
        .data-table {
            width: 100%; border-collapse: collapse; margin-bottom: 8px;
            border: 1px solid #000;
        }
        .data-table th {
            background: #f0f0f0; padding: 5px 8px; text-align: left;
            font-size: 11.5px; font-weight: bold; border: 1px solid #000;
        }
        .data-table th.text-right { text-align: right; }
        .data-table th.text-center { text-align: center; }
        .data-table td {
            padding: 5px 8px; font-size: 12px; border: 1px solid #999;
            vertical-align: middle;
        }
        .data-table .label { font-weight: bold; width: 50%; background: #fafafa; }
        .data-table .value { text-align: right; font-weight: bold; }
        .data-table .value-red { text-align: right; font-weight: bold; color: #8b0000; }
        .data-table .value-green { text-align: right; font-weight: bold; color: #16a34a; }
        .data-table .value-usd { text-align: right; font-weight: bold; color: #0E2F76; }
        .data-table .value-cdf { text-align: right; font-weight: bold; color: #8b0000; }
        .data-table .text-center { text-align: center; }

        .total-row {
            background: #0E2F76 !important;
            color: #FFFFFF !important;
        }
        .total-row td {
            font-weight: bold; font-size: 13px; padding: 7px 10px;
            border: 1px solid #0E2F76;
        }

        /* ============================================================
           GRILLE 2 COLONNES
           ============================================================ */
        .two-cols {
            display: table; width: 100%;
            border-collapse: separate; border-spacing: 10px 0;
            margin: 0 -10px;
        }
        .col { display: table-cell; width: 50%; vertical-align: top; }

        /* ============================================================
           ÉCART DE CAISSE
           ============================================================ */
        .difference-box {
            padding: 8px 12px; border: 2px solid #000;
            margin: 10px 0; text-align: center;
            font-weight: bold; font-size: 13px;
        }
        .difference-box.ok {
            background: rgba(34, 197, 94, 0.08);
            border-color: #16a34a; color: #16a34a;
        }
        .difference-box.warning {
            background: rgba(245, 158, 11, 0.08);
            border-color: #f59e0b; color: #d97706;
        }
        .difference-box.danger {
            background: rgba(179, 42, 42, 0.08);
            border-color: #b32a2a; color: #b32a2a;
        }

        /* ============================================================
           OBSERVATIONS
           ============================================================ */
        .notes-box {
            border: 1.5px solid #000; padding: 8px 10px;
            min-height: 55px; font-size: 12px; margin-bottom: 10px;
        }

        /* ============================================================
           SIGNATURES
           ============================================================ */
        .signatures-table {
            width: 100%; margin-top: 10px; border-collapse: collapse;
        }
        .signatures-table td {
            width: 50%; vertical-align: top; padding: 5px 8px; font-size: 12.5px;
        }
        .signature-box {
            border: 1.5px solid #000; height: 70px; margin-top: 4px;
            padding: 8px 10px; font-size: 12px; color: #333;
        }

        /* ============================================================
           PIED DE PAGE
           ============================================================ */
        .footer-container { margin-top: 14px; width: 100%; }
        .footer-left { font-size: 10.5px; line-height: 1.5; text-align: center; }
        .footer-line { border-bottom: 1.5px solid #cbd5e0; margin-top: 6px; }

        @media print {
            body { padding: 10px 20px; }
            @page { size: A4 portrait; margin: 0.6cm 0.8cm; }
        }
    </style>
</head>
<body>

<!-- ============================================================
     EN-TÊTE
     ============================================================ -->
<div class="report-header">
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Salang Logo">
                @endif
            </td>
            <td class="header-center">
                <div class="company-title">SALANG GROUP SARL</div>
                <div class="header-text">
                    N° IDN:22-M7300-N63464Q &nbsp; N° RCCM:CD/BKV/RCCM/20-B-00116 &nbsp; N° IMPORT-EXPORT:0024/CBX-21/I000439SK/Z<br>
                    Contact : +243 975 220 079 &nbsp; Web:www.salanggroup.com &nbsp; Email:support@salanggroup.com
                </div>
                <div class="address">
                    <span class="city">Kinshasa :</span> 4 AV, Ixoras 382, 7eme Rue Resid. &nbsp;|&nbsp;
                    <span class="city">Bukavu :</span> N°4 Av. FIZI, Q. Nyawera, C/Ibanda &nbsp;|&nbsp;
                    <span class="city">Goma :</span> Rondpoint Chikudu, Bat. KBS 3e N.
                </div>
            </td>
            <td class="logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Salang Logo">
                @endif
            </td>
        </tr>
    </table>
</div>

<!-- TITRE -->
<div class="main-title">Rapport Journalier de Caisse</div>

<div class="form-meta">
    <div>N° Rapport : <strong>{{ $report->report_number }}</strong></div>
    <div>Date : <strong>{{ $report->report_date->format('d/m/Y') }}</strong></div>
</div>

<div class="status-center">
    <span class="status-badge status-{{ $report->status }}">{{ $report->status_label }}</span>
</div>

<!-- ============================================================
     SECTION 1 : INFORMATIONS GÉNÉRALES
     ============================================================ -->
<div class="section-header">1. Informations Générales</div>
<table class="data-table">
    <tr>
        <td class="label">Caissier</td>
        <td style="text-align: left;">{{ $report->user->name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label">Code caissier</td>
        <td style="text-align: left;">{{ $report->user->sponsor_id ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label">Date du rapport</td>
        <td style="text-align: left;">{{ $report->report_date->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="label">Heure de soumission</td>
        <td style="text-align: left;">{{ $report->signature_at ? $report->signature_at->format('d/m/Y H:i') : '-' }}</td>
    </tr>
    <tr>
        <td class="label">Statut</td>
        <td style="text-align: left;">{{ $report->status_label }}</td>
    </tr>
    @if($report->approver)
    <tr>
        <td class="label">Approuvé par</td>
        <td style="text-align: left;">{{ $report->approver->name }} le {{ $report->approved_at?->format('d/m/Y H:i') }}</td>
    </tr>
    @endif
</table>

<!-- ============================================================
     SECTION 2 : RÉSUMÉ DES VENTES (USD + CDF)
     ============================================================ -->
<div class="section-header">2. Résumé des Ventes</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Désignation</th>
            <th class="text-right" style="width: 22%;">USD ($)</th>
            <th class="text-right" style="width: 22%;">CDF (FC)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="label">Ventes POS (guichet)</td>
            <td class="value-usd">${{ number_format($report->total_pos, 2) }}</td>
            <td class="value-cdf">FC {{ number_format($report->total_pos_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Ventes MLM (packages)</td>
            <td class="value-usd">${{ number_format($report->total_mlm, 2) }}</td>
            <td class="value-cdf">FC {{ number_format($report->total_mlm_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL VENTES</td>
            <td style="text-align: right;">${{ number_format($report->total_sales, 2) }}</td>
            <td style="text-align: right;">FC {{ number_format($report->total_sales_cdf, 0, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>

<div class="two-cols">
    <div class="col">
        <table class="data-table">
            <tr>
                <td class="label">Nombre de commandes</td>
                <td class="value">{{ $report->total_orders }}</td>
            </tr>
            <tr>
                <td class="label">Total PV</td>
                <td class="value">{{ number_format($report->total_pv) }} PV</td>
            </tr>
            <tr>
                <td class="label">Total BV</td>
                <td class="value">{{ number_format($report->total_bv) }} BV</td>
            </tr>
        </table>
    </div>
    <div class="col">
        <table class="data-table">
            <tr>
                <td class="label">Nouveaux membres</td>
                <td class="value">{{ $report->new_members }}</td>
            </tr>
            <tr>
                <td class="label">Nouveaux clients</td>
                <td class="value">{{ $report->new_clients }}</td>
            </tr>
        </table>
    </div>
</div>

<!-- ============================================================
     SECTION 3 : DÉTAIL PAR MODE DE PAIEMENT (USD + CDF)
     ============================================================ -->
<div class="section-header">3. Détail par Mode de Paiement</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Mode de paiement</th>
            <th class="text-right" style="width: 22%;">USD ($)</th>
            <th class="text-right" style="width: 22%;">CDF (FC)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="label">Espèces (Cash)</td>
            <td class="value-usd">${{ number_format($report->cash_amount, 2) }}</td>
            <td class="value-cdf">FC {{ number_format($report->cash_amount_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Mobile Money</td>
            <td class="value-usd">${{ number_format($report->mobile_money_amount, 2) }}</td>
            <td class="value-cdf">FC {{ number_format($report->mobile_money_amount_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Banque / Virement</td>
            <td class="value-usd">${{ number_format($report->bank_amount, 2) }}</td>
            <td class="value-cdf">FC {{ number_format($report->bank_amount_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL ENCAISSÉ</td>
            <td style="text-align: right;">
                ${{ number_format($report->cash_amount + $report->mobile_money_amount + $report->bank_amount, 2) }}
            </td>
            <td style="text-align: right;">
                FC {{ number_format($report->cash_amount_cdf + $report->mobile_money_amount_cdf + $report->bank_amount_cdf, 0, ',', ' ') }}
            </td>
        </tr>
    </tbody>
</table>

<!-- ============================================================
     SECTION 4 : DÉPENSES ET COMMISSIONS
     ============================================================ -->
<div class="section-header">4. Dépenses & Commissions</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Désignation</th>
            <th class="text-right" style="width: 22%;">USD ($)</th>
            <th class="text-right" style="width: 22%;">CDF (FC)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="label">Total dépenses de la journée</td>
            <td class="value-red">- ${{ number_format($report->total_expenses, 2) }}</td>
            <td class="value-red">- FC {{ number_format($report->total_expenses_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Commissions CASH POS données</td>
            <td class="value-red">- ${{ number_format($report->total_commissions, 2) }}</td>
            <td class="value-red">- FC {{ number_format($report->total_commissions_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr class="total-row">
            <td>SOLDE NET</td>
            <td style="text-align: right;">${{ number_format($report->net_balance, 2) }}</td>
            <td style="text-align: right;">FC {{ number_format($report->net_balance_cdf, 0, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>

<!-- ============================================================
     SECTION 5 : ÉTAT DE LA CAISSE
     ============================================================ -->
<div class="section-header">5. État de la Caisse</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Désignation</th>
            <th class="text-right" style="width: 22%;">USD ($)</th>
            <th class="text-right" style="width: 22%;">CDF (FC)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="label">Fond de caisse (ouverture)</td>
            <td class="value-usd">${{ number_format($report->opening_balance, 2) }}</td>
            <td class="value-cdf">FC {{ number_format($report->opening_balance_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Encaissements (espèces du jour)</td>
            <td class="value-usd">+ ${{ number_format($report->cash_amount, 2) }}</td>
            <td class="value-cdf">+ FC {{ number_format($report->cash_amount_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Dépenses payées en espèces</td>
            <td class="value-red">- ${{ number_format($report->total_expenses, 2) }}</td>
            <td class="value-red">- FC {{ number_format($report->total_expenses_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Caisse théorique</td>
            <td class="value-usd">${{ number_format($report->theoretical_balance, 2) }}</td>
            <td class="value-cdf">FC {{ number_format($report->theoretical_balance_cdf, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Caisse comptée (physique)</td>
            <td class="value-usd">${{ number_format($report->closing_balance, 2) }}</td>
            <td class="value-cdf">FC {{ number_format($report->closing_balance_cdf, 0, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>

{{-- Écart de caisse USD --}}
@php
    $diffClassUsd = 'ok';
    if (abs($report->difference) >= 0.01 && abs($report->difference) <= 5) $diffClassUsd = 'warning';
    elseif (abs($report->difference) > 5) $diffClassUsd = 'danger';
@endphp

<div class="difference-box {{ $diffClassUsd }}">
    USD :
    @if(abs($report->difference) < 0.01)
        ÉCART : $0.00 (Caisse juste)
    @elseif($report->difference > 0)
        EXCÉDENT : + ${{ number_format($report->difference, 2) }}
    @else
        MANQUANT : ${{ number_format($report->difference, 2) }}
    @endif
</div>

{{-- Écart de caisse CDF --}}
@php
    $diffClassCdf = 'ok';
    if (abs($report->difference_cdf) >= 1 && abs($report->difference_cdf) <= 5000) $diffClassCdf = 'warning';
    elseif (abs($report->difference_cdf) > 5000) $diffClassCdf = 'danger';
@endphp

<div class="difference-box {{ $diffClassCdf }}">
    CDF :
    @if(abs($report->difference_cdf) < 1)
        ÉCART : FC 0 (Caisse juste)
    @elseif($report->difference_cdf > 0)
        EXCÉDENT : + FC {{ number_format($report->difference_cdf, 0, ',', ' ') }}
    @else
        MANQUANT : FC {{ number_format($report->difference_cdf, 0, ',', ' ') }}
    @endif
</div>

<!-- ============================================================
     SECTION 7 : DÉTAIL DES PRODUITS VENDUS
     ============================================================ -->
@if(!empty($report->details['products_sold']) && count($report->details['products_sold']) > 0)
    <div class="section-header">7 . Détail des Produits Vendus</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th class="text-center" style="width: 10%;">Qté</th>
                <th class="text-center" style="width: 15%;">Devise</th>
                <th class="text-right" style="width: 20%;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->details['products_sold'] as $product)
                @php
                    $currency = is_array($product) ? ($product['currency'] ?? 'USD') : ($product->currency ?? 'USD');
                    $name = is_array($product) ? ($product['name'] ?? 'N/A') : ($product->name ?? 'N/A');
                    $qty = is_array($product) ? ($product['total_quantity'] ?? 0) : ($product->total_quantity ?? 0);
                    $amount = is_array($product) ? ($product['total_amount'] ?? 0) : ($product->total_amount ?? 0);
                @endphp
                <tr>
                    <td>{{ $name }}</td>
                    <td class="text-center">{{ $qty }}</td>
                    <td class="text-center">{{ $currency }}</td>
                    <td class="{{ $currency === 'CDF' ? 'value-cdf' : 'value-usd' }}">
                        @if($currency === 'CDF')
                            FC {{ number_format($amount, 0, ',', ' ') }}
                        @else
                            ${{ number_format($amount, 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<!-- ============================================================
     SECTION 6 : SIGNATURES
     ============================================================ -->
<div class="section-header">6. Signatures</div>
<table class="signatures-table">
    <tr>
        <td>
            <strong>Signature du Caissier :</strong>
            <div class="signature-box">
                <strong>Nom :</strong> {{ $report->signature_name ?? $report->user->name }}<br>
                <strong>Date :</strong> {{ $report->signature_at ? $report->signature_at->format('d/m/Y H:i') : '-' }}
            </div>
        </td>
        <td>
            <strong>Visa de l'Administrateur :</strong>
            <div class="signature-box">
                @if($report->approver)
                    <strong>Nom :</strong> {{ $report->approver->name }}<br>
                    <strong>Date :</strong> {{ $report->approved_at?->format('d/m/Y H:i') }}<br>
                    <span style="color: #16a34a; font-weight: bold;">✓ Approuvé</span>
                @else
                    <span style="color:#999;">En attente de validation</span>
                @endif
            </div>
        </td>
    </tr>
</table>

<!-- ============================================================
     PIED DE PAGE
     ============================================================ -->
<div class="footer-container">
    <div class="footer-left">
        <strong>Salang Group International SARL</strong> — Service Comptabilité &amp; Caisse<br>
        Site web : www.salanggroup.com &nbsp;|&nbsp; E-mail : support@salanggroup.com
    </div>
    <div class="footer-line"></div>
    <div class="footer-left" style="margin-top: 4px; font-size: 9px; color: #666;">
        Rapport généré le {{ now()->format('d/m/Y à H:i') }} — Document officiel Salang Group
    </div>
</div>

</body>
</html>