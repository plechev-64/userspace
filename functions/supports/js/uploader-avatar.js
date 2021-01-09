jQuery( function( $ ) {

	if ( USPUploaders.isset( 'usp_avatar' ) ) {
		USPUploaders.get( 'usp_avatar' ).afterDone = function( e, data ) {
			jQuery( '#usp-avatar .avatar-image img, #usp-bar img.avatar' ).attr( 'srcset', '' )
				.attr( 'src', data.result.src.thumbnail )
				.load()
				.animateCss( 'zoomIn' );

			usp_do_action( 'usp_success_upload_avatar', data );
		};

		USPUploaders.get( 'usp_avatar' ).animateLoading = function( status ) {
			status ? usp_preloader_show( jQuery( '#usp-avatar' ) ) : usp_preloader_hide();
		};
	}

} );