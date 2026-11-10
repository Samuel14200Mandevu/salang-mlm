{{-- Scène adhésion : carte 30$ + grades 1→9 derrière / dans l’iPhone --}}
@php
    $fee = config('salang-site.membership_fee_usd', 30);
    $logoUrl = asset('images/salang_logo.webp');
    $gradeUrls = [];
    foreach (\App\Support\MlmRank::levelImageFiles() as $level => $file) {
        $path = 'images/grades/'.$file;
        if (! is_file(public_path($path))) {
            continue;
        }
        $gradeUrls[] = asset($path).'?v='.filemtime(public_path($path));
    }
    $gradeUrl = $gradeUrls[0] ?? asset('images/grades/distributeur.png');
@endphp
<div
    class="public-iphone-scene public-iphone-scene--home"
    id="publicOnboardingGradeScene"
    data-grade-urls='@json($gradeUrls)'
    aria-hidden="true"
>
    <div class="public-iphone-scene__card public-iphone-scene__card--join">
        <span>{{ $fee }}&nbsp;$</span>
        <strong>Adhésion<br>unique</strong>
        <em>Santé · Prospérité<br>· Liberté</em>
    </div>

    <div class="public-iphone-scene__grade">
        <span class="public-iphone-scene__grade-glow"></span>
        <img
            class="public-iphone-scene__grade-img is-fading"
            data-grade-cycle
            src="{{ $gradeUrl }}"
            alt=""
            width="140"
            height="175"
            loading="lazy"
            decoding="async"
        >
    </div>

    <div class="public-iphone public-iphone--home-front">
        <div class="public-iphone__body">
            <div class="public-iphone__island"></div>
            <div class="public-iphone__screen public-iphone__screen--home">
                <div class="public-iphone-home">
                    <div class="public-iphone-home__hero">
                        <div class="public-iphone-home__mesh"></div>

                        <div class="public-iphone-home__top">
                            <p class="public-iphone-home__hello">Bonjour, Samuel</p>
                            <div class="public-iphone-home__tools">
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                </span>
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </span>
                            </div>
                        </div>

                        <div class="public-iphone-home__balance-row">
                            <div class="public-iphone-home__pv">
                                <p class="public-iphone-home__label">PV personnel</p>
                                <p class="public-iphone-home__value">1 240<span> PV</span></p>
                            </div>
                            <div class="public-iphone-home__grade">
                                <img
                                    data-grade-cycle
                                    src="{{ $gradeUrl }}"
                                    alt=""
                                    width="56"
                                    height="70"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>
                        </div>

                        <div class="public-iphone-home__pnl">
                            <div>
                                <span>24h</span>
                                <strong class="is-up">+85 PV</strong>
                            </div>
                            <div>
                                <span>PV mois</span>
                                <strong>420 PV</strong>
                            </div>
                            <div>
                                <span>PV équipe</span>
                                <strong>3 180</strong>
                            </div>
                        </div>

                        <div class="public-iphone-home__goal">
                            <div class="public-iphone-home__goal-labels">
                                <span>PV personnel</span>
                                <span>1 240 / 2 000</span>
                            </div>
                            <div class="public-iphone-home__goal-bar"><i style="width:62%"></i></div>
                        </div>
                    </div>

                    <div class="public-iphone-home__actions">
                        <span class="is-primary">
                            <i>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </i>
                            Wallet
                        </span>
                        <span>
                            <i>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </i>
                            Gains
                        </span>
                        <span>
                            <i>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </i>
                            Équipe
                        </span>
                        <span>
                            <i>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </i>
                            Boutique
                        </span>
                    </div>

                    <div class="public-iphone-home__nav">
                        <span class="is-active">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 10v10a1 1 0 001 1h5v-6a1 1 0 011-1h4a1 1 0 011 1v6h5a1 1 0 001-1V10a1 1 0 00-.293-.707l-7-7z"/></svg>
                            Accueil
                        </span>
                        <span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Boutique
                        </span>
                        <span class="public-iphone-home__nav-center">
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
