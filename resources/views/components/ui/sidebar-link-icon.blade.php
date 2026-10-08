@props(['tone' => 'blue'])

<span {{ $attributes->merge(['class' => 'sidebar-link-icon sidebar-link-icon--' . $tone]) }} aria-hidden="true">
    {{ $slot }}
</span>
