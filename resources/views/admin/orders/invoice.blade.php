{{-- resources/views/admin/orders/invoice.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Arial', sans-serif; 
            padding: 30px; 
            color: #333; 
            font-size: 12px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #0E2F76; 
            padding-bottom: 15px; 
            margin-bottom: 20px;
        }
        .header h1 { 
            color: #0E2F76; 
            font-size: 22px;
            letter-spacing: 2px;
        }
        .header p { color: #666; font-size: 11px; margin: 2px 0; }
        
        .info { margin-bottom: 20px; }
        .info .row { display: flex; justify-content: space-between; padding: 3px 0; }
        .info .row .label { color: #666; }
        .info .row .value { font-weight: bold; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0;
        }
        table thead th {
            background: #0E2F76;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        table tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .total { 
            border-top: 2px solid #0E2F76; 
            padding-top: 10px; 
            margin-top: 10px;
        }
        .total .row { display: flex; justify-content: flex-end; padding: 3px 0; }
        .total .row .label { width: 150px; text-align: right; padding-right: 10px; }
        .total .row .value { font-weight: bold; width: 100px; text-align: right; }
        .total .row.total { font-size: 16px; border-top: 1px solid #333; padding-top: 8px; margin-top: 5px; }
        .total .row.total .value { color: #0E2F76; font-size: 18px; }
        
        .footer { 
            text-align: center; 
            font-size: 10px; 
            color: #999; 
            margin-top: 30px; 
            border-top: 1px solid #e5e7eb; 
            padding-top: 15px;
        }
        
        .badge { 
            display: inline-block; 
            padding: 2px 10px; 
            border-radius: 4px; 
            font-size: 10px; 
            font-weight: bold;
        }
        .badge-paid { background: #dcfce7; color: #22c55e; }
        .badge-pending { background: #fef3c7; color: #f59e0b; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SALANG GROUP</h1>
        <p>Abidjan, Côte d'Ivoire</p>
        <p>Tel: +225 07 00 00 00 00</p>
        <h2 style="margin-top: 10px; color: #0E2F76;">FACTURE #{{ $order->order_number }}</h2>
        <p>Date: {{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p>
            <span class="badge {{ $order->payment_status == 'completed' ? 'badge-paid' : 'badge-pending' }}">
                {{ $order->payment_status == 'completed' ? 'PAYÉ' : 'EN ATTENTE' }}
            </span>
        </p>
    </div>

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
            <span class="value" style="color: #ef4444;">-${{ number_format($order->discount, 2) }}</span>
        </div>
        @endif
        <div class="row total">
            <span class="label">TOTAL</span>
            <span class="value">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Merci pour votre confiance !</p>
        <p style="font-size: 9px;">Cette facture est générée automatiquement.</p>
    </div>

    <div style="text-align: center; margin-top: 20px;" class="no-print">
        <button onclick="window.print()" style="padding: 8px 20px; background: #0E2F76; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()" style="padding: 8px 20px; background: #f3f4f6; color: #333; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; margin-left: 8px;">
            ✕ Fermer
        </button>
    </div>
</body>
</html>