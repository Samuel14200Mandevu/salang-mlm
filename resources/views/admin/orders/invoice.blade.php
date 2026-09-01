{{-- resources/views/admin/orders/invoice.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 30px;
            color: #1a1a1a;
            font-size: 12px;
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0A2A6C;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0A2A6C;
            font-size: 22px;
            letter-spacing: 2px;
            font-weight: 700;
        }
        .header .sub {
            color: #666;
            font-size: 11px;
            margin: 2px 0;
        }
        .header .invoice-number {
            font-size: 18px;
            color: #0A2A6C;
            font-weight: 700;
            margin-top: 8px;
        }
        .header .invoice-date {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }

        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 6px;
        }
        .badge-paid {
            background: #dcfce7;
            color: #1C7E4A;
        }
        .badge-pending {
            background: #fef3c7;
            color: #B54708;
        }

        .info {
            margin-bottom: 20px;
        }
        .info .row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info .row .label {
            color: #666;
            font-weight: 500;
        }
        .info .row .value {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table thead th {
            background: #0A2A6C;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        table tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .font-mono { font-family: monospace; }

        .total {
            border-top: 2px solid #0A2A6C;
            padding-top: 10px;
            margin-top: 10px;
        }
        .total .row {
            display: flex;
            justify-content: flex-end;
            padding: 3px 0;
        }
        .total .row .label {
            width: 150px;
            text-align: right;
            padding-right: 10px;
            color: #666;
        }
        .total .row .value {
            font-weight: 600;
            width: 100px;
            text-align: right;
        }
        .total .row.total {
            font-size: 16px;
            border-top: 2px solid #0A2A6C;
            padding-top: 8px;
            margin-top: 5px;
        }
        .total .row.total .value {
            color: #0A2A6C;
            font-size: 18px;
            font-weight: 700;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .footer .signature {
            margin-top: 4px;
            font-size: 9px;
            color: #666;
        }

        .actions {
            text-align: center;
            margin-top: 20px;
        }
        .actions .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.15s ease;
        }
        .actions .btn-print {
            background: #0A2A6C;
            color: white;
        }
        .actions .btn-print:hover {
            background: #061B4A;
        }
        .actions .btn-close {
            background: #f3f4f6;
            color: #333;
            border: 1px solid #d1d5db;
        }
        .actions .btn-close:hover {
            background: #e5e7eb;
        }
        .actions .btn + .btn {
            margin-left: 8px;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 15px; }
            .actions { display: none !important; }
        }

        @media (max-width: 600px) {
            body { padding: 15px; font-size: 11px; }
            .header h1 { font-size: 18px; }
            .header .invoice-number { font-size: 15px; }
            table thead th { font-size: 8px; padding: 5px 6px; }
            table tbody td { padding: 4px 6px; font-size: 11px; }
            .total .row.total { font-size: 14px; }
            .total .row.total .value { font-size: 15px; }
            .total .row .label { width: 100px; }
            .total .row .value { width: 70px; }
            .info .row { font-size: 11px; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>SALANG GROUP</h1>
        <div class="sub">Abidjan, Côte d'Ivoire</div>
        <div class="sub">Tel: +225 07 00 00 00 00</div>
        <div class="invoice-number">FACTURE #{{ $order->order_number }}</div>
        <div class="invoice-date">Date: {{ $order->created_at->format('d/m/Y H:i') }}</div>
        <span class="badge {{ $order->payment_status == 'completed' ? 'badge-paid' : 'badge-pending' }}">
            {{ $order->payment_status == 'completed' ? 'Payé' : 'En attente' }}
        </span>
    </div>

    <!-- Client Info -->
    <div class="info">
        <div class="row">
            <span class="label">Client</span>
            <span class="value">{{ $order->user->name ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span class="label">Email</span>
            <span>{{ $order->user->email ?? 'N/A' }}</span>
        </div>
        @if($order->shipping_address)
        <div class="row">
            <span class="label">Adresse</span>
            <span>{{ $order->shipping_address }}</span>
        </div>
        @endif
    </div>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Quantité</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right font-bold">${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="total">
        <div class="row">
            <span class="label">Sous-total</span>
            <span class="value">${{ number_format($order->subtotal, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">TVA (18%)</span>
            <span class="value">${{ number_format($order->tax, 2) }}</span>
        </div>
        @if($order->shipping > 0)
        <div class="row">
            <span class="label">Livraison</span>
            <span class="value">${{ number_format($order->shipping, 2) }}</span>
        </div>
        @endif
        @if($order->discount > 0)
        <div class="row">
            <span class="label">Réduction</span>
            <span class="value" style="color: #B91C1C;">-${{ number_format($order->discount, 2) }}</span>
        </div>
        @endif
        <div class="row total">
            <span class="label">TOTAL</span>
            <span class="value">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Merci pour votre confiance !</p>
        <p class="signature">Cette facture est générée automatiquement par le système Salang Group.</p>
    </div>

    <!-- Actions -->
    <div class="actions no-print">
        <button onclick="window.print()" class="btn btn-print">
            Imprimer
        </button>
        <button onclick="window.close()" class="btn btn-close">
            Fermer
        </button>
    </div>

</body>
</html>