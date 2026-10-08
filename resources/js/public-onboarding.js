/**
 * Accueil public — parcours type onboarding sur smartphone.
 */
(function () {
    const MOBILE_MAX = 767;
    const STORAGE_KEY = 'salang_welcome_onboarding_done';

    function isMobile() {
        return window.matchMedia(`(max-width: ${MOBILE_MAX}px)`).matches;
    }

    function initPublicOnboarding() {
        const body = document.body;
        if (!body.classList.contains('public-welcome-page')) {
            return;
        }

        const main = document.getElementById('main-content');
        const chrome = document.getElementById('publicOnboardingChrome');
        const slides = Array.from(document.querySelectorAll('.public-mobile-slide'));
        const nextBtn = document.getElementById('publicOnboardingNext');
        const skipBtn = document.getElementById('publicOnboardingSkip');
        const stepNum = document.getElementById('publicOnboardingStepNum');
        const dotsRoot = document.getElementById('publicOnboardingDots');

        if (!main || !chrome || !slides.length || !nextBtn || !skipBtn) {
            return;
        }

        let activeIndex = 0;
        let enabled = false;

        function expandFullSite() {
            body.classList.add('public-welcome-expanded');
            chrome.hidden = true;
            try {
                localStorage.setItem(STORAGE_KEY, '1');
            } catch (e) {}
        }

        function enableOnboarding() {
            if (enabled) {
                return;
            }
            enabled = true;
            chrome.hidden = false;
            body.classList.remove('public-welcome-expanded');

            dotsRoot.innerHTML = '';
            slides.forEach(function (_, i) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'public-onboarding-dot' + (i === 0 ? ' is-active' : '');
                dot.setAttribute('role', 'tab');
                dot.setAttribute('aria-label', 'Étape ' + (i + 1));
                dot.addEventListener('click', function () {
                    scrollToSlide(i);
                });
                dotsRoot.appendChild(dot);
            });

            bindObservers();
            updateChrome(0);
        }

        function disableOnboarding() {
            enabled = false;
            chrome.hidden = true;
            body.classList.add('public-welcome-expanded');
        }

        function scrollToSlide(index) {
            const slide = slides[index];
            if (!slide) {
                return;
            }
            slide.scrollIntoView({ behavior: 'smooth', block: 'start' });
            updateChrome(index);
        }

        function updateChrome(index) {
            activeIndex = index;
            if (stepNum) {
                stepNum.textContent = String(index + 1);
            }
            dotsRoot.querySelectorAll('.public-onboarding-dot').forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === index);
                dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
            });

            const isLast = index >= slides.length - 1;
            if (isLast) {
                nextBtn.textContent = 'Adhérer — 30 USD';
                nextBtn.dataset.mode = 'register';
            } else {
                nextBtn.textContent = 'Suivant';
                nextBtn.dataset.mode = 'next';
            }
        }

        function bindObservers() {
            if (!('IntersectionObserver' in window)) {
                return;
            }
            const observer = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting && entry.intersectionRatio >= 0.45) {
                            const idx = slides.indexOf(entry.target);
                            if (idx >= 0) {
                                updateChrome(idx);
                            }
                        }
                    });
                },
                { root: main, threshold: [0.45, 0.6] }
            );
            slides.forEach(function (slide) {
                observer.observe(slide);
            });
        }

        nextBtn.addEventListener('click', function () {
            if (nextBtn.dataset.mode === 'register') {
                window.location.href = nextBtn.dataset.registerUrl || '/register';
                return;
            }
            if (activeIndex < slides.length - 1) {
                scrollToSlide(activeIndex + 1);
            }
        });

        skipBtn.addEventListener('click', expandFullSite);

        const registerUrl = document.querySelector('.public-welcome-page .public-btn-primary[href*="register"]')?.getAttribute('href');
        if (registerUrl) {
            nextBtn.dataset.registerUrl = registerUrl;
        }

        function syncMode() {
            if (!isMobile()) {
                disableOnboarding();
                return;
            }
            try {
                if (localStorage.getItem(STORAGE_KEY) === '1') {
                    disableOnboarding();
                    return;
                }
            } catch (e) {}
            enableOnboarding();
        }

        function startOnboardingFlow() {
            syncMode();
            window.addEventListener('resize', function () {
                syncMode();
            });
        }

        if (document.getElementById('publicMobileSplash')) {
            document.addEventListener('salang:splash-done', startOnboardingFlow, { once: true });
        } else {
            startOnboardingFlow();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPublicOnboarding);
    } else {
        initPublicOnboarding();
    }
})();
