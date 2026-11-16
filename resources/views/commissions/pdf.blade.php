<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport des Commissions</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background: #fff;
        }
        @include('partials.pdf._styles')
        @include('partials.pdf._letterhead_full_bleed')
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h1 {
            color: #0E2F76;
            margin: 0;
            font-size: 24px;
        }
        .report-title p {
            color: #666;
            font-size: 14px;
            margin: 5px 0 0;
        }
        .summary {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-around;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-size: 12px;
            color: #666;
        }
        .summary-item .value {
            font-size: 20px;
            font-weight: bold;
            color: #5ab638;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #5ab638;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        tr:hover {
            background: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .badge-paid {
            color: #22c55e;
            font-weight: bold;
        }
        .badge-pending {
            color: #f59e0b;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background: #f8fafc;
        }
        .total-row td {
            border-top: 2px solid #5ab638;
        }
        @page {
            size: A4 portrait;
            margin: 0 0 12mm 0;
        }
    </style>
</head>
<body>
    @include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])
    <div class="page-content-padded">
    <div class="report-title">
        <h1>Rapport des Commissions</h1>
        <p>{{ $user->name }} • Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">Total des commissions</div>
            <div class="value">${{ number_format($total, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Nombre de commissions</div>
            <div class="value">{{ $commissions->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Moyenne par commission</div>
            <div class="value">${{ number_format($commissions->count() > 0 ? $total / $commissions->count() : 0, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>De</th>
                <th>Montant</th>
                <th>%</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commissions as $commission)
            <tr>
                <td>{{ $commission->id }}</td>
                <td>{{ ucfirst($commission->type) }}</td>
                <td>{{ $commission->fromUser->name ?? 'Système' }}</td>
                <td class="text-right">${{ number_format($commission->amount, 2) }}</td>
                <td>{{ $commission->percentage }}%</td>
                <td>
                    <span class="{{ $commission->status == 'paid' ? 'badge-paid' : 'badge-pending' }}">
                        {{ $commission->status == 'paid' ? 'Payé' : 'En attente' }}
                    </span>
                </td>
                <td>{{ $commission->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">${{ number_format($total, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])
    </div>
</body>
</html>