var USPFields = { };

jQuery( function( $ ) {

	usp_init_cookie();

	if ( usp_url_params['usp-addon-options'] ) {
		$( '.wrap-recall-options' ).hide();
		$( '#recall .title-option' ).removeClass( 'active' );
		$( '#options-' + usp_url_params['usp-addon-options'] ).show();
		$( '#title-' + usp_url_params['usp-addon-options'] ).addClass( 'active' );
	}

	$( '.usp-custom-fields-box' ).find( '.required-checkbox' ).each( function() {
		usp_update_require_checkbox( this );
	} );

	$( 'body' ).on( 'click', '.required-checkbox', function() {
		usp_update_require_checkbox( this );
	} );

	/**/
	$( ".wrap-recall-options" ).find( ".parent-option" ).each( function() {
		$( this ).find( "input,select" ).each( function() {
			var id = $( this ).attr( 'id' );
			var val = $( this ).val();
			$( '.' + id + '-' + val ).show();
		} );
	} );

	$( '.parent-option select, .parent-option input' ).change( function() {
		var id = $( this ).attr( 'id' );
		$( '.parent-' + id ).hide();
		$( '.' + id + '-' + $( this ).val() ).show();
	} );
	/**/

	$( "#recall" ).find( ".parent-select" ).each( function() {
		var id = $( this ).attr( 'id' );
		var val = $( this ).val();
		$( '.child-select.' + id + '-' + val ).show();
	} );

	$( '.wrap-recall-options .parent-select' ).change( function() {
		var id = $( this ).attr( 'id' );
		var val = $( this ).val();
		$( '.wrap-recall-options .child-select.' + id ).slideUp();
		$( '.wrap-recall-options .child-select.' + id + '-' + val ).slideDown();
	} );

	$( '#usp-custom-fields-editor' ).on( 'change', '.select-type-field', function() {
		usp_get_custom_field_options( this );
	} );

	$( '#usp-custom-fields-editor' ).on( 'click', '.field-delete', function() {
		var field = $( this ).parents( '.usp-custom-field' );

		if ( field.hasClass( 'must-meta-delete' ) ) {

			if ( confirm( $( '#field-delete-confirm' ).text() ) ) {
				var itemID = field.data( 'slug' );
				var val = $( '#usp-deleted-fields' ).val();
				if ( val )
					itemID += ',';
				itemID += val;
				$( '#usp-deleted-fields' ).val( itemID );
			}

		}

		field.remove();

		return false;
	} );

	$( '.usp-custom-fields-box' ).on( 'click', '.field-edit', function() {
		$( this ).parents( '.field-header' ).next( '.field-settings' ).slideToggle();
		return false;
	} );

	$( '#recall' ).on( 'click', '.title-option', function() {

		if ( $( this ).hasClass( 'active' ) )
			return false;

		var titleSpan = $( this );

		var addonId = titleSpan.data( 'addon' );
		var url = titleSpan.data( 'url' );

		usp_update_history_url( url );

		$( '.wrap-recall-options' ).hide();
		$( '#recall .title-option' ).removeClass( 'active' );
		titleSpan.addClass( 'active' );
		titleSpan.next( '.wrap-recall-options' ).show();
		return false;
	} );

	$( '.update-message .update-add-on' ).click( function() {
		if ( $( this ).hasClass( "updating-message" ) )
			return false;
		var addon = $( this ).data( 'addon' );
		$( '#' + addon + '-update .update-message' ).addClass( 'updating-message' );

		usp_ajax({
			data: {
				action: 'usp_update_addon',
				addon: addon
			},
			success: function( data ) {
				if ( data.addon_id == addon ) {
					$( '#' + addon + '-update .update-message' ).toggleClass( 'updating-message updated-message' ).html( data.success );
				}
				if ( data.error ) {

					$( '#' + addon + '-update .update-message' ).removeClass( 'updating-message' );

					var ssiOptions = {
						className: 'usp-dialog-tab usp-update-error',
						sizeClass: 'auto',
						title: USP.local.error,
						buttons: [ {
							label: USP.local.close,
							closeAfter: true
						} ],
						content: data.error
					};

					ssi_modal.show( ssiOptions );

				}
			}
		});

		return false;

	} );

	$( '#usp-notice,body' ).on( 'click', 'a.close-notice', function() {
		usp_close_notice( jQuery( this ).parent() );
		return false;
	} );

	jQuery( 'body' ).on( 'click', '#usp-addon-details .sections-menu .no-active-section', function() {
		var li = jQuery( this );

		li.parent().find( '.active-section' ).each( function() {
			var tab = jQuery( this ).data( 'tab' );
			jQuery( this ).removeClass( 'active-section' );
			jQuery( this ).addClass( 'no-active-section' );

			var box = jQuery( '#usp-addon-details .section-content [data-box="' + tab + '"]' );

			box.removeClass( 'active-box' );
			box.addClass( 'no-active-box' );
		} );

		var tab = li.data( 'tab' );

		li.removeClass( 'no-active-section' );
		li.addClass( 'active-section' );

		var box = jQuery( '#usp-addon-details .section-content [data-box="' + tab + '"]' );

		box.removeClass( 'no-active-box' );
		box.addClass( 'active-box' );

		return false;

	} );

} );

function usp_get_details_addon( props, e ) {

	usp_preloader_show( jQuery( e ).parents( '.addon-box' ) );

	props.action = 'usp_get_details_addon';

	usp_ajax( {
		data: props,
		success: function( data ) {

			ssi_modal.show( {
				className: 'usp-dialog-tab usp-addon-details',
				sizeClass: 'medium',
				title: data.title,
				buttons: [ {
						label: USP.local.close,
						closeAfter: true
					} ],
				content: data.content
			} );

		}
	} );

	return false;

}

function usp_update_addon( props, e ) {

	var button = jQuery( e );

	if ( button.hasClass( "updating-message" ) || button.hasClass( "updated-message" ) )
		return false;

	button.addClass( 'updating-message' );

	usp_ajax( {
		data: 'action=usp_update_addon&addon=' + props.slug,
		success: function( data ) {
			if ( data.addon_id == props.slug ) {
				button.addClass( 'button-disabled' ).toggleClass( 'updating-message updated-message' ).html( data.success );
			}
			if ( data.error ) {

				button.removeClass( 'updating-message' );

				var ssiOptions = {
					className: 'usp-dialog-tab usp-update-error',
					sizeClass: 'auto',
					title: USP.local.error,
					buttons: [ {
							label: USP.local.close,
							closeAfter: true
						} ],
					content: data.error
				};

				ssi_modal.show( ssiOptions );

			}
		}
	} );
	return false;

}

function usp_update_history_url( url ) {

	if ( url != window.location ) {
		if ( history.pushState ) {
			window.history.pushState( null, null, url );
		}
	}

}

function usp_init_custom_fields( fields_type, primaryOptions, defaultOptions ) {

	USPFields = {
		'type': fields_type,
		'primary': primaryOptions,
		'default': defaultOptions
	};

}

function usp_get_custom_field_options( e ) {

	var typeField = jQuery( e ).val();
	var boxField = jQuery( e ).parents( '.usp-custom-field' );
	var oldType = boxField.attr( 'data-type' );

	var multiVals = [ 'multiselect', 'checkbox' ];

	if ( jQuery.inArray( typeField, multiVals ) >= 0 && jQuery.inArray( oldType, multiVals ) >= 0 ) {

		boxField.attr( 'data-type', typeField );
		return;

	}

	var multiVals = [ 'radio', 'select' ];

	if ( jQuery.inArray( typeField, multiVals ) >= 0 && jQuery.inArray( oldType, multiVals ) >= 0 ) {

		boxField.attr( 'data-type', typeField );
		return;

	}

	var singleVals = [ 'date', 'time', 'email', 'url', 'dynamic', 'tel' ];

	if ( jQuery.inArray( typeField, singleVals ) >= 0 && jQuery.inArray( oldType, singleVals ) >= 0 ) {

		boxField.attr( 'data-type', typeField );
		return;

	}

	var sliderVals = [ 'runner', 'range' ];

	if ( jQuery.inArray( typeField, sliderVals ) >= 0 && jQuery.inArray( oldType, sliderVals ) >= 0 ) {

		boxField.attr( 'data-type', typeField );
		return;

	}

	usp_preloader_show( boxField );

	usp_ajax( {
		data: {
			action: 'usp_get_custom_field_options',
			type_field: typeField,
			old_type: oldType,
			post_type: USPFields.type,
			primary_options: USPFields.primary,
			default_options: USPFields.default,
			slug: boxField.data( 'slug' )
		},
		success: function( data ) {

			if ( data['content'] ) {

				boxField.find( '.options-custom-field' ).html( data['content'] );

				boxField.attr( 'data-type', typeField );

			}

		}
	} );

	return false;

}

function usp_get_new_custom_field() {

	usp_preloader_show( jQuery( '#usp-custom-fields-editor' ) );

	usp_ajax( {
		data: {
			action: 'usp_get_new_custom_field',
			post_type: USPFields.type,
			primary_options: USPFields.primary,
			default_options: USPFields.default
		},
		success: function( data ) {

			if ( data['content'] ) {
				jQuery( "#usp-custom-fields-editor ul" ).append( data['content'] );
			}

		}
	} );

	return false;

}
