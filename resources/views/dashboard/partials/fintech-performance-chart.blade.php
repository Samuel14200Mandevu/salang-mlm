@php
    $points = $series ?? [];
    $count = count($points);
    $maxPv = max(1.0, (float) collect($points)->max('pv'));
    $maxComm = max(1.0, (float) collect($points)->max('commission'));
    $hasComm = collect($points)->contains(fn ($row) => ((float) ($row['commission'] ?? 0)) > 0);

    $width = 320;
    $height = 152;
    $padLeft = 34;
    $padRight = $hasComm ? 36 : 12;
    $padTop = 18;
    $padBottom = 26;
    $plotW = $width - $padLeft - $padRight;
    $plotH = $height - $padTop - $padBottom;

    $barSlots = max(1, $count);
    $slotW = $plotW / $barSlots;

    $commCoords = [];
    foreach ($points as $i => $row) {
        $cx = $padLeft + $slotW * ($i + 0.5);
        $commVal = (float) ($row['commission'] ?? 0);
        $cy = $padTop + $plotH - ($commVal / $maxComm) * $plotH;
        $commCoords[] = round($cx, 1) . ',' . round($cy, 1);
    }
    $commLine = count($commCoords) > 1 ? implode(' ', $commCoords) : '';
    $chartUid = 'mp' . substr(md5(json_encode($points)), 0, 8);
@endphp

<div class="member-fintech-performance__chart-wrap">
    <svg
        class="member-fintech-performance__svg"
        viewBox="0 0 {{ $width }} {{ $height }}"
        preserveAspectRatio="xMidYMid meet"
        role="img"
        aria-label="Graphique PV crédités et commissions sur six mois"
    >
        <defs>
            <linearGradient id="{{ $chartUid }}-bar" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" class="member-fintech-performance__grad-top" stop-color="currentColor"/>
                <stop offset="100%" class="member-fintech-performance__grad-bottom" stop-color="currentColor"/>
            </linearGradient>
            <linearGradient id="{{ $chartUid }}-comm" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="var(--color-success-500, #22c55e)" stop-opacity="0.35"/>
                <stop offset="100%" stop-color="var(--color-success-500, #22c55e)" stop-opacity="0"/>
            </linearGradient>
        </defs>

        {{-- Grille horizontale --}}
        @for($g = 0; $g <= 4; $g++)
            @php
                $gy = $padTop + ($plotH / 4) * $g;
            @endphp
            <line
                x1="{{ $padLeft }}"
                y1="{{ $gy }}"
                x2="{{ $width - $padRight }}"
                y2="{{ $gy }}"
                class="member-fintech-performance__grid"
            />
        @endfor

        {{-- Axe PV (gauche) --}}
        @for($g = 0; $g <= 4; $g++)
            @php
                $gy = $padTop + ($plotH / 4) * $g;
                $pvTick = (int) round($maxPv * (1 - $g / 4));
            @endphp
            <text x="{{ $padLeft - 6 }}" y="{{ $gy + 3 }}" class="member-fintech-performance__axis member-fintech-performance__axis--left" text-anchor="end">
                {{ $pvTick >= 1000 ? number_format($pvTick / 1000, 1) . 'k' : $pvTick }}
            </text>
        @endfor

        @if($hasComm)
            @for($g = 0; $g <= 4; $g++)
                @php
                    $gy = $padTop + ($plotH / 4) * $g;
                    $commTick = $maxComm * (1 - $g / 4);
                @endphp
                <text x="{{ $width - $padRight + 6 }}" y="{{ $gy + 3 }}" class="member-fintech-performance__axis member-fintech-performance__axis--right" text-anchor="start">
                    ${{ $commTick >= 1000 ? number_format($commTick / 1000, 1) . 'k' : number_format($commTick, 0) }}
                </text>
            @endfor
        @endif

        @foreach($points as $i => $row)
            @php
                $pv = (float) ($row['pv'] ?? 0);
                $comm = (float) ($row['commission'] ?? 0);
                $cx = $padLeft + $slotW * ($i + 0.5);
                $barW = min(26, $slotW * 0.52);
                $barH = max($pv > 0 ? 3 : 0, ($pv / $maxPv) * $plotH);
                $yBar = $padTop + $plotH - $barH;
                $monthLabel = $row['month'] ?? '';
                $monthShort = $row['month_short'] ?? $monthLabel;
                $tip = number_format($pv, 0) . ' PV';
                if ($comm > 0) {
                    $tip .= ' · $' . number_format($comm, 2);
                }
            @endphp
            @if($pv > 0)
                <rect
                    x="{{ $cx - $barW / 2 }}"
                    y="{{ $yBar }}"
                    width="{{ $barW }}"
                    height="{{ $barH }}"
                    rx="5"
                    ry="5"
                    fill="url(#{{ $chartUid }}-bar)"
                    class="member-fintech-performance__bar"
                >
                    <title>{{ $monthLabel }} — {{ $tip }}</title>
                </rect>
                @php
                    $pvLabel = $pv >= 1000 ? number_format($pv / 1000, 1) . 'k' : number_format($pv, 0);
                    $valueY = $barH >= 26
                        ? max($padTop + 11, $yBar + 13)
                        : max($padTop + 8, $yBar - 5);
                    $valueClass = $barH >= 26
                        ? 'member-fintech-performance__bar-value'
                        : 'member-fintech-performance__bar-value member-fintech-performance__bar-value--above';
                @endphp
                <text x="{{ $cx }}" y="{{ $valueY }}" class="{{ $valueClass }}" text-anchor="middle">
                    {{ $pvLabel }}
                </text>
            @endif
            <text x="{{ $cx }}" y="{{ $height - 6 }}" class="member-fintech-performance__month" text-anchor="middle">
                {{ $monthShort }}
            </text>
        @endforeach

        @if($hasComm && $commLine)
            <polygon
                points="{{ $padLeft }},{{ $padTop + $plotH }} {{ $commLine }} {{ $padLeft + $plotW }},{{ $padTop + $plotH }}"
                fill="url(#{{ $chartUid }}-comm)"
            />
            <polyline
                points="{{ $commLine }}"
                fill="none"
                class="member-fintech-performance__comm-line"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                vector-effect="non-scaling-stroke"
            />
            @foreach($points as $i => $row)
                @php
                    $comm = (float) ($row['commission'] ?? 0);
                    $cx = $padLeft + $slotW * ($i + 0.5);
                    $cy = $padTop + $plotH - ($comm / $maxComm) * $plotH;
                @endphp
                @if($comm > 0)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="3.5" class="member-fintech-performance__comm-dot">
                        <title>{{ $row['month'] ?? '' }} — ${{ number_format($comm, 2) }}</title>
                    </circle>
                @endif
            @endforeach
        @endif
    </svg>
</div>
