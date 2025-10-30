/**
 * UserSpace First Theme Scripts
 *
 * Логика для управления интерактивными элементами темы.
 */
document.addEventListener('DOMContentLoaded', function () {
    const layout = document.querySelector('.usp-account-layout');
    const toggleButton = document.querySelector('.usp-mobile-menu-toggle');

    if (!toggleButton || !layout) {
        return;
    }

    toggleButton.addEventListener('click', function () {
        layout.classList.toggle('usp-sidebar-open');
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', String(!isExpanded));
    });
});