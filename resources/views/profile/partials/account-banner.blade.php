@php
    $eyebrow = $eyebrow ?? 'Salang MLM';
    $title = $title ?? 'Compte';
    $sub = $sub ?? null;
    $backUrl = $backUrl ?? null;
    $showPhotoTool = $showPhotoTool ?? false;
@endphp
<div class="profile-mobile-banner animate-fadeInUp">
    <div class="shop-catalog-banner profile-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">{{ $eyebrow }}</p>
            <p class="shop-catalog-banner__title">{{ $title }}</p>
            @if($sub)
                <p class="shop-catalog-banner__sub">{{ $sub }}</p>
            @endif
        </div>
        @if($showPhotoTool)
            <div class="shop-catalog-banner__tools">
                @include('profile.partials.account-banner-tools-photo')
            </div>
        @elseif($backUrl || !empty($showAssistantFabSwitch))
            <div class="shop-catalog-banner__tools profile-banner-tools">
                @if(!empty($showAssistantFabSwitch))
                    @include('profile.partials.assistant-fab-toggle', [
                        'toggleId' => 'assistantFabTogglePage',
                        'compact' => true,
                    ])
                @endif
                @if($backUrl)
                <a href="{{ $backUrl }}" class="shop-banner-icon-btn" aria-label="Retour au compte">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                @endif
            </div>
        @endif
    </div>
</div>
