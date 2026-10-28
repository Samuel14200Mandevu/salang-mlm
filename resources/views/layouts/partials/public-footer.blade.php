@php
    $site = config('salang-site');
@endphp
<footer class="public-footer">
    <div class="public-footer-top">
        <div class="public-shell public-footer-grid">
            <div class="public-footer-brand">
                <a href="{{ url('/?site=1') }}" class="public-footer-brand__link">
                    <x-ui.image src="images/salang_logo.png" alt="" class="public-footer-brand__logo" width="140" height="44" :lazy="true" />
                    <span>
                        <span class="public-footer-brand__name">{{ $site['brand'] }}</span>
                        <span class="public-footer-brand__tag">{{ $site['subtitle'] }}</span>
                    </span>
                </a>
                <p class="public-footer-brand__text">
                    {{ $site['tagline'] }}. {{ $site['promise'] }}
                </p>
                <div class="public-footer-contact">
                    <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>
                    <a href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}">{{ $site['phone'] }}</a>
                    <span>{{ $site['hq'] }}</span>
                </div>
            </div>

            <div class="public-footer-col">
                <p class="public-footer-col__title">Explorer</p>
                <nav class="public-footer-links" aria-label="Pages du site">
                    <a href="{{ url('/?site=1') }}">Accueil</a>
                    <a href="{{ route('site.offer') }}">Offre</a>
                    <a href="{{ route('site.remuneration') }}">Rémunération</a>
                    <a href="{{ route('contact') }}">Contact</a>
                    <a href="{{ route('site.about') }}">À propos</a>
                </nav>
            </div>

            <div class="public-footer-col">
                <p class="public-footer-col__title">Membres</p>
                <nav class="public-footer-links" aria-label="Espace membre">
                    <a href="{{ route('login') }}">Connexion</a>
                    <a href="{{ route('register') }}">Adhérer — {{ $site['membership_fee_usd'] }}&nbsp;USD</a>
                    <a href="{{ asset('docs/livret-salang-international.pdf') }}" target="_blank" rel="noopener">Livret PDF</a>
                </nav>
            </div>

            <div class="public-footer-col public-footer-col--cta">
                <p class="public-footer-col__title">Nous écrire</p>
                <p class="public-footer-cta-text">Une question sur l’adhésion ou le plan&nbsp;? Notre équipe vous répond.</p>
                <a href="{{ route('contact') }}" class="public-footer-cta">
                    Contacter le support
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </div>

    <div class="public-footer-bottom">
        <div class="public-shell public-footer-bottom__inner">
            <p>&copy; {{ date('Y') }} {{ $site['brand'] }}. Tous droits réservés.</p>
            <nav class="public-footer-legal" aria-label="Liens légaux">
                @if (Route::has('legal.mentions'))
                    <a href="{{ route('legal.mentions') }}">Mentions légales</a>
                @endif
                <a href="{{ route('legal.terms') }}">Conditions générales</a>
                <a href="{{ route('legal.privacy') }}">Confidentialité</a>
            </nav>
        </div>
    </div>
</footer>
