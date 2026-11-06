{{--
  Ligne d’historique de paiement.

  Usage :
  <x-ui.payment-row
      name="FlexPay dépôt"
      date="06/11/2026"
      status="success"
      amount="+$120.00"
  >
      <x-slot:avatar>
          <x-ui.avatar-brand name="FlexPay" size="sm" />
      </x-slot:avatar>
  </x-ui.payment-row>
--}}
@props([
    'name',
    'date' => null,
    'status' => 'success',
    'amount',
])

@php
    $statusKey = in_array($status, ['success', 'pending', 'failed'], true) ? $status : 'pending';
    $statusLabel = match ($statusKey) {
        'success' => 'Réussi',
        'failed' => 'Échoué',
        default => 'En attente',
    };
@endphp

<div
    {{ $attributes->merge([
        'class' => 'flex items-center gap-3 p-3 border-b border-[var(--border-color)] last:border-0',
    ]) }}
>
    @isset($avatar)
        <div class="shrink-0">
            {{ $avatar }}
        </div>
    @else
        <x-ui.avatar-brand :name="$name" size="sm" class="shrink-0" />
    @endisset

    <div class="min-w-0 flex-1 flex flex-col">
        <p class="truncate text-sm font-semibold text-[var(--text-primary)]">{{ $name }}</p>
        @if ($date)
            <p class="truncate text-xs text-[var(--text-secondary)]">{{ $date }}</p>
        @endif
    </div>

    <span @class([
        'inline-flex shrink-0 items-center rounded-md px-2 py-0.5 text-xs font-semibold',
        'text-[var(--success)] bg-[var(--success-light)]' => $statusKey === 'success',
        'text-[var(--warning)] bg-[var(--warning-light)]' => $statusKey === 'pending',
        'text-[var(--danger)] bg-[var(--danger-light)]' => $statusKey === 'failed',
    ])>{{ $statusLabel }}</span>

    <p @class([
        'shrink-0 text-sm font-bold tabular-nums',
        'text-[var(--success)]' => $statusKey === 'success',
        'text-[var(--text-primary)]' => $statusKey === 'pending',
        'text-[var(--danger)]' => $statusKey === 'failed',
    ])>{{ $amount }}</p>
</div>
