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
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
            line-height: 1.45;
            padding: 8px 18px 10px 18px;
            background: #fff;
        }

        @include('partials.pdf._styles')

        .salang-letterhead--compact {
            margin-bottom: 4px;
        }
        .salang-pdf-footer {
            margin-top: 10px;
        }

        /* TITRE */
        .main-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #0A254E;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 4px 0 0 0;
            line-height: 1.3;
        }
        .main-title-rule {
            width: 36%;
            margin: 3px auto 4px auto;
            border-bottom: 1.5px solid #C5A04D;
        }
        .adhesion-date {
            text-align: center;
            font-size: 11.5px;
            color: #0A254E;
            font-weight: bold;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }
        .adhesion-date span {
            color: #111;
            font-weight: bold;
        }

        /* SECTIONS — navy / or */
        .section-header {
            font-size: 12px;
            font-weight: bold;
            color: #0A254E;
            border-bottom: 1.5px solid #C5A04D;
            padding-bottom: 2px;
            margin: 10px 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            line-height: 1.3;
        }

        /* CHAMPS */
        .form-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        .form-grid td {
            padding: 5px 5px 5px 3px;
            vertical-align: middle;
            font-size: 12px;
            line-height: 1.4;
        }
        .field-label {
            font-weight: bold;
            white-space: nowrap;
            width: 15%;
            color: #0A254E;
            padding-right: 6px;
        }
        .field-line {
            border-bottom: 1px dotted #555;
            height: 22px;
            padding-left: 5px;
            padding-bottom: 2px;
        }

        /* ENGAGEMENTS */
        .engagements-list {
            margin: 0 0 4px 18px;
            padding: 0;
            font-size: 11.5px;
            line-height: 1.5;
        }
        .engagements-list li {
            margin-bottom: 4px;
            list-style-type: square;
        }
        .engagements-intro {
            font-weight: bold;
            margin: 3px 0 5px 0;
            font-size: 11.5px;
            color: #0A254E;
        }

        /* SIGNATURES — taille raisonnable */
        .signatures-table {
            width: 100%;
            margin-top: 4px;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: top;
            padding: 4px 8px;
            font-size: 11.5px;
        }
        .signature-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .signature-box td {
            border: 1.2px solid #0A254E;
            height: 68px;
            padding: 6px 8px;
            font-size: 11px;
            color: #555;
            vertical-align: top;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm 9mm 10mm;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="page-content">

    @include('partials.pdf.letterhead', ['letterheadCompact' => true])

    <div class="main-title">Formulaire d'Adhésion</div>
    <div class="main-title-rule"></div>
    @php
        $adhesionDate = $member->created_at
            ?? $metadata['adhesion_date']
            ?? $metadata['created_at']
            ?? ($date ?? null)
            ?? now();
        $adhesionDateLabel = \Carbon\Carbon::parse($adhesionDate)->format('d/m/Y');
    @endphp
    <div class="adhesion-date">Date d'adhésion : <span>{{ $adhesionDateLabel }}</span></div>

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
                @php
                    $addrParts = array_values(array_filter([
                        trim((string) ($member->address ?? '')),
                        trim((string) ($member->city ?? $metadata['city'] ?? '')),
                        trim((string) ($member->country ?? $metadata['country'] ?? '')),
                    ], fn ($v) => $v !== ''));
                @endphp
                <strong>{{ implode(', ', $addrParts) }}</strong>
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
            <td class="field-line" style="width:34%;">
                <strong>{{ $member->package->name ?? $metadata['package_name'] ?? 'N/A' }}</strong>
            </td>
            <td class="field-label" style="width:10%; text-align:right; padding-right:4px;">Grade :</td>
            <td class="field-line" style="width:40%;">
                <strong>{{ $member->rank ?? $metadata['rank'] ?? 'Distributeur' }}</strong>
                <span style="font-size:11px; color:#555;">(Niv. {{ $member->rank_level ?? 1 }})</span>
            </td>
        </tr>
        <tr>
            <td class="field-label">PV :</td>
            <td class="field-line" style="width:34%;"><strong>{{ number_format($member->pv_balance ?? 0) }} PV</strong></td>
            <td class="field-label" style="width:10%; text-align:right; padding-right:4px;">Prix :</td>
            <td class="field-line" style="width:40%;"><strong>${{ number_format($member->package->price ?? $metadata['package_price'] ?? 0, 2) }}</strong></td>
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
    @php
        $sigName = $member->signature_name ?? $metadata['signature_name'] ?? null;
        $sigDate = $member->signature_date ?? $metadata['signature_date'] ?? null;
        $sigLoc  = $member->signature_location ?? $metadata['signature_location'] ?? null;
    @endphp
    <table class="signatures-table">
        <tr>
            <td>
                <strong>Signature du Demandeur (Membre) :</strong>
                <table class="signature-box">
                    <tr>
                        <td>
                            @if($sigName)
                                <strong>Nom :</strong> {{ $sigName }}<br>
                                <strong>Date :</strong> {{ $sigDate ? \Carbon\Carbon::parse($sigDate)->format('d/m/Y') : '' }}
                                &nbsp;|&nbsp; <strong>Lieu :</strong> {{ $sigLoc }}
                            @else
                                <span style="color:#999;">Lu et approuvé</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <strong>Signature &amp; Cachet du Parrain :</strong>
                <table class="signature-box">
                    <tr>
                        <td style="color:#999;">Validé par le parrain</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @include('partials.pdf.footer', ['pdfFooterLabel' => 'FORMULAIRE OFFICIEL – SALANG GROUP SARL'])

</div>
</body>
</html>