/**
 * Splash mobile (logo Salang) — barre 1% → 100% puis onboarding / auth.
 */
(function () {
    const MOBILE_MAX = 767;
    const SPLASH_MS = 2200;
    const PROGRESS_FROM = 1;
    const PROGRESS_TO = 100;

    function isMobile() {
        return window.matchMedia(`(max-width: ${MOBILE_MAX}px)`).matches;
    }

    function wantsOfficialSite() {
        try {
            const params = new URLSearchParams(window.location.search);
            if (params.get('site') === '1') {
                sessionStorage.setItem('salang_force_website', '1');
                return true;
            }
            return sessionStorage.getItem('salang_force_website') === '1';
        } catch (e) {
            return new URLSearchParams(window.location.search).get('site') === '1';
        }
    }

    function dispatchDone() {
        document.dispatchEvent(new CustomEvent('salang:splash-done'));
    }

    function initPublicSplash() {
        const body = document.body;
        if (!body.classList.contains('public-welcome-page')) {
            dispatchDone();
            return;
        }

        const splash = document.getElementById('publicMobileSplash');
        if (!splash) {
            dispatchDone();
            return;
        }

        // Desktop ou site officiel (?site=1) : jamais de splash.
        if (
            !isMobile()
            || wantsOfficialSite()
            || document.documentElement.classList.contains('salang-no-splash')
        ) {
            body.classList.add('public-official-site');
            body.classList.add('public-welcome-expanded');
            splash.remove();
            dispatchDone();
            return;
        }

        document.documentElement.classList.add('salang-mobile-app');
        body.classList.add('public-splash-active', 'public-onboarding-active');

        const progress = document.getElementById('publicSplashProgress');
        const bar = document.getElementById('publicSplashProgressBar');
        const label = document.getElementById('publicSplashProgressLabel');
        let closed = false;
        let progressTimer = null;
        let rafId = 0;

        function setProgress(pct) {
            const value = Math.max(PROGRESS_FROM, Math.min(PROGRESS_TO, Math.round(pct)));
            if (progress) {
                progress.setAttribute('aria-valuenow', String(value));
            }
            if (bar) {
                bar.style.width = value + '%';
            }
            if (label) {
                label.textContent = value + '%';
            }
        }

        function closeSplash() {
            if (closed) {
                return;
            }
            closed = true;
            if (progressTimer) {
                window.clearInterval(progressTimer);
                progressTimer = null;
            }
            if (rafId) {
                window.cancelAnimationFrame(rafId);
                rafId = 0;
            }
            setProgress(PROGRESS_TO);
            dispatchDone();
            splash.classList.add('is-leaving');
            body.classList.remove('public-splash-active');

            window.setTimeout(function () {
                splash.remove();
            }, 380);
        }

        setProgress(PROGRESS_FROM);
        const started = Date.now();
        const span = PROGRESS_TO - PROGRESS_FROM;

        function tick() {
            const t = Math.min(1, (Date.now() - started) / SPLASH_MS);
            setProgress(PROGRESS_FROM + t * span);
            if (t < 1 && !closed) {
                rafId = window.requestAnimationFrame(tick);
            }
        }
        rafId = window.requestAnimationFrame(tick);

        window.setTimeout(closeSplash, SPLASH_MS);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPublicSplash);
    } else {
        initPublicSplash();
    }
})();
