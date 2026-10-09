document.addEventListener('alpine:init', () => {
    Alpine.data('servicesFeaturedCarousel', (total) => ({
        active: 0,
        total: Number(total) || 0,
        timer: null,

        trackStyle() {
            return `transform: translateX(calc((100% - var(--featured-slide-size)) / 2 - ${this.active} * (var(--featured-slide-size) + var(--featured-gap))))`;
        },

        start() {
            this.pause();
            if (this.total < 2) {
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
            this.start();
        },

        go(index) {
            this.active = index;
            this.start();
        },
    }));
});
