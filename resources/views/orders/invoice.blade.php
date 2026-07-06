<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            margin: 20px;
            color: #333;
            font-size: 13px;
            background: #fff;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #5ab638;
            padding-bottom: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .invoice-title h1 {
            color: #5ab638;
            margin: 0;
            font-size: 24px;
        }
        .invoice-title p {
            color: #666;
            margin: 5px 0 0;
            font-size: 13px;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info p {
            margin: 3px 0;
            font-size: 12px;
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
            flex-wrap: wrap;
            gap: 10px;
        }
        .company-info div p {
            margin: 3px 0;
            font-size: 12px;
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
            background: #5ab638;
            color: white;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 20px;
            border-top: 2px solid #5ab638;
            padding-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .total-box {
            width: 280px;
        }
        .total-box .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }
        .total-box .row.total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 8px;
            margin-top: 4px;
            color: #5ab638;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-completed {
            background: #dcfce7;
            color: #22c55e;
        }
        .status-pending {
            background: #fef3c7;
            color: #f59e0b;
        }
        .status-processing {
            background: #dbeafe;
            color: #3b82f6;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #ef4444;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        @media print {
            body {
                margin: 10px;
            }
            .no-print {
                display: none;
            }
        }
        @media (max-width: 600px) {
            body {
                margin: 10px;
                font-size: 11px;
            }
            .invoice-header {
                flex-direction: column;
                text-align: center;
            }
            .invoice-info {
                text-align: center;
            }
            .company-info {
                flex-direction: column;
                text-align: center;
            }
            .total-section {
                justify-content: center;
            }
            .total-box {
                width: 100%;
            }
            th, td {
                padding: 5px 8px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="invoice-title">
            <h1>INVOICE</h1>
            <p>Salang Group • E-Commerce & MLM</p>
        </div>
        <div class="invoice-info">
            <p><strong>Invoice #:</strong> {{ $order->order_number }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('d/m/Y') }}</p>
            <p><strong>Status:</strong> 
                <span class="status-badge status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
        </div>
    </div>

    <div class="company-info">
        <div>
            <p><strong>Salang Group</strong></p>
            <p>Abidjan, Cote d'Ivoire</p>
            <p>Email: contact@salang.com</p>
            <p>Tel: +225 07 00 00 00 00</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Customer</strong></p>
            <p>{{ $order->user->name }}</p>
            <p>{{ $order->user->email }}</p>
            @if($order->user->phone)
                <p>{{ $order->user->phone }}</p>
            @endif
        </div>
    </div>

    <h3 style="margin: 20px 0 10px; font-size: 16px;">Order Items</h3>
    
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->name }}
                    @if($item->package_id)
                        <span style="color: #5ab638; font-size: 10px;">(Package)</span>
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
                <span>Subtotal</span>
                <span>${{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="row">
                <span>Tax (18%)</span>
                <span>${{ number_format($order->tax, 2) }}</span>
            </div>
            <div class="row">
                <span>Shipping</span>
                <span>${{ number_format($order->shipping, 2) }}</span>
            </div>
            @if($order->discount > 0)
            <div class="row" style="color: #ef4444;">
                <span>Discount</span>
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
        <p><strong>Shipping Address</strong></p>
        <p style="margin: 5px 0 0; color: #666;">{{ nl2br($order->shipping_address) }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your trust!</p>
        <p style="font-size: 10px;">This invoice is automatically generated by Salang MLM.</p>
    </div>
</body>
</html>