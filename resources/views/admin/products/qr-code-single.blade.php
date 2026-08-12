{{-- resources/views/admin/products/qr-code-single.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .qr-container {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
        max-width: 450px;
        margin: 30px auto;
        border: 1px solid #e5e7eb;
    }
    
    .qr-container .header {
        border-bottom: 2px solid #0E2F76;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    
    .qr-container .header h2 {
        color: #0E2F76;
        font-size: 16px;
        letter-spacing: 2px;
        margin: 0;
    }
    
    .qr-container .header .sub {
        font-size: 11px;
        color: #666;
    }
    
    .qr-container .qr-code {
        margin: 15px 0;
        padding: 15px;
        background: #fafafa;
        border-radius: 8px;
    }
    
    .qr-container .qr-code svg {
        max-width: 100%;
        height: auto;
    }
    
    .qr-container .product-name {
        font-size: 18px;
        font-weight: 700;
        margin-top: 10px;
        color: #1a1a1a;
    }
    
    .qr-container .product-info {
        display: flex;
        justify-content: center;
        gap: 15px;
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        flex-wrap: wrap;
    }
    
    .qr-container .product-info span {
        background: #f8fafc;
        padding: 2px 10px;
        border-radius: 4px;
    }
    
    .qr-container .product-id {
        font-size: 13px;
        font-weight: 700;
        color: #0E2F76;
        margin-top: 8px;
        padding: 4px 15px;
        background: #e8f0fe;
        border-radius: 4px;
        display: inline-block;
    }
    
    .qr-container .price {
        font-size: 16px;
        font-weight: 700;
        color: #22c55e;
        margin-top: 5px;
    }
    
    .qr-container .footer {
        border-top: 1px solid #e5e7eb;
        padding-top: 10px;
        margin-top: 15px;
        font-size: 10px;
        color: #999;
    }
    
    .actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .actions .btn {
        padding: 10px 24px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    
    .actions .btn:hover {
        transform: translateY(-2px);
    }
    
    .actions .btn:active {
        transform: scale(0.95);
    }
    
    .btn-print {
        background: #0E2F76;
        color: white;
    }
    
    .btn-print:hover {
        background: #1a3f8a;
    }
    
    .btn-back {
        background: #f3f4f6;
        color: #333;
        border: 1px solid #d1d5db;
    }
    
    .btn-back:hover {
        background: #e5e7eb;
    }
    
    .btn-download {
        background: #22c55e;
        color: white;
    }
    
    .btn-download:hover {
        background: #16a34a;
    }
    
    @media print {
        .no-print { display: none !important; }
        .qr-container { 
            box-shadow: none; 
            border: none; 
            padding: 15px;
            margin: 0 auto;
        }
        .qr-container .qr-code { background: none; }
        body { background: white; }
    }
    
    @media (max-width: 480px) {
        .qr-container { padding: 20px; margin: 15px; }
        .qr-container .product-name { font-size: 15px; }
        .actions .btn { padding: 8px 16px; font-size: 12px; }
    }
</style>
@endpush

@section('title', 'QR Code - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="qr-container">
        <div class="header">
            <h2>SALANG GROUP SARL</h2>
            <div class="sub">E-COMMERCE &amp; MLM</div>
        </div>
        
        <div class="qr-code">
            {!! $qrCode !!}
        </div>
        
        <div class="product-name">{{ $product->name }}</div>
        
        <div class="product-info">
            <span>SKU: {{ $product->sku }}</span>
            <span>Catégorie: {{ $product->category ?? 'N/A' }}</span>
            @if($product->pv_value)
                <span>{{ $product->pv_value }} PV</span>
            @endif
        </div>
        
        <div class="product-id">
            ID: {{ $product->id }} 
            @if($product->stock > 0)
                <span style="color:#22c55e;">✓ En stock ({{ $product->stock }})</span>
            @else
                <span style="color:#ef4444;">✗ Rupture</span>
            @endif
        </div>
        
        <div class="price">${{ number_format($product->price, 2) }}</div>
        
        <div class="footer">
            Scannez ce QR code au guichet POS
            <br>
            <small>ID: {{ $product->id }} | Généré le {{ now()->format('d/m/Y') }}</small>
        </div>
        
        <div class="actions no-print">
            <button onclick="window.print()" class="btn btn-print">
                 Imprimer
            </button>
            <a href="{{ route('admin.products.download-qr', $product->id) }}" class="btn btn-download">
                ⬇ Télécharger
            </a>
            <a href="{{ route('admin.products.qr-codes') }}" class="btn btn-back">
                ↩ Retour
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
@endpush