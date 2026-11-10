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
<div class="member-dash-head member-dash-head--bento animate-fadeInUp">
    <div class="min-w-0">
        <p class="member-dash-head__eyebrow">Accueil</p>
        <h1 class="member-dash-head__title">
            {{ $salutation }}, <span>{{ $userName }}</span>
        </h1>
        <p class="member-dash-head__sub">
            <span>{{ now()->translatedFormat('l j F') }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ now()->format('H:i') }}</span>
        </p>
    </div>
</div>
