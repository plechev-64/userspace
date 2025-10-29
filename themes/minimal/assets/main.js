/**
 * UserSpace Minimal Theme Scripts
 *
 * Логика для управления интерактивными элементами темы.
 */
document.addEventListener('DOMContentLoaded', function () {
    // --- Мобильное меню ---
    const layout = document.querySelector('.usp-account-layout--minimal');
    const mobileMenuToggle = document.querySelector('.usp-mobile-menu-toggle');

    if (mobileMenuToggle && layout) {
        mobileMenuToggle.addEventListener('click', function () {
            layout.classList.toggle('usp-mobile-menu-open');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!isExpanded));
        });
    }
});