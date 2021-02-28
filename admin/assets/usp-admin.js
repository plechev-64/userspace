var USPFields = { };

jQuery( function( $ ) {

	usp_init_cookie();

	$( '.usp-frame__box-fields' ).find( '.required-checkbox' ).each( function() {
		usp_update_require_checkbox( this );
	} );

	$( 'body' ).on( 'click', '.required-checkbox', function() {
		usp_update_require_checkbox( this );
	} );

	/**/

	$( '.parent-option select, .parent-option input' ).change( function() {
		var id = $( this ).attr( 'id' );
		$( '.parent-' + id ).hide();
		$( '.' + id + '-' + $( this ).val() ).show();
	} );
	/**/

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

	$( '.usp-frame__box-fields' ).on( 'click', '.field-edit', function() {
		$( this ).parents( '.field-header' ).next( '.field-settings' ).slideToggle();
		return false;
	} );

	$( '#usp-notice,body' ).on( 'click', 'a.close-notice', function() {
		usp_close_notice( jQuery( this ).parent() );
		return false;
	} );
} );

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
