@php
    $links = [
        ['route' => 'admin.pharmacy.dashboard', 'label' => 'Tableau de bord', 'match' => ['admin.pharmacy.dashboard']],
        ['route' => 'admin.products', 'label' => 'Produits', 'match' => [
            'admin.products',
            'admin.products.index',
            'admin.products.create',
            'admin.products.edit',
            'admin.products.show',
            'admin.products.barcodes',
            'admin.products.barcode',
            'admin.products.generate-barcode',
            'admin.products.download-barcode',
            'admin.products.print-all-barcodes',
            'admin.products.generate-all-barcodes',
            'admin.products.qr-codes',
            'admin.products.qr-code',
        ]],
        ['route' => 'admin.products.stock-overview', 'label' => 'Stock', 'match' => ['admin.products.stock*']],
        ['route' => 'admin.pharmacy.purchases.index', 'label' => 'Achats', 'match' => ['admin.pharmacy.purchases.*']],
        ['route' => 'admin.pharmacy.sales.index', 'label' => 'Ventes', 'match' => ['admin.pharmacy.sales.*']],
        ['route' => 'admin.pharmacy.cash.index', 'label' => 'Caisse', 'match' => ['admin.pharmacy.cash.*']],
        ['route' => 'admin.pharmacy.insurers.index', 'label' => 'Assurances', 'match' => ['admin.pharmacy.insurers.*']],
        ['route' => 'admin.pharmacy.registers.index', 'label' => 'Registres', 'match' => ['admin.pharmacy.registers.*']],
    ];
@endphp
<nav class="flex flex-wrap gap-2 mb-4" aria-label="Navigation pharmacie">
    @foreach($links as $link)
        <a href="{{ route($link['route']) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium border transition
                  {{ request()->routeIs(...$link['match'])
                      ? 'bg-[var(--primary)] text-white border-[var(--primary)]'
                      : 'bg-[var(--bg-card)] text-[var(--text-secondary)] border-[var(--border-color)] hover:border-[var(--primary)]' }}">
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>
