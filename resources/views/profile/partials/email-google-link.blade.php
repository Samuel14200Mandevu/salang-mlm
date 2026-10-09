@php
    $canLinkGoogle = $user->hasPlaceholderEmail();
    $googleLinked = $user->isGoogleLinked();
@endphp

<div class="profile-email-google {{ $canLinkGoogle ? 'profile-email-google--placeholder' : '' }}">
    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Email</label>

    @if($canLinkGoogle)
        <div class="profile-email-google__placeholder-body">
        <p class="text-sm text-[var(--text-primary)] font-medium break-all">{{ $user->email }}</p>
        <p class="text-xs text-[var(--text-secondary)] leading-relaxed">
            Adresse provisoire Salang. Associez votre compte Google pour vous connecter avec Gmail et recevoir les notifications.
        </p>
        <a href="{{ route('profile.google.link') }}" class="profile-email-google__btn auth-social">
            @include('partials.auth.google-icon')
            <span>Associer mon compte Google</span>
        </a>
        </div>
    @elseif($googleLinked)
        <div class="flex flex-wrap items-center gap-2">
            <p class="text-sm text-[var(--text-primary)] font-medium break-all">{{ $user->email }}</p>
            <span class="text-chip text-chip--success text-xs">Google</span>
        </div>
        <p class="mt-1 text-xs text-[var(--text-muted)]">Compte Google associé.</p>
    @else
        <input type="email" value="{{ $user->email }}" class="input text-sm sm:text-base opacity-70 cursor-not-allowed" disabled readonly>
        <p class="mt-1 text-xs text-[var(--text-muted)]">Pour modifier l’email, contactez le support.</p>
    @endif
</div>
