{{-- resources/views/admin/products/qr-code-single.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
}

.qr-container {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    text-align: center;
    max-width: 450px;
    margin: 2rem auto;
    border: 1px solid #e5e7eb;
}

.qr-container .header {
    border-bottom: 2px solid var(--primary-blue);
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
}

.qr-container .header h2 {
    color: var(--primary-blue);
    font-size: 1rem;
    letter-spacing: 1.5px;
    margin: 0;
    font-weight: 700;
}

.qr-container .header .sub {
    font-size: 0.688rem;
    color: #666;
}

.qr-container .qr-code {
    margin: 1rem 0;
    padding: 1rem;
    background: #fafafa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-container .qr-code svg {
    max-width: 100%;
    height: auto;
}

.qr-container .product-name {
    font-size: 1.125rem;
    font-weight: 700;
    margin-top: 0.625rem;
    color: #1a1a1a;
}

.qr-container .product-info {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    font-size: 0.75rem;
    color: #666;
    margin-top: 0.25rem;
    flex-wrap: wrap;
}

.qr-container .product-info span {
    background: #f8fafc;
    padding: 0.125rem 0.625rem;
    border-radius: 4px;
}

.qr-container .product-id {
    font-size: 0.813rem;
    font-weight: 700;
    color: var(--primary-blue);
    margin-top: 0.5rem;
    padding: 0.25rem 0.875rem;
    background: #e8f0fe;
    border-radius: 4px;
    display: inline-block;
}

.qr-container .product-id .in-stock {
    color: #1C7E4A;
}
.qr-container .product-id .out-of-stock {
    color: #B91C1C;
}

.qr-container .price {
    font-size: 1rem;
    font-weight: 700;
    color: #1C7E4A;
    margin-top: 0.25rem;
}

.qr-container .footer {
    border-top: 1px solid #e5e7eb;
    padding-top: 0.625rem;
    margin-top: 0.875rem;
    font-size: 0.625rem;
    color: #999;
}

.actions {
    margin-top: 1.25rem;
    display: flex;
    gap: 0.625rem;
    justify-content: center;
    flex-wrap: wrap;
}

.actions .btn {
    padding: 0.625rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.813rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: background 0.15s ease, transform 0.1s ease;
}
.actions .btn:active {
    transform: scale(0.97);
}

.btn-print {
    background: var(--primary-blue);
    color: white;
}
.btn-print:hover {
    background: var(--primary-blue-dark);
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
    background: #1C7E4A;
    color: white;
}
.btn-download:hover {
    background: #14633A;
}

@media print {
    .no-print { display: none !important; }
    .qr-container {
        box-shadow: none;
        border: none;
        padding: 1rem;
        margin: 0 auto;
    }
    .qr-container .qr-code { background: none; }
    body { background: white; }
}

@media (max-width: 480px) {
    .qr-container { padding: 1.25rem; margin: 1rem; }
    .qr-container .product-name { font-size: 0.938rem; }
    .actions .btn { padding: 0.5rem 1rem; font-size: 0.75rem; }
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
                <span class="in-stock">✓ En stock ({{ $product->stock }})</span>
            @else
                <span class="out-of-stock">✗ Rupture</span>
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
                Télécharger
            </a>
            <a href="{{ route('admin.products.qr-codes') }}" class="btn btn-back">
                Retour
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if(request()->has('print'))
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800);
        };
    @endif
</script>
@endpush