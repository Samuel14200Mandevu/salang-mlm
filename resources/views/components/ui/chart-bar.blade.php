{{--
  Wrapper Chart.js — barres (actif = primary plein, autres = 20 % opacité).

  Usage :
  <x-ui.chart-bar
      id="salesByMonth"
      :labels="['Jan', 'Fév', 'Mar', 'Avr']"
      :values="[12, 19, 8, 15]"
      :active-index="3"
      class="h-48"
  />
--}}
@props([
    'id',
    'labels' => [],
    'values' => [],
    'activeIndex' => null,
])

@php
    $canvasId = (string) $id;
    $labelsJson = json_encode(array_values($labels), JSON_UNESCAPED_UNICODE);
    $valuesJson = json_encode(array_map('floatval', array_values($values)));
    $activeJson = $activeIndex === null || $activeIndex === '' ? 'null' : (int) $activeIndex;
@endphp

<div {{ $attributes->merge(['class' => 'relative w-full']) }}>
    <canvas id="{{ $canvasId }}" aria-label="Graphique en barres"></canvas>
</div>

@push('scripts')
<script type="module">
(async () => {
    const canvas = document.getElementById(@json($canvasId));
    if (!canvas || typeof canvas.getContext !== 'function') {
        return;
    }

    const chartModuleUrl = @json(Vite::asset('resources/js/charts.js'));
    const { default: Chart } = await import(chartModuleUrl);

    const labels = {!! $labelsJson !!};
    const values = {!! $valuesJson !!};
    const activeIndex = {{ $activeJson }};

    const root = getComputedStyle(document.documentElement);
    const SALANG_PRIMARY = '#1E5DAD';

    function resolveCssColor(value, fallback) {
        const raw = (value || '').trim();
        if (!raw || raw.startsWith('var(')) {
            return fallback;
        }
        return raw;
    }

    function hexToRgba(hex, alpha) {
        let h = String(hex || '').replace('#', '').trim();
        if (h.length === 3) {
            h = h.split('').map((c) => c + c).join('');
        }
        if (h.length !== 6 || Number.isNaN(parseInt(h, 16))) {
            return `rgba(30, 93, 173, ${alpha})`;
        }
        const r = parseInt(h.slice(0, 2), 16);
        const g = parseInt(h.slice(2, 4), 16);
        const b = parseInt(h.slice(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    const primary = resolveCssColor(
        root.getPropertyValue('--primary') || root.getPropertyValue('--color-primary-500'),
        SALANG_PRIMARY
    );
    const muted = hexToRgba(primary.startsWith('#') ? primary : SALANG_PRIMARY, 0.2);

    const backgroundColor = values.map((_, i) =>
        activeIndex === null || i === activeIndex ? primary : muted
    );

    if (canvas._salangChart) {
        canvas._salangChart.destroy();
    }

    canvas._salangChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor,
                borderWidth: 0,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 28,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: root.getPropertyValue('--bg-card').trim(),
                    titleColor: root.getPropertyValue('--text-primary').trim(),
                    bodyColor: root.getPropertyValue('--text-secondary').trim(),
                    borderColor: root.getPropertyValue('--border-color').trim(),
                    borderWidth: 1,
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: root.getPropertyValue('--text-tertiary').trim(),
                        font: { size: 11 },
                    },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: root.getPropertyValue('--border-light').trim() || root.getPropertyValue('--border-color').trim(),
                    },
                    ticks: {
                        color: root.getPropertyValue('--text-tertiary').trim(),
                        font: { size: 11 },
                    },
                    border: { display: false },
                },
            },
        },
    });
})();
</script>
@endpush
