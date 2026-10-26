/**
 * Compteur animé pour les chiffres du tableau de bord membre.
 * Usage : <span data-count-up="1234.5" data-count-decimals="0" data-count-prefix="" data-count-suffix="" data-count-signed></span>
 */

const DURATION_MS = 1100;
const EASE = (t) => 1 - Math.pow(1 - t, 3);

function prefersReducedMotion() {
    if (document.documentElement.classList.contains('salang-reduce-motion')) {
        return true;
    }
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function formatValue(value, decimals, signed) {
    const abs = Math.abs(value);
    const formatted = abs.toLocaleString('fr-FR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
    if (!signed) {
        return formatted;
    }
    if (value > 0) {
        return `+${formatted}`;
    }
    if (value < 0) {
        return `−${formatted}`;
    }
    return formatted;
}

function render(el, value) {
    const decimals = Number(el.dataset.countDecimals ?? 0);
    const signed = el.hasAttribute('data-count-signed');
    const prefix = el.dataset.countPrefix ?? '';
    const suffix = el.dataset.countSuffix ?? '';
    el.textContent = `${prefix}${formatValue(value, decimals, signed)}${suffix}`;
}

function animateEl(el) {
    if (el.dataset.countUpDone === '1') {
        return;
    }
    el.dataset.countUpDone = '1';

    const target = Number(el.dataset.countUp);
    if (!Number.isFinite(target)) {
        return;
    }

    if (prefersReducedMotion()) {
        render(el, target);
        return;
    }

    const start = performance.now();
    const from = 0;

    function frame(now) {
        const t = Math.min(1, (now - start) / DURATION_MS);
        const current = from + (target - from) * EASE(t);
        render(el, current);
        if (t < 1) {
            requestAnimationFrame(frame);
        } else {
            render(el, target);
        }
    }

    render(el, from);
    requestAnimationFrame(frame);
}

function initCountUps(root = document) {
    const nodes = root.querySelectorAll('[data-count-up]');
    if (!nodes.length) {
        return;
    }

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        nodes.forEach((el) => animateEl(el));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                animateEl(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.25, rootMargin: '0px 0px -8% 0px' }
    );

    nodes.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', () => {
    initCountUps(document);
});

export { initCountUps };
