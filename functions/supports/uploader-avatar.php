<?php

USP()->use_module( 'uploader' );

add_action( 'usp_enqueue_scripts', 'usp_support_avatar_uploader_scripts', 10 );
function usp_support_avatar_uploader_scripts() {
	if ( usp_is_office( get_current_user_id() ) ) {
		usp_enqueue_script( 'avatar-uploader', USP_URL . 'functions/supports/assets/js/usp-uploader-avatar.js', false, true );
	}
}

add_filter( 'usp_init_js_variables', 'usp_init_js_avatar_variables', 10 );
function usp_init_js_avatar_variables( $data ) {
	global $user_ID;

	if ( usp_is_office( $user_ID ) ) {
		$data['avatar_size']                 = usp_get_option( 'usp_avatar_weight', 1024 );
		$data['local']['upload_size_avatar'] = sprintf( __( 'Exceeds the maximum image size! Max. %s Kb', 'userspace' ), usp_get_option( 'usp_avatar_weight', 1024 ) );
		$data['local']['title_image_upload'] = __( 'Image being loaded', 'userspace' );
		//$data['local']['title_webcam_upload'] = __( 'Image from camera', 'userspace' );
	}

	return $data;
}

add_filter( 'usp_avatar_bttns', 'usp_button_avatar_upload', 10 );
function usp_button_avatar_upload( $buttons ) {
	global $user_ID;

	if ( ! usp_is_office( $user_ID ) ) {
		return false;
	}

	USP()->use_module( 'uploader' );

	$uploader = new USP_Uploader( 'usp_avatar', [
		'multiple'    => 0,
		'crop'        => 1,
		'filetitle'   => 'usp-user-avatar-' . $user_ID,
		'filename'    => $user_ID,
		'dir'         => '/uploads/usp-uploads/avatars',
		'image_sizes' => [
			[
				'height' => 70,
				'width'  => 70,
				'crop'   => 1
			],
			[
				'height' => 150,
				'width'  => 150,
				'crop'   => 1
			],
			[
				'height' => 300,
				'width'  => 300,
				'crop'   => 1
			]
		],
		'resize'      => [ 1000, 1000 ],
		'min_height'  => 150,
		'min_width'   => 150,
		'max_size'    => usp_get_option( 'usp_avatar_weight', 1024 )
	] );

	$args_uploads = [
		'type'    => 'simple',
		'size'    => 'medium',
		'class'   => 'usp-ava__uploads usp-ava__bttn usps__jc-center',
		'title'   => __( 'Upload avatar', 'userspace' ),
		'content' => $uploader->get_input(),
		'icon'    => 'fa-download',
	];
	$buttons      .= usp_get_button( $args_uploads );

	if ( get_user_meta( $user_ID, 'usp_avatar', 1 ) ) {
		$args_del = [
			'type'  => 'simple',
			'size'  => 'medium',
			'class' => 'usp-ava__del usp-ava__bttn usps__jc-center',
			'title' => __( 'Delete avatar', 'userspace' ),
			'href'  => wp_nonce_url( add_query_arg( [ 'usp-action' => 'delete_avatar' ], usp_user_get_url( $user_ID ) ), $user_ID ),
			'icon'  => 'fa-times',
		];
		$buttons  .= usp_get_button( $args_del );
	}

	return $buttons;
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

add_action( 'usp_pre_upload', 'usp_avatar_pre_upload', 10 );
function usp_avatar_pre_upload( $uploader ) {
	global $user_ID;

	if ( $uploader->uploader_id != 'usp_avatar' ) {
		return;
	}

	if ( $oldAvatarId = get_user_meta( $user_ID, 'usp_avatar', 1 ) ) {
		wp_delete_attachment( $oldAvatarId );
	}
}

add_action( 'usp_upload', 'usp_avatar_upload', 10, 2 );
function usp_avatar_upload( $uploads, $uploader ) {
	global $user_ID;

	if ( $uploader->uploader_id != 'usp_avatar' ) {
		return;
	}

	update_user_meta( $user_ID, 'usp_avatar', $uploads['id'] );

	do_action( 'usp_avatar_upload' );
}

add_action( 'wp', 'usp_delete_avatar_action' );
function usp_delete_avatar_action() {
	global $user_ID;
	if ( ! isset( $_GET['usp-action'], $_GET['_wpnonce'] ) || $_GET['usp-action'] != 'delete_avatar' ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], $user_ID ) ) {
		wp_die( 'Error 101' );
	}

	$avatar_id = get_user_meta( $user_ID, 'usp_avatar', 1 );

	$result = delete_user_meta( $user_ID, 'usp_avatar' );

	if ( ! $result ) {
		wp_die( 'Error 103' );
	}

	wp_delete_attachment( $avatar_id );

	do_action( 'usp_delete_avatar' );

	wp_safe_redirect( add_query_arg( [ 'usp-avatar' => 'deleted' ], usp_user_get_url( $user_ID ) ) );
	exit;
}

add_action( 'wp', 'usp_notice_avatar_deleted' );
function usp_notice_avatar_deleted() {
	if ( isset( $_GET['usp-avatar'] ) && $_GET['usp-avatar'] == 'deleted' ) {
		add_action( 'usp_area_notice', function () {
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
