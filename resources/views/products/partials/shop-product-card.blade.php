@php
    $inStock = $product->stock > 0;
    $stockClass = $product->stock > 10 ? 'is-ok' : ($product->stock > 0 ? 'is-low' : 'is-out');
    $stockLabel = $product->stock > 10 ? 'En stock' : ($product->stock > 0 ? $product->stock . ' restants' : 'Rupture');
@endphp
<article class="product-card member-shop-card shop-product-card"
         data-name="{{ strtolower($product->name) }}"
         data-description="{{ strtolower($product->description ?? '') }}"
         data-category="{{ strtolower($product->category ?? '') }}"
         data-featured="{{ $product->is_featured ? '1' : '0' }}"
         data-in-stock="{{ $inStock ? '1' : '0' }}">

    <a href="{{ route('products.show', $product->slug) }}" class="shop-product-card__media image-container">
        @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
            <img src="{{ asset('storage/products/' . $product->image) }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 class="shop-product-card__img"
                 onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}'">
        @else
            <div class="shop-product-card__placeholder">
                <svg class="w-10 h-10 sm:w-12 sm:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </div>
        @endif

        <div class="shop-product-card__badges">
            @if($product->is_featured)
                <span class="shop-tag shop-tag--hot">Top</span>
            @endif
            @if($product->category)
                <span class="shop-tag shop-tag--cat">{{ Str::limit($product->category, 14) }}</span>
            @endif
        </div>

        @if(!$inStock)
            <div class="shop-product-card__soldout">
                <span>Rupture</span>
            </div>
        @elseif($product->stock < 5)
            <div class="shop-product-card__ribbon shop-product-card__ribbon--warn">Stock limité</div>
        @endif

        <span class="shop-product-card__quick">Voir</span>
    </a>

    <div class="shop-product-card__body product-content">
        <a href="{{ route('products.show', $product->slug) }}" class="shop-product-card__title-link">
            <h3 class="shop-product-card__title product-name">{{ $product->name }}</h3>
        </a>

        <div class="shop-product-card__meta">
            @if($product->pv_value)
                <span class="shop-tag shop-tag--pv">{{ $product->pv_value }} PV</span>
            @endif
            @if($product->bv_value)
                <span class="shop-tag shop-tag--bv">{{ $product->bv_value }} BV</span>
            @endif
            <span class="shop-product-card__stock shop-product-card__stock--{{ $stockClass }}">{{ $stockLabel }}</span>
        </div>

        <div class="shop-product-card__price-row">
            <span class="shop-product-card__price product-price">${{ number_format($product->price, 2) }}</span>
            @if($inStock)
                <form action="{{ route('cart.add') }}" method="POST" class="shop-product-card__quick-add"
                      onsubmit="addToCart(event, this, {{ json_encode($product->name) }})">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="shop-product-card__quick-add-btn btn-shop-add" aria-label="Ajouter au panier">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>

        @include('products.partials.shop-card-actions')
    </div>
</article>
