@php
    $profileRankLevel = (int) ($dashboardLevelNumber ?? $currentRankLevel ?? $user->rank_level ?? 1);
    $profileRankImage = \App\Support\MlmRank::imageUrl($profileRankLevel);
    $profileRankLabel = $currentRankName ?? 'Distributeur';
@endphp
<div class="member-dash-hero member-dash-bento grid grid-cols-1 lg:grid-cols-12 gap-3 md:gap-4 animate-fadeInUp delay-1">
    <div class="lg:col-span-4 card member-dash-profile-card member-dash-profile-card--hero h-full">
        <div class="member-dash-profile-card__top">
            <div class="avatar avatar-xl member-dash-profile-card__avatar avatar-gradient avatar-ring shrink-0">
                @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="Avatar">
                @else
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
            </div>
            <div class="member-dash-profile-card__identity min-w-0 flex-1">
                <h3 class="font-bold text-[var(--text-primary)] truncate">{{ $user->name }}</h3>
                <p class="text-xs text-[var(--text-secondary)]">Membre</p>
            </div>
            @if($profileRankImage)
                <div class="member-dash-profile-card__insignia shrink-0" title="{{ $profileRankLabel }}">
                    <img
                        src="{{ $profileRankImage }}"
                        alt="{{ $profileRankLabel }}"
                        width="80"
                        height="92"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
            @endif
        </div>

        <div class="member-dash-profile-card__sponsor">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mon Parrain</p>
            @if($sponsor)
                <div class="flex items-center gap-3 mt-1">
                    <div class="avatar avatar-md avatar-gradient shrink-0">{{ strtoupper(substr($sponsor->name, 0, 1)) }}</div>
                    <div class="min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base truncate">{{ $sponsor->name }}</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate">{{ $sponsor->email }}</p>
                    </div>
                </div>
            @else
                <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Aucun parrain</p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Vous êtes le premier de votre réseau</p>
            @endif
        </div>

        <div class="member-dash-profile-card__chips grid grid-cols-2 gap-2">
            <div class="member-dash-chip">
                <p class="member-dash-chip__label">Filleuls</p>
                <p class="member-dash-chip__value">{{ $totalDownlines ?? 0 }}</p>
            </div>
            <div class="member-dash-chip">
                <p class="member-dash-chip__label">Mon Code</p>
                <p class="member-dash-chip__value member-dash-chip__value--mono truncate">{{ $user->sponsor_id }}</p>
            </div>
        </div>
    </div>

    <div class="lg:col-span-8 grid grid-cols-2 gap-2 sm:gap-3 lg:items-stretch member-dash-kpi-grid">
        <div class="member-dash-kpi is-primary h-full flex flex-col justify-between min-h-[7.5rem]">
            <p class="member-dash-kpi__label">PV Personnel</p>
            <p class="member-dash-kpi__value">{{ number_format($pvPersonnel ?? 0) }}</p>
        </div>
        <div class="member-dash-kpi is-cumul h-full flex flex-col justify-between min-h-[7.5rem]">
            <p class="member-dash-kpi__label">PV Cumulé</p>
            <p class="member-dash-kpi__value">{{ number_format($pvCumul ?? 0) }}</p>
        </div>
        <div class="member-dash-kpi is-month h-full flex flex-col justify-between min-h-[7.5rem]">
            <p class="member-dash-kpi__label">PV Mensuel</p>
            <p class="member-dash-kpi__value">{{ number_format($rankProgress['monthly_pv'] ?? 0) }}</p>
        </div>
        <div class="member-dash-kpi is-team h-full flex flex-col justify-between min-h-[7.5rem]">
            <p class="member-dash-kpi__label">PV Mensuel équipe</p>
            <p class="member-dash-kpi__value">{{ number_format($pvMensuelEquipe ?? 0) }}</p>
        </div>
    </div>
</div>
