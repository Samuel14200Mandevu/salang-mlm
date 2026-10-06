/**
 * Animations légères (accueil + auth). Désactivées si prefers-reduced-motion.
 */
(function () {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) {
        document.querySelectorAll('.public-reveal').forEach((el) => el.classList.add('is-visible'));
        document.body.classList.add('motion-ready');
        return;
    }

    document.body.classList.add('motion-ready');

    // Accueil : apparition au scroll
    const revealEls = document.querySelectorAll('.public-reveal');
    if (revealEls.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
        );
        revealEls.forEach((el) => observer.observe(el));
    } else {
        revealEls.forEach((el) => el.classList.add('is-visible'));
    }

    // Auth : décalage des champs du formulaire principal
    const authForm = document.querySelector('.auth-card form:not(.auth-form-skip)');
    if (authForm) {
        authForm.querySelectorAll('.auth-form-group').forEach((group, index) => {
            group.style.animationDelay = `${0.08 + index * 0.045}s`;
        });
    }
})();
