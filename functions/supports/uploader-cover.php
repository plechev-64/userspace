<?php

USP()->use_module( 'uploader' );

if ( ! is_admin() ):
	add_action( 'usp_enqueue_scripts', 'usp_support_cover_uploader_scripts', 10 );
endif;
function usp_support_cover_uploader_scripts() {
	global $user_ID;
	if ( usp_is_office( $user_ID ) ) {
		usp_enqueue_script( 'cover-uploader', USP_URL . 'functions/supports/js/uploader-cover.js', false, true );
	}
}

add_filter( 'usp_init_js_variables', 'usp_init_js_cover_variables', 10 );
function usp_init_js_cover_variables( $data ) {
	global $user_ID;

	if ( usp_is_office( $user_ID ) ) {
		$data['cover_size']					 = usp_get_option( 'cover_weight', 1024 );
		$data['local']['upload_size_cover']	 = sprintf( __( 'Exceeds the maximum image size! Max. %s Kb', 'userspace' ), usp_get_option( 'cover_weight', 1024 ) );
		$data['local']['title_image_upload'] = __( 'Image being loaded', 'userspace' );
	}

	return $data;
}

add_action( 'usp_area_top', 'usp_add_cover_uploader_button', 10 );
function usp_add_cover_uploader_button() {
	global $user_ID;
	if ( usp_is_office( $user_ID ) ) {

		USP()->use_module( 'uploader' );

		$uploder = new USP_Uploader( 'usp_cover', array(
			'multiple'		 => 0,
			'filetitle'		 => 'usp-user-cover-' . $user_ID,
			'filename'		 => $user_ID,
			'dir'			 => '/uploads/usp-uploads/covers',
			'crop'			 => array(
				'ratio' => 0
			),
			'image_sizes'	 => array(
				array(
					'height' => 9999,
					'width'	 => 9999,
					'crop'	 => 0
				)
			),
			'resize'		 => array( 1500, 1500 ),
			'min_height'	 => 300,
			'min_width'		 => 600,
			'max_size'		 => usp_get_option( 'cover_weight', 1024 )
			) );

		echo '<span class="usp-cover-icon" title="' . __( 'Upload background', 'userspace' ) . '">
                <i class="uspi fa-image"></i>
                ' . $uploder->get_input() . '
            </span>';
	}
}

add_action( 'usp_upload', 'usp_cover_upload', 10, 2 );
function usp_cover_upload( $upload, $class ) {
	global $user_ID;

	if ( $class->uploader_id != 'usp_cover' )
		return;

	$oldCoverId = get_user_meta( $user_ID, 'usp_cover', 1 );

	wp_delete_attachment( $oldCoverId );

	update_user_meta( $user_ID, 'usp_cover', $upload['id'] );

	do_action( 'usp_cover_upload' );
}
