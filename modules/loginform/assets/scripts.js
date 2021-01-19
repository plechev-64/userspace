/* global USP, usp_url_params */

jQuery( window ).load( function() {
    jQuery( 'body' ).on( 'click', '.usp-register', function( e ) {
        e.preventDefault();
        USP.loginform.call( 'register' );
    } );

    jQuery( 'body' ).on( 'click', '.usp-login', function( e ) {
        e.preventDefault();
        USP.loginform.call( 'login' );
    } );

    if ( usp_url_params['usp-form'] ) {
        if ( usp_url_params['type-form'] == 'float' ) {
            USP.loginform.call( usp_url_params['usp-form'], usp_url_params['formaction'] );
        } else {
            USP.loginform.tabShow( usp_url_params['usp-form'] );
        }
    }

    jQuery( '.usp-bar .usp-login,.usp-bar .usp-register' ).click( function() {
        usp_preloader_show( jQuery( this ), 30 );
    } );

} );

USP.loginform = {
    animating: false,
    tabShow: function( tabId, e ) {
        var form = jQuery( '.usp-loginform' );
        form.find( '.tab, .tab-content' ).removeClass( 'active' );
        form.find( '.tab-' + tabId ).addClass( 'active' );
        if ( e )
            jQuery( e ).addClass( 'active' );
        else
            form.find( '.tab-' + tabId ).addClass( 'active' );

    },
    send: function( tabId, e ) {
        var form = jQuery( e ).parents( 'form' );
        if ( !usp_check_form( form ) )
            return false;

        usp_preloader_show( jQuery( '.usp-loginform' ) );

        usp_ajax( {
            data: form.serialize( ) + '&tab_id=' + tabId + '&action=usp_send_loginform',
            afterSuccess: function( result ) {
                jQuery( '.tab-content.tab-' + tabId ).html( result.content );
            }
        } );

    },
    call: function( form, action ) {

        var typeform = form ? form : 'login';
        var formaction = action ? action : '';

        usp_ajax( {
            data: {
                form: typeform,
                formaction: formaction,
                action: 'usp_call_loginform'
            }
        } );

    }
};
