/**
 * Accueil public mobile — parcours type app iOS :
 * splash → onboarding horizontal (images + vidéo) → login / register.
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
        const stepNum = document.getElementById('publicOnboardingStepNum');
        const dotsRoot = document.getElementById('publicOnboardingDots');
        const siteContent = document.getElementById('publicOfficialSiteContent');

        const loginUrl = document.body.dataset.loginUrl || '/login';
        const registerUrl = document.body.dataset.registerUrl || '/register';

        let activeIndex = 0;
        let scrollLock = false;
        let gradeTimer = null;
        let gradeIndex = 0;

        function showOfficialSite() {
            stopGradeRotation();
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
            pauseAllVideos();
        }

        function pauseAllVideos() {
            slides.forEach(function (slide) {
                const video = slide.querySelector('video');
                if (video) {
                    video.pause();
                }
            });
        }

        function playActiveVideo(index) {
            slides.forEach(function (slide, i) {
                const video = slide.querySelector('video');
                if (!video) {
                    return;
                }
                if (i !== index) {
                    video.pause();
                    return;
                }

                video.muted = true;
                video.defaultMuted = true;
                video.playsInline = true;
                video.setAttribute('playsinline', '');
                video.setAttribute('webkit-playsinline', '');

                const tryPlay = function () {
                    try {
                        video.currentTime = 0;
                    } catch (e) {}
                    const playPromise = video.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function () {
                            window.setTimeout(function () {
                                video.play().catch(function () {});
                            }, 180);
                        });
                    }
                };

                if (video.readyState >= 2) {
                    tryPlay();
                } else {
                    video.preload = 'auto';
                    const onReady = function () {
                        video.removeEventListener('canplay', onReady);
                        tryPlay();
                    };
                    video.addEventListener('canplay', onReady);
                    try {
                        video.load();
                    } catch (e) {}
                }
            });
        }

        function animateStatCounters(slide) {
            const values = slide.querySelectorAll('.public-app-onboarding__stat-value[data-count-to]');
            values.forEach(function (el) {
                const target = parseFloat(el.dataset.countTo || '0');
                const decimals = parseInt(el.dataset.decimals || '0', 10);
                const suffix = el.dataset.suffix || '';
                const duration = 1100;
                const start = performance.now();

                function frame(now) {
                    const t = Math.min(1, (now - start) / duration);
                    const eased = 1 - Math.pow(1 - t, 3);
                    const current = target * eased;
                    el.textContent =
                        (decimals > 0 ? current.toFixed(decimals) : String(Math.round(current))) +
                        suffix;
                    if (t < 1) {
                        window.requestAnimationFrame(frame);
                    }
                }

                el.textContent = (decimals > 0 ? (0).toFixed(decimals) : '0') + suffix;
                window.requestAnimationFrame(frame);
            });
        }

        function stopGradeRotation() {
            if (gradeTimer) {
                window.clearInterval(gradeTimer);
                gradeTimer = null;
            }
        }

        function showGrade(rotator, index) {
            const grades = Array.from(rotator.querySelectorAll('.public-app-onboarding__grade'));
            if (!grades.length) {
                return;
            }
            const labelsNode = rotator.querySelector('[data-grade-labels]');
            let labels = [];
            try {
                labels = JSON.parse(labelsNode?.textContent || '[]');
            } catch (e) {
                labels = [];
            }

            const next = ((index % grades.length) + grades.length) % grades.length;
            const current = grades.find(function (el) {
                return el.classList.contains('is-current');
            });

            if (current && current !== grades[next]) {
                current.classList.add('is-leaving');
                current.classList.remove('is-active', 'is-current');
                window.setTimeout(function () {
                    current.classList.remove('is-leaving');
                }, 480);
            }

            grades.forEach(function (el, i) {
                el.classList.toggle('is-current', i === next);
            });

            const accent = grades[next]?.style.getPropertyValue('--grade-accent') || '#1e5dad';
            rotator.style.setProperty('--grade-accent', accent);

            const labelEl = rotator.querySelector('[data-grade-label]');
            const stepEl = rotator.querySelector('[data-grade-step]');
            if (labelEl) {
                labelEl.classList.add('is-swap');
                window.setTimeout(function () {
                    labelEl.textContent = labels[next] || '';
                    labelEl.classList.remove('is-swap');
                }, 180);
            }
            if (stepEl) {
                stepEl.textContent = next + 1 + ' / ' + grades.length;
            }

            gradeIndex = next;
        }

        function startGradeRotation(slide) {
            stopGradeRotation();
            const rotator = slide.querySelector('[data-grade-rotator]');
            if (!rotator) {
                return;
            }
            const grades = rotator.querySelectorAll('.public-app-onboarding__grade');
            if (grades.length < 2) {
                return;
            }

            gradeIndex = 0;
            showGrade(rotator, 0);

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            gradeTimer = window.setInterval(function () {
                showGrade(rotator, gradeIndex + 1);
            }, 1600);
        }

        function restartSlideMotion(slide) {
            if (!slide) {
                return;
            }
            const media = slide.querySelector('.public-app-onboarding__media');
            const image = slide.querySelector('.public-app-onboarding__image');
            const stats = slide.querySelector('.public-app-onboarding__stats');

            [media, image, stats].forEach(function (node) {
                if (!node) {
                    return;
                }
                node.style.animation = 'none';
                void node.offsetWidth;
                node.style.animation = '';
            });

            slide.querySelectorAll('.public-app-onboarding__stat').forEach(function (stat) {
                stat.style.animation = 'none';
                void stat.offsetWidth;
                stat.style.animation = '';
            });

            if (slide.dataset.anim === 'stats') {
                animateStatCounters(slide);
            }

            if (slide.dataset.anim === 'grades') {
                startGradeRotation(slide);
            } else {
                stopGradeRotation();
            }
        }

        function updateChrome(index) {
            activeIndex = index;
            if (stepNum) {
                stepNum.textContent = String(index + 1);
            }
            if (dotsRoot) {
                dotsRoot.querySelectorAll('.public-onboarding-dot').forEach(function (dot, i) {
                    dot.classList.toggle('is-active', i === index);
                    dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
                });
            }
            slides.forEach(function (slide, i) {
                const active = i === index;
                slide.classList.toggle('is-active', active);
                if (active) {
                    restartSlideMotion(slide);
                }
            });

            const isLast = index >= slides.length - 1;
            if (nextBtn) {
                if (isLast) {
                    nextBtn.textContent = 'Se connecter';
                    nextBtn.dataset.mode = 'login';
                } else {
                    nextBtn.textContent = 'Suivant';
                    nextBtn.dataset.mode = 'next';
                }
            }

            playActiveVideo(index);
            // Relance la dernière slide vidéo après le scroll (autoplay mobile).
            if (index >= slides.length - 1) {
                window.setTimeout(function () {
                    playActiveVideo(index);
                }, 280);
            }
        }

        function goToSlide(index, smooth) {
            if (!track || !slides[index]) {
                return;
            }
            scrollLock = true;
            const left = slides[index].offsetLeft;
            track.scrollTo({
                left: left,
                behavior: smooth === false ? 'auto' : 'smooth',
            });
            updateChrome(index);
            window.setTimeout(function () {
                scrollLock = false;
                playActiveVideo(index);
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

            skipBtn.textContent = 'Passer';

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
