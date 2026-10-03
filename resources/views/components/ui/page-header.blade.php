@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="mb-6">
    @if (!empty($breadcrumbs))
        <nav class="mb-2 text-sm text-neutral-500" aria-label="Fil d'Ariane">
            @foreach ($breadcrumbs as $label => $url)
                <a href="{{ $url }}" class="hover:text-primary-600">{{ $label }}</a>
                @if (!$loop->last)<span class="mx-2" aria-hidden="true">/</span>@endif
            @endforeach
        </nav>
    @endif
    <h1 class="text-2xl font-semibold text-neutral-900">{{ $title }}</h1>
    @if ($subtitle)
        <p class="mt-1 text-sm text-neutral-600">{{ $subtitle }}</p>
    @endif
</div>
