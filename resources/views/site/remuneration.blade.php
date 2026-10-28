@extends('layouts.public')

@section('title', 'Rémunération — Salang Group')

@php
    use App\Support\MlmRank;

    $site = config('salang-site');
    $careerPrizes = config('career_qualification_prizes.levels', []);
@endphp

@section('content')
<section class="public-page-hero">
    <div class="public-shell">
        <p class="public-kicker">Commencer à gagner</p>
        <h1 class="public-page-hero__title">Types de revenus et grades</h1>
        <p class="public-page-hero__lead">
            {{ $site['pv_note'] }}
        </p>
    </div>
</section>

<section class="public-section">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Revenus',
            'title' => 'Comment gagner avec Salang',
        ])
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($site['income_types'] as [$name, $detail])
                <div class="public-rank-card">
                    <p class="text-[0.6875rem] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Commission</p>
                    <p class="font-semibold text-[var(--text-primary)] mt-1">{{ $name }}</p>
                    <p class="text-sm text-[var(--text-secondary)] mt-2">{{ $detail }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section-alt">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Prix',
            'title' => 'Prix de qualification carrière',
            'lead' => 'Récompenses à l’atteinte des grades 4 à 9 — mêmes règles que dans Services / Publications.',
            'center' => true,
        ])

        <div class="public-rewards-grid">
            @foreach ($careerPrizes as $level => $prize)
                @php
                    $img = $prize['image'] ?? null;
                    $imgUrl = $img && is_file(public_path($img)) ? asset($img) : null;
                    $gradeName = MlmRank::label((int) $level);
                @endphp
                <article class="public-reward-card">
                    <div class="public-reward-card__media{{ $imgUrl ? '' : ' public-reward-card__media--empty' }}">
                        @if ($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $prize['reward'] }}" class="public-reward-card__img" loading="lazy" decoding="async">
                        @else
                            <span class="public-reward-card__placeholder" aria-hidden="true">★</span>
                        @endif
                    </div>
                    <div class="public-reward-card__body">
                        <p class="public-reward-card__grade">Niveau {{ $level }} · {{ $gradeName }}</p>
                        <h3 class="public-reward-card__title">{{ $prize['reward'] }}</h3>
                        <p class="public-reward-card__detail">
                            {{ $prize['publication']['description'] ?? ('Récompense à la qualification '.$gradeName.'.') }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Grades',
            'title' => 'Qualifications et progression',
            'lead' => 'Branches = filleuls directs ou indirects, rang max dans la jambe.',
        ])

        <div class="public-grades-list">
            @foreach ($site['ranks_summary'] as $rank)
                @php
                    $level = (int) $rank['level'];
                    $badgeUrl = MlmRank::imageUrl($level);
                    $accent = MlmRank::dashboardAccent($level);
                @endphp
                <article class="public-grade-row{{ $level <= 3 ? ' public-grade-row--simple' : '' }}" style="--grade-accent: {{ $accent }}">
                    @if ($badgeUrl)
                        <div class="public-grade-row__badge">
                            <img
                                src="{{ $badgeUrl }}"
                                alt="Insigne {{ $rank['name'] }}"
                                class="public-grade-row__img"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    @endif
                    <div class="public-grade-row__copy">
                        <p class="public-grade-row__name">{{ $rank['name'] }}</p>
                        <p class="public-grade-row__bonus">
                            @if (($rank['bonus'] ?? '—') === '—')
                                Sans bonus direct
                            @else
                                Bonus direct · {{ $rank['bonus'] }}
                            @endif
                        </p>
                        <ul class="public-grade-row__conditions">
                            @foreach ($rank['conditions'] as $condition)
                                <li>{{ $condition }}</li>
                            @endforeach
                        </ul>
                        @if (! empty($rank['commissions']))
                            <p class="public-grade-row__comms">{{ implode(' · ', $rank['commissions']) }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section-alt">
    <div class="public-shell">
        @include('partials.public.section-head', [
            'kicker' => 'Annexe',
            'title' => 'Pourcentages par grade',
            'lead' => 'PV de maintenance du mois requis pour toucher les bonus. L’indirect est un différentiel de taux.',
            'center' => true,
        ])
        <div class="public-table-wrap">
            <table class="public-table public-table--grades">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Direct</th>
                        <th>Indirect</th>
                        <th>Leadership</th>
                        <th>PV pers.</th>
                        <th>PV équipe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($site['bonus_table'] as $row)
                        @php
                            $level = (int) $row['level'];
                            $badgeUrl = MlmRank::imageUrl($level);
                        @endphp
                        <tr style="--grade-accent: {{ MlmRank::dashboardAccent($level) }}">
                            <td>
                                <span class="public-table-grade">
                                    @if ($badgeUrl)
                                        <span class="public-table-grade__badge">
                                            <img src="{{ $badgeUrl }}" alt="" class="public-table-grade__img" width="36" height="40" loading="lazy" decoding="async">
                                        </span>
                                    @endif
                                    <span>{{ $row['name'] }}</span>
                                </span>
                            </td>
                            <td>{{ $row['direct'] }}</td>
                            <td>{{ $row['indirect'] }}</td>
                            <td>{{ $row['leadership'] }}</td>
                            <td>{{ $row['pv_personal'] }}</td>
                            <td>{{ $row['pv_team'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-sm text-center text-[var(--text-muted)] mt-6">
            PV pers. = PV personnels du mois · PV équipe = PV groupe du mois (leadership). Bénéfice de détail : de 5 $ à 15 $ selon le produit. Détails dans le
            <a href="{{ asset('docs/livret-salang-international.pdf') }}" class="underline" target="_blank" rel="noopener">livret PDF</a>
            et l’espace membre (Mes grades).
        </p>
    </div>
</section>

<section class="public-cta-banner">
    <div class="public-shell public-cta-banner__inner">
        <div>
            <p class="public-kicker">Inscription</p>
            <h2 class="public-heading">Rejoignez-nous dès aujourd’hui</h2>
            <p class="public-cta-banner__lead">{{ $site['promise'] }}</p>
        </div>
        <div class="public-cta-banner__actions">
            <a href="{{ route('register') }}" class="public-btn-primary">Créer un compte — {{ $site['membership_fee_usd'] }}&nbsp;USD</a>
            <a href="{{ route('contact') }}" class="public-btn-hero-secondary">Nous contacter</a>
        </div>
    </div>
</section>
@endsection
