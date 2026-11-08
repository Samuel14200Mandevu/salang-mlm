@extends('layouts.app')

@section('title', 'FAQ membres')

@push('styles')
<style>
/* FAQ desktop — mêmes primitives que l’accueil IT (.it-dash) */
.it-manager-shell .it-faq-desk .card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.it-manager-shell .it-faq-desk .faq-row {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    padding: 1rem 1.1rem;
}
.it-manager-shell .it-faq-desk .faq-row + .faq-row {
    margin-top: 0.75rem;
}
.it-manager-shell .it-faq-desk .faq-row__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.it-manager-shell .it-faq-desk .faq-row__index {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #1E5DAD;
    background: rgba(30, 93, 173, 0.14);
}
.it-manager-shell .it-faq-desk .faq-row__label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.it-manager-shell .it-faq-desk .faq-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 8px;
    border: 1px solid transparent;
    color: var(--text-tertiary);
    background: transparent;
    cursor: pointer;
}
.it-manager-shell .it-faq-desk .faq-remove:hover {
    color: #B91C1C;
    background: rgba(185, 28, 28, 0.08);
    border-color: rgba(185, 28, 28, 0.15);
}
.it-manager-shell .it-faq-desk .faq-field + .faq-field {
    margin-top: 0.75rem;
}
.it-manager-shell .it-faq-desk .faq-field label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.it-manager-shell .it-faq-desk .faq-field input,
.it-manager-shell .it-faq-desk .faq-field textarea {
    width: 100%;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 0.813rem;
    padding: 0.5rem 0.75rem;
    outline: none;
}
.it-manager-shell .it-faq-desk .faq-field textarea {
    min-height: 5rem;
    resize: vertical;
}
.it-manager-shell .it-faq-desk .faq-field input:focus,
.it-manager-shell .it-faq-desk .faq-field textarea:focus {
    border-color: var(--primary-blue, #1E5DAD);
    box-shadow: 0 0 0 3px rgba(30, 93, 173, 0.12);
    background: var(--bg-card);
}
.it-manager-shell .it-faq-desk .faq-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}
.it-manager-shell .it-faq-desk .badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
}
.it-manager-shell .it-faq-desk .dest-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}
.it-manager-shell .it-faq-desk .dest-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.it-manager-shell .it-faq-desk .dest-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(30, 93, 173, 0.14);
    color: #1E5DAD;
}
.it-manager-shell .it-faq-desk .dest-icon.is-success {
    background: rgba(28, 126, 74, 0.14);
    color: #1C7E4A;
}
</style>
@endpush

@section('content')
{{-- Desktop : même langage que l’accueil IT --}}
<div class="it-faq-desk it-desktop-only space-y-4 max-w-3xl">
    <div class="animate-fadeInUp flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">FAQ membres</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                Questions-réponses du centre d’aide et de l’assistant Salang.
            </p>
        </div>
        <span class="badge" id="faq-count-badge-desktop">
            <span id="faq-count-label-desktop">{{ count($faqs) }}</span> / 40
        </span>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="dest-grid animate-fadeInUp delay-1">
        <div class="dest-card">
            <span class="dest-icon" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-[var(--text-primary)]">Centre d’aide</p>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">Profil membre · Questions fréquentes</p>
            </div>
        </div>
        <div class="dest-card">
            <span class="dest-icon is-success" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-[var(--text-primary)]">Assistant Salang</p>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">Suggestions dans le chat d’aide</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('it-manager.faq.update') }}" class="card animate-fadeInUp delay-2" id="faq-form-desktop" data-faq-form>
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between gap-2 mb-4">
            <div>
                <p class="text-sm font-semibold text-[var(--text-primary)]">Bibliothèque</p>
                <p class="text-xs text-[var(--text-tertiary)] mt-0.5">Les lignes vides sont ignorées à l’enregistrement.</p>
            </div>
        </div>

        <div class="faq-list" data-faq-list aria-live="polite">
            @foreach ($faqs as $i => $faq)
                <article class="faq-row" data-faq-row>
                    <div class="faq-row__top">
                        <div class="flex items-center gap-2">
                            <span class="faq-row__index" data-faq-index>{{ $i + 1 }}</span>
                            <span class="faq-row__label">Question-réponse</span>
                        </div>
                        <button type="button" class="faq-remove" data-faq-remove title="Retirer" aria-label="Retirer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                    <div class="faq-field">
                        <label>Question</label>
                        <input type="text" name="faqs[{{ $i }}][question]" value="{{ $faq['question'] }}" maxlength="255"
                               placeholder="Ex. Comment activer mon compte ?">
                    </div>
                    <div class="faq-field">
                        <label>Réponse</label>
                        <textarea name="faqs[{{ $i }}][answer]" rows="3" maxlength="2000"
                                  placeholder="Réponse claire et professionnelle…">{{ $faq['answer'] }}</textarea>
                    </div>
                </article>
            @endforeach
        </div>

        <p class="hidden text-sm text-[var(--text-secondary)] text-center py-6" data-faq-empty>
            Aucune entrée. Ajoutez une première question.
        </p>

        <div class="faq-actions">
            <button type="button" class="btn btn-outline btn-sm" data-faq-add>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter
            </button>
            <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
        </div>
    </form>
</div>

{{-- Mobile : inchangé (formulaire simple) --}}
<div class="it-mobile-only space-y-4">
    <div class="member-page-intro">
        <h1 class="text-xl font-bold text-[var(--text-primary)]">FAQ membres</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Centre d’aide et assistant Salang.</p>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('it-manager.faq.update') }}" class="space-y-3" data-faq-form>
        @csrf
        @method('PUT')

        <div class="space-y-3" data-faq-list>
            @foreach ($faqs as $i => $faq)
                <article class="faq-row rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-3 space-y-2" data-faq-row>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-[var(--text-secondary)]">
                            #<span data-faq-index>{{ $i + 1 }}</span>
                        </span>
                        <button type="button" class="text-xs font-medium text-rose-600" data-faq-remove>Retirer</button>
                    </div>
                    <input type="text" name="faqs[{{ $i }}][question]" value="{{ $faq['question'] }}" maxlength="255"
                           placeholder="Question"
                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                    <textarea name="faqs[{{ $i }}][answer]" rows="3" maxlength="2000" placeholder="Réponse"
                              class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">{{ $faq['answer'] }}</textarea>
                </article>
            @endforeach
        </div>

        <p class="hidden text-sm text-[var(--text-secondary)] text-center py-4" data-faq-empty>Aucune entrée.</p>

        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline" data-faq-add>Ajouter</button>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const MAX = 40;

    function bindForm(form) {
        if (!form) return;
        const list = form.querySelector('[data-faq-list]');
        const addBtn = form.querySelector('[data-faq-add]');
        const emptyHint = form.querySelector('[data-faq-empty]');
        const countLabel = document.getElementById('faq-count-label-desktop');
        if (!list || !addBtn) return;

        const isDesktop = form.hasAttribute('id') && form.id === 'faq-form-desktop';

        function rows() {
            return list.querySelectorAll('[data-faq-row]');
        }

        function updateMeta() {
            const n = rows().length;
            if (countLabel && isDesktop) countLabel.textContent = String(n);
            if (emptyHint) emptyHint.classList.toggle('hidden', n > 0);
            addBtn.disabled = n >= MAX;
            rows().forEach(function (row, i) {
                const badge = row.querySelector('[data-faq-index]');
                if (badge) badge.textContent = String(i + 1);
                row.querySelectorAll('[name^="faqs["]').forEach(function (el) {
                    const name = el.getAttribute('name') || '';
                    el.setAttribute('name', name.replace(/faqs\[\d+]/, 'faqs[' + i + ']'));
                });
            });
        }

        function bindRemove(row) {
            const btn = row.querySelector('[data-faq-remove]');
            if (!btn) return;
            btn.addEventListener('click', function () {
                if (rows().length <= 1) {
                    row.querySelectorAll('input, textarea').forEach(function (el) { el.value = ''; });
                    updateMeta();
                    return;
                }
                row.remove();
                updateMeta();
            });
        }

        function createRow(index) {
            const row = document.createElement('article');
            if (isDesktop) {
                row.className = 'faq-row';
                row.setAttribute('data-faq-row', '');
                row.innerHTML =
                    '<div class="faq-row__top">' +
                        '<div class="flex items-center gap-2">' +
                            '<span class="faq-row__index" data-faq-index>' + (index + 1) + '</span>' +
                            '<span class="faq-row__label">Question-réponse</span>' +
                        '</div>' +
                        '<button type="button" class="faq-remove" data-faq-remove title="Retirer" aria-label="Retirer">' +
                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
                        '</button>' +
                    '</div>' +
                    '<div class="faq-field"><label>Question</label>' +
                        '<input type="text" name="faqs[' + index + '][question]" maxlength="255" placeholder="Ex. Comment activer mon compte ?">' +
                    '</div>' +
                    '<div class="faq-field"><label>Réponse</label>' +
                        '<textarea name="faqs[' + index + '][answer]" rows="3" maxlength="2000" placeholder="Réponse claire et professionnelle…"></textarea>' +
                    '</div>';
            } else {
                row.className = 'faq-row rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-3 space-y-2';
                row.setAttribute('data-faq-row', '');
                row.innerHTML =
                    '<div class="flex items-center justify-between gap-2">' +
                        '<span class="text-xs font-semibold text-[var(--text-secondary)]">#<span data-faq-index>' + (index + 1) + '</span></span>' +
                        '<button type="button" class="text-xs font-medium text-rose-600" data-faq-remove>Retirer</button>' +
                    '</div>' +
                    '<input type="text" name="faqs[' + index + '][question]" maxlength="255" placeholder="Question" class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">' +
                    '<textarea name="faqs[' + index + '][answer]" rows="3" maxlength="2000" placeholder="Réponse" class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm"></textarea>';
            }
            bindRemove(row);
            return row;
        }

        rows().forEach(bindRemove);
        addBtn.addEventListener('click', function () {
            if (rows().length >= MAX) return;
            list.appendChild(createRow(rows().length));
            updateMeta();
            const last = list.querySelector('[data-faq-row]:last-child input');
            if (last) last.focus();
        });
        updateMeta();
    }

    document.querySelectorAll('[data-faq-form]').forEach(bindForm);
})();
</script>
@endpush
