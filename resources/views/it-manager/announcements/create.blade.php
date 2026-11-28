@extends('layouts.app')

@section('title', 'Annonces')

@section('content')
{{-- Desktop --}}
<div class="it-desktop-head it-desktop-only mb-6 items-center justify-between gap-4">
    <div class="member-page-intro mb-0 min-w-0">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Annonce aux membres</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">
            Envoi dans la cloche de notifications de tous les membres actifs.
        </p>
    </div>
</div>

{{-- Mobile --}}
<div class="shop-catalog-head it-mobile-only mb-5">
    <div class="shop-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Notifications</p>
            <p class="shop-catalog-banner__title">Annonce aux membres</p>
            <p class="shop-catalog-banner__sub">Cloche · tous les membres actifs</p>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-rose-800">
        {{ session('error') }}
    </div>
@endif

<div class="follow-up-stat-grid mb-5">
    <div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-3">
        <p class="text-lg font-bold tabular-nums text-[var(--text-primary)]">{{ number_format($membersCount) }}</p>
        <p class="text-xs text-[var(--text-muted)] uppercase tracking-wide">Membres destinataires</p>
    </div>
    <div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-3">
        <p class="text-lg font-bold text-[var(--text-primary)]">Cloche</p>
        <p class="text-xs text-[var(--text-muted)] uppercase tracking-wide">Badge + liste</p>
    </div>
    <div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-3">
        <p class="text-lg font-bold text-[var(--text-primary)]">Annonces</p>
        <p class="text-xs text-[var(--text-muted)] uppercase tracking-wide">Catégorie notif.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
    <form method="POST"
          action="{{ route('it-manager.announcements.store') }}"
          id="announcementForm"
          class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 sm:p-5 space-y-4">
        @csrf

        <div>
            <div class="flex items-center justify-between gap-2 mb-1">
                <label for="announcementTitle" class="block text-xs font-medium text-[var(--text-secondary)]">Titre *</label>
                <span class="text-[0.65rem] tabular-nums text-[var(--text-muted)]" data-announcement-count="title">0 / 120</span>
            </div>
            <input type="text"
                   id="announcementTitle"
                   name="title"
                   value="{{ old('title') }}"
                   required
                   maxlength="120"
                   autocomplete="off"
                   placeholder="Ex. Maintenance planifiée ce soir"
                   class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2.5 text-sm text-[var(--text-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]/30 focus:border-[var(--primary)]"
                   data-announcement-field="title">
            @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between gap-2 mb-1">
                <label for="announcementMessage" class="block text-xs font-medium text-[var(--text-secondary)]">Message *</label>
                <span class="text-[0.65rem] tabular-nums text-[var(--text-muted)]" data-announcement-count="message">0 / 1000</span>
            </div>
            <textarea id="announcementMessage"
                      name="message"
                      rows="5"
                      required
                      maxlength="1000"
                      placeholder="Texte affiché dans la cloche du membre…"
                      class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2.5 text-sm text-[var(--text-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]/30 focus:border-[var(--primary)] resize-y min-h-[7.5rem]"
                      data-announcement-field="message">{{ old('message') }}</textarea>
            @error('message')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="announcementUrl" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Lien (optionnel)</label>
            <input type="url"
                   id="announcementUrl"
                   name="url"
                   value="{{ old('url') }}"
                   maxlength="500"
                   placeholder="https://…"
                   class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2.5 text-sm text-[var(--text-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]/30 focus:border-[var(--primary)]"
                   data-announcement-field="url">
            @error('url')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            <p class="text-[0.65rem] text-[var(--text-muted)] mt-1">Si renseigné, le membre pourra ouvrir ce lien depuis la notification.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button type="submit"
                    id="announcementSubmit"
                    class="btn btn-primary">
                Envoyer à {{ number_format($membersCount) }} membre(s)
            </button>
            <p class="text-xs text-[var(--text-muted)]">
                L’envoi peut prendre quelques secondes.
            </p>
        </div>
    </form>

    <aside class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 sm:p-5">
        <p class="text-[0.6875rem] font-semibold uppercase tracking-wide text-[var(--text-muted)] mb-3">
            Aperçu cloche
        </p>
        <div class="rounded-lg border border-[var(--border-light)] bg-[var(--bg-secondary)]/60 p-3.5">
            <div class="flex items-start gap-3">
                <span class="mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-[var(--primary)]" aria-hidden="true"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[0.65rem] font-semibold uppercase tracking-wide text-[var(--primary)]">Annonces</span>
                        <span class="text-[0.65rem] text-[var(--text-muted)]">à l’instant</span>
                    </div>
                    <p id="announcementPreviewTitle" class="text-sm font-semibold text-[var(--text-primary)] truncate">
                        Titre de l’annonce
                    </p>
                    <p id="announcementPreviewMessage" class="text-xs text-[var(--text-secondary)] mt-1 line-clamp-3 whitespace-pre-wrap">
                        Le message apparaîtra ici…
                    </p>
                    <p id="announcementPreviewUrl" class="hidden text-[0.65rem] text-[var(--primary)] mt-2 truncate"></p>
                </div>
            </div>
        </div>
        <p class="text-xs text-[var(--text-muted)] mt-3 leading-relaxed">
            Chaque membre actif (hors admin, IT et caisse) reçoit cette carte dans sa cloche, avec badge non lu.
        </p>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('announcementForm');
    if (!form) return;

    const titleInput = form.querySelector('[data-announcement-field="title"]');
    const messageInput = form.querySelector('[data-announcement-field="message"]');
    const urlInput = form.querySelector('[data-announcement-field="url"]');
    const titleCount = document.querySelector('[data-announcement-count="title"]');
    const messageCount = document.querySelector('[data-announcement-count="message"]');
    const previewTitle = document.getElementById('announcementPreviewTitle');
    const previewMessage = document.getElementById('announcementPreviewMessage');
    const previewUrl = document.getElementById('announcementPreviewUrl');
    const submitBtn = document.getElementById('announcementSubmit');
    const membersCount = @json((int) $membersCount);
    let submitting = false;

    function syncPreview() {
        const title = (titleInput?.value || '').trim();
        const message = (messageInput?.value || '').trim();
        const url = (urlInput?.value || '').trim();

        if (titleCount && titleInput) {
            titleCount.textContent = titleInput.value.length + ' / 120';
        }
        if (messageCount && messageInput) {
            messageCount.textContent = messageInput.value.length + ' / 1000';
        }
        if (previewTitle) {
            previewTitle.textContent = title || 'Titre de l’annonce';
        }
        if (previewMessage) {
            previewMessage.textContent = message || 'Le message apparaîtra ici…';
        }
        if (previewUrl) {
            if (url) {
                previewUrl.textContent = url;
                previewUrl.classList.remove('hidden');
            } else {
                previewUrl.textContent = '';
                previewUrl.classList.add('hidden');
            }
        }
    }

    [titleInput, messageInput, urlInput].forEach(function (el) {
        if (!el) return;
        el.addEventListener('input', syncPreview);
    });
    syncPreview();

    form.addEventListener('submit', function (e) {
        if (submitting) {
            e.preventDefault();
            return;
        }

        e.preventDefault();

        const title = (titleInput?.value || '').trim();
        const message = (messageInput?.value || '').trim();
        if (!title || !message) {
            form.reportValidity();
            return;
        }

        const doSubmit = function () {
            submitting = true;
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Envoi en cours…';
            }
            form.submit();
        };

        if (typeof window.showConfirmDialog === 'function') {
            window.showConfirmDialog({
                type: 'warning',
                title: 'Envoyer l’annonce',
                message: 'Envoyer « ' + title + ' » à ' + membersCount.toLocaleString('fr-FR') + ' membre(s) ? Chaque destinataire la verra dans sa cloche.',
                confirmText: 'Envoyer',
                onConfirm: function () {
                    doSubmit();
                    if (typeof window.closeConfirmDialog === 'function') {
                        window.closeConfirmDialog();
                    }
                },
            });
            return;
        }

        if (window.confirm('Envoyer cette annonce à ' + membersCount + ' membre(s) ?')) {
            doSubmit();
        }
    });
})();
</script>
@endpush
