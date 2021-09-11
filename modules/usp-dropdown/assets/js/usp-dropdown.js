jQuery(document).ready(function () {

    const $ = jQuery;

    $(document).on('mouseenter', '.usp-menu-button', function () {

        const wrapper_pos = $(this).offset();
        const wrapper_width = $(this).outerWidth();
        const $menu = $(this).next();

        console.log($menu.get(0).getBoundingClientRect());

        if ($menu.hasClass('usp-menu-items_pos_right')) {
            $menu.removeClass('usp-menu-items_pos_right');
            $menu.addClass('usp-menu-items_pos_left');
            return;

            const document_width = window.outerWidth;
            const menu_width = $menu.outerWidth();

            if (wrapper_pos.left + wrapper_width + menu_width > document_width) {
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