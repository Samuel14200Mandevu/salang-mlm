@extends('layouts.app')

@section('title', 'Aide / FAQ')

@section('content')
<div class="member-page-intro mb-4">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">Aide / FAQ membres</h1>
    <p class="text-sm text-[var(--text-secondary)] mt-1">Affiché dans le centre d’aide et l’assistant Salang.</p>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('it-manager.faq.update') }}" class="space-y-4" id="faq-form">
    @csrf
    @method('PUT')

    <div id="faq-list" class="space-y-3">
        @foreach ($faqs as $i => $faq)
            <div class="faq-row rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-2">
                <input type="text" name="faqs[{{ $i }}][question]" value="{{ $faq['question'] }}"
                       placeholder="Question"
                       class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                <textarea name="faqs[{{ $i }}][answer]" rows="3" placeholder="Réponse"
                          class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">{{ $faq['answer'] }}</textarea>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button" id="faq-add" class="btn btn-outline">Ajouter une question</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const list = document.getElementById('faq-list');
    const addBtn = document.getElementById('faq-add');
    if (!list || !addBtn) return;

    addBtn.addEventListener('click', function () {
        const index = list.querySelectorAll('.faq-row').length;
        const row = document.createElement('div');
        row.className = 'faq-row rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-2';
        row.innerHTML = `
            <input type="text" name="faqs[${index}][question]" placeholder="Question"
                   class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
            <textarea name="faqs[${index}][answer]" rows="3" placeholder="Réponse"
                      class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm"></textarea>`;
        list.appendChild(row);
    });
})();
</script>
@endpush
