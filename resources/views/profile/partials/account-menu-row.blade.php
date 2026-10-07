@props([
    'href' => null,
    'label',
    'danger' => false,
    'badge' => null,
    'icon',
    'fabSwitch' => false,
    'fabSwitchId' => null,
])

@if($href && $fabSwitch)
    <div {{ $attributes->merge(['class' => 'profile-account-row profile-account-row--fab-switch']) }}>
        <a href="{{ $href }}" class="profile-account-row__nav">
            <span class="profile-account-row__icon" aria-hidden="true">{!! $icon !!}</span>
            <span class="profile-account-row__label">{{ $label }}</span>
        </a>
        <div class="profile-account-row__switch-wrap">
            @include('profile.partials.assistant-fab-toggle', [
                'toggleId' => $fabSwitchId ?? 'assistantFabToggleMenu',
            ])
        </div>
    </div>
@elseif($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'profile-account-row' . ($danger ? ' profile-account-row--danger' : '')]) }}>
        <span class="profile-account-row__icon {{ $danger ? 'profile-account-row__icon--danger' : '' }}" aria-hidden="true">{!! $icon !!}</span>
        <span class="profile-account-row__label">{{ $label }}</span>
        @if($badge)
            <span class="profile-account-row__badge">{{ $badge }}</span>
        @endif
        <svg class="profile-account-row__chev {{ $danger ? 'profile-account-row__chev--danger' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
@endif
