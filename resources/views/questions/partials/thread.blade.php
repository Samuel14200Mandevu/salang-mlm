@php
    $viewerId = auth()->id();
    $isMemberAuthor = $viewerId === $question->user_id;
    $author = $question->author;
    $responder = $question->answeredBy;
@endphp

<div class="question-thread">
    <div class="question-thread__messages" id="questionThreadMessages" role="log" aria-live="polite">
        <div class="question-chat-day">
            <span>{{ $question->created_at->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</span>
        </div>

        <div @class([
            'question-chat-row',
            'question-chat-row--outgoing' => $isMemberAuthor,
            'question-chat-row--incoming' => ! $isMemberAuthor,
        ])>
            @if(! $isMemberAuthor)
                @include('questions.partials.user-avatar', ['user' => $author, 'size' => 'sm'])
            @endif
            <div class="question-chat-stack">
                @if(! $isMemberAuthor)
                    <span class="question-chat-stack__name">{{ $author?->name ?? 'Membre' }}</span>
                @endif
                <div @class([
                    'question-chat-bubble',
                    'question-chat-bubble--outgoing' => $isMemberAuthor,
                    'question-chat-bubble--incoming' => ! $isMemberAuthor,
                ])>
                    <p class="question-chat-bubble__subject">{{ $question->subject }}</p>
                    <p class="question-chat-bubble__text">{{ $question->body }}</p>
                    <time class="question-chat-bubble__time" datetime="{{ $question->created_at->toIso8601String() }}">
                        {{ $question->created_at->format('H:i') }}
                    </time>
                </div>
            </div>
            @if($isMemberAuthor)
                @include('questions.partials.user-avatar', ['user' => $author, 'size' => 'sm'])
            @endif
        </div>

        @if(filled($question->answer))
            <div @class([
                'question-chat-row',
                'question-chat-row--outgoing' => ! $isMemberAuthor,
                'question-chat-row--incoming' => $isMemberAuthor,
            ])>
                @if($isMemberAuthor)
                    @include('questions.partials.user-avatar', ['user' => $responder, 'size' => 'sm'])
                @endif
                <div class="question-chat-stack">
                    @if($isMemberAuthor)
                        <span class="question-chat-stack__name">{{ $responder?->name ?? 'Équipe Salang' }}</span>
                    @endif
                    <div @class([
                        'question-chat-bubble',
                        'question-chat-bubble--outgoing' => ! $isMemberAuthor,
                        'question-chat-bubble--incoming' => $isMemberAuthor,
                    ])>
                        <p class="question-chat-bubble__text">{{ $question->answer }}</p>
                        <time class="question-chat-bubble__time" datetime="{{ $question->answered_at?->toIso8601String() }}">
                            {{ $question->answered_at?->format('H:i') }}
                        </time>
                    </div>
                </div>
                @if(! $isMemberAuthor)
                    @include('questions.partials.user-avatar', ['user' => $responder ?? auth()->user(), 'size' => 'sm'])
                @endif
            </div>
        @endif
    </div>

    @if($canAnswer)
        <div class="question-thread__composer">
            <form action="{{ route('questions.answer', $question) }}" method="POST" class="question-composer-form">
                @csrf
                <label for="questionAnswerInput" class="sr-only">Votre réponse</label>
                <textarea id="questionAnswerInput"
                          name="answer"
                          rows="1"
                          class="question-composer-form__input"
                          required
                          minlength="5"
                          placeholder="Écrivez un message…">{{ old('answer') }}</textarea>
                <button type="submit" class="question-composer-form__send" aria-label="Envoyer">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.652 60.652 0 0018.445-8.986.75.75 0 000-1.218A60.652 60.652 0 003.478 2.404z"/>
                    </svg>
                </button>
            </form>
            @error('answer')<p class="question-composer-form__error">{{ $message }}</p>@enderror
        </div>
    @elseif(filled($question->answer))
        <div class="question-thread__composer question-thread__composer--readonly">
            <span class="text-xs text-[var(--text-secondary)]">Conversation résolue · {{ $question->answered_at?->format('d/m/Y H:i') }}</span>
        </div>
    @endif
</div>
