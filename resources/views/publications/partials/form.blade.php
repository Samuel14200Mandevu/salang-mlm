@php
    $publication = $publication ?? null;
    $selectedType = old('type', $publication?->type ?? 'event');
@endphp

<div class="publication-social-card">
    <div class="publication-social-author">
        <div class="publication-composer__type" role="radiogroup" aria-label="Type de publication">
            <label class="publication-composer__type-opt {{ $selectedType === 'event' ? 'is-selected' : '' }}">
                <input type="radio" name="type" value="event" class="sr-only publication-type-input" @checked($selectedType === 'event')>
                <span>Événement</span>
            </label>
            <label class="publication-composer__type-opt {{ $selectedType === 'promotion' ? 'is-selected' : '' }}">
                <input type="radio" name="type" value="promotion" class="sr-only publication-type-input" @checked($selectedType === 'promotion')>
                <span>Promotion</span>
            </label>
        </div>
    </div>
    @error('type')
        <p class="publication-form-error">{{ $message }}</p>
    @enderror

    <div class="publication-social-fields">
        <label for="title" class="sr-only">Titre</label>
        <input type="text"
               name="title"
               id="title"
               value="{{ old('title', $publication?->title) }}"
               class="publication-composer__title-input"
               required
               maxlength="255"
               placeholder="Ajouter un titre…">
        @error('title')
            <p class="publication-form-error">{{ $message }}</p>
        @enderror

        <label for="description" class="sr-only">Description</label>
        <textarea name="description"
                  id="description"
                  rows="4"
                  class="publication-composer__body-input"
                  placeholder="À quoi pensez-vous ?">{{ old('description', $publication?->description) }}</textarea>
        @error('description')
            <p class="publication-form-error">{{ $message }}</p>
        @enderror
    </div>

    <div id="media-preview-wrap" class="publication-social-preview hidden">
        <div id="media-preview-meta" class="publication-media-preview-meta"></div>
        <div id="media-preview" class="publication-media-preview"></div>
    </div>

    <div class="publication-social-toolbar" role="toolbar" aria-label="Joindre un fichier">
        <button type="button" class="publication-social-attach" data-attach="image" aria-label="Photo">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Photo</span>
        </button>
        <button type="button" class="publication-social-attach" data-attach="video" aria-label="Vidéo">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span>Vidéo</span>
        </button>
        <button type="button" class="publication-social-attach" data-attach="pdf" aria-label="PDF">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span>PDF</span>
        </button>
        <input type="file"
               name="medias[]"
               id="medias"
               class="sr-only"
               accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,application/pdf,.pdf"
               multiple>
    </div>
    @error('medias')
        <p class="publication-form-error px-1">{{ $message }}</p>
    @enderror
    @error('medias.*')
        <p class="publication-form-error px-1">{{ $message }}</p>
    @enderror

    <details class="publication-social-more">
        <summary>Options · dates &amp; visibilité</summary>
        <div class="publication-social-more__body">
            <div class="publication-composer__dates publication-dates">
                <div>
                    <label for="start_date" class="publication-composer-label">Début</label>
                    <input type="date"
                           name="start_date"
                           id="start_date"
                           value="{{ old('start_date', $publication?->start_date?->format('Y-m-d')) }}"
                           class="input w-full publication-form-input">
                </div>
                <div class="publication-end-date">
                    <label for="end_date" class="publication-composer-label">Fin</label>
                    <input type="date"
                           name="end_date"
                           id="end_date"
                           value="{{ old('end_date', $publication?->end_date?->format('Y-m-d')) }}"
                           class="input w-full publication-form-input">
                    <p class="publication-form-hint publication-end-date-hint">Optionnel</p>
                </div>
            </div>

            <label class="publication-composer__publish">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox"
                       name="is_published"
                       value="1"
                       id="is_published"
                       class="publication-publish-toggle__checkbox"
                       @checked(old('is_published', $publication?->is_published ?? true))>
                <span class="publication-publish-toggle__switch" aria-hidden="true"></span>
                <span class="publication-composer__publish-text">
                    <span class="publication-composer__publish-title">Visible aux membres</span>
                    <span class="publication-composer__publish-hint">Comme un post public du réseau</span>
                </span>
            </label>
        </div>
    </details>
</div>

@push('scripts')
<script>
(function () {
    const typeInputs = document.querySelectorAll('.publication-type-input');
    const typeOptions = document.querySelectorAll('.publication-composer__type-opt');
    const endHint = document.querySelector('.publication-end-date-hint');
    const input = document.getElementById('medias');
    const preview = document.getElementById('media-preview');
    const previewMeta = document.getElementById('media-preview-meta');
    const previewWrap = document.getElementById('media-preview-wrap');
    const attachBtns = document.querySelectorAll('.publication-social-attach');
    let dataTransfer = new DataTransfer();

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
        if (endHint) {
            endHint.textContent = value === 'promotion'
                ? 'Fin de validité recommandée'
                : 'Optionnel';
        }
    }

    typeInputs.forEach(function (radio) {
        radio.addEventListener('change', syncTypePickerVisual);
    });
    syncTypePickerVisual();

    function syncInputFiles() {
        if (!input) return;
        input.files = dataTransfer.files;
    }

    function renderPreview() {
        if (!preview || !previewWrap) return;
        preview.innerHTML = '';
        const files = Array.from(dataTransfer.files);
        if (files.length === 0) {
            previewWrap.classList.add('hidden');
            if (previewMeta) previewMeta.textContent = '';
            return;
        }
        previewWrap.classList.remove('hidden');
        if (previewMeta) {
            previewMeta.textContent = files.length + ' fichier(s)';
        }
        files.forEach(function (file, index) {
            const box = document.createElement('div');
            box.className = 'publication-preview-item';
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'publication-preview-item__media';
                img.alt = '';
                img.src = URL.createObjectURL(file);
                box.appendChild(img);
            } else if (file.type.startsWith('video/')) {
                box.innerHTML = '<div class="publication-preview-item__video"><span aria-hidden="true">▶</span><span class="publication-preview-item__name">' + file.name + '</span></div>';
            } else {
                box.innerHTML = '<div class="publication-preview-item__doc"><span class="publication-preview-item__doc-badge">PDF</span><span class="publication-preview-item__name">' + file.name + '</span></div>';
            }
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'publication-preview-item__remove';
            remove.setAttribute('aria-label', 'Retirer');
            remove.innerHTML = '&times;';
            remove.addEventListener('click', function () {
                const next = new DataTransfer();
                Array.from(dataTransfer.files).forEach(function (f, i) {
                    if (i !== index) next.items.add(f);
                });
                dataTransfer = next;
                syncInputFiles();
                renderPreview();
            });
            box.appendChild(remove);
            preview.appendChild(box);
        });
    }

    function addFiles(fileList, acceptKind) {
        const incoming = Array.from(fileList || []);
        incoming.forEach(function (file) {
            if (dataTransfer.files.length >= 10) return;
            if (acceptKind === 'image' && !file.type.startsWith('image/')) return;
            if (acceptKind === 'video' && !file.type.startsWith('video/')) return;
            if (acceptKind === 'pdf' && file.type !== 'application/pdf' && !/\.pdf$/i.test(file.name)) return;
            dataTransfer.items.add(file);
        });
        syncInputFiles();
        renderPreview();
    }

    attachBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!input) return;
            const kind = btn.getAttribute('data-attach');
            if (kind === 'image') {
                input.accept = 'image/jpeg,image/png,image/webp';
            } else if (kind === 'video') {
                input.accept = 'video/mp4,video/quicktime';
            } else {
                input.accept = 'application/pdf,.pdf';
            }
            input.dataset.attachKind = kind;
            input.click();
        });
    });

    input?.addEventListener('change', function () {
        addFiles(input.files, input.dataset.attachKind || 'all');
        input.value = '';
        input.accept = 'image/jpeg,image/png,image/webp,video/mp4,video/quicktime,application/pdf,.pdf';
    });
})();
</script>
@endpush
