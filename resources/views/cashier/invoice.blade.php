{{-- resources/views/cashier/invoice.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Facture #{{ $order->order_number }}</title>
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
        
        /* ===== EN-TETE ===== */
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
            background: #dcfce7;
            color: #22c55e;
        }
        
        /* ===== SOURCE BADGE ===== */
        .source-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .source-badge-pos {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .source-badge-mlm {
            background: #dbeafe;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .source-badge-web {
            background: #f3e8ff;
            color: #7c3aed;
            border: 1px solid #e9d5ff;
        }
        
        /* ===== SEPARATEURS ===== */
        .separator-dashed { border-top: 1px dashed #ddd; margin: 5px 0; }
        .separator-dotted { border-top: 1px dotted #eee; margin: 3px 0; }
        
        /* ===== INFOS ===== */
        .info { margin-bottom: 8px; border-bottom: 1px dashed #ddd; padding-bottom: 6px; }
        .info .row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 9px; }
        .info .row .label { color: #888; }
        .info .row .value { font-weight: 600; color: #1a1a1a; }
        
        /* ===== SPONSOR ===== */
        .sponsor-info { 
            background: #f0f7ff; 
            padding: 6px 8px; 
            border-radius: 4px; 
            margin: 6px 0; 
            font-size: 8px; 
            border-left: 3px solid #0E2F76;
        }
        .sponsor-info .row { display: flex; justify-content: space-between; padding: 1px 0; }
        .sponsor-info .row .label { color: #666; }
        .sponsor-info .row .value { font-weight: 700; color: #0E2F76; }
        
        /* ===== ARTICLES ===== */
        .items { margin: 6px 0; }
        .items .item-header {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            color: #888;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            margin-bottom: 3px;
        }
        .items .item { 
            display: flex; 
            justify-content: space-between; 
            padding: 2px 0; 
            border-bottom: 1px dotted #f0f0f0; 
            font-size: 9px; 
        }
        .items .item:last-child { border-bottom: none; }
        .items .item .qty { color: #888; width: 22px; font-weight: 600; }
        .items .item .name { flex: 1; padding: 0 4px; }
        .items .item .price { font-weight: 600; white-space: nowrap; }
        .items .item .package-tag { font-size: 6px; color: #0E2F76; font-weight: 700; }
        
        /* ===== TOTAUX ===== */
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
        .total .row.discount .value { color: #ef4444; }
        
        /* ===== PV & COMMISSIONS ===== */
        .pv-info { 
            background: #f0fdf4; 
            padding: 4px 8px; 
            border-radius: 4px; 
            margin: 4px 0; 
            text-align: center; 
            font-size: 8px; 
            border: 1px solid #bbf7d0;
        }
        .pv-info strong { color: #16a34a; }
        
        .commission-info { 
            background: #fef3c7; 
            padding: 4px 8px; 
            border-radius: 4px; 
            margin: 4px 0; 
            text-align: center; 
            font-size: 8px; 
            border: 1px solid #fcd34d;
        }
        .commission-info strong { color: #d97706; }
        
        /* ===== PAIEMENT ===== */
        .payment-info { 
            font-size: 8px; 
            padding: 6px 8px; 
            background: #f8fafc; 
            border-radius: 4px; 
            margin: 6px 0;
            border: 1px solid #e5e7eb;
        }
        .payment-info .row { display: flex; justify-content: space-between; padding: 1px 0; }
        .payment-info .row .label { color: #888; }
        .payment-info .row .value { font-weight: 600; }
        .payment-info .row .value.paid { color: #22c55e; }
        
        /* ===== PIED ===== */
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
        
        /* ===== BOUTONS MOBILE ===== */
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
        
        /* ============================================================
        MEDIA QUERIES - MOBILE
        ============================================================ */
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
            .header .store-contact { font-size: 6.5px; }
            .header .store-address { font-size: 6px; padding: 3px 4px; }
            .header .store-reg { font-size: 5.5px; }
            .header .ticket-number { font-size: 11px; }
            
            .info .row { font-size: 8px; }
            .items .item { font-size: 8px; }
            .items .item-header { font-size: 7px; }
            .total .row { font-size: 8px; }
            .total .row.total { font-size: 12px; }
            
            .payment-info { font-size: 7px; padding: 4px 6px; }
            .footer { font-size: 6px; }
            .footer .thank-you { font-size: 9px; }
            .footer .barcode { font-size: 12px; }
            
            .no-print .btn {
                padding: 8px 12px;
                font-size: 10px;
                min-width: 60px;
                max-width: 120px;
            }
        }
        
        @media (max-width: 360px) {
            .ticket { padding: 8px 8px 12px; }
            .header .store-name { font-size: 12px; }
            .header .store-logo { max-width: 45px; }
            .header .ticket-number { font-size: 10px; }
            .items .item { font-size: 7px; padding: 1px 0; }
            .total .row.total { font-size: 10px; }
            .no-print .btn {
                padding: 6px 10px;
                font-size: 9px;
                min-width: 50px;
                max-width: 100px;
            }
        }
        
        /* ============================================================
        IMPRESSION
        ============================================================ */
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

    <!-- ============================================================
    EN-TETE - SALANG GROUP
    ============================================================ -->
    <div class="header">
        <!-- LOGO -->
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
            FACTURE
        </div>
        <div style="font-size: 7px; color: #888; margin-top: 2px;">
            Goma, le {{ $order->created_at->format('d/m/Y') }}
        </div>
        
        <div class="ticket-number">#{{ $order->order_number }}</div>
        <div class="ticket-date">{{ $order->created_at->format('d/m/Y H:i') }}</div>
        
        {{-- SOURCE BADGE --}}
        @if($order->source == 'pos')
            <span class="source-badge source-badge-pos">POS (Guichet)</span>
        @elseif($order->source == 'web' || $order->source == 'online')
            <span class="source-badge source-badge-web">En ligne</span>
        @else
            <span class="source-badge source-badge-mlm">MLM</span>
        @endif
        
        <span class="status-badge">PAYE</span>
    </div>

    <!-- ============================================================
    CLIENT / MEMBRE - AVEC ADRESSE, VILLE, PAYS
    ============================================================ -->
    <div class="info">
        <div class="row">
            <span class="label">
                @if($order->source == 'pos')
                    Client
                @elseif($order->source == 'web' || $order->source == 'online')
                    Client
                @else
                    Membre
                @endif
            </span>
            <span class="value">{{ $order->user->name }}</span>
        </div>
        <div class="row"><span class="label">Email</span><span>{{ $order->user->email ?? 'N/A' }}</span></div>
        @if($order->user->phone && $order->user->phone != 'N/A')
        <div class="row"><span class="label">Tel</span><span>{{ $order->user->phone }}</span></div>
        @endif
        @if($order->user->address)
        <div class="row"><span class="label">Adresse</span><span>{{ $order->user->address }}</span></div>
        @endif
        @if($order->user->city)
        <div class="row"><span class="label">Ville</span><span>{{ $order->user->city }}</span></div>
        @endif
        @if($order->user->country)
        <div class="row"><span class="label">Pays</span><span>{{ $order->user->country }}</span></div>
        @endif
        <div class="row"><span class="label">N° ID</span><span>{{ $order->user->sponsor_id ?? 'N/A' }}</span></div>
        <div class="row">
            <span class="label">Source</span>
            <span class="value">
                @if($order->source == 'pos')
                    <span style="color: #16a34a;">POS (Guichet)</span>
                @elseif($order->source == 'web' || $order->source == 'online')
                    <span style="color: #7c3aed;">En ligne</span>
                @else
                    <span style="color: #2563eb;">MLM</span>
                @endif
            </span>
        </div>
    </div>

    <!-- ============================================================
    PARRAIN (SPONSOR)
    ============================================================ -->
    @if(isset($sponsor) && $sponsor)
    <div class="sponsor-info">
        <div class="row"><span class="label">Code Parrain</span><span class="value">{{ $sponsor->sponsor_id }}</span></div>
        <div class="row"><span class="label">Membre</span><span class="value">{{ $sponsor->name }}</span></div>
        <div class="row"><span class="label">Grade</span><span class="value">{{ $sponsor->rank ?? 'Distributeur' }}</span></div>
    </div>
    @endif

    <!-- ============================================================
    ARTICLES
    ============================================================ -->
    <div class="items">
        <div class="item-header">
            <span>Qte</span>
            <span>Article</span>
            <span>Prix</span>
        </div>
        
        @foreach($order->items as $item)
        <div class="item">
            <span class="qty">{{ $item->quantity }}x</span>
            <span class="name">
                {{ Str::limit($item->name, 16) }}
                @if($item->package_id)
                    <span class="package-tag"> [PKG]</span>
                @endif
            </span>
            <span class="price">${{ number_format($item->total, 2) }}</span>
        </div>
        @endforeach
    </div>

    <!-- ============================================================
    TOTAUX
    ============================================================ -->
    @php
        $totalPv = $order->items->sum(function($item) {
            return ($item->pv_value ?? 0) * $item->quantity;
        });
        $commissionAmount = isset($order->metadata['commission_amount']) ? $order->metadata['commission_amount'] : 0;
        $isPosCommission = $order->source == 'pos' && $commissionAmount > 0;
    @endphp

    <div class="total">
        <div class="row">
            <span class="label">Sous-total</span>
            <span class="value">${{ number_format($order->subtotal, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">Taxe (16%)</span>
            <span class="value">${{ number_format($order->tax, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">Livraison</span>
            <span class="value">${{ number_format($order->shipping, 2) }}</span>
        </div>
        @if($order->discount > 0)
        <div class="row discount">
            <span class="label">Remise</span>
            <span class="value">-${{ number_format($order->discount, 2) }}</span>
        </div>
        @endif
        <div class="row total">
            <span class="label">TOTAL</span>
            <span class="value">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <!-- ============================================================
    PV & COMMISSIONS
    ============================================================ -->
    @if($totalPv > 0)
    <div class="pv-info">
         <strong>{{ $totalPv }} PV</strong> credits au parrain
    </div>
    @endif

    @if($commissionAmount > 0)
        @if($isPosCommission)
        <div class="commission-info">
             <strong>${{ number_format($commissionAmount, 2) }}</strong> de commission CASH POS pour le parrain
        </div>
        @else
        <div class="commission-info">
             <strong>${{ number_format($commissionAmount, 2) }}</strong> de commission pour le parrain
        </div>
        @endif
    @endif

    <!-- ============================================================
    PAIEMENT
    ============================================================ -->
    <div class="payment-info">
        <div class="row">
            <span class="label">Paiement</span>
            <span class="value">
                @if($order->payment_method == 'cash')
                    Espèces
                @elseif($order->payment_method == 'mobile_money')
                    Mobile Money
                @elseif($order->payment_method == 'card')
                    Carte bancaire
                @elseif($order->payment_method == 'bank_transfer')
                    Virement bancaire
                @else
                    {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}
                @endif
            </span>
        </div>
        <div class="row">
            <span class="label">Statut</span>
            <span class="value paid">Payé</span>
        </div>
        @if(isset($order->metadata['cashier_name']))
        <div class="row">
            <span class="label">Caissier</span>
            <span class="value" style="color:#0E2F76; font-weight:bold;">{{ $order->metadata['cashier_name'] }}</span>
        </div>
        @endif
        <div class="row">
            <span class="label">Source</span>
            <span class="value" style="font-weight:bold;">
                @if($order->source == 'pos')
                    <span style="color:#16a34a;">POS (Guichet)</span>
                @elseif($order->source == 'web' || $order->source == 'online')
                    <span style="color:#7c3aed;">En ligne</span>
                @else
                    <span style="color:#2563eb;">MLM</span>
                @endif
            </span>
        </div>
    </div>

    <!-- ============================================================
    PIED DE PAGE
    ============================================================ -->
    <div class="footer">
        <div class="separator-dashed"></div>
        <div style="display:flex; justify-content:space-between; font-size:7px; color:#888; margin-bottom:3px;">
            <span>Date: {{ $order->created_at->format('d/m/Y') }}</span>
            <span>Heure: {{ $order->created_at->format('H:i') }}</span>
        </div>
        
        <div class="barcode">|| ||| || ||| ||| ||</div>
        
        <div class="thank-you">MERCI POUR VOTRE CONFIANCE</div>
        
        <div style="font-size:6px; color:#aaa; margin-top:2px;">
            Ce ticket fait office de facture<br>
            Présentez-le au guichet pour récupérer votre produit
        </div>
        
        <div style="margin-top:3px; font-size:5px; color:#ccc; border-top:1px dotted #eee; padding-top:3px;">
            Salang Group SARL - Tous droits réservés
        </div>
    </div>

</div>

<!-- ============================================================
BOUTONS D'ACTION
============================================================ -->
<div class="no-print">
    <button onclick="window.print()" class="btn btn-print">
         Imprimer
    </button>
    <a href="{{ route('cashier.orders') }}" class="btn btn-back">
        ↩ Retour
    </a>
</div>

<script>
    // Impression automatique si demandée
    @if(request()->has('print'))
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800);
        };
    @endif
</script>

</body>
</html>