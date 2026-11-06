{{--
  Carte bancaire 3D (bleu Salang).

  Usage :
  <x-ui.payment-card
      holder="Samuel Mandevu"
      number="•••• 009090"
      balance="$12,450.00"
      expiry="09/28"
      brand="visa"
  />

  Note : fond en style inline — les classes Tailwind from-[#…] ne sont pas
  toujours présentes dans le CSS Vite buildé (JIT non scanné / assets stale).
--}}
@props([
    'holder',
    'number',
    'balance' => null,
    'expiry' => null,
    'brand' => 'visa',
])

@once
<style>
    /* Dark : forcer le dégradé bleu (inline light écrasé ici) */
    html.dark [data-ui-payment-card] {
        background: linear-gradient(135deg, #184F94 0%, #0A2540 100%) !important;
    }
</style>
@endonce

<div
    data-ui-payment-card
    {{ $attributes->merge([
        'class' => 'relative isolate overflow-hidden rounded-2xl p-5 text-white shadow-[var(--shadow-lg)] aspect-[1.586/1] min-h-[11rem] flex flex-col justify-between',
        'style' => 'background: linear-gradient(135deg, #1E5DAD 0%, #184F94 100%);',
    ]) }}
>
    {{-- Reflets légers (profondeur) --}}
    <div class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-10 -left-6 h-28 w-28 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>

    <div class="relative z-10 flex items-start justify-between gap-3">
        <div class="min-w-0">
            @if ($balance !== null && $balance !== '')
                <p class="text-xs font-medium text-white/70">Solde</p>
                <p class="mt-0.5 truncate text-xl font-bold tracking-tight text-white">{{ $balance }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2">
            {{-- Chip / contactless --}}
            <div class="flex h-8 w-10 items-center justify-center rounded-md bg-white/15 backdrop-blur-sm border border-white/20" aria-hidden="true">
                <svg class="h-4 w-4 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 8.5c2.5-2.5 6.5-2.5 9 0M7 12c3.5-3.5 9.5-3.5 13 0M5.5 15.5c4.5-4.5 12.5-4.5 17 0"/>
                </svg>
            </div>
            <span class="rounded-md bg-white/15 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-sm border border-white/20">
                {{ $brand }}
            </span>
        </div>
    </div>

    <p class="relative z-10 mt-4 font-mono text-lg tracking-[0.2em] text-white/95 backdrop-blur-[2px]">
        {{ $number }}
    </p>

    <div class="relative z-10 mt-auto flex items-end justify-between gap-3 pt-3">
        <div class="min-w-0">
            <p class="text-[10px] uppercase tracking-wider text-white/60">Titulaire</p>
            <p class="truncate text-sm font-semibold uppercase tracking-wide text-white">{{ $holder }}</p>
        </div>
        @if ($expiry)
            <div class="shrink-0 text-right">
                <p class="text-[10px] uppercase tracking-wider text-white/60">Exp.</p>
                <p class="text-sm font-semibold tabular-nums text-white">{{ $expiry }}</p>
            </div>
        @endif
    </div>
</div>
