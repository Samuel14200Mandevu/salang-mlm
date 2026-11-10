{{-- Onboarding — slide 1 photo thèmes / 2–3 iPhones CSS --}}
@php
    $fee = config('salang-site.membership_fee_usd', 30);
    $slide1Light = 'images/onboarding/slide1-light.png';
    $slide1Dark = 'images/onboarding/slide1-dark.png';
    $slide1LightUrl = asset($slide1Light).(file_exists(public_path($slide1Light)) ? '?v='.filemtime(public_path($slide1Light)) : '');
    $slide1DarkUrl = asset($slide1Dark).(file_exists(public_path($slide1Dark)) ? '?v='.filemtime(public_path($slide1Dark)) : '');

    $slides = [
        [
            'type' => 'theme-image',
            'light' => $slide1LightUrl,
            'dark' => $slide1DarkUrl,
            'alt' => 'Produits naturels Salang autour de l’application',
            'title' => 'Des produits 100 % naturels',
            'text' => 'Vision, beauté, immunité et bien-être : la gamme Salang Group pour votre santé au quotidien.',
            'legal' => true,
        ],
        [
            'type' => 'iphone-pay',
            'alt' => 'Paiement Mobile Money sur iPhone',
            'title' => 'Payez simplement vos soins Salang',
            'text' => 'Commandes, Mobile Money et suivi clair — compléments, Ceragem et Détox.',
        ],
        [
            'type' => 'iphone-salang',
            'alt' => 'Application Salang Group sur iPhone',
            'title' => 'Rejoignez Salang Group',
            'title_accent' => 'Santé · Prospérité · Liberté',
            'text' => 'Entreprise internationale de santé naturelle (Hong Kong, depuis 2015). Adhésion '.$fee.' USD — accompagnement et commissions dans votre espace membre.',
        ],
    ];
@endphp

<div class="public-app-onboarding" id="publicAppOnboarding" hidden>
    <button type="button" class="public-onboarding-skip" id="publicOnboardingSkip">Passer</button>

    <div class="public-app-onboarding__track" id="publicAppOnboardingTrack" tabindex="0" aria-label="Présentation Salang Group">
        @foreach ($slides as $index => $slide)
            <article
                class="public-app-onboarding__slide{{ $index === 0 ? ' is-active' : '' }}"
                data-slide-index="{{ $index }}"
                aria-roledescription="slide"
                aria-label="Étape {{ $index + 1 }} sur {{ count($slides) }}"
            >
                <div
                    class="public-app-onboarding__media @if($slide['type'] === 'theme-image') public-app-onboarding__media--theme-image @else public-app-onboarding__media--iphone @endif"
                    role="img"
                    aria-label="{{ $slide['alt'] }}"
                >
                    @if ($slide['type'] === 'theme-image')
                        <div class="public-onb-theme-pic">
                            <img
                                class="public-onb-theme-pic__img public-onb-theme-pic__img--light"
                                src="{{ $slide['light'] }}"
                                alt="{{ $slide['alt'] }}"
                                width="864"
                                height="1152"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                decoding="async"
                            >
                            <img
                                class="public-onb-theme-pic__img public-onb-theme-pic__img--dark"
                                src="{{ $slide['dark'] }}"
                                alt=""
                                width="864"
                                height="1152"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                decoding="async"
                                aria-hidden="true"
                            >
                        </div>
                    @elseif ($slide['type'] === 'iphone-pay')
                        @include('partials.public.onboarding-iphone-pay')
                    @else
                        @include('partials.public.onboarding-iphone-salang')
                    @endif
                </div>
                <div class="public-app-onboarding__copy">
                    <h2 class="public-app-onboarding__title">{{ $slide['title'] }}</h2>
                    @if (! empty($slide['title_accent']))
                        <p class="public-app-onboarding__title-accent">{{ $slide['title_accent'] }}</p>
                    @endif
                    <p class="public-app-onboarding__text">{{ $slide['text'] }}</p>
                    @if (! empty($slide['legal']))
                        <p class="public-app-onboarding__legal">
                            En utilisant cette application, vous acceptez les
                            <a href="{{ route('legal.terms') }}">Conditions d’utilisation</a>
                            et reconnaissez avoir lu notre
                            <a href="{{ route('legal.privacy') }}">Politique de confidentialité</a>.
                        </p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</div>
