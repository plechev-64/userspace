
/* global usp_url_params */

jQuery( function() {
	usp_do_action( 'usp_init' );
} );

usp_add_action( 'usp_init', 'usp_init_cookie' );

jQuery( window ).load( function() {
	jQuery( 'body' ).on( 'drop', function() {
		return false;
	} );
	jQuery( document.body ).bind( "drop", function( e ) {
		e.preventDefault();
	} );
} );

function usp_load_tab( tab_id, subtab_id, e ) {

	var button = jQuery( e );

	usp_do_action( 'usp_before_upload_tab', button );

	usp_preloader_show( jQuery( '#usp-tab-content' ) );

	let data = {
		action: 'usp_load_tab',
		tab_id: tab_id,
		subtab_id: subtab_id,
		office_id: USP.office_ID
	};

	/* support old pager */
	if(pagerKey = button.data('pager-key')){
		data[pagerKey] = button.data('page');
		data['pager-id'] = button.data('pager-id')
	}

	usp_ajax( {
		rest: true,
		data: data,
		success: function( data ) {

			data = usp_apply_filters( 'usp_upload_tab', data );

			if ( data.error ) {
				usp_notice( data.error, 'error', 10000 );
				return false;
			}

			var supports = data.supports;
			var subtab_id = data.subtab_id;
			var box_id = '';

			if ( supports && supports.indexOf( 'dialog' ) >= 0 ) { //если вкладка поддерживает диалог

				if ( !subtab_id ) { //если загружается основная вкладка

					ssi_modal.show( {
						className: 'usp-dialog-tab ' + data.tab_id,
						sizeClass: 'small',
						buttons: [ {
								label: USP.local.close,
								closeAfter: true
							} ],
						content: data.content
					} );

				} else {

					box_id = '#ssi-modalContent';

				}

			} else {

				usp_update_history_url( data.tab_url );

				if ( !subtab_id )
					jQuery( '.usp-tabs-menu a' ).removeClass( 'usp-bttn__active' );

				button.addClass( 'usp-bttn__active' );

				box_id = '#usp-tab-content';

			}

			if ( box_id ) {

				jQuery( box_id ).html( data.content );

				var options = usp_get_options_url_params();

				if ( options.scroll === 1 ) {
					var offsetTop = jQuery( box_id ).offset().top;
					jQuery( 'body,html' ).animate( {
						scrollTop: offsetTop - options.offset
					},
						1000 );
				}

				if ( data.includes ) {

					var includes = data.includes;

					includes.forEach( function( src ) {

						jQuery.getScript( src );

					} );

				}

			}

                        if ( typeof animateCss !== 'undefined' ) {
                            jQuery( box_id ).animateCss( 'fadeIn' );
                        }
                        
			usp_do_action( 'usp_upload_tab', {
				element: button,
				result: data
			} );

		}
	} );

}

function usp_get_options_url_params() {

	var options = {
		scroll: 1,
		offset: 120
	};

	options = usp_apply_filters( 'usp_options_url_params', options );

	return options;
}

function usp_add_dropzone( idzone ) {

	jQuery( document.body ).bind( "drop", function( e ) {
		var dropZone = jQuery( idzone ),
			node = e.target,
			found = false;

		if ( dropZone[0] ) {
			dropZone.removeClass( 'in hover' );
			do {
				if ( node === dropZone[0] ) {
					found = true;
					break;
				}
				node = node.parentNode;
			} while ( node != null );

			if ( found ) {
				e.preventDefault();
			} else {
				return false;
			}
		}
	} );

	jQuery( idzone ).bind( 'dragover', function( e ) {
		var dropZone = jQuery( idzone ),
			timeout = window.dropZoneTimeout;

		if ( !timeout ) {
			dropZone.addClass( 'in' );
		} else {
			clearTimeout( timeout );
		}

		var found = false,
			node = e.target;

		do {
			if ( node === dropZone[0] ) {
				found = true;
				break;
			}
			node = node.parentNode;
		} while ( node != null );

		if ( found ) {
			dropZone.addClass( 'hover' );
		} else {
			dropZone.removeClass( 'hover' );
		}

		window.dropZoneTimeout = setTimeout( function() {
			window.dropZoneTimeout = null;
			dropZone.removeClass( 'in hover' );
		}, 100 );
	} );
}

function usp_manage_user_black_list( e, user_id, confirmText ) {

	var class_i = jQuery( e ).children( 'i' ).attr( 'class' );

	if ( class_i === 'uspi fa-refresh fa-spin' )
		return false;

	if ( !confirm( confirmText ) )
		return false;

	jQuery( e ).children( 'i' ).attr( 'class', 'uspi fa-refresh fa-spin' );

	usp_ajax( {
		data: {
			action: 'usp_manage_user_black_list',
			user_id: user_id
		},
		success: function( data ) {

			jQuery( e ).children( 'i' ).attr( 'class', class_i );

			if ( data['label'] ) {
				jQuery( e ).find( 'span' ).text( data['label'] );
			}

		}
	} );

	return false;
}

//usp_add_action( 'usp_init', 'usp_init_userspace_bar_hover' );
//function usp_init_userspace_bar_hover() {
//	jQuery( "#usp-bar .menu-item-has-children" ).hover( function() {
//		jQuery( this ).children( ".sub-menu" ).css( {
//			'visibility': 'visible'
//		} );
//	}, function() {
//		jQuery( this ).children( ".sub-menu" ).css( {
//			'visibility': ''
//		} );
//	} );
//}

/*usp_add_action( 'usp_before_upload_tab', 'usp_add_class_upload_tab' );
 function usp_add_class_upload_tab( e ) {
 e.addClass( 'tab-upload' );
 }*/

usp_add_action( 'usp_before_upload_tab', 'usp_add_preloader_tab' );
function usp_add_preloader_tab() {
	usp_preloader_show( '#usp-tab-content > div' );
	usp_preloader_show( '#ssi-modalContent > div' );
}

usp_add_action( 'usp_init', 'usp_init_get_smilies' );
function usp_init_get_smilies() {
	jQuery( document ).on( {
		mouseenter: function() {
			var sm_box = jQuery( this ).next();
			var block = sm_box.children();
			sm_box.show();
			if ( block.html() )
				return false;
			block.html( USP.local.loading + '...' );
			var dir = jQuery( this ).data( 'dir' );

			usp_ajax( {
				data: {
					action: 'usp_get_smiles_ajax',
					area: jQuery( this ).parent().data( 'area' ),
					dir: dir ? dir : 0
				},
				success: function( data ) {
					if ( data['content'] ) {
						block.html( data['content'] );
					}
				}
			} );

		},
		mouseleave: function() {
			jQuery( this ).next().hide();
		}
	},
		"body .usp-smiles .fa-beaming-face-with-smiling-eyes" );
}

usp_add_action( 'usp_init', 'usp_init_hover_smilies' );
function usp_init_hover_smilies() {

	jQuery( document ).on( {
		mouseenter: function() {
			jQuery( this ).show();
		},
		mouseleave: function() {
			jQuery( this ).hide();
		}
	},
		"body .usp-smiles > .usp-smiles-list" );

	jQuery( 'body' ).on( 'hover click', '.usp-smiles > img', function() {
		var block = jQuery( this ).next().children();
		if ( block.html() )
			return false;
		block.html( USP.local.loading + '...' );
		var dir = jQuery( this ).data( 'dir' );

		usp_ajax( {
			data: {
				action: 'usp_get_smiles_ajax',
				area: jQuery( this ).parent().data( 'area' ),
				dir: dir ? dir : 0
			},
			success: function( data ) {
				if ( data['content'] ) {
					block.html( data['content'] );
				}
			}
		} );

		return false;
	} );
}

usp_add_action( 'usp_init', 'usp_init_click_smilies' );
function usp_init_click_smilies() {
	jQuery( "body" ).on( "click", '.usp-smiles-list img', function() {
		var alt = jQuery( this ).attr( "alt" );
		var area = jQuery( this ).parents( ".usp-smiles" ).data( "area" );
		var box = jQuery( "#" + area );
		box.val( box.val() + " " + alt + " " );
	} );
}

usp_add_action( 'usp_init', 'usp_init_loginform_shift_tabs' );
function usp_init_loginform_shift_tabs() {
	jQuery( 'body' ).on( 'click', '.form-tab-usp .link-tab-usp', function() {
		jQuery( '.form-tab-usp' ).hide();

		if ( jQuery( this ).hasClass( 'link-login-usp' ) )
			usp_show_login_form_tab( 'login' );

		if ( jQuery( this ).hasClass( 'link-register-usp' ) )
			usp_show_login_form_tab( 'register' );

		if ( jQuery( this ).hasClass( 'link-remember-usp' ) )
			usp_show_login_form_tab( 'remember' );

		return false;
	} );
}

usp_add_action( 'usp_init', 'usp_init_check_url_params' );
function usp_init_check_url_params() {

    var options = usp_get_options_url_params();

    if ( usp_url_params['tab'] ) {
        var lkContent = jQuery( '#usp-tab-content' );
        if ( !lkContent.length )
            return false;

        if ( options.scroll == 1 ) {
            var offsetTop = lkContent.offset().top;
            jQuery( 'body,html' ).animate( {
                scrollTop: offsetTop - options.offset
            }, 1000 );
        }
    }
}

usp_add_action( 'usp_init', 'usp_init_close_notice' );
function usp_init_close_notice() {
	jQuery( '#usp-notice,body' ).on( 'click', 'a.close-notice', function() {
		usp_close_notice( jQuery( this ).parent() );
		return false;
	} );
}

usp_add_action( 'usp_footer', 'usp_beat' );
function usp_beat() {

	var beats = usp_apply_filters( 'usp_beats', usp_beats );

	var DataBeat = usp_get_actual_beats_data( beats );

	if ( usp_beats_delay && DataBeat.length ) {

		usp_do_action( 'usp_beat' );

		usp_ajax( {
			data: {
				action: 'usp_beat',
				databeat: JSON.stringify( DataBeat )
			},
			success: function( data ) {

				data.beat_result.forEach( function( result ) {

					usp_do_action( 'usp_beat_success_' + result['beat_name'] );

					new ( window[result['success']] )( result['result'] );

				} );

			}
		} );

	}

	usp_beats_delay++;

	setTimeout( 'usp_beat()', 1000 );
}

function usp_get_actual_beats_data( beats ) {

	var beats_actual = [];

	if ( beats ) {

		beats.forEach( function( beat ) {
			var rest = usp_beats_delay % beat.delay;
			if ( rest === 0 ) {

				var object = new ( window[beat.beat_name] )( beat.data );

				if ( object.data ) {

					object = usp_apply_filters( 'usp_beat_' + beat.beat_name, object );

					object.beat_name = beat.beat_name;

					var k = beats_actual.length;
					beats_actual[k] = object;
				}
			}
		} );

	}

	return beats_actual;

}
