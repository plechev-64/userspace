jQuery(document).ready(function () {

    const $ = jQuery;

    $(document).on('mouseenter', '.usp-menu-button', function () {

        const wrapper_pos = $(this).offset();
        const $menu = $(this).next();

        if ($menu.hasClass('usp-menu-items_pos_right')) {

            const document_width = window.outerWidth;
            const menu_right_x = $menu.offset().left + $menu.outerWidth();

            if (menu_right_x > document_width) {
                $menu.removeClass('usp-menu-items_pos_right');
                if ($menu.outerWidth() < wrapper_pos.left) {
                    $menu.addClass('usp-menu-items_pos_left');
                } else {
                    $menu.addClass('usp-menu-items_pos_bottom');
                }
            }

        }

    });

});