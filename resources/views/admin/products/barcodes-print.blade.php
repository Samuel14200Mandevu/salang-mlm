<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étiquettes code-barres - Salang Group</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; padding: 16px; background: #fff; color: #111; }

        .report-header {
            width: 100%;
            border-bottom: 3px solid #0A2A6C;
            padding-bottom: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .report-header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #0A2A6C;
            letter-spacing: 2px;
        }
        .report-header .sub { font-size: 10px; color: #666; }
        .report-header .period {
            font-size: 13px;
            font-weight: 700;
            color: #0A2A6C;
            margin-top: 6px;
        }
        .report-header .date { font-size: 9px; color: #888; margin-top: 2px; }

        .label-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-top: 16px;
        }
        .label-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 10px;
            text-align: center;
            page-break-inside: avoid;
            background: white;
        }
        .label-item .barcode-image {
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .label-item .barcode-image img,
        .label-item .barcode-image svg {
            max-width: 100%;
            max-height: 70px;
            width: auto;
            height: auto;
        }
        .label-item .name {
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
            color: #1a1a1a;
        }
        .label-item .sku {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            color: #0A2A6C;
            font-weight: 700;
            letter-spacing: 0.06em;
            margin-top: 2px;
        }
        .label-item .price {
            font-size: 11px;
            color: #1C7E4A;
            font-weight: 700;
            margin-top: 2px;
        }

        @media print {
            body { padding: 8px; }
            .label-grid { gap: 10px; }
            .label-item { border-color: #ccc; }
        }
        @media (max-width: 768px) {
            .label-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .label-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="report-header">
    <h1>SALANG GROUP SARL</h1>
    <div class="sub">E-COMMERCE &amp; MLM</div>
    <div class="period">ÉTIQUETTES CODE-BARRES</div>
    <div class="date">{{ count($products) }} produits — {{ now()->format('d/m/Y H:i') }}</div>
</div>

<div class="label-grid">
    @foreach($products as $product)
        @php
            $path = $product->metadata['barcode_svg'] ?? null;
            $hasBarcode = $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
        @endphp
        <div class="label-item">
            <div class="barcode-image">
                @if($hasBarcode)
                    <img src="{{ asset('storage/' . $path) }}" alt="{{ $product->sku }}">
                @elseif(!empty($product->metadata['barcode_base64']))
                    <img src="data:image/svg+xml;base64,{{ $product->metadata['barcode_base64'] }}" alt="{{ $product->sku }}">
                @else
                    <span style="font-size:10px;color:#999;">SKU {{ $product->sku ?: '—' }}</span>
                @endif
            </div>
            <div class="name">{{ Str::limit($product->name, 22) }}</div>
            <div class="sku">{{ $product->sku }}</div>
            <div class="price">${{ number_format($product->display_price ?? $product->price, 2) }}</div>
        </div>
    @endforeach
</div>

<script>
    window.onload = function () {
        setTimeout(function () { window.print(); }, 800);
    };
</script>
</body>
</html>
