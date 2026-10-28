/**
 * Splash mobile (logo Salang) — style app iOS avant onboarding / auth.
 */
(function () {
    const MOBILE_MAX = 767;

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

        const skipBtn = document.getElementById('publicSplashSkip');
        let closed = false;

        function closeSplash() {
            if (closed) {
                return;
            }
            closed = true;
            // Préparer l’onboarding AVANT de retirer le splash (évite le flash du site).
            dispatchDone();
            splash.classList.add('is-leaving');
            body.classList.remove('public-splash-active');

            window.setTimeout(function () {
                splash.remove();
            }, 380);
        }

        if (skipBtn) {
            skipBtn.addEventListener('click', closeSplash);
        }

        window.setTimeout(closeSplash, 2200);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPublicSplash);
    } else {
        initPublicSplash();
    }
})();
