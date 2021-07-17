<?php

usp_ajax_action( 'usp_update_options', false );
function usp_update_options() {

	$POST = $_POST;

	array_walk_recursive(
		$POST, function ( &$v, $k ) {
		$v = trim( $v );
	} );

	foreach ( $POST as $option_name => $values ) {

		if ( ! is_array( $values ) ) {
			continue;
		}

		$values = apply_filters( $option_name . '_pre_update', $values );

		if ( $option_name == 'local' ) {

			foreach ( $values as $local_name => $value ) {
				update_site_option( $local_name, $value );
			}
		} else {
			update_site_option( $option_name, $values );
		}
	}

	do_action( 'usp_update_options' );

	return array(
		'success' => __( 'Settings saved!', 'userspace' )
	);
}

// after save options clear temp db
add_action( 'usp_update_options', 'usp_delete_temp_default_avatar_cover', 10 );
function usp_delete_temp_default_avatar_cover() {

	if ( isset( $_POST['usp_global_options']['usp_default_avatar'] ) ) {
		usp_delete_temp_media( $_POST['usp_global_options']['usp_default_avatar'] );
	}

	if ( isset( $_POST['usp_global_options']['usp_default_cover'] ) ) {
		usp_delete_temp_media( $_POST['usp_global_options']['usp_default_cover'] );
	}
}

function usp_add_cover_options( $options ) {

	$options->box( 'primary' )->group( 'design' )->add_options( [
		array(
			'type'        => 'uploader',
			'temp_media'  => 1,
			'max_size'    => 5120,
			'multiple'    => 0,
			'image_thumb' => 'large',
			'crop'        => [ 'ratio' => 0 ],
			'filetitle'   => 'usp-default-cover',
			'filename'    => 'usp-default-cover',
			'slug'        => 'usp_default_cover',
			'title'       => __( 'Default cover', 'userspace' ),
		),
		array(
			'type'       => 'runner',
			'value_min'  => 0,
			'value_max'  => 5120,
			'value_step' => 256,
			'default'    => 1024,
			'slug'       => 'usp_cover_weight',
			'title'      => __( 'Max weight of cover', 'userspace' ) . ', Kb',
			'notice'     => __( 'Set the image upload limit in kb, by default', 'userspace' ) . ' 1024Kb' .
			                '. ' . __( 'If 0 is specified, download is disallowed.', 'userspace' )
		)
	] );

	return $options;
}
