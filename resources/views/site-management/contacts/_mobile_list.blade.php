@forelse ($messages as $msg)
    @php
        $at = $msg->created_at;
        $timeLabel = ! $at
            ? '—'
            : ($at->isToday()
                ? $at->format('H:i')
                : ($at->copy()->startOfDay()->equalTo(now()->subDay()->startOfDay())
                    ? 'Hier'
                    : ($at->isCurrentYear() ? $at->format('d/m') : $at->format('d/m/Y'))));
        $initial = mb_strtoupper(mb_substr($msg->name ?: '?', 0, 1));
        $isNew = $msg->status === 'new';
    @endphp
    <a href="{{ route('site-management.contacts.show', $msg) }}"
       class="contact-convo-row {{ $isNew ? 'contact-convo-row--unread' : '' }}">
        <span class="contact-convo-avatar" aria-hidden="true">{{ $initial }}</span>
        <div class="contact-convo-row__main">
            <div class="contact-convo-row__top">
                <span class="contact-convo-row__title">{{ $msg->name }}</span>
                <time class="contact-convo-row__time" datetime="{{ $at?->toIso8601String() }}">{{ $timeLabel }}</time>
            </div>
            <div class="contact-convo-row__bottom">
                <span class="contact-convo-row__preview">
                    @if ($msg->subject)
                        <span class="contact-convo-row__subject">{{ \Illuminate\Support\Str::limit($msg->subject, 28) }}</span>
                        ·
                    @endif
                    {{ \Illuminate\Support\Str::limit($msg->message, 64) }}
                </span>
                @if ($isNew)
                    <span class="contact-convo-row__unread" aria-label="Nouveau"></span>
                @endif
            </div>
        </div>
    </a>
@empty
    <div class="contact-convo-empty">
        Aucun message pour ce filtre.
    </div>
@endforelse
