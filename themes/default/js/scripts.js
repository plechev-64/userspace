( function( $ ) {
    var LkMenu = $( '#usp-tabs .usp-tabs-menu' );
    var typeButton = $( '#usp-office' );
    var UspOverlay = $( '#usp-overlay' );

// when resizing, we update it
    function moveMenu() {
        LkMenu.append( $( '#sunshine_ext_menu ul' ).html() );
        $( '#usp-tabs .hideshow' ).remove();
        $( '#sunshine_ext_menu' ).remove();
    }

// closing the menu
    function closeExtMenu() {
        // check that this is our overlay
        if ( UspOverlay.hasClass( 'sunshine_mbl_menu' ) ) {
            UspOverlay.fadeOut( 100 ).removeClass( 'sunshine_mbl_menu' );
        }
        $( '#sunshine_ext_menu' ).removeClass( 'bounce' ).css( {
            'top': '',
            'right': ''
        } );
    }

// determine what type of buttons we have
    if ( typeButton.hasClass( 'usp-tabs-menu__column' ) ) {
        // screen width
        if ( $( window ).width() <= 768 ) {
            typeButton.removeClass( 'usp-tabs-menu__column' ).addClass( 'usp-tabs-menu__row' );
            alignMenu();
        }
        // actions when resizing the window
        $( window ).resize( function() {
            if ( $( window ).width() <= 768 ) {
                typeButton.removeClass( 'usp-tabs-menu__column' ).addClass( 'usp-tabs-menu__row' );
                closeExtMenu();
                moveMenu();
                alignMenu();
            } else {
                typeButton.removeClass( 'usp-tabs-menu__row' ).addClass( 'usp-tabs-menu__column' );
                closeExtMenu();
                moveMenu();
            }
        } );
    } else if ( typeButton.hasClass( 'usp-tabs-menu__row' ) ) {
        alignMenu();
        $( window ).resize( function() {
            closeExtMenu();
            moveMenu();
            alignMenu();
        } );
    }

// indent from the top-right to our buttons
    function menuPosition() {
        var hUpMenu = LkMenu.offset().top + 2;
        $( '#sunshine_ext_menu' ).css( {
            'top': hUpMenu
        } );

        // consider the indent lower when the screen is wider than the content. We prevent the window from being pressed to the right edge. 
        // Now the menu is in the hamburger area
        var wRightMenu = ( $( window ).width() - ( LkMenu.offset().left + LkMenu.outerWidth() ) ) - 100;

        // if we have an indent and it is not negative, we move the menu
        if ( wRightMenu > 10 ) {
            $( '#sunshine_ext_menu' ).css( {
                'right': wRightMenu
            } );
        }
    }

// grouping buttons
    function alignMenu() {
        var mw = LkMenu.outerWidth() - 30;                              // block width-indent per button
        var menuhtml = '';
        var totalWidth = 0;                                             // sum of the width of all buttons

        $.each( LkMenu.children( '.usp-tab-button' ), function() {
            totalWidth += $( this ).outerWidth( true );          // calculate the width of all buttons, taking into account the margins
            if ( mw < totalWidth ) {                                      // if the width of the button block is less than the sum of the button widths:
                menuhtml += $( '<div>' ).append( $( this ).clone() ).html();
                $( this ).remove();
            }
        } );
        LkMenu.append(
            '<a class="usp-bttn usp-tab-button usp-bttn__type-primary usp-bttn__size-standart usp-tab-butt hideshow bars">'
            + '<i class="usp-bttn__ico usp-bttn__ico-left uspi fa-bars"></i>'
            + '</a>'
            );
        // creating content in the button
        $( 'body' ).append( '<div id="sunshine_ext_menu"><ul class="usps__line-1">' + menuhtml + '</ul></div>' );

        var hideshow = $( '#usp-tabs .usp-tab-butt.hideshow' );
        if ( menuhtml == '' ) {                                           // if there is no content in the button, hide it
            hideshow.hide();
        } else {
            hideshow.show();
        }

        $( '#usp-tabs .hideshow' ).on( 'click', function() {
            UspOverlay.fadeToggle( 100 ).toggleClass( 'sunshine_mbl_menu' ); // adding our class to the overlay. So as not to close someone else's
            menuPosition();
            $( '#sunshine_ext_menu' ).toggleClass( 'bounce', 100 );
        } );

        UspOverlay.on( 'click', function() {
            closeExtMenu();
        } );
        $( '#sunshine_ext_menu' ).on( 'click', function() {
            closeExtMenu();
        } );
    }

} )( jQuery );
