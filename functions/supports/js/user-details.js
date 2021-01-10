function usp_zoom_avatar( e ) {
	var link = jQuery( e );
	var src = link.data( 'zoom' );
	ssi_modal.show( {
		sizeClass: 'auto',
		className: 'usp-user-avatar-zoom',
		content: '<div id="usp-preview"><img class=aligncenter src=\'' + src + '\'></div>'
	} );
	jQuery( '.usp-user-avatar-zoom .ssi-modalWindow' ).animateCss( 'zoomIn' );
}

function usp_get_user_info( element ) {

	usp_preloader_show( '#usp-office-profile > div' );

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
					content: '<div id="usp-popup-content">' + data['content'] + '</div>'
				} );

			}
		}
	} );

}
