{{-- Splash mobile — logo Salang (première ouverture) --}}
<div class="public-mobile-splash" id="publicMobileSplash" aria-hidden="false">
    <div class="public-mobile-splash-inner">
        <div class="public-mobile-splash-glow" aria-hidden="true"></div>
        <img
            src="{{ asset('images/salang_logo.png') }}"
            alt="Salang Group"
            class="public-mobile-splash-logo"
            width="280"
            height="72"
            decoding="async"
            fetchpriority="high"
        >
        <p class="public-mobile-splash-tagline">Health Care International</p>
        <div class="public-mobile-splash-progress-wrap">
            <div
                class="public-mobile-splash-progress"
                role="progressbar"
                aria-label="Chargement"
                aria-valuemin="1"
                aria-valuemax="100"
                aria-valuenow="1"
                id="publicSplashProgress"
            >
                <span class="public-mobile-splash-progress__bar" id="publicSplashProgressBar" style="width:1%"></span>
            </div>
            <p class="public-mobile-splash-progress__label" id="publicSplashProgressLabel" aria-live="polite">1%</p>
        </div>
    </div>
</div>
