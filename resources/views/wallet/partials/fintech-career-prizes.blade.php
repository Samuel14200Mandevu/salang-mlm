@php
    $careerQualificationPrizes = $careerQualificationPrizes ?? [];
@endphp
@if(!empty($careerQualificationPrizes))
<div class="member-fintech-grid-wrap member-fintech-career-wrap member-fintech-animate member-fintech-animate--d5">
    <p class="member-fintech-section-label">Bonus carrière · qualification</p>
    <div class="member-fintech-grid member-fintech-grid--2x3 member-fintech-grid--career">
            @foreach($careerQualificationPrizes as $prize)
                <article class="member-fintech-career-card {{ $prize['unlocked'] ? 'is-unlocked' : '' }}">
                    <div class="member-fintech-career-card__media">
                        @if($prize['image_url'])
                            <img src="{{ $prize['image_url'] }}" alt="" loading="lazy" decoding="async">
                        @else
                            <div class="member-fintech-career-card__cash" aria-hidden="true">
                                <span class="member-fintech-career-card__cash-symbol">$</span>
                                <span class="member-fintech-career-card__cash-amount">15</span>
                            </div>
                        @endif
                        @if($prize['unlocked'])
                            <span class="member-fintech-career-card__badge">Atteint</span>
                        @endif
                    </div>
                    <div class="member-fintech-career-card__body">
                        <p class="member-fintech-career-card__level">Niv. {{ $prize['level'] }}</p>
                        <p class="member-fintech-career-card__rank">{{ $prize['rank_name'] }}</p>
                        <p class="member-fintech-career-card__reward">{{ $prize['reward'] }}</p>
                    </div>
                </article>
            @endforeach
    </div>
    <p class="member-fintech-career-hint text-xs text-[var(--text-secondary)] mt-2 px-0.5">
        Les récompenses matérielles sont aussi présentées dans
        <a href="{{ route('services.index') }}" class="text-primary-500 hover:underline">Services</a>
        et la galerie
        <a href="{{ route('publications.index') }}" class="text-primary-500 hover:underline">Publications</a>.
    </p>
</div>
@endif
