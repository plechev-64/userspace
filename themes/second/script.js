document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.querySelector('.usp-account-wrapper-second');
    if (!wrapper) {
        return;
    }

    const menu = wrapper.querySelector('.usp-account-menu');

    menu.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link) {
            return;
        }
        e.preventDefault();

        const targetPaneId = link.getAttribute('href');
        if (!targetPaneId || targetPaneId === '#') {
            return;
        }

        const targetPane = wrapper.querySelector(targetPaneId);
        if (!targetPane) {
            console.error(`Usp Account: No tab pane found with id ${targetPaneId}`);
            return;
        }

        // Убираем active со всех ссылок и панелей
        wrapper.querySelectorAll('.usp-account-menu a').forEach(a => a.classList.remove('active'));
        wrapper.querySelectorAll('.usp-account-tab-pane').forEach(pane => pane.classList.remove('active'));

        // Добавляем active к нажатой ссылке и показываем нужный контент
        const parentMenuItem = link.closest('.usp-account-menu-item');
        if (parentMenuItem.classList.contains('has-submenu')) {
            // Если это родительский пункт с подменю, делаем активной и его ссылку
            parentMenuItem.querySelector('a').classList.add('active');
        }
        link.classList.add('active'); // Делаем активной нажатую ссылку

        targetPane.classList.add('active');
    });
});