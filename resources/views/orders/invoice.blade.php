<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-title h1 {
            color: #6366f1;
            margin: 0;
            font-size: 28px;
        }
        .invoice-title p {
            color: #666;
            margin: 5px 0 0;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info p {
            margin: 3px 0;
            font-size: 13px;
        }
        .invoice-info strong {
            color: #333;
        }
        .company-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
        }
        .company-info div p {
            margin: 3px 0;
            font-size: 13px;
            color: #666;
        }
        .company-info div strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #6366f1;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 20px;
            border-top: 2px solid #6366f1;
            padding-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .total-box {
            width: 300px;
        }
        .total-box .row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 14px;
        }
        .total-box .row.total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 5px;
            color: #6366f1;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-completed { background: #dcfce7; color: #22c55e; }
        .status-pending { background: #fef3c7; color: #f59e0b; }
        .status-processing { background: #dbeafe; color: #3b82f6; }
        .status-cancelled { background: #fee2e2; color: #ef4444; }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="invoice-title">
            <h1>🧾 FACTURE</h1>
            <p>Salang Group • E-Commerce & MLM</p>
        </div>
        <div class="invoice-info">
            <p><strong>N° Facture:</strong> #{{ $order->order_number }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('d/m/Y') }}</p>
            <p><strong>Statut:</strong> 
                <span class="status-badge status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
        </div>
    </div>

    <div class="company-info">
        <div>
            <p><strong>Salang Group</strong></p>
            <p>Abidjan, Côte d'Ivoire</p>
            <p>Email: contact@salang.com</p>
            <p>Tel: +225 07 00 00 00 00</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Client</strong></p>
            <p>{{ $order->user->name }}</p>
            <p>{{ $order->user->email }}</p>
            @if($order->user->phone)
                <p>{{ $order->user->phone }}</p>
            @endif
        </div>
    </div>

    <h3 style="margin: 20px 0 10px;">🛍️ Articles commandés</h3>
    
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->name }}
                    @if($item->package_id)
                        <span style="color: #6366f1; font-size: 11px;">(Package)</span>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                <td class="text-right">${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-box">
            <div class="row">
                <span>Sous-total</span>
                <span>${{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="row">
                <span>TVA (18%)</span>
                <span>${{ number_format($order->tax, 2) }}</span>
            </div>
            <div class="row">
                <span>Livraison</span>
                <span>${{ number_format($order->shipping, 2) }}</span>
            </div>
            @if($order->discount > 0)
            <div class="row" style="color: #ef4444;">
                <span>Réduction</span>
                <span>-${{ number_format($order->discount, 2) }}</span>
            </div>
            @endif
            <div class="row total">
                <span>TOTAL</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    @if($order->shipping_address)
    <div style="margin-top: 30px; padding: 15px; background: #f8fafc; border-radius: 8px;">
        <p><strong>📦 Adresse de livraison</strong></p>
        <p style="margin: 5px 0 0; color: #666;">{{ nl2br($order->shipping_address) }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Merci pour votre confiance !</p>
        <p style="font-size: 10px;">Cette facture est générée automatiquement par Salang MLM.</p>
    </div>
</body>
</html>