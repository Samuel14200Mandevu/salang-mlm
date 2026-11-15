@php
    $performanceChartPayload = collect($performanceChart ?? [])->map(fn ($row) => [
        'month' => (string) ($row['month_short'] ?? $row['month'] ?? ''),
        'pv' => (float) ($row['pv'] ?? 0),
    ])->values();
    $performanceAccent = (string) ($dashboardLevel['gradient'] ?? '#1E5DAD');
@endphp

@push('scripts')
@if($performanceChartPayload->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" defer></script>
<script defer>
document.addEventListener('DOMContentLoaded', function () {
    function easeInOutSine(t) {
        return -(Math.cos(Math.PI * t) - 1) / 2;
    }

    function animateCount(el, duration) {
        if (!el || el.dataset.countReady === '1') return;
        el.dataset.countReady = '1';

        const target = Number(el.getAttribute('data-count-to') || 0);
        const start = performance.now();
        const format = function (n) {
            return Math.round(n).toLocaleString('fr-FR');
        };

        if (!Number.isFinite(target) || target <= 0) {
            el.textContent = format(0);
            return;
        }

        el.textContent = format(0);

        function frame(now) {
            const progress = Math.min(1, (now - start) / duration);
            el.textContent = format(target * easeInOutSine(progress));
            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                el.textContent = format(target);
            }
        }

        requestAnimationFrame(frame);
    }

    animateCount(document.getElementById('memberPerformanceTotalCount'), 3200);
    animateCount(document.getElementById('memberPerformancePeakCount'), 2800);

    function bootMemberPerformanceChart() {
        if (typeof Chart === 'undefined') {
            return false;
        }

        const canvas = document.getElementById('memberPerformanceChart');
        if (!canvas) {
            return true;
        }

        if (canvas.dataset.chartReady === '1') {
            return true;
        }
        canvas.dataset.chartReady = '1';

        const monthlyData = @json($performanceChartPayload);
        const labels = monthlyData.map(function (item) { return item.month; });
        const pvValues = monthlyData.map(function (item) { return Number(item.pv || 0); });

        const shell = canvas.parentElement;
        const height = (shell && shell.clientHeight) ? shell.clientHeight : 220;
        const accentFromBlade = @json($performanceAccent);
        const accentHost = canvas.closest('.member-dashboard')
            || canvas.closest('[style*="--dashboard-accent"]')
            || document.documentElement;
        const hostStyles = getComputedStyle(accentHost);
        const rootStyles = getComputedStyle(document.documentElement);
        const accentRaw = (
            accentFromBlade
            || hostStyles.getPropertyValue('--dashboard-accent')
            || rootStyles.getPropertyValue('--dashboard-accent')
            || rootStyles.getPropertyValue('--color-primary-600')
            || '#1E5DAD'
        ).trim() || '#1E5DAD';

        function hexToRgb(hex) {
            const h = String(hex).replace('#', '').trim();
            if (h.length === 3) {
                return {
                    r: parseInt(h[0] + h[0], 16),
                    g: parseInt(h[1] + h[1], 16),
                    b: parseInt(h[2] + h[2], 16),
                };
            }
            if (h.length !== 6 || /[^0-9a-fA-F]/.test(h)) {
                return { r: 30, g: 93, b: 173 };
            }
            return {
                r: parseInt(h.slice(0, 2), 16),
                g: parseInt(h.slice(2, 4), 16),
                b: parseInt(h.slice(4, 6), 16),
            };
        }

        const rgb = hexToRgb(accentRaw);
        const accent = 'rgb(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ')';
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.32)');
        gradient.addColorStop(0.55, 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.10)');
        gradient.addColorStop(1, 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.01)');

        const totalDuration = 3200;
        const delayBetweenPoints = totalDuration / Math.max(pvValues.length, 1);
        const previousY = function (ctxArg) {
            if (ctxArg.index === 0) {
                return ctxArg.chart.scales.y.getPixelForValue(0);
            }
            const meta = ctxArg.chart.getDatasetMeta(ctxArg.datasetIndex);
            return meta.data[ctxArg.index - 1].getProps(['y'], true).y;
        };

        const datasets = [{
            label: 'PV crédités',
            data: pvValues,
            borderColor: accent,
            backgroundColor: gradient,
            borderWidth: 2.75,
            borderCapStyle: 'round',
            borderJoinStyle: 'round',
            fill: true,
            tension: 0.5,
            pointRadius: 0,
            pointHoverRadius: 6,
            pointBackgroundColor: '#FFFFFF',
            pointBorderColor: accent,
            pointBorderWidth: 2,
            pointHoverBackgroundColor: accent,
            pointHoverBorderColor: '#FFFFFF',
            pointHoverBorderWidth: 2,
            yAxisID: 'y',
        }];

        new Chart(canvas, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    x: {
                        type: 'number',
                        easing: 'easeInOutSine',
                        duration: delayBetweenPoints,
                        from: NaN,
                        delay: function (ctxArg) {
                            if (ctxArg.type !== 'data' || ctxArg.xStarted) return 0;
                            ctxArg.xStarted = true;
                            return ctxArg.index * delayBetweenPoints;
                        },
                    },
                    y: {
                        type: 'number',
                        easing: 'easeInOutSine',
                        duration: delayBetweenPoints,
                        from: previousY,
                        delay: function (ctxArg) {
                            if (ctxArg.type !== 'data' || ctxArg.yStarted) return 0;
                            ctxArg.yStarted = true;
                            return ctxArg.index * delayBetweenPoints;
                        },
                    },
                },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(10, 42, 108, 0.95)',
                        titleColor: '#FFFFFF',
                        bodyColor: 'rgba(255,255,255,0.9)',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function (context) {
                                const n = context.parsed.y || 0;
                                return 'PV : ' + Number(n).toLocaleString('fr-FR');
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: 'rgba(100, 116, 139, 0.9)',
                            font: { size: 11, weight: '500' },
                            maxRotation: 0,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grace: '12%',
                        border: { display: false },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)',
                            drawTicks: false,
                        },
                        ticks: {
                            precision: 0,
                            color: 'rgba(100, 116, 139, 0.85)',
                            font: { size: 10 },
                            padding: 8,
                        },
                    },
                },
            },
            plugins: [{
                id: 'revealPointsAfterFlow',
                afterRender: function (chart) {
                    if (chart.$pointsRevealed) return;
                    chart.$pointsRevealed = true;
                    setTimeout(function () {
                        chart.data.datasets.forEach(function (ds) {
                            ds.pointRadius = 4;
                        });
                        chart.update({ duration: 700, easing: 'easeInOutSine' });
                    }, totalDuration + 120);
                },
            }],
        });

        return true;
    }

    if (!bootMemberPerformanceChart()) {
        var tries = 0;
        var timer = setInterval(function () {
            tries += 1;
            if (bootMemberPerformanceChart() || tries > 40) {
                clearInterval(timer);
            }
        }, 100);
    }
});
</script>
@endif
@endpush
