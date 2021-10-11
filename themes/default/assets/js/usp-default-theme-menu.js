function menuOffsetTop() {
    let menu = jQuery('#usp-nav-menu');

    jQuery('#usp-ext-nav').css({
        'top': menu.offset().top + menu.outerHeight(),
    });
}

(function ($) {
    var navMenu = $('#usp-nav-menu');
    var typeButton = $('#usp-office');
    var uspOverlay = $('#usp-overlay');

// when resizing, we update it
    function moveMenu() {
        navMenu.append($('#usp-ext-nav').html());
        $('.usp-expand, #usp-ext-nav').remove();
    }

    $('.usp-office-shift,.usp-tab-button').on('click', function () {
        usp_recalculate_height();
    });

// closing the menu
    function closeExtMenu() {
        // check that this is our overlay
        if (uspOverlay.hasClass('usp-overlay__ext-nav')) {
            uspOverlay.fadeOut().removeClass('usp-overlay__ext-nav');
        }
        $('#usp-ext-nav').removeClass('usp-ext-nav__open');
        $('.usp-expand').removeClass('usp-bttn__active');
    }

// determine what type of buttons we have
    if (typeButton.hasClass('usp-nav__column')) {
        // screen width
        if ($(window).width() <= 768) {
            typeButton.removeClass('usp-nav__column').addClass('usp-nav__row');
            alignMenu(52);
        }
        // actions when resizing the window
        $(window).on('resize', function () {
            if ($(window).width() <= 768) {
                typeButton.removeClass('usp-nav__column').addClass('usp-nav__row');
                closeExtMenu();
                moveMenu();
                alignMenu();
            } else {
                typeButton.removeClass('usp-nav__row').addClass('usp-nav__column');
                closeExtMenu();
                moveMenu();
            }
        });
    } else if (typeButton.hasClass('usp-nav__row')) {
        alignMenu(38);
        $(window).on('resize', function () {
            closeExtMenu();
            moveMenu();
            alignMenu();
        });
    }

// indent from the top-right to our buttons
    function menuPosition() {
        // consider the indent lower when the screen is wider than the content. We prevent the window from being pressed to the right edge. 
        // Now the menu is in the hamburger area
        let wRightMenu = ($(window).width() - (navMenu.offset().left + navMenu.outerWidth(true)));

        menuOffsetTop();
        $('#usp-ext-nav').css({
            'right': wRightMenu
        });
    }

// grouping buttons
    function alignMenu(offset = 0) {
        let mw = navMenu.outerWidth(true) - 69; // block width-indent per button
        let menuDiv = '';
        let totalWidth = 0;                                             // sum of the width of all buttons

        $.each(navMenu.children('.usp-tab-button'), function () {
            totalWidth += $(this).outerWidth(true);          // calculate the width of all buttons, taking into account the margins
            if (mw < (totalWidth + offset)) {                                      // if the width of the button block is less than the sum of the button widths
                menuDiv += $('<div>').append($(this).clone()).html();
                $(this).remove();
            }
        });
        navMenu.append(
            '<a class="usp-expand usp-tab-button usp-bttn usp-bttn__type-primary usp-bttn__size-standard">'
            + '<i class="usp-bttn__ico usp-bttn__ico-left uspi fa-bars"></i>'
            + '<span class="usp-bttn__count"></span>'
            + '</a>'
        );
        // creating content in the button
        $('body').append('<div id="usp-ext-nav" class="usp-nav usps__line-1">' + menuDiv + '</div>');

        $('.usp-expand span').text($('#usp-ext-nav > a').length + '');

        let dropdown = $('.usp-expand');
        // if there is no content in the button, hide it
        (menuDiv === '') ? dropdown.hide() : dropdown.show();

        menuPosition();

        dropdown.on('click', function () {
            $(this).addClass('usp-bttn__active');
            uspOverlay.fadeToggle(100).toggleClass('usp-overlay__ext-nav'); // adding our class to the overlay. So as not to close someone else's

            $('#usp-ext-nav').addClass('usp-ext-nav__open');
        });

        $('#usp-ext-nav').add(uspOverlay).on('click', function () {
            closeExtMenu();
        });
    }

})(jQuery);


usp_add_action('usp_upload_tab', 'usp_recalculate_height');

function usp_recalculate_height() {
    setTimeout(function () {
        menuOffsetTop();
    }, 1000);
}
