document.addEventListener('alpine:init', () => {
    Alpine.data('servicesFeaturedCarousel', (total) => ({
        active: 0,
        total: Number(total) || 0,
        timer: null,
        desktopStatic: false,

        initCarousel() {
            this.desktopStatic =
                !!this.$el.closest('.member-services-featured-desktop') &&
                window.matchMedia('(min-width: 768px)').matches;

            if (!this.desktopStatic) {
                this.start();
            }
        },

        trackStyle() {
            if (this.desktopStatic) {
                return '';
            }

            return `transform: translateX(calc((100% - var(--featured-slide-size)) / 2 - ${this.active} * (var(--featured-slide-size) + var(--featured-gap))))`;
        },

        start() {
            this.pause();
            if (this.desktopStatic || this.total < 2) {
                return;
            }
            if (document.documentElement.classList.contains('salang-reduce-motion')) {
                return;
            }
            this.timer = setInterval(() => {
                this.active = (this.active + 1) % this.total;
            }, 5000);
        },

        pause() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        resume() {
            if (this.desktopStatic) {
                return;
            }
            this.start();
        },

        go(index) {
            if (this.desktopStatic) {
                return;
            }
            this.active = index;
            this.start();
        },

        slideAriaHidden(index) {
            return this.desktopStatic ? false : this.active !== index;
        },

        slideTabIndex(index) {
            return this.desktopStatic || this.active === index ? 0 : -1;
        },
    }));
});
