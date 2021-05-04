/* global USPUploaders, USP */

jQuery( function( $ ) {
    if ( USPUploaders.isset( 'usp_cover' ) ) {
        USPUploaders.get( 'usp_cover' ).afterDone = function( e, data ) {
            $( '#usp-office-profile' ).css( 'background-image', 'url(' + data.result.src.full + ')' ).animateCss( 'fadeIn' );

            usp_notice( USP.local.image_load_ok, 'success', 10000 );

            usp_do_action( 'usp_success_upload_cover', data );
        };

        USPUploaders.get( 'usp_cover' ).animateLoading = function( status ) {
            status ? usp_preloader_show( $( '#usp-office-profile' ) ) : usp_preloader_hide();
        };
    }
} );
