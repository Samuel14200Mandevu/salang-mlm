@include('profile.partials.account-banner', [
    'eyebrow' => 'Compte',
    'title' => $title,
    'sub' => $sub ?? null,
    'backUrl' => $backUrl ?? route('profile.index'),
    'showAssistantFabSwitch' => $showAssistantFabSwitch ?? false,
])
