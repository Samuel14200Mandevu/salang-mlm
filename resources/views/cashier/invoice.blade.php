{{-- resources/views/cashier/invoice.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            padding: 20px; 
            color: #333; 
            font-size: 12px;
            background: #fff;
            max-width: 300px;
            margin: 0 auto;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px dashed #333; 
            padding-bottom: 10px; 
            margin-bottom: 10px;
        }
        .header h1 { 
            color: #0E2F76; 
            font-size: 18px;
            letter-spacing: 2px;
        }
        .header p { color: #666; font-size: 10px; margin: 2px 0; }
        .info { margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .info .row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 11px; }
        .info .row .label { color: #666; }
        .info .row .value { font-weight: bold; }
        .items { margin: 10px 0; }
        .items .item { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dotted #eee; font-size: 11px; }
        .items .item .qty { color: #666; width: 30px; }
        .items .item .name { flex: 1; padding: 0 5px; }
        .items .item .price { font-weight: bold; }
        .total { border-top: 2px dashed #333; padding-top: 10px; margin-top: 10px; }
        .total .row { display: flex; justify-content: space-between; padding: 3px 0; }
        .total .row.total { font-size: 16px; font-weight: bold; border-top: 1px solid #333; padding-top: 5px; margin-top: 5px; color: #0E2F76; }
        .sponsor-info { background: #f8fafc; padding: 8px; border-radius: 4px; margin: 10px 0; font-size: 10px; border: 1px solid #e5e7eb; }
        .sponsor-info strong { color: #0E2F76; }
        .footer { text-align: center; font-size: 10px; color: #999; margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px; }
        .payment-info { font-size: 10px; padding: 8px; background: #f8fafc; border-radius: 4px; margin: 10px 0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-completed { background: #dcfce7; color: #22c55e; }
        .pv-info { background: #f0fdf4; padding: 8px; border-radius: 4px; margin: 5px 0; text-align: center; font-size: 10px; border: 1px solid #bbf7d0; }
        .pv-info strong { color: #0E2F76; }
        .commission-info { background: #fef3c7; padding: 8px; border-radius: 4px; margin: 5px 0; text-align: center; font-size: 10px; border: 1px solid #fcd34d; }
        .commission-info strong { color: #d97706; }
        @media print { body { padding: 10px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>SALANG GROUP SARL</h1>
        <p>
            N° ID.NAT: 22-7300- N634640, NRCM: CD/BKVIRCM/20-8-001165001
            N° IMPORT-EXPORT: 0024/CBX-21/1000439SK/Z
        </p>
        <p>Contacts: +243 975 220 079</p>
        <p style="font-size: 14px; font-weight: bold; margin-top: 5px;">
            FACTURE #{{ $order->order_number }}
        </p>
        <p style="font-size: 10px;">{{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p><span class="badge badge-completed"> PAYÉ</span></p>
    </div>

    <div class="info">
        <div class="row"><span class="label">Client</span><span class="value">{{ $order->user->name }}</span></div>
        <div class="row"><span class="label">Email</span><span>{{ $order->user->email }}</span></div>
        @if($order->user->phone && $order->user->phone != 'N/A')
        <div class="row"><span class="label">Tél</span><span>{{ $order->user->phone }}</span></div>
        @endif
    </div>

    @if(isset($sponsor) && $sponsor)
    <div class="sponsor-info">
        <div style="display: flex; justify-content: space-between;">
            <span>Code parrain</span><strong>{{ $sponsor->sponsor_id }}</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 2px;">
            <span>Membre</span><strong>{{ $sponsor->name }}</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 2px;">
            <span>Grade</span><strong>{{ $sponsor->rank ?? 'Distributeur' }}</strong>
        </div>
    </div>
    @endif

    <div class="items">
        <div style="font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 4px; margin-bottom: 4px; display: flex; justify-content: space-between;">
            <span>PRODUIT</span><span>PRIX</span>
        </div>
        @foreach($order->items as $item)
        <div class="item">
            <span class="qty">{{ $item->quantity }}x</span>
            <span class="name">{{ Str::limit($item->name, 20) }}</span>
            <span class="price">${{ number_format($item->total, 2) }}</span>
        </div>
        @endforeach
    </div>

    <div class="total">
        <div class="row total"><span>TOTAL</span><span>${{ number_format($order->total, 2) }}</span></div>
    </div>

    @php
        $totalPv = $order->items->sum(function($item) {
            return ($item->pv_value ?? 0) * $item->quantity;
        });
        $commissionAmount = isset($order->metadata['commission_amount']) ? $order->metadata['commission_amount'] : 0;
    @endphp

    @if($totalPv > 0)
    <div class="pv-info">
         <strong>{{ $totalPv }} PV</strong> crédités au parrain
    </div>
    @endif

    @if($commissionAmount > 0)
    <div class="commission-info">
         <strong>${{ number_format($commissionAmount, 2) }}</strong> de commission CASH pour le parrain
    </div>
    @endif

    <div class="payment-info">
        <div style="display: flex; justify-content: space-between;">
            <span>Paiement</span><span>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 2px;">
            <span>Statut</span><span style="color: #22c55e; font-weight: bold;">Payé</span>
        </div>
        @if(isset($order->metadata['cashier_name']))
        <div style="display: flex; justify-content: space-between; margin-top: 2px;">
            <span>Caissier</span><span>{{ $order->metadata['cashier_name'] }}</span>
        </div>
        @endif
        @if(isset($order->metadata['multi_products']) && $order->metadata['multi_products'])
        <div style="display: flex; justify-content: space-between; margin-top: 2px;">
            <span>Type</span><span style="color: #0E2F76; font-weight: bold;">Multi-produits</span>
        </div>
        @endif
    </div>

    <div class="footer">
        <p>Merci pour votre confiance !</p>
        <p style="font-size: 8px; margin-top: 2px;">Cette facture est générée automatiquement.</p>
        <p style="font-size: 8px; margin-top: 2px; color: #ccc;">Salang MLM v1.0</p>
    </div>

    <div style="text-align: center; margin-top: 20px;" class="no-print">
        <button onclick="window.print()" style="padding: 8px 20px; background: #0E2F76; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;">
             Imprimer
        </button>
        <a href="{{ route('cashier.pos') }}" style="padding: 8px 20px; background: #22c55e; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block; margin-left: 8px;">
             Nouvelle vente
        </a>
        <a href="{{ route('cashier.orders') }}" style="padding: 8px 20px; background: #f3f4f6; color: #333; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block; margin-left: 8px;">
             Commandes
        </a>
    </div>
</body>
</html>