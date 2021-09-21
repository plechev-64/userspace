jQuery(document).ready(function () {

    const $ = jQuery;

    $(document).on('click', '.usp-menu_on_click > .usp-menu-button', function () {
        const $menu = $(this).parent();
        const $menuItems = $(this).next();
        const opened = $menu.hasClass('usp-menu_open');
        const clickOutsideListener = (e) => {
            if (!$menuItems.get(0).contains(e.target)) {
                close();
            }
        };

        function close() {
            $menu.removeClass('usp-menu_open');
            $(document).off('click', clickOutsideListener);
        }

        function open() {
            $menu.addClass('usp-menu_open');
            $(document).on('click', clickOutsideListener);
        }

        if (opened) {
            close();
        } else {
            open();
        }

    });

    $(document).on('mouseenter focusin', '.usp-menu-button', function () {
        const $menu = $(this).parent();
        const $menuContent = $(this).next();
        const menuButtonRect = $(this).get(0).getBoundingClientRect();
        const menuContentRect = $menuContent.get(0).getBoundingClientRect();
        const windowWidth = document.body.clientWidth;
        const [menuContentPosPrim, menuContentPosSec] = $menuContent.data('position').split('-');

        const menuOutRight = menuButtonRect.x + menuButtonRect.width + menuContentRect.width > windowWidth;
        const menuOutLeft = menuButtonRect.x - menuContentRect.width < 0;
        const menuOutAlignLeft = menuButtonRect.x + menuContentRect.width > windowWidth;
        const menuOutAlignRight = menuButtonRect.x + menuButtonRect.width - menuContentRect.width < 0;

        const update_pos = (newPos, makeOnClick) => {
            $menuContent.removeClass('usp-menu-items_pos_' + menuContentPosPrim + '-' + menuContentPosSec);
            $menuContent.addClass('usp-menu-items_pos_' + newPos);
            if (makeOnClick && $menu.hasClass('usp-menu_on_hover')) {
                $menu.removeClass('usp-menu_on_hover');
                $menu.addClass('usp-menu_on_click');
            }
        }

        if (menuContentPosPrim === 'right' && menuOutRight) {
            if (!menuOutLeft) {
                update_pos('left-' + menuContentPosSec);
            } else {
                update_pos('bottom-' + (menuOutAlignLeft ? 'right' : 'left'), true);
            }
            return;
        }

        if (menuContentPosPrim === 'left' && menuOutLeft) {
            if (!menuOutRight) {
                update_pos('right-' + menuContentPosSec);
            } else {
                update_pos('bottom-' + (menuOutAlignLeft ? 'right' : 'left'), true);
            }
            return;
        }

        if ((menuContentPosSec === 'right' && menuOutAlignRight) || (menuContentPosSec === 'left' && menuOutAlignLeft)) {
            update_pos(menuContentPosPrim + '-' + (menuContentPosSec === 'left' ? 'right' : 'left'));
        }

    });

});