{{-- Scène 1 : vrai iPhone Ma Boutique + flacons Salang autour (2 thèmes) --}}
@php
    $logoUrl = asset('images/salang_logo.webp');
    $bottle = function (string $file) {
        $path = 'images/onboarding/bottles/'.$file;
        return asset($path).(file_exists(public_path($path)) ? '?v='.filemtime(public_path($path)) : '');
    };
    $products = [
        [
            'name' => 'Vision Kit',
            'pv' => '45 PV',
            'price' => '48 $',
            'img' => 'images/produits/0c7a5b36-ba87-4acc-9e6b-75781177adcd.jpeg',
        ],
        [
            'name' => 'Gluta Glow',
            'pv' => '38 PV',
            'price' => '42 $',
            'img' => 'images/produits/a40666ab-94bd-41e4-9248-21395bab1387.jpeg',
        ],
        [
            'name' => 'Bilymarin',
            'pv' => '32 PV',
            'price' => '36 $',
            'img' => 'images/produits/d94dbceb-f0f2-4fc8-a9c6-6568922fff8d.jpeg',
        ],
        [
            'name' => 'Vitamine D3',
            'pv' => '40 PV',
            'price' => '44 $',
            'img' => 'images/produits/0a286ec6-443d-4dbb-91ef-7cac9ff166d8.jpeg',
        ],
    ];
@endphp
<div class="public-iphone-scene public-iphone-scene--shop" aria-hidden="true">
    <div class="public-iphone-scene__stage">
        <span class="public-iphone-scene__leaf public-iphone-scene__leaf--a"></span>
        <span class="public-iphone-scene__leaf public-iphone-scene__leaf--b"></span>
        <span class="public-iphone-scene__leaf public-iphone-scene__leaf--c"></span>
        <span class="public-iphone-scene__leaf public-iphone-scene__leaf--d"></span>

        <img class="public-iphone-scene__bottle public-iphone-scene__bottle--tl" src="{{ $bottle('vision.png') }}" alt="" width="120" height="150" loading="lazy" decoding="async">
        <img class="public-iphone-scene__bottle public-iphone-scene__bottle--tr" src="{{ $bottle('gluta.png') }}" alt="" width="120" height="120" loading="lazy" decoding="async">
        <img class="public-iphone-scene__bottle public-iphone-scene__bottle--bl" src="{{ $bottle('bilymarin.png') }}" alt="" width="110" height="160" loading="lazy" decoding="async">
        <img class="public-iphone-scene__bottle public-iphone-scene__bottle--br" src="{{ $bottle('d3.png') }}" alt="" width="100" height="180" loading="lazy" decoding="async">

        <div class="public-iphone public-iphone--shop-front">
            <div class="public-iphone__body">
                <div class="public-iphone__island"></div>
                <div class="public-iphone__screen public-iphone__screen--shop">
                    <div class="public-iphone-shop">
                        <div class="public-iphone-shop__banner">
                            <div class="public-iphone-shop__banner-text">
                                <p class="public-iphone-shop__eyebrow">Boutique Salang</p>
                                <p class="public-iphone-shop__title">Ma Boutique</p>
                                <p class="public-iphone-shop__sub">4 articles · PV &amp; commandes</p>
                            </div>
                            <div class="public-iphone-shop__tools">
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <span class="has-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <i>2</i>
                                </span>
                            </div>
                        </div>

                        <div class="public-iphone-shop__chips">
                            <span class="is-active">Tous</span>
                            <span>Vision</span>
                            <span>Beauté</span>
                            <span>Détox</span>
                        </div>

                        <div class="public-iphone-shop__grid">
                            @foreach ($products as $product)
                                @php
                                    $imgUrl = asset($product['img']).(file_exists(public_path($product['img'])) ? '?v='.filemtime(public_path($product['img'])) : '');
                                @endphp
                                <article class="public-iphone-shop__card">
                                    <div class="public-iphone-shop__media">
                                        <img src="{{ $imgUrl }}" alt="" width="80" height="80" loading="lazy" decoding="async">
                                    </div>
                                    <p class="public-iphone-shop__name">{{ $product['name'] }}</p>
                                    <div class="public-iphone-shop__meta">
                                        <span>{{ $product['pv'] }}</span>
                                        <strong>{{ $product['price'] }}</strong>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="public-iphone-shop__nav">
                            <span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10v10a1 1 0 001 1h5v-6a1 1 0 011-1h4a1 1 0 011 1v6h5a1 1 0 001-1V10a1 1 0 00-.293-.707l-7-7z"/></svg>
                                Accueil
                            </span>
                            <span class="is-active">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Boutique
                            </span>
                            <span class="public-iphone-shop__nav-center">
                                <i><img src="{{ $logoUrl }}" alt="" width="28" height="28" loading="lazy" decoding="async"></i>
                                Services
                            </span>
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Wallet
                            </span>
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Compte
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="public-iphone__btn public-iphone__btn--silent"></div>
            <div class="public-iphone__btn public-iphone__btn--vol-up"></div>
            <div class="public-iphone__btn public-iphone__btn--vol-down"></div>
            <div class="public-iphone__btn public-iphone__btn--power"></div>
        </div>
    </div>
</div>
