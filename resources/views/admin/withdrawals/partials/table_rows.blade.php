{{-- resources/views/admin/withdrawals/partials/table_rows.blade.php --}}
@forelse($withdrawals as $withdrawal)
    @php
        $statusLabels = [
            'pending' => 'En attente',
            'processing' => 'En traitement',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
        ];
        $statusClasses = [
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'completed' => 'badge-success',
            'failed' => 'badge-danger',
        ];
    @endphp
    <tr class="withdrawal-row">
        <td class="font-mono text-xs text-[var(--primary-navy)]">#{{ $withdrawal->id }}</td>
        <td class="font-medium text-sm">
            {{ $withdrawal->user?->name ?? 'N/A' }}
        </td>
        <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
            {{ $withdrawal->user?->email ?? 'N/A' }}
        </td>
        <td class="font-bold text-sm text-[#1F7B4D]">
            {{ number_format($withdrawal->amount, 2) }} $
        </td>
        <td class="hidden md:table-cell">
            <span class="badge badge-info text-[10px] sm:text-xs">
                {{ ucfirst($withdrawal->method) }}
            </span>
        </td>
        <td>
            <span class="badge {{ $statusClasses[$withdrawal->status] ?? 'badge-warning' }} text-[10px] sm:text-xs">
                {{ $statusLabels[$withdrawal->status] ?? ucfirst($withdrawal->status) }}
            </span>
        </td>
        <td class="hidden lg:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
            {{ $withdrawal->created_at->format('d/m/Y H:i') }}
        </td>
        <td class="text-right">
            <div class="flex items-center justify-end gap-1">
                <a href="{{ route('admin.withdrawals.show', $withdrawal->id) }}"
                   class="btn btn-outline btn-sm btn-icon" title="Voir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
                @if($withdrawal->status == 'pending' || $withdrawal->status == 'processing')
                    <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm btn-icon"
                                onclick="return confirm('Approuver ce retrait ?')" title="Approuver">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </form>
                    <button onclick="openRejectModal('{{ $withdrawal->id }}')"
                            class="btn btn-danger btn-sm btn-icon" title="Rejeter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-base sm:text-lg font-medium">Aucun retrait</p>
            <p class="text-sm text-[var(--text-tertiary)]">
                @if(request('search') || request('status') || request('method'))
                    Aucun résultat pour ces critères
                @else
                    Les retraits apparaîtront ici lorsqu'ils seront demandés
                @endif
            </p>
        </td>
    </tr>
@endforelse