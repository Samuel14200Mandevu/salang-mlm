@php
    $selectedId = $selectedMessage?->id;
    $queryBase = array_filter([
        'status' => ($status ?? '') !== '' ? $status : null,
        'q' => ($q ?? '') !== '' ? $q : null,
    ]);
@endphp

@forelse ($messages as $msg)
    @php
        $isActive = $selectedId && (int) $selectedId === (int) $msg->id;
        $isNew = $msg->status === 'new';
        $initial = mb_strtoupper(mb_substr($msg->name ?: '?', 0, 1));
        $showUrl = route('site-management.contacts.show', array_filter([
            'contactMessage' => $msg,
            'status' => $queryBase['status'] ?? null,
            'q' => $queryBase['q'] ?? null,
        ]));
    @endphp
    <a href="{{ $showUrl }}"
       class="contact-gmail-row {{ $isActive ? 'is-active' : '' }} {{ $isNew ? 'is-unread' : '' }}">
        <span class="contact-gmail-row__avatar" aria-hidden="true">{{ $initial }}</span>
        <span class="contact-gmail-row__sender">{{ $msg->name }}</span>
        <span class="contact-gmail-row__content">
            <span class="contact-gmail-row__subject">{{ $msg->subject ?: '(Sans objet)' }}</span>
            <span class="contact-gmail-row__sep">—</span>
            <span class="contact-gmail-row__snippet">{{ \Illuminate\Support\Str::limit($msg->message, 90) }}</span>
        </span>
        <time class="contact-gmail-row__date" datetime="{{ $msg->created_at?->toIso8601String() }}">
            {{ $msg->created_at?->isToday() ? $msg->created_at->format('H:i') : $msg->created_at?->format('d/m/Y') }}
        </time>
    </a>
@empty
    <div class="contact-gmail-empty">Aucun message pour ce filtre.</div>
@endforelse
