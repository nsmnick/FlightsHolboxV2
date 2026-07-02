const ANIM_OPEN  = { duration: 420, easing: 'ease-out', fill: 'forwards' };
const ANIM_CLOSE = { duration: 320, easing: 'ease-out', fill: 'forwards' };

class FaqItem {
    constructor(el) {
        this.el        = el;
        this.summary   = el.querySelector('summary');
        this.content   = el.querySelector('.faq-item__content');
        this.animation = null;
        this.isClosing   = false;
        this.isExpanding = false;

        this.summary.addEventListener('click', (e) => this.onClick(e));
        el._faqInstance = this;
    }

    onClick(e) {
        e.preventDefault();
        if (this.isClosing || this.isExpanding) return;
        this.el.open ? this.shrink() : this.open();
    }

    shrink() {
        this.isClosing = true;
        this.el.style.overflow = 'hidden';
        const start = this.el.offsetHeight;
        const end   = this.summary.offsetHeight;
        this.el.style.height = `${start}px`;
        this.cancel();
        this.animation = this.el.animate({ height: [`${start}px`, `${end}px`] }, ANIM_CLOSE);
        this.animation.onfinish = () => this.finish(false);
        this.animation.oncancel = () => { this.isClosing = false; };
    }

    open() {
        // Close any other open items in the same accordion
        const accordion = this.el.closest('.faq-panel__accordion');
        if (accordion) {
            accordion.querySelectorAll('details[open]').forEach((el) => {
                if (el !== this.el && el._faqInstance) el._faqInstance.shrink();
            });
        }

        this.el.style.overflow = 'hidden';
        this.el.style.height   = `${this.el.offsetHeight}px`;
        this.el.open = true;
        window.requestAnimationFrame(() => this.expand());
    }

    expand() {
        this.isExpanding = true;
        const start = this.el.offsetHeight;
        const end   = this.summary.offsetHeight + this.content.offsetHeight;
        this.cancel();
        this.animation = this.el.animate({ height: [`${start}px`, `${end}px`] }, ANIM_OPEN);
        this.animation.onfinish = () => this.finish(true);
        this.animation.oncancel = () => { this.isExpanding = false; };
    }

    finish(open) {
        this.cancel();
        this.el.open       = open;
        this.isClosing     = false;
        this.isExpanding   = false;
        this.el.style.height   = '';
        this.el.style.overflow = '';
    }

    cancel() {
        if (this.animation) { this.animation.cancel(); this.animation = null; }
    }
}

export default function initFaqAccordions() {
    document.querySelectorAll('.faq-panel__accordion details').forEach((el) => {
        new FaqItem(el);
    });
}
