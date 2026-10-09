@props([
    'eyebrow',
    'title',
    'sub' => null,
    'bannerClass' => '',
])

<div {{ $attributes->class(['shop-catalog-head', 'member-page-catalog-head']) }}>
    <div @class(['shop-catalog-banner', $bannerClass])>
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">{{ $eyebrow }}</p>
            <p class="shop-catalog-banner__title">{{ $title }}</p>
            @if($sub)
                <p class="shop-catalog-banner__sub">{{ $sub }}</p>
            @endif
        </div>
        @if(isset($tools))
            <div class="shop-catalog-banner__tools">
                {{ $tools }}
            </div>
        @endif
    </div>
</div>
