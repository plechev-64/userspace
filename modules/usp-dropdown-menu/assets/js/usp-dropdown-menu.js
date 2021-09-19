jQuery(document).ready(function () {

    const $ = jQuery;

    $(document).on('mousedown', '.usp-menu_on_click > .usp-menu-button', function (e) {
        const focusInSubmenu = $(this).parent().get(0).matches(':focus-within');
        if (focusInSubmenu) {
            e.preventDefault();
            if ($(this).closest('.usp-menu-items').length) {
                $(this).closest('.usp-menu-items').focus();
            } else {
                $(this).focus().blur();
            }
        }
    });

    $(document).on('mouseenter focusin', '.usp-menu-button', function () {

        if ($(this).hasClass('usp-menu-fixed')) {
            return;
        }

        $(this).addClass('usp-menu-fixed');

        const $menu = $(this).parent();
        const $menuContent = $(this).next();
        const menuButtonRect = $(this).get(0).getBoundingClientRect();
        const menuContentRect = $menuContent.get(0).getBoundingClientRect();
        const windowWidth = document.body.clientWidth;
        const [menuContentPosPrim, menuContentPosSec] = $menuContent.data('position').split('-');

        const menuOutRight = menuButtonRect.x + menuButtonRect.width + menuContentRect.width > windowWidth;
        const menuOutLeft = menuButtonRect.x - menuContentRect.width < 0;

        if ((menuContentPosPrim === 'right' && menuOutRight) || (menuContentPosPrim === 'left' && menuOutLeft)) {
            $menuContent.removeClass('usp-menu-items_pos_' + menuContentPosPrim + '-' + menuContentPosSec);
            $menuContent.addClass('usp-menu-items_pos_bottom-' + (windowWidth / 2 > menuButtonRect.x ? 'left' : 'right'));
            if ($menu.hasClass('usp-menu_on_hover')) {
                $menu.removeClass('usp-menu_on_hover');
                $menu.addClass('usp-menu_on_click');
            }
            return;
        }

        const menuOutAlignLeft = menuButtonRect.x + menuContentRect.width > windowWidth;
        const menuOutAlignRight = menuButtonRect.x + menuButtonRect.width >= menuContentRect.width;

        if ((menuContentPosSec === 'right' && menuOutAlignRight) || (menuContentPosSec === 'left' && menuOutAlignLeft)) {
            $menuContent.removeClass('usp-menu-items_pos_' + menuContentPosPrim + '-' + menuContentPosSec);
            $menuContent.addClass('usp-menu-items_pos_' + menuContentPosPrim + '-' + (menuContentPosSec === 'left' ? 'right' : 'left'));
        }

    });

});