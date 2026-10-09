@php
    $viewer = auth()->user();
    $member = $conversation->member;
    $isMemberViewer = $viewer->id === $conversation->user_id;
    $messages = $conversation->messages;
    $lastMessage = $messages->last();
    $waitingForSupport = $isMemberViewer
        && $lastMessage
        && $lastMessage->isFromMember($conversation);
    $currentDay = null;
@endphp

<div class="question-thread">
    <div class="question-thread__messages" id="questionThreadMessages" role="log" aria-live="polite">
        @foreach($messages as $chatMessage)
            @php
                $dayLabel = $chatMessage->created_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
                $fromMember = $chatMessage->isFromMember($conversation);
                $outgoing = $isMemberViewer ? $fromMember : ! $fromMember;
                $sender = $chatMessage->sender;
            @endphp

            @if($currentDay !== $dayLabel)
                @php($currentDay = $dayLabel)
                <div class="question-chat-day">
                    <span>{{ $dayLabel }}</span>
                </div>
            @endif

            <div class="question-chat-row {{ $outgoing ? 'question-chat-row--outgoing' : 'question-chat-row--incoming' }}">
                @if(! $outgoing)
                    @include('questions.partials.user-avatar', [
                        'user' => $fromMember ? $member : $sender,
                        'size' => 'sm',
                    ])
                @endif
                <div class="question-chat-bubble-group">
                    <p class="question-chat-bubble-meta {{ $outgoing ? 'question-chat-bubble-meta--out' : '' }}">
                        @if($outgoing)
                            Vous
                        @elseif($fromMember)
                            {{ $member?->name ?? 'Membre' }}
                        @else
                            {{ $sender?->name ?? 'Support Salang' }}
                        @endif
                    </p>
                    <div class="question-chat-bubble {{ $outgoing ? 'question-chat-bubble--outgoing' : '' }}">
                        @if(filled($chatMessage->subject))
                            <p class="question-chat-bubble__subject">{{ $chatMessage->subject }}</p>
                        @endif
                        <div class="question-chat-bubble__text">{{ $chatMessage->body }}</div>
                    </div>
                    <time class="question-chat-bubble-time" datetime="{{ $chatMessage->created_at->toIso8601String() }}">
                        {{ $chatMessage->created_at->format('H:i') }}
                    </time>
                </div>
                @if($outgoing)
                    @include('questions.partials.user-avatar', ['user' => $sender, 'size' => 'sm'])
                @endif
            </div>
        @endforeach
    </div>

    <div class="question-thread__footer">
        @if($canAnswer ?? false)
            <form action="{{ route('questions.answer', $conversation) }}" method="POST" class="question-thread-composer">
                @csrf
                <label for="questionAnswerInput" class="sr-only">Votre réponse</label>
                <div class="question-thread-composer__field">
                    <textarea id="questionAnswerInput"
                              name="answer"
                              rows="1"
                              class="question-thread-composer__input"
                              required
                              minlength="5"
                              placeholder="Écrivez un message…">{{ old('answer') }}</textarea>
                    <button type="submit" class="question-thread-composer__send" aria-label="Envoyer">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
                @error('answer')
                    <p class="question-thread-composer__error">{{ $message }}</p>
                @enderror
            </form>
        @elseif($canMessage ?? false)
            <form action="{{ route('questions.messages.store', $conversation) }}" method="POST" class="question-thread-composer">
                @csrf
                <label for="questionMemberMessageInput" class="sr-only">Votre message</label>
                <div class="question-thread-composer__field">
                    <textarea id="questionMemberMessageInput"
                              name="body"
                              rows="1"
                              class="question-thread-composer__input"
                              required
                              minlength="5"
                              placeholder="Écrivez un message…">{{ old('body') }}</textarea>
                    <button type="submit" class="question-thread-composer__send" aria-label="Envoyer">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
                @error('body')
                    <p class="question-thread-composer__error">{{ $message }}</p>
                @enderror
            </form>
        @elseif($waitingForSupport)
            <p class="question-thread__waiting">
                <span class="question-thread__waiting-dot" aria-hidden="true"></span>
                En attente d’une réponse de l’équipe
            </p>
        @elseif($conversation->is_resolved)
            <p class="question-thread__resolved">Conversation résolue</p>
        @endif
    </div>
</div>
