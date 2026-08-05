{{-- resources/views/admin/products/qr-codes-print.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étiquettes QR Code - Salang Group</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            padding: 20px;
            background: white;
            color: #1a1a1a;
        }
        
        .report-header {
            width: 100%;
            border-bottom: 3px solid #0E2F76;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        
        .report-header table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-header table td {
            border: none;
            padding: 5px 10px;
            vertical-align: middle;
            text-align: center;
        }
        
        .report-header .logo-cell {
            width: 200px;
            text-align: center;
        }
        
        .report-header .logo-cell img {
            max-height: 80px;
            width: auto;
            display: inline-block;
        }
        
        .report-header .header-center {
            text-align: center;
        }
        .report-header .header-center h1 {
            font-size: 18px;
            font-weight: 900;
            color: #0E2F76;
            letter-spacing: 2px;
            margin: 0;
        }
        .report-header .header-center .sub {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        .report-header .header-center .period {
            font-size: 13px;
            font-weight: 700;
            color: #0E2F76;
            margin-top: 4px;
        }
        .report-header .header-center .date {
            font-size: 9px;
            color: #888;
            margin-top: 2px;
        }
        .report-header .header-center .signature {
            font-size: 8px;
            color: #666;
            line-height: 1.5;
        }
        
        .label-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .label-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            page-break-inside: avoid;
            background: white;
        }
        
        .label-item .qr-image {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .label-item .qr-image img {
            max-width: 120px;
            max-height: 120px;
            width: auto;
            height: auto;
        }
        
        .label-item .qr-image svg {
            max-width: 120px;
            max-height: 120px;
            width: auto;
            height: auto;
        }
        
        .label-item .name {
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
            color: #1a1a1a;
        }
        .label-item .id {
            font-size: 10px;
            color: #0E2F76;
            font-weight: 700;
        }
        .label-item .price {
            font-size: 11px;
            color: #22c55e;
            font-weight: 700;
        }
        .label-item .sku {
            font-size: 8px;
            color: #999;
            margin-top: 3px;
        }
        
        .report-footer {
            text-align: center;
            font-size: 7px;
            color: #999;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
        .report-footer .signature {
            margin-top: 4px;
            font-size: 8px;
            color: #666;
        }
        
        @media print {
            body { padding: 10px; }
            .report-header { border-bottom-color: #000; }
            .report-header .header-center h1 { color: #000; }
            .report-header .header-center .period { color: #000; }
            .label-grid { gap: 10px; }
            .label-item { border-color: #ccc; }
        }
        
        @media (max-width: 768px) {
            .label-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 480px) {
            .label-grid { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 600px) {
            .report-header table td {
                display: block;
                width: 100% !important;
                text-align: center !important;
                padding: 5px 0;
            }
            .report-header .logo-cell {
                width: 100% !important;
            }
            .report-header .logo-cell img {
                max-height: 60px;
            }
        }
    </style>
</head>
<body>

<!-- ✅ EN-TÊTE AVEC LOGOS -->
<div class="report-header">
    <table>
        <tr>
            <td class="logo-cell" style="text-align: right; width: auto; padding-right: 5px;">
                <img src="{{ $logoBase64 ?? asset('images/salang_logo.png') }}" alt="Salang Group" style="max-height: 80px; width: auto;">
            </td>
            <td class="header-center" style="padding: 0 5px; text-align: center;">
                <h1>SALANG GROUP SARL</h1>
                <div class="sub">E-COMMERCE &amp; MLM</div>
                <div style="margin-top:2px; font-size:12px;">
                    <div class="signature">
                        N° ID.NAT: 22-7300-N634640 | N° RCCM: CD/BKVIRCM/20-8-001165001<br>
                        Rond Point CHIKUDU, Batiment KBS au 3eme Niveau<br>
                        Tel: +243 975 220 079 | Email: support@salanggroup.com
                    </div>
                    <div class="period">ÉTIQUETTES QR CODE</div>
                    <div class="date">{{ count($products) }} produits - Généré le {{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </td>
            <td class="logo-cell" style="text-align: left; width: auto; padding-left: 5px;">
                <img src="{{ $logoBase64 ?? asset('images/salang_logo.png') }}" alt="Salang Group" style="max-height: 80px; width: auto;">
            </td>
        </tr>
    </table>
</div>

<!-- ✅ GRILLE DES QR CODES -->
<div class="label-grid">
    @foreach($products as $product)
        @php
            $qrPath = $product->metadata['qr_code_svg'] ?? null;
            $hasQr = $qrPath && Storage::disk('public')->exists($qrPath);
            $qrContent = $product->metadata['qr_content'] ?? $product->id;
        @endphp
        <div class="label-item">
            <div class="qr-image">
                @if($hasQr)
                    <img src="{{ asset('storage/' . $qrPath) }}" alt="QR Code {{ $product->name }}">
                @elseif(isset($product->metadata['qr_base64']))
                    <img src="data:image/svg+xml;base64,{{ $product->metadata['qr_base64'] }}" 
                         alt="QR Code {{ $product->name }}">
                @else
                    {!! QrCode::size(120)->color(14, 47, 118)->generate((string)$product->id) !!}
                @endif
            </div>
            <div class="name">{{ Str::limit($product->name, 20) }}</div>
            <div class="id">ID: {{ $product->id }}</div>
            <div class="price">${{ number_format($product->price, 2) }}</div>
            <div class="sku">SKU: {{ $product->sku }}</div>
        </div>
    @endforeach
</div>

<!-- ✅ PIED DE PAGE -->
<div class="report-footer">
    <div>Ce rapport est généré automatiquement par le système Salang Group</div>
    <div class="signature">
        Rond Point CHIKUDU, Batiment KBS au 3eme Niveau<br>
        Tel: +243 975 220 079 | Email: support@salanggroup.com
    </div>
    <div style="margin-top:2px; font-size:6px; color:#ccc;">
        N° ID.NAT: 22-7300-N634640 | N° RCCM: CD/BKVIRCM/20-8-001165001
    </div>
</div>

<script>
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 1500);
    };
</script>

</body>
</html>