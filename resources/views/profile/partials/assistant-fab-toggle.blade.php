@php
    $toggleId = $toggleId ?? 'assistantFabToggle';
    $compact = !empty($compact);
@endphp
<label
    class="profile-assistant-fab-switch{{ $compact ? ' profile-assistant-fab-switch--compact' : '' }} is-on"
    for="{{ $toggleId }}"
>
    <input
        type="checkbox"
        id="{{ $toggleId }}"
        class="profile-assistant-fab-switch__input"
        data-assistant-fab-switch
        checked
        aria-label="Assistant flottant"
    >
    <span class="profile-assistant-fab-switch__track" aria-hidden="true">
        <span class="profile-assistant-fab-switch__thumb"></span>
    </span>
</label>
