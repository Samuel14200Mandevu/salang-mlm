@php
    $shareUrl = route('products.show', $product->slug);
    $shareTitle = $product->name;
@endphp
<div class="shop-card-actions" role="group" aria-label="Actions produit">
    @auth
        <button type="button"
                class="shop-action-btn shop-action-btn--like"
                data-shop-wishlist="{{ $product->id }}"
                aria-label="Ajouter aux favoris">
            <span class="shop-action-btn__glyph">
                <svg class="shop-action-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </span>
            <span class="shop-action-btn__label">J'aime</span>
        </button>
    @else
        <a href="{{ route('login') }}" class="shop-action-btn shop-action-btn--like" aria-label="Se connecter pour aimer">
            <span class="shop-action-btn__glyph">
                <svg class="shop-action-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </span>
            <span class="shop-action-btn__label">J'aime</span>
        </a>
    @endauth

    <a href="{{ route('products.show', $product->slug) }}" class="shop-action-btn shop-action-btn--view">
        <span class="shop-action-btn__glyph">
            <svg class="shop-action-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </span>
        <span class="shop-action-btn__label">Voir</span>
    </a>

    @if($product->stock > 0)
        <form action="{{ route('cart.add') }}" method="POST" class="shop-action-btn-wrap shop-action-btn--cart-wrap"
              onsubmit="addToCart(event, this, {{ json_encode($product->name) }})">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="shop-action-btn shop-action-btn--cart btn-shop-add">
                <span class="shop-action-btn__glyph">
                    <svg class="shop-action-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span class="shop-action-btn__label">Panier</span>
            </button>
        </form>
    @else
        <span class="shop-action-btn shop-action-btn--disabled" aria-disabled="true">
            <span class="shop-action-btn__glyph">
                <svg class="shop-action-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </span>
            <span class="shop-action-btn__label">Rupture</span>
        </span>
    @endif

    <button type="button"
            class="shop-action-btn shop-action-btn--share"
            data-shop-share-url="{{ $shareUrl }}"
            data-shop-share-title="{{ $shareTitle }}"
            aria-label="Partager">
        <span class="shop-action-btn__glyph">
            <svg class="shop-action-btn__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
        </span>
        <span class="shop-action-btn__label">Partager</span>
    </button>
</div>
