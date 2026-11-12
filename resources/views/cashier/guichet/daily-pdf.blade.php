{{-- resources/views/cashier/guichet/daily-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Retraits Guichet - Salang Group</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif, Arial;
            font-size: 14px;
            color: #000;
            padding: 16px 25px;
            background: #fff;
        }

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

        .section-header {
            font-size: 14.5px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 2px;
            margin: 10px 0 6px 0;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table th {
            background-color: #fff;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            padding: 5px 3px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 4px 3px;
            border-bottom: 1.5px dotted #000;
            border-left: 1px solid #ccc;
            border-right: 1px solid #ccc;
            text-align: center;
            vertical-align: middle;
            font-size: 12.5px;
        }
        .data-table td.text-left { text-align: left; }

        .total-row td {
            font-weight: bold;
            border-top: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
            font-size: 13px;
            background-color: #fff;
        }

        .amount-positive {
            color: #000;
            font-weight: bold;
        }
        .amount-zero {
            color: #666;
        }

        .empty-state {
            text-align: center;
            padding: 30px 0;
            color: #666;
            font-size: 13.5px;
        }

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
            background: #fff;
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

<div class="page-content">

    <div class="report-header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if(!empty($logoBase64))
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
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" alt="Salang Logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="main-title">Rapport des Retraits Guichet</div>
    <div class="form-meta">
        <div>Journée : <strong>{{ $reportDate->format('d/m/Y') }}</strong></div>
        <div>Généré le : <strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong></div>
    </div>

    <div class="section-header">Retraits remis en espèces — détail journalier</div>

    @if(count($withdrawals) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th style="width:10%;">Heure</th>
                <th style="width:12%;">Code ID</th>
                <th style="width:24%;">Nom Complet</th>
                <th style="width:12%;">Montant</th>
                <th style="width:16%;">N° Reçu</th>
                <th style="width:12%;">Caissier</th>
                <th style="width:8%;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($withdrawals as $w)
            <tr>
                <td>{{ $w->id }}</td>
                <td>{{ ($w->completed_at ?? $w->created_at)?->format('H:i') ?? '—' }}</td>
                <td><strong>{{ $w->user?->sponsor_id ?? 'N/A' }}</strong></td>
                <td class="text-left"><strong>{{ $w->user?->name ?? 'N/A' }}</strong></td>
                <td>
                    @if((float) $w->amount > 0)
                        <span class="amount-positive">${{ number_format((float) $w->amount, 2) }}</span>
                    @else
                        <span class="amount-zero">-</span>
                    @endif
                </td>
                <td>{{ $w->receipt_number ?: '—' }}</td>
                <td>{{ $w->cashier?->name ?? '—' }}</td>
                <td>{{ $w->status === 'completed' ? 'Remis' : ucfirst((string) $w->status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align:right; font-weight:bold;">NOMBRE : {{ count($withdrawals) }}</td>
                <td colspan="4" style="text-align:left; font-weight:bold;">&nbsp;</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="text-align:right; font-weight:bold; color:#0E2F76;">TOTAL GÉNÉRAL :</td>
                <td style="color:#0E2F76; font-size:14px;"><strong>${{ number_format($totalAmount, 2) }}</strong></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">
        <p>Aucun retrait guichet remis pour le {{ $reportDate->format('d/m/Y') }}</p>
    </div>
    @endif

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
