/**
 * Accueil public mobile — 3 écrans d’onboarding statiques → login / register.
 * Desktop et ?site=1 : site officiel sans onboarding.
 */
(function () {
    const MOBILE_MAX = 767;
    const FORCE_SITE_KEY = 'salang_force_website';

    function isMobile() {
        return window.matchMedia(`(max-width: ${MOBILE_MAX}px)`).matches;
    }

    function wantsOfficialSite() {
        try {
            const params = new URLSearchParams(window.location.search);
            if (params.get('site') === '1') {
                sessionStorage.setItem(FORCE_SITE_KEY, '1');
                return true;
            }
            return sessionStorage.getItem(FORCE_SITE_KEY) === '1';
        } catch (e) {
            return new URLSearchParams(window.location.search).get('site') === '1';
        }
    }

    function goToAuth(path) {
        window.location.href = path;
    }

    function initPublicOnboarding() {
        const body = document.body;
        if (!body.classList.contains('public-welcome-page')) {
            return;
        }

        const chrome = document.getElementById('publicOnboardingChrome');
        const board = document.getElementById('publicAppOnboarding');
        const track = document.getElementById('publicAppOnboardingTrack');
        const slides = Array.from(document.querySelectorAll('.public-app-onboarding__slide'));
        const nextBtn = document.getElementById('publicOnboardingNext');
        const skipBtn = document.getElementById('publicOnboardingSkip');
        const backBtn = document.getElementById('publicOnboardingBack');
        const dotsRoot = document.getElementById('publicOnboardingDots');
        const siteContent = document.getElementById('publicOfficialSiteContent');

        const loginUrl = document.body.dataset.loginUrl || '/login';
        const registerUrl = document.body.dataset.registerUrl || '/register';

        let activeIndex = 0;
        let scrollLock = false;

        function showOfficialSite() {
            document.documentElement.classList.remove('salang-mobile-app');
            document.documentElement.classList.add('salang-no-splash');
            body.classList.add('public-welcome-expanded', 'public-official-site');
            body.classList.remove('public-onboarding-active', 'public-splash-active');
            if (chrome) {
                chrome.hidden = true;
            }
            if (board) {
                board.hidden = true;
            }
            if (siteContent) {
                siteContent.hidden = false;
            }
        }

        let gradeTimer = null;
        let gradeIndex = 0;

        function stopGradeCycle() {
            if (gradeTimer) {
                window.clearInterval(gradeTimer);
                gradeTimer = null;
            }
        }

        function startGradeCycle() {
            const scene = document.getElementById('publicOnboardingGradeScene');
            if (!scene) {
                return;
            }
            let urls = [];
            try {
                urls = JSON.parse(scene.getAttribute('data-grade-urls') || '[]');
            } catch (e) {
                urls = [];
            }
            if (!urls.length) {
                return;
            }

            const imgs = scene.querySelectorAll('[data-grade-cycle]');
            stopGradeCycle();
            gradeIndex = 0;

            function applyGrade(i) {
                const url = urls[i];
                imgs.forEach(function (img) {
                    img.classList.add('is-grade-swap');
                    window.setTimeout(function () {
                        img.src = url;
                        img.classList.remove('is-grade-swap');
                    }, 180);
                });
            }

            applyGrade(0);
            gradeTimer = window.setInterval(function () {
                gradeIndex = (gradeIndex + 1) % urls.length;
                applyGrade(gradeIndex);
            }, 1800);
        }

        function updateChrome(index) {
            activeIndex = index;
            slides.forEach(function (slide, i) {
                slide.classList.toggle('is-active', i === index);
            });

            if (dotsRoot) {
                dotsRoot.querySelectorAll('.public-onboarding-dot').forEach(function (dot, i) {
                    dot.classList.toggle('is-active', i === index);
                    dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
                });
            }

            const isLast = index >= slides.length - 1;
            if (nextBtn) {
                nextBtn.textContent = isLast ? 'Commencer' : 'Suivant';
                nextBtn.dataset.mode = isLast ? 'login' : 'next';
            }
            if (backBtn) {
                backBtn.hidden = index === 0;
            }

            // Slide 3 : cycle des insignes grades 1 → 9
            if (index === slides.length - 1) {
                startGradeCycle();
            } else {
                stopGradeCycle();
            }
        }

        function goToSlide(index, smooth) {
            if (!track || !slides[index]) {
                return;
            }
            scrollLock = true;
            track.scrollTo({
                left: slides[index].offsetLeft,
                behavior: smooth === false ? 'auto' : 'smooth',
            });
            updateChrome(index);
            window.setTimeout(function () {
                scrollLock = false;
            }, 420);
        }

        function enableOnboarding() {
            if (!chrome || !board || !track || !slides.length || !nextBtn || !skipBtn) {
                goToAuth(loginUrl);
                return;
            }

            document.documentElement.classList.add('salang-mobile-app');
            body.classList.add('public-onboarding-active');
            body.classList.remove('public-welcome-expanded', 'public-official-site');
            chrome.hidden = false;
            board.hidden = false;
            if (siteContent) {
                siteContent.hidden = true;
                siteContent.setAttribute('aria-hidden', 'true');
            }

            if (dotsRoot) {
                dotsRoot.innerHTML = '';
                slides.forEach(function (_, i) {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'public-onboarding-dot' + (i === 0 ? ' is-active' : '');
                    dot.setAttribute('role', 'tab');
                    dot.setAttribute('aria-label', 'Étape ' + (i + 1));
                    dot.addEventListener('click', function () {
                        goToSlide(i);
                    });
                    dotsRoot.appendChild(dot);
                });
            }

            track.addEventListener(
                'scroll',
                function () {
                    if (scrollLock) {
                        return;
                    }
                    const width = track.clientWidth || 1;
                    const index = Math.round(track.scrollLeft / width);
                    if (index !== activeIndex && index >= 0 && index < slides.length) {
                        updateChrome(index);
                    }
                },
                { passive: true }
            );

            goToSlide(0, false);
        }

        if (nextBtn) {
            nextBtn.dataset.loginUrl = loginUrl;
            nextBtn.dataset.registerUrl = registerUrl;
            nextBtn.addEventListener('click', function () {
                if (nextBtn.dataset.mode === 'login') {
                    goToAuth(nextBtn.dataset.loginUrl || loginUrl);
                    return;
                }
                if (nextBtn.dataset.mode === 'register') {
                    goToAuth(nextBtn.dataset.registerUrl || registerUrl);
                    return;
                }
                if (activeIndex < slides.length - 1) {
                    goToSlide(activeIndex + 1);
                }
            });
        }

        if (skipBtn) {
            skipBtn.addEventListener('click', function () {
                goToAuth(loginUrl);
            });
        }

        if (backBtn) {
            backBtn.addEventListener('click', function () {
                if (activeIndex > 0) {
                    goToSlide(activeIndex - 1);
                }
            });
        }

        function startMobileAppFlow() {
            if (!isMobile()) {
                showOfficialSite();
                return;
            }

            if (
                wantsOfficialSite() ||
                document.documentElement.classList.contains('salang-no-splash')
            ) {
                showOfficialSite();
                return;
            }

            enableOnboarding();
        }

        function startOnboardingFlow() {
            startMobileAppFlow();
            window.addEventListener('resize', function () {
                if (!isMobile()) {
                    showOfficialSite();
                }
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
