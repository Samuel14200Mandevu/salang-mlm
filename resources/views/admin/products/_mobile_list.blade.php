{{-- Liste mobile — sync AJAX via #productsMobileList --}}
<div id="productsMobileList" class="md:hidden space-y-2.5">
    @forelse($products as $product)
        @php
            [$stockClass, $stockLabel] = match (true) {
                $product->stock <= 0 => ['badge-danger', 'Rupture'],
                $product->stock <= 5 => ['badge-warning', 'Bas'],
                default => [null, null],
            };
            $hasImage = $product->image && file_exists(storage_path('app/public/products/'.$product->image));
            $flaconPrice = (float) ($product->display_price ?? $product->full_price ?? $product->price);
            $catalogPv = (int) ($product->display_pv ?? $product->pv_value ?? 0);
            $halfPrice = $product->hasHalfOption() ? (float) $product->half_price : null;
        @endphp
        <a href="{{ route('admin.products.show', $product->id) }}" class="products-mobile-card">
            <div class="products-mobile-top">
                @if($hasImage)
                    <img src="{{ asset('storage/products/'.$product->image) }}"
                         alt=""
                         class="product-thumb"
                         loading="lazy">
                @else
                    <span class="product-thumb product-thumb-fallback" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </span>
                @endif
                <div class="products-mobile-copy">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="truncate">{{ $product->name }}</h3>
                        @if($stockLabel)
                            <span class="badge {{ $stockClass }} shrink-0">{{ $stockLabel }}</span>
                        @endif
                    </div>
                    <p class="truncate">
                        {{ $product->category ?? 'Sans catégorie' }}
                        @if($product->sku)
                            · {{ $product->sku }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="products-mobile-grid">
                <div class="metric">
                    <span>PV</span>
                    <strong>{{ $catalogPv }}</strong>
                </div>
                <div class="metric">
                    <span>Flacon</span>
                    <strong class="is-primary">{{ number_format($flaconPrice, 2) }} $</strong>
                </div>
                <div class="metric">
                    <span>½</span>
                    <strong>{{ $halfPrice !== null ? number_format($halfPrice, 2).' $' : '—' }}</strong>
                </div>
            </div>
        </a>
    @empty
        <x-ui.empty-state
            title="Aucun produit"
            description="Aucun résultat pour ce filtre. Essayez un autre filtre ou créez un produit."
        >
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </x-slot:icon>
            <x-slot:action>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Créer un produit</a>
            </x-slot:action>
        </x-ui.empty-state>
    @endforelse
</div>
