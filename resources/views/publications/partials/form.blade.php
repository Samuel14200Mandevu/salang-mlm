@php
    $publication = $publication ?? null;
    $selectedType = old('type', $publication?->type ?? 'event');
@endphp

<div class="publication-form space-y-5">
    <section class="publication-form-section" aria-labelledby="publication-section-info">
        <h2 id="publication-section-info" class="publication-form-section__title">Informations</h2>
        <div class="publication-form-section__body space-y-4">
            <div>
                <label for="title" class="publication-form-label">Titre <span class="text-red-500">*</span></label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $publication?->title) }}"
                       class="input w-full"
                       required
                       maxlength="255"
                       placeholder="Ex. Convention annuelle, Promo rentrée…">
                @error('title')
                    <p class="publication-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="publication-form-label">Type <span class="text-red-500">*</span></span>
                <div class="publication-type-picker" role="radiogroup" aria-label="Type de publication">
                    <label class="publication-type-picker__option {{ $selectedType === 'event' ? 'is-selected' : '' }}">
                        <input type="radio" name="type" value="event" class="sr-only publication-type-input" @checked($selectedType === 'event')>
                        <span class="publication-type-picker__icon publication-type-picker__icon--event" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <span class="publication-type-picker__label">Événement</span>
                        <span class="publication-type-picker__hint">Rencontre, convention, annonce</span>
                    </label>
                    <label class="publication-type-picker__option {{ $selectedType === 'promotion' ? 'is-selected' : '' }}">
                        <input type="radio" name="type" value="promotion" class="sr-only publication-type-input" @checked($selectedType === 'promotion')>
                        <span class="publication-type-picker__icon publication-type-picker__icon--promo" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                        </span>
                        <span class="publication-type-picker__label">Promotion</span>
                        <span class="publication-type-picker__hint">Offre limitée dans le temps</span>
                    </label>
                </div>
                @error('type')
                    <p class="publication-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="publication-form-label">Description</label>
                <textarea name="description"
                          id="description"
                          rows="4"
                          class="input w-full"
                          placeholder="Détails visibles sur la fiche publication…">{{ old('description', $publication?->description) }}</textarea>
                @error('description')
                    <p class="publication-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="publication-form-section" aria-labelledby="publication-section-dates">
        <h2 id="publication-section-dates" class="publication-form-section__title">Dates</h2>
        <div class="publication-form-section__body grid grid-cols-1 sm:grid-cols-2 gap-3 publication-dates">
            <div>
                <label for="start_date" class="publication-form-label">Date de début</label>
                <input type="date"
                       name="start_date"
                       id="start_date"
                       value="{{ old('start_date', $publication?->start_date?->format('Y-m-d')) }}"
                       class="input w-full">
            </div>
            <div class="publication-end-date">
                <label for="end_date" class="publication-form-label">Date de fin</label>
                <input type="date"
                       name="end_date"
                       id="end_date"
                       value="{{ old('end_date', $publication?->end_date?->format('Y-m-d')) }}"
                       class="input w-full">
                <p class="publication-form-hint publication-end-date-hint">Recommandée pour les promotions.</p>
            </div>
        </div>
    </section>

    <section class="publication-form-section" aria-labelledby="publication-section-visibility">
        <h2 id="publication-section-visibility" class="publication-form-section__title">Visibilité</h2>
        <label class="publication-publish-toggle card p-3 flex items-start gap-3 cursor-pointer">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox"
                   name="is_published"
                   value="1"
                   class="mt-1 rounded border-[var(--border-color)]"
                   @checked(old('is_published', $publication?->is_published ?? true))>
            <span class="min-w-0">
                <span class="block text-sm font-semibold text-[var(--text-primary)]">Publier pour les membres</span>
                <span class="block text-xs text-[var(--text-secondary)] mt-0.5">Sinon, la publication reste un brouillon dans la galerie supervision.</span>
            </span>
        </label>
    </section>

    <section class="publication-form-section" aria-labelledby="publication-section-media">
        <h2 id="publication-section-media" class="publication-form-section__title">Médias</h2>
        <div class="publication-form-section__body space-y-3">
            <p class="publication-form-hint">Jusqu’à 10 fichiers · 50 Mo max · JPG, PNG, WebP, MP4, MOV</p>

            <div class="publication-upload-zone drop-zone"
                 id="publicationDropZone"
                 role="button"
                 tabindex="0"
                 aria-controls="medias"
                 aria-label="Ajouter des photos ou vidéos">
                <input type="file"
                       name="medias[]"
                       id="medias"
                       class="sr-only"
                       accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime"
                       multiple>
                <svg class="publication-upload-zone__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="publication-upload-zone__title">Glissez vos fichiers ici</p>
                <p class="publication-upload-zone__sub">ou <span class="text-primary-500 font-semibold">parcourir</span></p>
            </div>

            <div id="media-preview-meta" class="publication-media-preview-meta hidden text-xs font-medium text-[var(--text-secondary)]"></div>
            <div id="media-preview" class="publication-media-preview grid grid-cols-2 sm:grid-cols-4 gap-2"></div>
            @error('medias')
                <p class="publication-form-error">{{ $message }}</p>
            @enderror
            @error('medias.*')
                <p class="publication-form-error">{{ $message }}</p>
            @enderror
        </div>
    </section>
</div>

@push('scripts')
<script>
(function () {
    const typeInputs = document.querySelectorAll('.publication-type-input');
    const typeOptions = document.querySelectorAll('.publication-type-picker__option');
    const endWrap = document.querySelector('.publication-end-date');
    const endHint = document.querySelector('.publication-end-date-hint');
    const input = document.getElementById('medias');
    const preview = document.getElementById('media-preview');
    const previewMeta = document.getElementById('media-preview-meta');
    const dropZone = document.getElementById('publicationDropZone');

    function selectedType() {
        const checked = document.querySelector('.publication-type-input:checked');
        return checked ? checked.value : 'event';
    }

    function syncTypePickerVisual() {
        const value = selectedType();
        typeOptions.forEach(function (option) {
            const radio = option.querySelector('.publication-type-input');
            option.classList.toggle('is-selected', radio && radio.value === value);
        });
    }

    function toggleEndDate() {
        if (!endWrap) return;
        const isPromo = selectedType() === 'promotion';
        endWrap.style.display = isPromo ? '' : '';
        if (endHint) {
            endHint.textContent = isPromo
                ? 'Indiquez la fin de validité de la promotion.'
                : 'Optionnel pour un événement ponctuel.';
        }
    }

    typeInputs.forEach(function (radio) {
        radio.addEventListener('change', function () {
            syncTypePickerVisual();
            toggleEndDate();
        });
    });
    syncTypePickerVisual();
    toggleEndDate();

    function renderPreview() {
        if (!preview) return;
        preview.innerHTML = '';
        const files = Array.from(input?.files || []);
        if (previewMeta) {
            if (files.length === 0) {
                previewMeta.classList.add('hidden');
                previewMeta.textContent = '';
            } else {
                previewMeta.classList.remove('hidden');
                previewMeta.textContent = files.length + ' fichier(s) sélectionné(s)';
            }
        }
        files.forEach(function (file) {
            const box = document.createElement('div');
            box.className = 'publication-preview-item rounded-lg overflow-hidden border border-[var(--border-color)] aspect-square bg-[var(--bg-secondary)] relative';
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'w-full h-full object-cover';
                img.alt = '';
                img.src = URL.createObjectURL(file);
                box.appendChild(img);
            } else {
                box.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-[10px] sm:text-xs p-2 text-center text-[var(--text-secondary)]"><span class="text-lg mb-1" aria-hidden="true">▶</span><span class="line-clamp-3">' + file.name + '</span></div>';
            }
            preview.appendChild(box);
        });
    }

    input?.addEventListener('change', renderPreview);

    if (dropZone && input) {
        dropZone.addEventListener('click', function () { input.click(); });
        dropZone.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                input.click();
            }
        });
        ['dragenter', 'dragover'].forEach(function (name) {
            dropZone.addEventListener(name, function (e) {
                e.preventDefault();
                dropZone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (name) {
            dropZone.addEventListener(name, function (e) {
                e.preventDefault();
                dropZone.classList.remove('dragover');
            });
        });
        dropZone.addEventListener('drop', function (e) {
            if (!e.dataTransfer?.files?.length) return;
            input.files = e.dataTransfer.files;
            renderPreview();
        });
    }
})();
</script>
@endpush
