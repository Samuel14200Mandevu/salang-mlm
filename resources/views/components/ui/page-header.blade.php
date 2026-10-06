@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="member-page-intro mb-6 animate-fadeInUp">
    @if (!empty($breadcrumbs))
        <nav class="mb-2 text-sm text-[var(--text-muted)]" aria-label="Fil d'Ariane">
            @foreach ($breadcrumbs as $label => $url)
                <a href="{{ $url }}" class="text-[var(--text-secondary)] hover:text-[var(--color-primary-600)] transition-colors">{{ $label }}</a>
                @if (!$loop->last)<span class="mx-2" aria-hidden="true">/</span>@endif
            @endforeach
        </nav>
    @endif
    <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">{{ $title }}</h1>
    @if ($subtitle)
        <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $subtitle }}</p>
    @endif
</div>
