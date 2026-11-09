@php
    $hour = (int) now()->format('G');
    $salutation = match (true) {
        $hour >= 5 && $hour < 12 => 'Bonjour',
        $hour >= 12 && $hour < 18 => 'Bon après-midi',
        $hour >= 18 && $hour < 22 => 'Bonsoir',
        default => 'Bonne nuit',
    };
    $userName = $user->name ?? 'Membre';
@endphp
<div class="member-dash-head animate-fadeInUp">
    <div class="min-w-0">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Accueil</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">
            {{ $salutation }},
            <span class="font-semibold text-primary-500">{{ $userName }}</span>
            <span class="text-[var(--text-tertiary)] text-xs ml-1">({{ now()->format('H:i') }})</span>
            <span class="text-[var(--text-tertiary)] text-xs">· {{ $currentRankName ?? 'Distributeur' }} · Niv. {{ $dashboardLevelNumber }}</span>
        </p>
    </div>
</div>
