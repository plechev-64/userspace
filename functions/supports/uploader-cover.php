<?php

add_action( 'usp_enqueue_scripts', 'usp_support_cover_uploader_scripts', 10 );
function usp_support_cover_uploader_scripts() {
	if ( usp_is_office( get_current_user_id() ) ) {
		wp_enqueue_script( 'cover-uploader', USP_URL . 'functions/supports/assets/js/usp-uploader-cover.js', false, false,true );
	}
}

// add inline js localization
add_filter( 'usp_init_js_variables', 'usp_init_js_cover_variables', 10 );
function usp_init_js_cover_variables( $data ) {
	if ( usp_is_office( get_current_user_id() ) ) {
		$data['cover_size'] = usp_get_option( 'usp_cover_weight', 1024 );
		// translators: %s = 1024 (e.g. Max. %s Kb)
		$data['local']['upload_size_cover']  = sprintf( __( 'Exceeds the maximum image size! Max. %s Kb', 'userspace' ), usp_get_option( 'usp_cover_weight', 1024 ) );
		$data['local']['title_image_upload'] = __( 'Image being loaded', 'userspace' );
		$data['local']['image_load_ok']      = __( 'Image uploaded successfully', 'userspace' );
	}

	return $data;
}

// remove standard WP sizes
add_filter( 'intermediate_image_sizes_advanced', 'usp_remove_wp_library_sizes_for_cover', 10, 2 );
function usp_remove_wp_library_sizes_for_cover( $sizes, $image_meta ) {
	if ( strpos( $image_meta['file'], 'usp-uploads/covers/' ) !== false ) {
		if ( isset( $sizes['medium'] ) ) {
			unset( $sizes['medium'] );
		}

		if ( isset( $sizes['medium_large'] ) ) {
			unset( $sizes['medium_large'] );
		}

		if ( isset( $sizes['large'] ) ) {
			unset( $sizes['large'] );
		}
	}

	return $sizes;
}

// upload cover
add_action( 'usp_upload', 'usp_cover_upload', 10, 2 );
function usp_cover_upload( $upload, $class ) {
	if ( 'usp_cover' != $class->uploader_id ) {
		return;
	}

	global $user_ID;

	$oldCoverId = get_user_meta( $user_ID, 'usp_cover', 1 );

	wp_delete_attachment( $oldCoverId );

	update_user_meta( $user_ID, 'usp_cover', $upload['id'] );

	/**
	 * Fires after the user upload cover.
	 *
	 * @param   $uploads_id     int     ID cover.
	 *
	 * @since   1.0.0
	 *
	 */
	do_action( 'usp_cover_upload', $upload['id'] );
}
