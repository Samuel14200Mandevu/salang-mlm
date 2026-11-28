@if ($selectedMessage)
    @php
        $message = $selectedMessage;
        $backQuery = array_filter([
            'status' => ($status ?? null) ?: null,
            'q' => ($q ?? null) ?: null,
        ]);
    @endphp
    <div class="contact-gmail-reader" id="contactsDesktopReader">
        <header class="contact-gmail-reader__head">
            <div class="min-w-0 flex-1">
                <div class="contact-gmail-reader__status-row">
                    <a href="{{ route('site-management.contacts.index', $backQuery) }}"
                       class="contact-gmail-reader__back"
                       title="Retour aux messages"
                       aria-label="Retour aux messages">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <span class="inline-flex rounded-md px-2 py-0.5 text-[0.625rem] font-semibold
                        {{ $message->status === 'new' ? 'bg-sky-100 text-sky-800' : ($message->status === 'archived' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-800') }}">
                        {{ $message->statusLabel() }}
                    </span>
                </div>
                <h2 class="contact-gmail-reader__subject">{{ $message->subject ?: '(Sans objet)' }}</h2>
                <div class="contact-gmail-reader__from">
                    <span class="contact-gmail-row__avatar contact-gmail-row__avatar--sm" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr($message->name ?: '?', 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="font-semibold text-[var(--text-primary)] truncate">
                            {{ $message->name }}
                            <span class="font-normal text-[var(--text-muted)]">&lt;{{ $message->email }}&gt;</span>
                        </p>
                        <p class="text-xs text-[var(--text-muted)]">
                            {{ $message->created_at?->format('d/m/Y à H:i') }}
                            @if ($message->phone)
                                · {{ $message->phone }}
                            @endif
                            @if ($message->handler)
                                · traité par {{ $message->handler->name }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="contact-gmail-reader__toolbar" role="toolbar" aria-label="Actions du message">
                <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}"
                   class="contact-gmail-reader__action contact-gmail-reader__action--primary"
                   title="Répondre par email"
                   aria-label="Répondre par email">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2M3 10l5 5m-5-5l5-5"/>
                    </svg>
                </a>
                <form method="POST" action="{{ route('site-management.contacts.update', $message) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="read">
                    <button type="submit"
                            class="contact-gmail-reader__action"
                            title="Marquer comme lu"
                            aria-label="Marquer comme lu">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </form>
                <form method="POST" action="{{ route('site-management.contacts.update', $message) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="archived">
                    <button type="submit"
                            class="contact-gmail-reader__action"
                            title="Archiver"
                            aria-label="Archiver">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12h4M9 8V5a1 1 0 011-1h4a1 1 0 011 1v3"/>
                        </svg>
                    </button>
                </form>
                <form method="POST" action="{{ route('site-management.contacts.destroy', $message) }}"
                      onsubmit="return confirm('Supprimer définitivement ce message ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="contact-gmail-reader__action contact-gmail-reader__action--danger"
                            title="Supprimer"
                            aria-label="Supprimer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>
        <div class="contact-gmail-reader__body">
            {{ $message->message }}
        </div>
    </div>
@endif
