/**
 * Splash mobile (logo Salang) avant onboarding accueil.
 */
(function () {
    const MOBILE_MAX = 767;

    function isMobile() {
        return window.matchMedia(`(max-width: ${MOBILE_MAX}px)`).matches;
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

        if (!isMobile()) {
            splash.remove();
            dispatchDone();
            return;
        }

        body.classList.add('public-splash-active');

        const skipBtn = document.getElementById('publicSplashSkip');
        let closed = false;

        function closeSplash() {
            if (closed) {
                return;
            }
            closed = true;
            splash.classList.add('is-leaving');
            body.classList.remove('public-splash-active');

            window.setTimeout(function () {
                splash.remove();
                dispatchDone();
            }, 380);
        }

        if (skipBtn) {
            skipBtn.addEventListener('click', closeSplash);
        }

        window.setTimeout(closeSplash, 2400);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPublicSplash);
    } else {
        initPublicSplash();
    }
})();
