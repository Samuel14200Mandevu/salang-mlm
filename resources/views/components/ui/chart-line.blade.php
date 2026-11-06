{{--
  Wrapper Chart.js — sparkline (courbe primary, fill primary-light → transparent).

  Usage :
  <x-ui.chart-line
      id="walletSpark"
      :labels="['L', 'M', 'M', 'J', 'V', 'S', 'D']"
      :values="[4, 6, 5, 8, 7, 9, 11]"
      class="h-16"
  />
--}}
@props([
    'id',
    'labels' => [],
    'values' => [],
])

@php
    $canvasId = (string) $id;
    $labelsJson = json_encode(array_values($labels), JSON_UNESCAPED_UNICODE);
    $valuesJson = json_encode(array_map('floatval', array_values($values)));
@endphp

<div {{ $attributes->merge(['class' => 'relative w-full']) }}>
    <canvas id="{{ $canvasId }}" aria-label="Sparkline"></canvas>
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

    const root = getComputedStyle(document.documentElement);
    const primary = (root.getPropertyValue('--primary') || root.getPropertyValue('--color-primary-500')).trim();
    const primaryLight = (root.getPropertyValue('--primary-light') || root.getPropertyValue('--color-primary-50')).trim();

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height || 64);
    gradient.addColorStop(0, primaryLight);
    gradient.addColorStop(1, 'transparent');

    if (canvas._salangChart) {
        canvas._salangChart.destroy();
    }

    canvas._salangChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: primary,
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 3,
                pointHoverBackgroundColor: primary,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true },
            },
            scales: {
                x: { display: false },
                y: { display: false },
            },
            elements: {
                line: { capBezierPoints: true },
            },
        },
    });
})();
</script>
@endpush
