<?php

add_action( 'usp_enqueue_scripts', 'usp_support_avatar_uploader_scripts', 10 );
function usp_support_avatar_uploader_scripts() {
	if ( usp_is_office( get_current_user_id() ) ) {
		usp_enqueue_script( 'avatar-uploader', USP_URL . 'functions/supports/assets/js/usp-uploader-avatar.js', false, true );
	}
}

// add inline js localization
add_filter( 'usp_init_js_variables', 'usp_init_js_avatar_variables', 10 );
function usp_init_js_avatar_variables( $data ) {
	if ( usp_is_office( get_current_user_id() ) ) {
		$data['avatar_size'] = usp_get_option( 'usp_avatar_weight', 1024 );
		// translators: %s = 1024 (e.g. Max. %s Kb)
		$data['local']['upload_size_avatar'] = sprintf( __( 'Exceeds the maximum image size! Max. %s Kb', 'userspace' ), usp_get_option( 'usp_avatar_weight', 1024 ) );
		$data['local']['title_image_upload'] = __( 'Image being loaded', 'userspace' );
	}

	return $data;
}

// remove standard WP sizes
add_filter( 'intermediate_image_sizes_advanced', 'usp_remove_wp_library_sizes_for_avatar', 10, 2 );
function usp_remove_wp_library_sizes_for_avatar( $sizes, $image_meta ) {
	if ( strpos( $image_meta['file'], 'usp-uploads/avatars/' ) !== false ) {
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

// remove old avatar
add_action( 'usp_pre_upload', 'usp_avatar_pre_upload', 10 );
function usp_avatar_pre_upload( $uploader ) {
	if ( 'usp_avatar' != $uploader->uploader_id ) {
		return;
	}

	$oldAvatarId = get_user_meta( get_current_user_id(), 'usp_avatar', 1 );
	if ( $oldAvatarId ) {
		wp_delete_attachment( $oldAvatarId );
	}
}

// upload avatar
add_action( 'usp_upload', 'usp_avatar_upload', 10, 2 );
function usp_avatar_upload( $uploads, $uploader ) {
	if ( 'usp_avatar' != $uploader->uploader_id ) {
		return;
	}

	update_user_meta( get_current_user_id(), 'usp_avatar', $uploads['id'] );

	/**
	 * Fires after the user upload avatar.
	 *
	 * @param   $uploads_id     int     ID avatar.
	 *
	 * @since   1.0.0
	 *
	 */
	do_action( 'usp_avatar_upload', $uploads['id'] );
}

// delete avatar
add_action( 'wp', 'usp_delete_avatar_action' );
function usp_delete_avatar_action() {
	if ( ! isset( $_GET['usp-action'], $_GET['_wpnonce'] ) || 'delete_avatar' != $_GET['usp-action'] ) {
		return;
	}

	global $user_ID;

	// phpcs:ignore
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], $user_ID ) ) {
		wp_die( 'Error 101' );
	}

	$avatar_id = get_user_meta( $user_ID, 'usp_avatar', 1 );

	/**
	 * Fires before the user deletes avatar.
	 *
	 * @param   $avatar_id  int  ID avatar.
	 *
	 * @since   1.0.0
	 *
	 */
	do_action( 'usp_pre_delete_avatar', $avatar_id );

	$result = delete_user_meta( $user_ID, 'usp_avatar' );

	if ( ! $result ) {
		wp_die( 'Error 103' );
	}

	$data = wp_delete_attachment( $avatar_id );

	/**
	 * Fires after the user deletes avatar.
	 *
	 * @param   $data   WP_Post|false|null  Post data on success, false or null on failure.
	 *
	 * @since   1.0.0
	 *
	 */
	do_action( 'usp_delete_avatar', $data );

	wp_safe_redirect( add_query_arg( [ 'usp-avatar' => 'deleted' ], usp_user_get_url( $user_ID ) ) );
	exit;
}

// notice - success delete avatar
add_action( 'wp', 'usp_notice_avatar_deleted' );
function usp_notice_avatar_deleted() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['usp-avatar'] ) && 'deleted' == $_GET['usp-avatar'] ) {
		add_action( 'usp_area_notice', function () {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo usp_get_notice( [ 'type' => 'success', 'text' => __( 'Your avatar has been deleted', 'userspace' ) ] );
		} );
	}
}

// disabling caching in chrome
add_filter( 'get_avatar_data', 'usp_add_avatar_time_creation', 10 );
function usp_add_avatar_time_creation( $args ) {
	$dataUrl  = wp_parse_url( $args['url'] );
	$ava_path = untrailingslashit( ABSPATH ) . $dataUrl['path'];

	if ( ! file_exists( $ava_path ) ) {
		return $args;
	}

	$args['url'] = $args['url'] . '?ver=' . filemtime( $ava_path );

	return $args;
}
