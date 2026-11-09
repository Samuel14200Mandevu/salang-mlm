{{-- resources/views/admin/products/barcode-single.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
.barcode-container {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    text-align: center;
    max-width: 520px;
    margin: 2rem auto;
    border: 1px solid #e5e7eb;
}

.barcode-container .header {
    border-bottom: 2px solid var(--primary-blue);
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
}

.barcode-container .header h2 {
    color: var(--primary-blue);
    font-size: 1rem;
    letter-spacing: 1.5px;
    margin: 0;
    font-weight: 700;
}

.barcode-container .header .sub {
    font-size: 0.688rem;
    color: #666;
}

.barcode-container .barcode-image {
    margin: 1rem 0;
    padding: 1rem 1.25rem;
    background: #fafafa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-x: auto;
}

.barcode-container .barcode-image svg {
    max-width: 100%;
    height: auto;
    min-height: 64px;
}

.barcode-container .product-name {
    font-size: 1.125rem;
    font-weight: 700;
    margin-top: 0.625rem;
    color: #1a1a1a;
}

.barcode-container .sku-value {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: var(--primary-blue);
    margin-top: 0.5rem;
}

.barcode-container .product-info {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    font-size: 0.75rem;
    color: #666;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.barcode-container .product-info span {
    background: #f8fafc;
    padding: 0.125rem 0.625rem;
    border-radius: 4px;
}

.barcode-container .price {
    font-size: 1rem;
    font-weight: 700;
    color: #1C7E4A;
    margin-top: 0.5rem;
}

.barcode-container .footer {
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
}

.btn-print { background: var(--primary-blue); color: white; }
.btn-download { background: #1C7E4A; color: white; }
.btn-back { background: #f3f4f6; color: #333; border: 1px solid #d1d5db; }

@media print {
    .no-print { display: none !important; }
    .barcode-container {
        box-shadow: none;
        border: none;
        padding: 1rem;
        margin: 0 auto;
    }
    .barcode-container .barcode-image { background: none; }
    body { background: white; }
}
</style>
@endpush

@section('title', 'Code-barres - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="barcode-container">
        <div class="header">
            <h2>SALANG GROUP SARL</h2>
            <div class="sub">E-COMMERCE &amp; MLM · CODE-BARRES</div>
        </div>

        <div class="barcode-image">
            {!! $barcodeSvg !!}
        </div>

        <div class="product-name">{{ $product->name }}</div>
        <div class="sku-value">{{ $product->sku }}</div>

        <div class="product-info">
            <span>ID: {{ $product->id }}</span>
            <span>Catégorie: {{ $product->category ?? 'N/A' }}</span>
            @if($product->display_pv)
                <span>{{ $product->display_pv }} PV</span>
            @endif
        </div>

        <div class="price">${{ number_format($product->display_price ?? $product->price, 2) }}</div>

        <div class="footer">
            Scannez ce code-barres au guichet POS
            <br>
            <small>SKU: {{ $product->sku }} · Code 128 · {{ now()->format('d/m/Y') }}</small>
        </div>

        <div class="actions no-print">
            <button onclick="window.print()" class="btn btn-print">Imprimer</button>
            <a href="{{ route('admin.products.download-barcode', $product->id) }}" class="btn btn-download">Télécharger</a>
            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-back">Retour au détail</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if(request()->has('print'))
        window.onload = function() {
            setTimeout(function() { window.print(); }, 800);
        };
    @endif
</script>
@endpush
