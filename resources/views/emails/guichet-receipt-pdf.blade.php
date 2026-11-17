{{-- Reçu PDF joint à l’e-mail membre (DomPDF) --}}
@php
    $member = $withdrawal->user;
    $receiptNo = $withdrawal->receipt_number ?: ('GUICH-'.$withdrawal->id);
    $receiptDate = $withdrawal->completed_at ?? $withdrawal->created_at ?? now();
    $phaseLabel = null;
    if (is_string($withdrawal->notes ?? null) && preg_match('/\[bonus_phase=(direct|il)\]/', $withdrawal->notes, $m)) {
        $phaseLabel = $m[1] === 'il' ? 'Indirect & Leadership' : 'Direct';
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $receiptNo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            padding: 24px;
        }
        .title {
            text-align: center;
            color: #0A254E;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .sub {
            text-align: center;
            color: #C5A04D;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 14px;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .meta td { padding: 3px 0; }
        .meta .l { color: #666; width: 38%; }
        .meta .v { font-weight: bold; text-align: right; }
        .box {
            border: 1.5px solid #0A254E;
            padding: 12px;
            text-align: center;
            margin: 14px 0;
        }
        .box .lbl {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 4px;
        }
        .box .amt {
            font-size: 22px;
            font-weight: bold;
            color: #0A254E;
        }
        .foot {
            margin-top: 18px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="title">Salang Group SARL</div>
    <div class="sub">Reçu de retrait guichet</div>

    <table class="meta">
        <tr>
            <td class="l">N° reçu</td>
            <td class="v">{{ $receiptNo }}</td>
        </tr>
        <tr>
            <td class="l">Date</td>
            <td class="v">{{ $receiptDate->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="l">Membre</td>
            <td class="v">{{ $member->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="l">Code membre</td>
            <td class="v">{{ $member->sponsor_id ?? '—' }}</td>
        </tr>
        @if($phaseLabel)
        <tr>
            <td class="l">Type de paiement</td>
            <td class="v">{{ $phaseLabel }}</td>
        </tr>
        @endif
        <tr>
            <td class="l">Mode</td>
            <td class="v">Espèces · Guichet</td>
        </tr>
        <tr>
            <td class="l">Caissier</td>
            <td class="v">{{ $withdrawal->cashier?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="l">Statut</td>
            <td class="v">REMIS</td>
        </tr>
    </table>

    <div class="box">
        <div class="lbl">Montant remis</div>
        <div class="amt">${{ number_format((float) $withdrawal->amount, 2) }}</div>
    </div>

    <div class="foot">
        Conservez ce reçu comme preuve de retrait.<br>
        support@salanggroup.com · www.salanggroup.com
    </div>
</body>
</html>
