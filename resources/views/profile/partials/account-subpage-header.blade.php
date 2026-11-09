@include('profile.partials.account-banner', [
    'eyebrow' => 'Compte',
    'title' => $title,
    'sub' => $sub ?? null,
    'backUrl' => ($backUrl ?? true) === false ? null : (($backUrl ?? null) ?: route('profile.index')),
    'showAssistantFabSwitch' => $showAssistantFabSwitch ?? false,
])
