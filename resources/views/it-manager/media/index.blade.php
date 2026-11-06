@extends('layouts.app')

@section('title', 'Médias')

@section('content')
<div class="member-page-intro mb-4">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">Médias / galerie</h1>
    <p class="text-sm text-[var(--text-secondary)] mt-1">Images pour le site et les publications (max 4 Mo).</p>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('it-manager.media.store') }}" enctype="multipart/form-data"
      class="mb-6 flex flex-col sm:flex-row gap-2 items-start rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
    @csrf
    <input type="file" name="image" accept="image/*" required
           class="text-sm text-[var(--text-secondary)]">
    <button type="submit" class="btn btn-primary">Uploader</button>
</form>

@if ($files->isEmpty())
    <p class="text-sm text-[var(--text-muted)]">Aucune image pour l’instant.</p>
@else
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach ($files as $file)
            <figure class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
                <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" class="w-full h-32 object-cover" loading="lazy">
                <figcaption class="p-2 space-y-1">
                    <p class="text-xs truncate text-[var(--text-secondary)]" title="{{ $file['name'] }}">{{ $file['name'] }}</p>
                    <input type="text" readonly value="{{ $file['url'] }}"
                           class="w-full text-[0.65rem] rounded border border-[var(--border-light)] bg-[var(--bg-secondary)] px-1.5 py-1"
                           onclick="this.select()">
                    <form method="POST" action="{{ route('it-manager.media.destroy') }}"
                          onsubmit="return confirm('Supprimer cette image ?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="path" value="{{ $file['path'] }}">
                        <button type="submit" class="text-xs font-semibold text-rose-600">Supprimer</button>
                    </form>
                </figcaption>
            </figure>
        @endforeach
    </div>
@endif
@endsection
