document.addEventListener('alpine:init', () => {
    window.Alpine.data('lotteryOddsTotal', () => ({
        totalBasisPoints: 0,
        observer: null,

        init() {
            this.recalculate();
            this.observer = new MutationObserver(() => this.recalculate());
            this.observer.observe(this.$root, { childList: true, subtree: true });
        },

        destroy() {
            this.observer?.disconnect();
        },

        recalculate() {
            this.totalBasisPoints = [...this.$root.querySelectorAll('[data-odds-input]')]
                .reduce((total, element) => {
                    const input = element.matches('input') ? element : element.querySelector('input');

                    return total + this.toBasisPoints(input?.value ?? '');
                }, 0);
        },

        toBasisPoints(value) {
            const match = String(value).trim().match(/^(\d{1,3})(?:\.(\d{0,2}))?$/);

            if (!match) return 0;

            return (Number(match[1]) * 100) + Number((match[2] ?? '').padEnd(2, '0'));
        },

        format(basisPoints) {
            const absolute = Math.abs(basisPoints);
            return `${Math.floor(absolute / 100)}.${String(absolute % 100).padStart(2, '0')}%`;
        },
    }));
});
