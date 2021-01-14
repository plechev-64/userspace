/* global ssi_modal, USP */

function usp_zoom_avatar( e ) {
    ssi_modal.show( {
        sizeClass: 'auto',
        className: 'usp-ava-zoom',
        content: '<img class="usps usps__img-reset" src="' + jQuery( e ).data( 'zoom' ) + '">'
    } );
    jQuery( '.usp-ava-zoom .ssi-modalWindow' ).animateCss( 'zoomIn' );
}

function usp_get_user_info( element ) {
    usp_preloader_show( '.usp-office-card' );

    usp_ajax( {
        data: {
            action: 'usp_return_user_details',
            user_id: jQuery( element ).parents( '.usp-office' ).data( 'account' )
        },
        success: function( data ) {
            if ( data['content'] ) {
                ssi_modal.show( {
                    title: USP.local.title_user_info,
                    sizeClass: 'auto',
                    className: 'usp-user-getails',
                    buttons: [ {
                            label: USP.local.close,
                            closeAfter: true
                        } ],
                    content: data['content']
                } );

            }
        }
    } );
}
