/* global USPUploaders */

jQuery( function( $ ) {
    if ( USPUploaders.isset( 'usp_avatar' ) ) {
        USPUploaders.get( 'usp_avatar' ).afterDone = function( e, data ) {
            $( '.usp-profile-ava' ).attr( 'srcset', '' ).attr( 'src', data.result.src.full ).load().animateCss( 'zoomIn' );
            $( '.icon-zoom-avatar' ).attr( 'data-zoom', data.result.src.full ).load();

            usp_do_action( 'usp_success_upload_avatar', data );
        };

        USPUploaders.get( 'usp_avatar' ).animateLoading = function( status ) {
            status ? usp_preloader_show( $( '#usp-avatar' ) ) : usp_preloader_hide();
        };
    }
} );