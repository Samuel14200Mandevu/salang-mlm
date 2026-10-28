/**
 * Animations site public Salang — scroll reveal, stagger, hero.
 * Respecte prefers-reduced-motion et salang-reduce-motion.
 */
(function () {
    const reduceMotion =
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
        || document.documentElement.classList.contains('salang-reduce-motion');

    function markVisible(els) {
        els.forEach((el) => el.classList.add('is-visible'));
    }

    function staggerChildren(container, baseDelay = 0.06) {
        Array.from(container.children).forEach((child, index) => {
            if (!child.classList.contains('public-reveal')) {
                child.classList.add('public-reveal');
            }
            child.style.transitionDelay = `${baseDelay + index * 0.055}s`;
        });
    }

    function enhancePublicSite() {
        const staggerRoots = document.querySelectorAll([
            '.public-pillars',
            '.public-offer-products',
            '.public-income-grid',
            '.public-steps-grid',
            '.public-grades-list',
            '.public-rewards-grid',
            '.public-offer-services',
            '.public-stats-bar__grid',
            '.public-contact-channels',
            '.public-contact-form__row',
        ].join(','));

        staggerRoots.forEach((root) => {
            root.classList.add('public-reveal-stagger');
            staggerChildren(root);
        });

        document.querySelectorAll([
            '.public-section-head',
            '.public-mission',
            '.public-why',
            '.public-faq',
            '.public-cta-banner',
            '.public-contact-aside',
            '.public-contact-form',
            '.public-page-hero .public-shell',
            '.public-table-wrap',
            '.public-body-actions',
            '.public-legal-tabs',
            '.public-legal-doc',
        ].join(',')).forEach((el) => {
            if (!el.classList.contains('public-reveal')) {
                el.classList.add('public-reveal');
            }
        });

        document.querySelectorAll('.public-legal-meta').forEach((root) => {
            root.classList.add('public-reveal-stagger');
            staggerChildren(root, 0.04);
        });

        // Hero : classes pour entrée séquentielle
        const heroCopy = document.querySelector('.public-hero-copy');
        if (heroCopy) {
            heroCopy.classList.add('public-hero-copy--animated');
            ['public-hero-brand', 'public-hero-title', 'public-hero-desc', 'public-hero-actions'].forEach((cls, i) => {
                const node = heroCopy.querySelector('.' + cls);
                if (node) {
                    node.style.setProperty('--hero-delay', `${0.08 + i * 0.09}s`);
                    node.classList.add('public-hero-enter');
                }
            });
        }
    }

    function observeReveals() {
        const revealEls = document.querySelectorAll('.public-reveal');
        if (!revealEls.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            markVisible(revealEls);
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            { rootMargin: '0px 0px -6% 0px', threshold: 0.08 }
        );

        revealEls.forEach((el) => observer.observe(el));
    }

    function navScrollEffect() {
        const nav = document.querySelector('.public-nav');
        if (!nav) {
            return;
        }

        const onScroll = () => {
            nav.classList.toggle('is-scrolled', window.scrollY > 12);
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    function authFormDelays() {
        const authForm = document.querySelector('.auth-card form:not(.auth-form-skip)');
        if (!authForm) {
            return;
        }
        authForm.querySelectorAll('.auth-form-group').forEach((group, index) => {
            group.style.animationDelay = `${0.08 + index * 0.045}s`;
        });
    }

    function enhanceFooter() {
        const footer = document.querySelector('.public-footer');
        if (footer && !footer.classList.contains('public-reveal')) {
            footer.classList.add('public-reveal');
        }
    }

    if (reduceMotion) {
        enhancePublicSite();
        enhanceFooter();
        markVisible(document.querySelectorAll('.public-reveal, .public-hero-enter'));
        document.body.classList.add('motion-ready', 'motion-reduced');
        navScrollEffect();
        return;
    }

    enhancePublicSite();
    enhanceFooter();
    document.body.classList.add('motion-ready');
    observeReveals();
    navScrollEffect();
    authFormDelays();
})();
