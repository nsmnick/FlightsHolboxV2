export default function initImageColumnPanel() {
    const items = Array.from(document.querySelectorAll('.image-column-panel__item.has-text'));

    if (!items.length) return;

    function openItem(item) {
        item.classList.add('is-active');
        item.setAttribute('aria-expanded', 'true');
    }

    function closeItem(item) {
        item.classList.remove('is-active');
        item.setAttribute('aria-expanded', 'false');
    }

    function toggle(item) {
        const isActive = item.classList.contains('is-active');
        items.forEach(closeItem);
        if (!isActive) openItem(item);
    }

    items.forEach(item => {
        item.addEventListener('click', () => toggle(item));

        item.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggle(item);
            }
            if (e.key === 'Escape') {
                closeItem(item);
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.image-column-panel__item.has-text')) {
            items.forEach(closeItem);
        }
    });
}
