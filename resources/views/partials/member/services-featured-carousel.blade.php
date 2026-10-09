@php
    $items = $servicesFeaturedPublications ?? collect();
@endphp

@if($items->isNotEmpty())
    <section class="services-featured"
             aria-label="Événements et promotions à la une"
             aria-roledescription="carousel"
             x-data="servicesFeaturedCarousel({{ $items->count() }})"
             x-init="start()"
             @mouseenter="pause()"
             @mouseleave="resume()"
             @touchstart.passive="pause()"
             @touchend.passive="resume()"
             @visibilitychange.window="document.hidden ? pause() : resume()">
        <div class="services-featured__heading">
            <span class="services-featured__heading-bar" aria-hidden="true"></span>
            <span class="services-featured__heading-text">À la une</span>
        </div>

        <div class="services-featured__viewport">
            <div class="services-featured__track"
                 :style="trackStyle()"
                 :aria-hidden="false">
                @foreach($items as $publication)
                    @php
                        $cover = $publication->medias->firstWhere('type', 'photo') ?? $publication->medias->first();
                        $dateLabel = $publication->start_date
                            ? $publication->start_date->locale(app()->getLocale())->isoFormat('D MMM YYYY')
                            : $publication->created_at->locale(app()->getLocale())->diffForHumans();
                    @endphp
                    <a href="{{ route('publications.show', $publication) }}"
                       class="services-featured__slide card overflow-hidden"
                       :aria-hidden="active !== {{ $loop->index }}"
                       :tabindex="active === {{ $loop->index }} ? 0 : -1">
                        <div class="services-featured__media">
                            @if($cover && $cover->type === 'photo')
                                <img src="{{ $cover->url }}" alt="" loading="lazy" decoding="async">
                            @elseif($cover && $cover->type === 'video')
                                <img src="{{ $cover->thumbnail_url }}" alt="" loading="lazy" decoding="async">
                                <span class="services-featured__play" aria-hidden="true">▶</span>
                            @else
                                <img src="{{ asset('images/salang_logo.webp') }}" alt="" class="services-featured__placeholder-logo" loading="lazy">
                            @endif
                            <span class="services-featured__date badge badge-info">
                                <svg class="services-featured__date-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                {{ $dateLabel }}
                            </span>
                            <span class="services-featured__type badge {{ $publication->type === 'event' ? 'badge-info' : 'badge-purple' }}">
                                {{ $publication->type === 'event' ? 'Événement' : 'Promotion' }}
                            </span>
                            <div class="services-featured__overlay">
                                <p class="services-featured__title">{{ $publication->title }}</p>
                                <span class="services-featured__cta">
                                    Voir le détail
                                    <span aria-hidden="true">→</span>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        @if($items->count() > 1)
            <div class="services-featured__dots" role="tablist" aria-label="Slides">
                @foreach($items as $index => $publication)
                    <button type="button"
                            class="services-featured__dot"
                            :class="{ 'is-active': active === {{ $index }} }"
                            @click="go({{ $index }})"
                            :aria-selected="active === {{ $index }}"
                            role="tab"
                            aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif
    </section>
@endif
