<?php

add_action( 'usp_area_top', 'usp_add_office_menu_options', 10 );
function usp_add_office_menu_options() {
	echo USP()->tabs()->get_menu( 'options' );
}

/**
 * Checks is the user profile page or the user profile of the specified user_id
 *
 * @param   int  $user_id  id user.
 *
 * @return bool         true - is office, false - not.
 *                      If user_id is passed: true - is office by user_id, false - not.
 * @since 1.0
 *
 */
function usp_is_office( $user_id = null ) {

	if ( isset( $_POST['action'] ) && $_POST['action'] == 'usp_ajax_tab' ) {

		$post = usp_decode( $_POST['post'] );

		if ( $post->master_id ) {
			USP()->office()->set_owner( $post->master_id );
		}
	} else if ( USP_Ajax()->is_rest_request() && isset( $_POST['office_id'] ) ) {
		USP()->office()->set_owner( intval( $_POST['office_id'] ) );
	}

	if ( USP()->office()->get_owner_id() ) {

		if ( isset( $user_id ) ) {
			if ( USP()->office()->is_owner( $user_id ) ) {
				return true;
			}

			return false;
		}

		return true;
	}

	return false;
}

function usp_get_office_class() {
	/**
	 * Adding class in user office.
	 *
	 * @param   string    added class.
	 *                  Default: empty string
	 *
	 * @since 1.0
	 *
	 */
	$class[] = apply_filters( 'usp_office_class', '' );

	return implode( ' ', $class );
}

function usp_template_support( $support ) {
	if ( usp_is_office() || USP_Ajax()->is_rest_request() ) {
		switch ( $support ) {
			case 'avatar-uploader':
				usp_include_uploader_avatar();
				break;
			case 'cover-uploader':
				usp_include_uploader_cover();
				break;
			case 'modal-user-details':
				usp_include_modal_user_details();
				break;

			case 'zoom-avatar':
				usp_include_zoom_avatar();
				break;
		}
	}
}

function usp_include_uploader_avatar() {
	if ( usp_get_option( 'usp_avatar_weight', 1024 ) > 0 ) {
		include_once USP_PATH . 'functions/supports/uploader-avatar.php';
	}
}

function usp_include_uploader_cover() {
	add_filter( 'usp_options', 'usp_add_cover_options', 10 );

	if ( usp_get_option( 'usp_cover_weight', 1024 ) > 0 ) {
		include_once USP_PATH . 'functions/supports/uploader-cover.php';
	}
}

function usp_include_modal_user_details() {
	include_once USP_PATH . 'functions/supports/modal-user-details.php';
}

function usp_include_zoom_avatar() {
	include_once USP_PATH . 'functions/supports/zoom-avatar.php';
}

function usp_add_balloon_menu( $data, $args ) {
	if ( $data['id'] != $args['tab_id'] ) {
		return $data;
	}
	$data['name'] = sprintf( '%s <span class="usp-menu-notice usps__line-1">%s</span>', $data['name'], $args['ballon_value'] );

	return $data;
}

add_filter( 'body_class', 'usp_add_office_class_body' );
function usp_add_office_class_body( $classes ) {
	if ( usp_is_office() ) {
		global $user_ID;

		$classes[] = 'usp-office';

		if ( $user_ID ) {
			$classes[] = USP()->office()->is_owner( $user_ID ) ? 'usp-visitor-master' : 'usp-visitor-guest';
		} else {
			$classes[] = 'usp-visitor-guest';
		}
	}

	return $classes;
}
