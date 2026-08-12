{{-- resources/views/admin/pos-reports/pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport POS - {{ date('d/m/Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Arial', sans-serif; 
            padding: 20px; 
            color: #333; 
            font-size: 11px;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #0E2F76; 
            padding-bottom: 10px; 
            margin-bottom: 15px;
        }
        .header h1 { 
            color: #0E2F76; 
            font-size: 20px;
            letter-spacing: 2px;
        }
        .header p { color: #666; font-size: 11px; margin: 2px 0; }
        .header .date { font-size: 12px; font-weight: bold; }
        
        .stats { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 10px;
            border-radius: 4px;
        }
        .stats .stat { text-align: center; }
        .stats .stat .number { font-size: 16px; font-weight: bold; color: #0E2F76; }
        .stats .stat .label { font-size: 9px; color: #666; text-transform: uppercase; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        table thead th {
            background: #0E2F76;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-green { color: #22c55e; }
        .text-primary { color: #0E2F76; }
        
        .footer { 
            text-align: center; 
            font-size: 9px; 
            color: #999; 
            margin-top: 20px; 
            border-top: 1px solid #e5e7eb; 
            padding-top: 10px;
        }
        
        .badge { 
            display: inline-block; 
            padding: 1px 6px; 
            border-radius: 4px; 
            font-size: 8px; 
            font-weight: bold;
        }
        .badge-success { background: #dcfce7; color: #22c55e; }
        .badge-warning { background: #fef3c7; color: #f59e0b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SALANG GROUP</h1>
        <p>Rapport des ventes POS</p>
        <p class="date">Date: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="number">{{ number_format($stats['total_orders'] ?? 0) }}</div>
            <div class="label">Commandes</div>
        </div>
        <div class="stat">
            <div class="number">${{ number_format($stats['total_sales'] ?? 0, 2) }}</div>
            <div class="label">Ventes</div>
        </div>
        <div class="stat">
            <div class="number">${{ number_format($stats['total_commissions'] ?? 0, 2) }}</div>
            <div class="label">Commissions</div>
        </div>
        <div class="stat">
            <div class="number">{{ number_format($stats['total_pv'] ?? 0) }}</div>
            <div class="label">PV distribués</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>N° commande</th>
                <th>Client</th>
                <th>Caissier</th>
                <th class="text-right">Total</th>
                <th class="text-right">PV</th>
                <th class="text-right">Commission</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders ?? [] as $order)
                @php
                    $totalPV = $order->items->sum(function($item) {
                        return ($item->pv_value ?? 0) * $item->quantity;
                    });
                    $commission = App\Models\Commission::where('order_id', $order->id)
                        ->where('source', 'pos')
                        ->first();
                @endphp
                <tr>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-primary">#{{ $order->order_number }}</td>
                    <td>{{ $order->user?->name ?? 'N/A' }}</td>
                    <td>{{ $order->cashier?->name ?? 'N/A' }}</td>
                    <td class="text-right font-bold">${{ number_format($order->total, 2) }}</td>
                    <td class="text-right text-green">{{ number_format($totalPV) }}</td>
                    <td class="text-right">${{ $commission ? number_format($commission->amount, 2) : '0.00' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Aucune donnée disponible</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Rapport généré automatiquement - Salang MLM v1.0</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>