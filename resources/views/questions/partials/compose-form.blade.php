<form action="{{ route('questions.store') }}"
      method="POST"
      class="question-thread question-thread--compose"
      id="questionComposeForm">
    @csrf

    <div class="question-thread__messages question-compose__thread" id="questionComposeThread">
        @if($errors->any())
            <div class="question-compose__errors" role="alert">
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="question-chat-row question-chat-row--incoming">
            <div class="question-chat-avatar question-chat-avatar--sm question-chat-avatar--support" aria-hidden="true">
                <span>S</span>
            </div>
            <div class="question-chat-bubble-group">
                <p class="question-chat-bubble-meta">Support Salang</p>
                <div class="question-chat-bubble">
                    <div class="question-chat-bubble__text">
                        Bonjour, {{ auth()->user()->name }} !
                        Expliquez votre demande ci-dessous — un conseiller vous répondra ici, comme dans une conversation.
                    </div>
                </div>
            </div>
        </div>

        <div class="question-chat-row question-chat-row--outgoing question-compose__preview" id="questionComposePreview" hidden>
            <div class="question-chat-bubble-group">
                <p class="question-chat-bubble-meta question-chat-bubble-meta--out">Vous</p>
                <div class="question-chat-bubble question-chat-bubble--outgoing">
                    <p class="question-chat-bubble__subject" id="questionComposePreviewSubject"></p>
                    <div class="question-chat-bubble__text" id="questionComposePreviewBody"></div>
                </div>
            </div>
            @include('questions.partials.user-avatar', ['user' => auth()->user(), 'size' => 'sm'])
        </div>
    </div>

    <div class="question-thread__footer question-compose__footer">
        <div class="question-compose-subject">
            <label class="sr-only" for="subject">Résumé de votre problème</label>
            <input type="text"
                   name="subject"
                   id="subject"
                   value="{{ old('subject') }}"
                   class="question-compose-subject__input question-compose-subject__input--full"
                   maxlength="255"
                   required
                   placeholder="Écrivez votre problème"
                   autocomplete="off">
        </div>

        <div class="question-thread-composer">
            <label class="sr-only" for="body">Détails</label>
            <div class="question-thread-composer__field">
                <textarea name="body"
                          id="body"
                          rows="1"
                          class="question-thread-composer__input"
                          required
                          minlength="10"
                          placeholder="Entrez les détails">{{ old('body') }}</textarea>
                <button type="submit" class="question-thread-composer__send" aria-label="Envoyer">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('questionComposeForm');
    var subject = document.getElementById('subject');
    var body = document.getElementById('body');
    var preview = document.getElementById('questionComposePreview');
    var previewSubject = document.getElementById('questionComposePreviewSubject');
    var previewBody = document.getElementById('questionComposePreviewBody');
    var thread = document.getElementById('questionComposeThread');
    if (!form || !body) return;

    function resizeBody() {
        body.style.height = 'auto';
        body.style.height = Math.min(body.scrollHeight, 120) + 'px';
    }

    function syncPreview() {
        var sub = (subject && subject.value.trim()) || '';
        var text = body.value.trim();
        if (!sub && !text) {
            preview.hidden = true;
            return;
        }
        preview.hidden = false;
        if (previewSubject) {
            previewSubject.textContent = sub;
            previewSubject.hidden = !sub;
        }
        if (previewBody) {
            previewBody.textContent = text || '…';
        }
        if (thread) {
            thread.scrollTop = thread.scrollHeight;
        }
    }

    function submitForm() {
        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    body.addEventListener('input', function () {
        resizeBody();
        syncPreview();
    });

    if (subject) {
        subject.addEventListener('input', syncPreview);
    }

    body.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (form.checkValidity()) {
                submitForm();
            } else {
                form.reportValidity();
            }
        }
    });

    resizeBody();
    syncPreview();
})();
</script>
@endpush
