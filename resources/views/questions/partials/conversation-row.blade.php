@php
    use Illuminate\Support\Str;

    $viewer = auth()->user();
    $isSupervision = $viewer->hasSupervisionAccess();
    $member = $question->member;
    $lastMessage = $question->latestMessage;
    $lastAt = $question->last_message_at ?? $question->updated_at;
    $timeLabel = $lastAt->isToday()
        ? $lastAt->format('H:i')
        : ($lastAt->isCurrentYear() ? $lastAt->locale(app()->getLocale())->isoFormat('D MMM') : $lastAt->format('d/m/Y'));

    $preview = $lastMessage
        ? Str::limit(trim($lastMessage->body), 96)
        : 'Nouvelle conversation';

    if ($lastMessage && ! $isSupervision && ! $lastMessage->isFromMember($question)) {
        $preview = 'Support : '.$preview;
    }

    $awaitingReply = $isSupervision
        && $lastMessage
        && $lastMessage->isFromMember($question);
    $rowUser = $isSupervision ? $member : $viewer;
@endphp

<a href="{{ route('questions.show', $question) }}"
   class="question-convo-row {{ $awaitingReply ? 'question-convo-row--unread' : '' }}">
    @include('questions.partials.user-avatar', ['user' => $rowUser, 'size' => 'list'])

    <div class="question-convo-row__main">
        <div class="question-convo-row__top">
            <span class="question-convo-row__title">
                @if($isSupervision)
                    {{ $member?->name ?? 'Membre' }}
                @else
                    Support Salang
                @endif
            </span>
            <time class="question-convo-row__time" datetime="{{ $lastAt->toIso8601String() }}">{{ $timeLabel }}</time>
        </div>
        <div class="question-convo-row__bottom">
            <span class="question-convo-row__preview">{{ $preview }}</span>
        </div>
    </div>

    @if($awaitingReply)
        <span class="question-convo-row__unread" aria-label="Sans réponse"></span>
    @elseif($question->is_resolved)
        <span class="question-convo-row__status badge badge-success text-[9px] shrink-0">OK</span>
    @endif
</a>
