{{-- Onboarding app mobile — carrousel horizontal (images + vidéo + grades live) --}}
@php
    use App\Support\MlmRank;

    $gradeShowcase = [];
    foreach (MlmRank::levelImageFiles() as $level => $file) {
        $url = MlmRank::imageUrl($level);
        if (! $url) {
            continue;
        }
        $gradeShowcase[] = [
            'level' => $level,
            'label' => MlmRank::label($level),
            'src' => $url,
            'accent' => MlmRank::dashboardAccent($level),
        ];
    }

    $slides = [
        [
            'type' => 'image',
            'src' => asset('images/onboarding/onboarding4.png'),
            'alt' => 'Réseau Salang Group',
            'anim' => 'network',
            'kicker' => 'Réseau',
            'title' => 'Connectez-vous à une communauté mondiale',
            'text' => 'Développez votre réseau avec des outils clairs et un accompagnement Salang Group.',
        ],
        [
            'type' => 'image',
            'src' => asset('images/onboarding/onboarding2.png'),
            'alt' => 'Performance et revenus',
            'anim' => 'stats',
            'kicker' => 'Performance',
            'title' => 'Suivez vos résultats en temps réel',
            'text' => 'Commissions, grades et objectifs : tout est visible dans votre espace membre.',
            'stats' => [
                ['value' => 0.7, 'suffix' => '%+', 'decimals' => 1, 'label' => 'Croissance'],
                ['value' => 27.59, 'suffix' => '', 'decimals' => 2, 'label' => 'Fiscal'],
                ['value' => 100, 'suffix' => '%', 'decimals' => 0, 'label' => 'Objectif'],
                ['value' => 18.36, 'suffix' => '', 'decimals' => 2, 'label' => 'Marché'],
            ],
        ],
        [
            'type' => 'grades',
            'anim' => 'grades',
            'kicker' => 'Carrière',
            'title' => 'Évoluez à travers les grades',
            'text' => 'Du Distributeur au Diamond Pearl : un parcours de distinctions motivant.',
            'grades' => $gradeShowcase,
        ],
        [
            'type' => 'video',
            'src' => asset('images/onboarding/onboarding4.mp4').'?v='.filemtime(public_path('images/onboarding/onboarding4.mp4')),
            'alt' => 'Découvrir Salang Group',
            'anim' => 'video',
            'kicker' => 'Bienvenue',
            'title' => 'Prêt à démarrer ?',
            'text' => 'Connectez-vous ou créez votre compte pour accéder à votre espace.',
        ],
    ];
@endphp

<div class="public-app-onboarding" id="publicAppOnboarding" hidden>
    <div class="public-app-onboarding__track" id="publicAppOnboardingTrack" tabindex="0" aria-label="Présentation Salang Group">
        @foreach ($slides as $index => $slide)
            <article
                class="public-app-onboarding__slide{{ $index === 0 ? ' is-active' : '' }}"
                data-slide-index="{{ $index }}"
                data-anim="{{ $slide['anim'] }}"
                aria-roledescription="slide"
                aria-label="Étape {{ $index + 1 }} sur {{ count($slides) }}"
            >
                <div class="public-app-onboarding__media public-app-onboarding__media--{{ $slide['anim'] }}">
                    @if ($slide['type'] === 'video')
                        <video
                            class="public-app-onboarding__video"
                            src="{{ $slide['src'] }}"
                            playsinline
                            muted
                            loop
                            preload="metadata"
                            aria-label="{{ $slide['alt'] }}"
                        ></video>
                    @elseif ($slide['type'] === 'grades')
                        <div class="public-app-onboarding__grades" data-grade-rotator>
                            <div class="public-app-onboarding__grades-motifs" aria-hidden="true">
                                <span class="public-app-onboarding__grades-orbit public-app-onboarding__grades-orbit--outer"></span>
                                <span class="public-app-onboarding__grades-orbit public-app-onboarding__grades-orbit--mid"></span>
                                <span class="public-app-onboarding__grades-orbit public-app-onboarding__grades-orbit--inner"></span>
                                <span class="public-app-onboarding__grades-sparkles">
                                    @for ($i = 1; $i <= 10; $i++)
                                        <i style="--s: {{ $i }}"></i>
                                    @endfor
                                </span>
                                <span class="public-app-onboarding__grades-rays">
                                    <i></i><i></i><i></i><i></i><i></i><i></i>
                                </span>
                                <span class="public-app-onboarding__grades-ghosts">
                                    <i class="is-rect is-a"></i>
                                    <i class="is-rect is-b"></i>
                                    <i class="is-square is-c"></i>
                                    <i class="is-rect is-d"></i>
                                    <i class="is-square is-e"></i>
                                    <i class="is-rect is-f"></i>
                                    <i class="is-square is-g"></i>
                                    <i class="is-rect is-h"></i>
                                </span>
                            </div>
                            <div class="public-app-onboarding__grades-stage" aria-hidden="true">
                                <span class="public-app-onboarding__grades-glow"></span>
                                <span class="public-app-onboarding__grades-ring"></span>
                            </div>
                            @foreach ($slide['grades'] as $gIndex => $grade)
                                <figure
                                    class="public-app-onboarding__grade{{ $gIndex === 0 ? ' is-current' : '' }}"
                                    data-grade-index="{{ $gIndex }}"
                                    style="--grade-accent: {{ $grade['accent'] }}"
                                >
                                    <img
                                        class="public-app-onboarding__grade-img"
                                        src="{{ $grade['src'] }}"
                                        alt="{{ $grade['label'] }}"
                                        width="320"
                                        height="320"
                                        loading="{{ $gIndex < 2 ? 'eager' : 'lazy' }}"
                                        decoding="async"
                                    >
                                </figure>
                            @endforeach
                            <p class="public-app-onboarding__grade-label" data-grade-label>
                                {{ $slide['grades'][0]['label'] ?? '' }}
                            </p>
                            <p class="public-app-onboarding__grade-step" data-grade-step aria-live="polite">
                                1 / {{ count($slide['grades']) }}
                            </p>
                            <script type="application/json" data-grade-labels>
                                @json(collect($slide['grades'])->pluck('label')->values())
                            </script>
                        </div>
                    @else
                        <img
                            class="public-app-onboarding__image"
                            src="{{ $slide['src'] }}"
                            alt="{{ $slide['alt'] }}"
                            width="1264"
                            height="842"
                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                            decoding="async"
                        >
                        @if (!empty($slide['stats']))
                            <div class="public-app-onboarding__stats" aria-hidden="true">
                                @foreach ($slide['stats'] as $stat)
                                    <div class="public-app-onboarding__stat" style="--stat-i: {{ $loop->index }}">
                                        <span
                                            class="public-app-onboarding__stat-value"
                                            data-count-to="{{ $stat['value'] }}"
                                            data-decimals="{{ $stat['decimals'] }}"
                                            data-suffix="{{ $stat['suffix'] }}"
                                        >0</span>
                                        <span class="public-app-onboarding__stat-label">{{ $stat['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
                <div class="public-app-onboarding__copy">
                    <p class="public-app-onboarding__kicker">{{ $slide['kicker'] }}</p>
                    <h2 class="public-app-onboarding__title">{{ $slide['title'] }}</h2>
                    <p class="public-app-onboarding__text">{{ $slide['text'] }}</p>
                </div>
            </article>
        @endforeach
    </div>
</div>
