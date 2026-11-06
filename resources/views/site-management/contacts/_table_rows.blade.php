@forelse ($messages as $msg)
    <tr class="border-t border-[var(--border-light)] hover:bg-[var(--bg-hover)]">
        <td class="px-3 py-2.5">
            <span class="inline-flex rounded-md px-2 py-0.5 text-[0.625rem] font-semibold
                {{ $msg->status === 'new' ? 'bg-sky-100 text-sky-800' : ($msg->status === 'archived' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-800') }}">
                {{ $msg->statusLabel() }}
            </span>
        </td>
        <td class="px-3 py-2.5">
            <p class="font-medium text-[var(--text-primary)]">{{ $msg->name }}</p>
            <p class="text-xs text-[var(--text-muted)]">{{ $msg->email }}</p>
        </td>
        <td class="px-3 py-2.5 text-[var(--text-secondary)]">{{ \Illuminate\Support\Str::limit($msg->subject, 48) }}</td>
        <td class="px-3 py-2.5 text-[var(--text-muted)] whitespace-nowrap">{{ $msg->created_at?->format('d/m/Y H:i') }}</td>
        <td class="px-3 py-2.5 text-right">
            <a href="{{ route('site-management.contacts.show', $msg) }}" class="text-sm font-semibold text-primary-600">Ouvrir</a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-3 py-10 text-center text-sm text-[var(--text-muted)]">Aucun message pour ce filtre.</td>
    </tr>
@endforelse
