{{-- resources/views/cashier/adhesion-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'Adhésion - Salang Group</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Times New Roman', Times, serif, Arial; 
            font-size: 14px; 
            color: #000; 
            padding: 16px 25px;
            background: #fff;
        }

        /* EN-TÊTE */
        .report-header {
            width: 100%;
            border-bottom: 2.5px solid #8b0000;
            padding-bottom: 6px;
            margin-bottom: 8px;
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
            width: 80px;
            text-align: center;
        }
        .logo-cell img {
            max-height: 55px;
            width: auto;
        }
        .header-center {
            text-align: center;
        }
        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #558b2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .header-text {
            font-size: 9px;
            line-height: 1.3;
            font-weight: bold;
        }
        .address {
            font-size: 8.5px;
            color: #000;
            margin-top: 2px;
            line-height: 1.3;
            font-weight: normal;
        }
        .address .city {
            font-weight: bold;
            color: #0E2F76;
        }

        /* TITRE */
        .main-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 6px;
        }
        .form-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            padding: 0 3px;
        }

        /* SECTIONS */
        .section-header {
            font-size: 14.5px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 2px;
            margin: 10px 0 6px 0;
            text-transform: uppercase;
        }

        /* CHAMPS */
        .form-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .form-grid td {
            padding: 3.5px 5px;
            vertical-align: middle;
            font-size: 13.5px;
        }
        .field-label {
            font-weight: bold;
            white-space: nowrap;
            width: 16%;
        }
        .field-line {
            border-bottom: 1.5px dotted #000;
            height: 26px;
            padding-left: 5px;
        }

        /* ENGAGEMENTS */
        .engagements-list {
            margin-left: 20px;
            font-size: 12.5px;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        .engagements-list li {
            margin-bottom: 2px;
            list-style-type: square;
        }
        .engagements-intro {
            font-weight: bold;
            margin: 4px 0 5px 0;
            font-size: 13px;
        }

        /* SIGNATURES */
        .signatures-table {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: top;
            padding: 5px 8px;
            font-size: 13px;
        }
        .signature-box {
            border: 1.5px solid #000;
            height: 85px;
            margin-top: 4px;
            padding: 8px 10px;
            font-size: 12.5px;
            color: #333;
        }

        /* PIED DE PAGE */
        .footer-container {
            margin-top: 16px;
            width: 100%;
        }
        .footer-left {
            font-size: 11px;
            line-height: 1.5;
            text-align: center;
        }
        .footer-line {
            border-bottom: 1.5px solid #cbd5e0;
            margin-top: 6px;
        }

        .page-content {
            max-height: 100vh;
            overflow: hidden;
        }

        @media print {
            body { padding: 10px 20px; }
            .no-print { display: none !important; }
            @page { 
                size: A4 portrait;
                margin: 0.6cm 0.8cm; 
            }
        }
    </style>
</head>
<body>

<!-- PAGE CONTENT -->
<div class="page-content">

    <!-- EN-TÊTE -->
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/salang_logo.png'))) }}" alt="Salang Logo">
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
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/salang_logo.png'))) }}" alt="Salang Logo">
                </td>
            </tr>
        </table>
    </div>

    <!-- TITRE + META -->
    <div class="main-title">Formulaire d'Adhésion</div>
    <div class="form-meta">
        <div>Code Membre : <strong>{{ $member->sponsor_id ?? $member->code ?? '........................' }}</strong></div>
        <div>Date : <strong>{{ isset($member->created_at) ? \Carbon\Carbon::parse($member->created_at)->format('d/m/Y') : date('d/m/Y') }}</strong></div>
    </div>

    <!-- SECTION 1 : INFORMATIONS PERSONNELLES -->
    <div class="section-header">1. Informations Personnelles</div>
    <table class="form-grid">
        <tr>
            <td class="field-label" style="width:16%;">Nom complet :</td>
            <td class="field-line" colspan="5"><strong>{{ $member->name ?? '' }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Né(e) le :</td>
            <td class="field-line" style="width:22%;">
                @php $birthDate = $member->birth_date ?? $metadata['birth_date'] ?? null; @endphp
                <strong>{{ $birthDate ? \Carbon\Carbon::parse($birthDate)->format('d/m/Y') : '' }}</strong>
            </td>
            <td class="field-label" style="width:9%; text-align:right; padding-right:4px;">Genre :</td>
            <td class="field-line" style="width:15%;">
                @php $gender = $member->gender ?? $metadata['gender'] ?? null; @endphp
                <strong>
                    @if($gender == 'male' || $gender == 'Masculin') Masculin
                    @elseif($gender == 'female' || $gender == 'Féminin') Féminin
                    @else {{ $gender }} 
                    @endif
                </strong>
            </td>
            <td class="field-label" style="width:11%; text-align:right; padding-right:4px;">Profession :</td>
            <td class="field-line" style="width:27%;"><strong>{{ $member->profession ?? $metadata['profession'] ?? '' }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Adresse :</td>
            <td class="field-line" colspan="5">
                <strong>
                    {{ $member->address ?? '' }}
                    @if(!empty($member->city ?? $metadata['city'])) , {{ $member->city ?? $metadata['city'] }} @endif
                    @if(!empty($member->country ?? $metadata['country'])) , {{ $member->country ?? $metadata['country'] }} @endif
                </strong>
            </td>
        </tr>
        <tr>
            <td class="field-label">Téléphone :</td>
            <td class="field-line" colspan="2"><strong>{{ $member->phone ?? '' }}</strong></td>
            <td class="field-label" style="text-align:right; padding-right:4px;">E-mail :</td>
            <td class="field-line" colspan="2"><strong>{{ $member->email ?? '' }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Pièce d'identité :</td>
            <td class="field-line" colspan="5">
                Type : <strong>{{ $member->identity_type ?? $metadata['identity_type'] ?? 'CNI / Passeport' }}</strong> 
                &nbsp;&nbsp; N° : <strong>{{ $member->identity_number ?? $metadata['identity_number'] ?? '' }}</strong>
            </td>
        </tr>
    </table>

    <!-- SECTION 2 : COORDONNÉES BANCAIRES -->
    <div class="section-header">2. Coordonnées Bancaires</div>
    <table class="form-grid">
        <tr>
            <td class="field-label" style="width:16%;">Nom de la banque :</td>
            <td class="field-line" style="width:34%;"><strong>{{ $member->bank_name ?? $metadata['bank_name'] ?? '' }}</strong></td>
            <td class="field-label" style="width:12%; text-align:right; padding-right:4px;">N° Compte :</td>
            <td class="field-line" style="width:38%;"><strong>{{ $member->account_number ?? $metadata['account_number'] ?? '' }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Titulaire :</td>
            <td class="field-line" colspan="3"><strong>{{ $member->account_holder ?? $metadata['account_holder'] ?? '' }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Mobile Money :</td>
            <td class="field-line" colspan="3">
                Opérateur : <strong>{{ $member->mobile_operator ?? $metadata['mobile_operator'] ?? 'Orange / Airtel / Vodacom' }}</strong> 
                &nbsp;&nbsp; Numéro : <strong>{{ $member->mobile_money ?? $metadata['mobile_money'] ?? '' }}</strong>
            </td>
        </tr>
    </table>

    <!-- SECTION 3 : PACKAGE -->
    <div class="section-header">3. Package d'Adhésion</div>
    <table class="form-grid">
        <tr>
            <td class="field-label" style="width:16%;">Package :</td>
            <td class="field-line" style="width:24%;">
                <strong>{{ $member->package->name ?? $metadata['package_name'] ?? 'N/A' }}</strong>
            </td>
            <td class="field-label" style="width:10%; text-align:right; padding-right:4px;">Grade :</td>
            <td class="field-line" style="width:20%;">
                <strong>{{ $member->rank ?? $metadata['rank'] ?? 'Distributeur' }}</strong>
                <span style="font-size:11px; color:#555;">(Niv. {{ $member->rank_level ?? 1 }})</span>
            </td>
            <td class="field-label" style="width:8%; text-align:right; padding-right:4px;">PV :</td>
            <td class="field-line" style="width:12%;"><strong>{{ number_format($member->pv_balance ?? 0) }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Prix :</td>
            <td class="field-line"><strong>${{ number_format($member->package->price ?? $metadata['package_price'] ?? 0, 2) }}</strong></td>
            <td class="field-label" style="text-align:right; padding-right:4px;">BV :</td>
            <td class="field-line"><strong>{{ number_format($member->bv_balance ?? 0) }}</strong></td>
            <td class="field-label" style="text-align:right; padding-right:4px;">Activation :</td>
            <td class="field-line">
                <strong>{{ isset($member->activated_at) ? \Carbon\Carbon::parse($member->activated_at)->format('d/m/Y') : (isset($member->created_at) ? \Carbon\Carbon::parse($member->created_at)->format('d/m/Y') : date('d/m/Y')) }}</strong>
            </td>
        </tr>
    </table>

    <!-- SECTION 4 : PARRAIN -->
    <div class="section-header">4. Parrain (Sponsor)</div>
    <table class="form-grid">
        <tr>
            <td class="field-label" style="width:16%;">Nom du parrain :</td>
            <td class="field-line" colspan="3"><strong>{{ $sponsor->name ?? $sponsor->full_name ?? '' }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Code distributeur :</td>
            <td class="field-line" style="width:34%;"><strong>{{ $sponsor->sponsor_id ?? $sponsor->code ?? '' }}</strong></td>
            <td class="field-label" style="width:12%; text-align:right; padding-right:4px;">Téléphone :</td>
            <td class="field-line" style="width:38%;"><strong>{{ $sponsor->phone ?? '' }}</strong></td>
        </tr>
    </table>

    <!-- SECTION 5 : ENGAGEMENT -->
    <div class="section-header">5. Engagement</div>
    <div class="engagements-intro">En signant ce formulaire, je déclare :</div>
    <ul class="engagements-list">
        <li>Avoir lu et accepté les conditions générales et le règlement d'ordre intérieur de Salang Group ;</li>
        <li>M'engager à respecter scrupuleusement les règles et l'éthique de l'entreprise ;</li>
        <li>Confirmer l'exactitude des informations fournies ci-dessus ;</li>
        <li>Comprendre que mes revenus dépendent exclusivement de mes efforts et performances.</li>
    </ul>

    <!-- SECTION 6 : SIGNATURES -->
    <div class="section-header">6. Signatures</div>
    <table class="signatures-table">
        <tr>
            <td>
                <strong>Signature du Demandeur (Membre) :</strong>
                <div class="signature-box">
                    @php
                        $sigName = $member->signature_name ?? $metadata['signature_name'] ?? null;
                        $sigDate = $member->signature_date ?? $metadata['signature_date'] ?? null;
                        $sigLoc  = $member->signature_location ?? $metadata['signature_location'] ?? null;
                    @endphp
                    @if($sigName)
                        <strong>Nom :</strong> {{ $sigName }}<br>
                        <strong>Date :</strong> {{ $sigDate ? \Carbon\Carbon::parse($sigDate)->format('d/m/Y') : '' }} &nbsp;|&nbsp; <strong>Lieu :</strong> {{ $sigLoc }}
                    @else
                        <span style="color:#999;">Lu et approuvé</span>
                    @endif
                </div>
            </td>
            <td>
                <strong>Signature &amp; Cachet du Parrain :</strong>
                <div class="signature-box" style="display:flex; align-items:center; justify-content:center; color:#999; font-size:13px;">
                    Validé par le parrain
                </div>
            </td>
        </tr>
    </table>

    <!-- BAS DE PAGE -->
    <div class="footer-container">
        <div class="footer-left">
            <strong>Salang Group International SARL</strong> — Service Administratif &amp; Réseau<br>
            Site web : www.salanggroup.com &nbsp;|&nbsp; E-mail : support@salanggroup.com
        </div>
        <div class="footer-line"></div>
    </div>

</div>
</body>
</html>