@php
    $eyebrow = $eyebrow ?? 'Espace membre';
    $title = $title ?? 'Compte';
@endphp
<div class="profile-mobile-banner animate-fadeInUp">
    <div class="shop-catalog-banner profile-catalog-banner profile-catalog-banner--hub">
        <div class="profile-catalog-banner__head shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">{{ $eyebrow }}</p>
            <p class="shop-catalog-banner__title">{{ $title }}</p>
        </div>

        <div class="profile-account-user profile-account-user--in-banner">
            <label for="avatar_input" class="profile-account-user__avatar profile-account-user__avatar--clickable" aria-label="Changer la photo">
                @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="">
                @else
                    <span class="profile-account-user__initials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                @endif
            </label>
            <div class="profile-account-user__body">
                <p class="profile-account-user__name">{{ $user->name }}</p>
                <p class="profile-account-user__meta">{{ $user->package?->name ?? 'Starter' }} · Code {{ $user->sponsor_id }}</p>
                <button type="button" class="profile-account-user__edit" data-profile-open-edit>
                    <span>Modifier le profil</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
