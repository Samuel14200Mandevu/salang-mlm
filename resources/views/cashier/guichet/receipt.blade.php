{{-- Reçu retrait guichet — même style ticket que facture commande --}}
@php
    $member = $withdrawal->user;
    $receiptNo = $withdrawal->receipt_number ?: ('GUICH-'.$withdrawal->id);
    $receiptDate = $withdrawal->completed_at ?? $withdrawal->created_at ?? now();
    $statusLabel = match ($withdrawal->status) {
        'completed' => 'REMIS',
        'pending' => 'EN ATTENTE',
        'cancelled' => 'ANNULE',
        'rejected' => 'REJETE',
        default => strtoupper((string) $withdrawal->status),
    };
    $statusPaid = $withdrawal->status === 'completed';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Reçu retrait {{ $receiptNo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', monospace;
            padding: 10px;
            color: #1a1a1a;
            font-size: 11px;
            background: #f5f5f5;
            max-width: 360px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .ticket {
            background: #ffffff;
            padding: 12px 14px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            border: 1px solid #e5e7eb;
            width: 100%;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header .store-logo {
            margin: 0 auto 4px;
            max-width: 70px;
        }
        .header .store-logo img {
            width: 100%;
            height: auto;
            display: block;
        }
        .header .store-name {
            color: #0E2F76;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 2px;
        }
        .header .store-sub {
            font-size: 7px;
            color: #666;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .header .store-contact {
            font-size: 7px;
            color: #555;
            line-height: 1.5;
            margin-top: 4px;
        }
        .header .store-address {
            font-size: 6.5px;
            color: #666;
            line-height: 1.4;
            margin-top: 3px;
            background: #f8fafc;
            padding: 4px 6px;
            border-radius: 3px;
        }
        .header .store-reg {
            font-size: 6px;
            color: #888;
            line-height: 1.4;
            margin-top: 3px;
            border-top: 1px dotted #e5e7eb;
            padding-top: 4px;
        }
        .header .ticket-number {
            font-size: 13px;
            font-weight: 700;
            color: #0E2F76;
            margin-top: 5px;
            letter-spacing: 1px;
        }
        .header .ticket-date {
            font-size: 8px;
            color: #888;
            margin-top: 2px;
        }
        .header .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 4px;
            background: {{ $statusPaid ? '#dcfce7' : '#fef3c7' }};
            color: {{ $statusPaid ? '#16a34a' : '#d97706' }};
        }
        .source-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 3px;
            background: #dbeafe;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .separator-dashed { border-top: 1px dashed #ddd; margin: 5px 0; }

        .info { margin-bottom: 8px; border-bottom: 1px dashed #ddd; padding-bottom: 6px; }
        .info .row { display: flex; justify-content: space-between; gap: 8px; padding: 2px 0; font-size: 9px; }
        .info .row .label { color: #888; flex-shrink: 0; }
        .info .row .value { font-weight: 600; color: #1a1a1a; text-align: right; }

        .amount-box {
            background: #f0f7ff;
            border: 1px solid #bfdbfe;
            border-left: 3px solid #0E2F76;
            border-radius: 4px;
            padding: 8px 10px;
            margin: 8px 0;
            text-align: center;
        }
        .amount-box .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .amount-box .value {
            font-size: 18px;
            font-weight: 900;
            color: #0E2F76;
            margin-top: 2px;
        }

        .total { border-top: 2px dashed #ccc; padding-top: 6px; margin-top: 6px; }
        .total .row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 9px; }
        .total .row .label { color: #666; }
        .total .row .value { color: #1a1a1a; }
        .total .row.total {
            font-size: 14px;
            font-weight: 900;
            border-top: 2px double #1a1a1a;
            padding-top: 4px;
            margin-top: 3px;
        }
        .total .row.total .label { color: #1a1a1a; }
        .total .row.total .value { color: #0E2F76; }

        .payment-info {
            font-size: 8px;
            padding: 6px 8px;
            background: #f8fafc;
            border-radius: 4px;
            margin: 6px 0;
            border: 1px solid #e5e7eb;
        }
        .payment-info .row { display: flex; justify-content: space-between; padding: 1px 0; gap: 8px; }
        .payment-info .row .label { color: #888; }
        .payment-info .row .value { font-weight: 600; text-align: right; }
        .payment-info .row .value.paid { color: #22c55e; }

        .footer {
            text-align: center;
            font-size: 7px;
            color: #999;
            margin-top: 8px;
            border-top: 1px dashed #ddd;
            padding-top: 6px;
            line-height: 1.5;
        }
        .footer .thank-you {
            font-size: 10px;
            font-weight: 700;
            color: #0E2F76;
            letter-spacing: 1px;
        }
        .footer .barcode {
            font-size: 14px;
            letter-spacing: 2px;
            color: #333;
            font-weight: 700;
            margin: 3px 0;
        }

        .no-print {
            text-align: center;
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
        }
        .no-print .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
            flex: 1;
            min-width: 80px;
            max-width: 150px;
            text-align: center;
        }
        .no-print .btn:active { transform: scale(0.95); }
        .btn-print { background: #0E2F76; color: white; }
        .btn-back { background: #f3f4f6; color: #333; border: 1px solid #d1d5db; }

        @media (max-width: 480px) {
            body {
                padding: 5px;
                max-width: 100%;
                justify-content: flex-start;
                padding-top: 10px;
            }
            .ticket {
                padding: 10px 12px 14px;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            }
            .header .store-name { font-size: 14px; }
            .header .store-logo { max-width: 55px; }
            .header .ticket-number { font-size: 11px; }
            .amount-box .value { font-size: 16px; }
            .total .row.total { font-size: 12px; }
            .no-print .btn {
                padding: 8px 12px;
                font-size: 10px;
                min-width: 60px;
                max-width: 120px;
            }
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
                max-width: 100%;
                justify-content: center;
            }
            .ticket {
                border-radius: 0;
                box-shadow: none;
                border: none;
                padding: 8px 10px 12px;
            }
            .no-print { display: none !important; }
            .header .store-logo { max-width: 60px; }
        }
    </style>
</head>
<body>

<div class="ticket">
    <div class="header">
        <div class="store-logo">
            <img src="{{ asset('images/salang_logo.png') }}" alt="Salang Group Logo">
        </div>
        <div class="store-name">SALANG GROUP SARL</div>
        <div class="store-sub">E-COMMERCE &amp; MLM</div>
        <div class="store-contact">
            <strong>Tel:</strong> +243 975 220 079<br>
            <strong>Email:</strong> support@salanggroup.com<br>
            <strong>Site:</strong> www.salanggroup.com
        </div>
        <div class="store-address">
            Rond Point CHIKUDU, Batiment KBS au 3eme Niveau
        </div>
        <div class="store-reg">
            <strong>N° ID.NAT:</strong> 22-7300-N634640<br>
            <strong>N° RCCM:</strong> CD/BKVIRCM/20-8-001165001<br>
            <strong>N° IMPORT-EXPORT:</strong> 0024/CBX-21/1000439SK/Z
        </div>

        <div class="separator-dashed"></div>

        <div style="font-size: 8px; font-weight: 700; color: #0E2F76; letter-spacing: 1px;">
            REÇU RETRAIT GUICHET
        </div>
        <div style="font-size: 7px; color: #888; margin-top: 2px;">
            Goma, le {{ $receiptDate->format('d/m/Y') }}
        </div>

        <div class="ticket-number">{{ $receiptNo }}</div>
        <div class="ticket-date">{{ $receiptDate->format('d/m/Y H:i') }}</div>

        <span class="source-badge">Espèces · Guichet</span>
        <span class="status-badge">{{ $statusLabel }}</span>
    </div>

    <div class="info">
        <div class="row">
            <span class="label">Membre</span>
            <span class="value">{{ $member->name ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">N° ID</span>
            <span class="value">{{ $member->sponsor_id ?? '—' }}</span>
        </div>
        @if(filled($member?->phone))
            <div class="row">
                <span class="label">Tel</span>
                <span class="value">{{ $member->phone }}</span>
            </div>
        @endif
        @if(filled($member?->email))
            <div class="row">
                <span class="label">Email</span>
                <span class="value">{{ $member->email }}</span>
            </div>
        @endif
        <div class="row">
            <span class="label">Retrait N°</span>
            <span class="value">#{{ $withdrawal->id }}</span>
        </div>
    </div>

    <div class="amount-box">
        <div class="label">Montant remis</div>
        <div class="value">${{ number_format((float) $withdrawal->amount, 2) }}</div>
    </div>

    <div class="total">
        <div class="row">
            <span class="label">Montant</span>
            <span class="value">${{ number_format((float) $withdrawal->amount, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">Frais</span>
            <span class="value">${{ number_format((float) ($withdrawal->fee ?? 0), 2) }}</span>
        </div>
        <div class="row total">
            <span class="label">NET REMIS</span>
            <span class="value">${{ number_format((float) ($withdrawal->net_amount ?? $withdrawal->amount), 2) }}</span>
        </div>
    </div>

    <div class="payment-info">
        <div class="row">
            <span class="label">Mode</span>
            <span class="value">Espèces (guichet)</span>
        </div>
        <div class="row">
            <span class="label">Statut</span>
            <span class="value {{ $statusPaid ? 'paid' : '' }}">{{ $statusLabel }}</span>
        </div>
        <div class="row">
            <span class="label">Caissier</span>
            <span class="value">{{ $withdrawal->cashier->name ?? auth()->user()->name }}</span>
        </div>
        @if($withdrawal->completed_at)
            <div class="row">
                <span class="label">Remis le</span>
                <span class="value">{{ $withdrawal->completed_at->format('d/m/Y H:i') }}</span>
            </div>
        @endif
    </div>

    <div class="footer">
        <div class="thank-you">MERCI — SALANG HEALTH CARE</div>
        <div class="barcode">*{{ $receiptNo }}*</div>
        <div>Conservez ce reçu comme preuve de retrait.</div>
        <div>www.salanggroup.com</div>
    </div>
</div>

<div class="no-print">
    <button type="button" class="btn btn-print" onclick="window.print()">Imprimer</button>
    <a href="{{ route('cashier.guichet.show', $withdrawal->id) }}" class="btn btn-back">Retour</a>
</div>

@if(!empty($autoPrint))
<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 350);
    });
</script>
@endif

</body>
</html>
