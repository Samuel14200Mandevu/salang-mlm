{{-- À inclure hors @push : pousse styles + scripts d’entrée dashboard --}}
@push('styles')
<style>
@keyframes dashFadeInUp {
    from { opacity: 0; transform: translateY(18px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp {
    opacity: 0;
    animation: dashFadeInUp 0.85s cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
}
.delay-1 { animation-delay: 0.08s; }
.delay-2 { animation-delay: 0.16s; }
.delay-3 { animation-delay: 0.24s; }
.delay-4 { animation-delay: 0.32s; }
.delay-5 { animation-delay: 0.42s; }
.delay-6 { animation-delay: 0.52s; }
.delay-7 { animation-delay: 0.62s; }
.delay-8 { animation-delay: 0.72s; }
.delay-9 { animation-delay: 0.82s; }
.delay-10 { animation-delay: 0.92s; }
.delay-11 { animation-delay: 1.02s; }
.delay-12 { animation-delay: 1.12s; }
[data-count-to] {
    font-variant-numeric: tabular-nums;
}
</style>
@endpush

@push('scripts')
<script defer>
document.addEventListener('DOMContentLoaded', function () {
    function easeInOutSine(t) {
        return -(Math.cos(Math.PI * t) - 1) / 2;
    }

    function animateCount(el, duration) {
        if (!el || el.dataset.countReady === '1') return;
        el.dataset.countReady = '1';

        const target = Number(el.getAttribute('data-count-to') || 0);
        const prefix = el.getAttribute('data-count-prefix') || '';
        const decimals = Number(el.getAttribute('data-count-decimals') || 0);
        const start = performance.now();

        const format = function (n) {
            if (decimals > 0) {
                return prefix + Number(n).toLocaleString('fr-FR', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });
            }
            return prefix + Math.round(n).toLocaleString('fr-FR');
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

    document.querySelectorAll('[data-count-to]').forEach(function (el, index) {
        animateCount(el, 1800 + (index * 40));
    });
});
</script>
@endpush
