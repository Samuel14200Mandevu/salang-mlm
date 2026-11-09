@forelse($products as $product)
    @php
        $stockClass = match (true) {
            $product->stock <= 0 => 'badge-danger',
            $product->stock <= 5 => 'badge-warning',
            default => null,
        };
        $stockLabel = match (true) {
            $product->stock <= 0 => 'Rupture',
            $product->stock <= 5 => 'Bas',
            default => null,
        };
        $hasImage = $product->image && file_exists(storage_path('app/public/products/'.$product->image));
        $flaconPrice = (float) ($product->display_price ?? $product->full_price ?? $product->price);
        $halfPrice = $product->hasHalfOption() ? (float) $product->half_price : null;
        $pv = (int) ($product->display_pv ?? $product->pv_value ?? 0);
    @endphp
    <tr class="product-row cursor-pointer {{ $product->is_active ? '' : 'is-inactive' }}"
        data-name="{{ strtolower($product->name) }}"
        data-category="{{ strtolower($product->category ?? '') }}"
        onclick="window.location='{{ route('admin.products.show', $product->id) }}'">
        <td>
            <div class="flex items-center gap-3 min-w-0">
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
                <div class="min-w-0">
                    <div class="product-name truncate">{{ $product->name }}</div>
                    <div class="product-meta truncate">
                        @if($product->sku)
                            {{ $product->sku }}
                        @else
                            #{{ $product->id }}
                        @endif
                    </div>
                </div>
            </div>
        </td>
        <td>
            <span class="category-chip" title="{{ $product->category ?? 'Sans catégorie' }}">
                {{ $product->category ?? '—' }}
            </span>
        </td>
        <td class="is-center">
            <span class="pv-badge">{{ $pv }}</span>
        </td>
        <td class="is-num">
            <div class="price-cell">
                <span class="price-main">{{ number_format($flaconPrice, 2) }} $</span>
            </div>
        </td>
        <td class="is-num">
            <div class="price-cell">
                @if($halfPrice !== null)
                    <span class="price-half">{{ number_format($halfPrice, 2) }} $</span>
                @else
                    <span class="price-empty">—</span>
                @endif
            </div>
        </td>
        <td class="is-num">
            <div class="stock-cell">
                <span class="stock-qty">{{ (int) $product->stock }}</span>
                @if($stockLabel)
                    <span class="badge {{ $stockClass }}">{{ $stockLabel }}</span>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-10 text-[var(--text-secondary)]">
            <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <p class="text-base font-medium text-[var(--text-primary)]">Aucun produit</p>
            <p class="text-sm text-[var(--text-tertiary)] mt-1 mb-3">Aucun résultat pour ce filtre</p>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Créer un produit</a>
        </td>
    </tr>
@endforelse
