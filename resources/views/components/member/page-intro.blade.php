@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'member-page-intro animate-fadeInUp']) }}>
    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">{{ $title }}</h1>
    @if($subtitle)
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</div>
