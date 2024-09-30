<?php

// todo: у нас же нет такого меню?
/*add_action( 'usp_area_top', 'usp_add_office_menu_options', 10 );
function usp_add_office_menu_options() {
	echo USP()->tabs()->get_menu( 'options' );
}*/

/**
 * Checks is the user profile page or the user profile of the specified user_id.
 *
 * @param   $user_id    int ID user.
 *
 * @return  bool        true - is office, false - not.
 *                      If user_id is passed: true - is office by user_id, false - not.
 * @since   1.0.0
 */
function usp_is_office( $user_id = null ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( isset( $_POST['action'] ) && 'usp_ajax_tab' == $_POST['action'] ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$post = ( isset( $_POST['post'] ) ) ? usp_decode( $_POST['post'] ) : false;
		if ( ! $post ) {
			return false;
		}

		$master_id = absint( $post->master_id );

		if ( $master_id ) {
			USP()->office()->set_owner( $master_id );
		}
	} else if ( Ajax()->is_rest_request() && isset( $_POST['office_id'] ) ) {
		USP()->office()->set_owner( intval( $_POST['office_id'] ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

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
	 * @param string    Added class.
	 *                  Default: empty string
	 *
	 * @since 1.0.0
	 */
	$class[] = apply_filters( 'usp_office_class', '' );

	return implode( ' ', $class );
}

/**
 * Added support for some features for the personal account theme.
 *
 * @param   $support    string  Available: 'avatar-uploader','cover-uploader','modal-user-details','zoom-avatar'
 *
 * @since   1.0.0
 */
function usp_template_support( $support ) {
	if ( usp_is_office() || Ajax()->is_rest_request() ) {
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

/**
 *  Adds the ability to upload an avatar.
 *
 * @since   1.0.0
 */
function usp_include_uploader_avatar() {
	if ( usp_get_option( 'usp_avatar_weight', 1024 ) > 0 ) {
		include_once USP_PATH . 'functions/supports/uploader-avatar.php';
	}
}

/**
 *  Adds the ability to upload background cover image.
 *
 * @since   1.0.0
 */
function usp_include_uploader_cover() {

	if ( usp_get_option( 'usp_cover_weight', 1024 ) > 0 ) {
		include_once USP_PATH . 'functions/supports/uploader-cover.php';
	}
}

/**
 *  Adds the ability to show modal user details.
 *
 * @since   1.0.0
 */
function usp_include_modal_user_details() {
	include_once USP_PATH . 'functions/supports/modal-user-details.php';
}

/**
 *  Adds the ability to show zoom avatar.
 *
 * @since   1.0.0
 */
function usp_include_zoom_avatar() {
	include_once USP_PATH . 'functions/supports/zoom-avatar.php';
}

// todo: что за ballon? Функция нигде не используется. Функция нотисов отдельная у нас есть. Это лишнее?
function usp_add_balloon_menu( $data, $args ) {
	if ( $data['id'] != $args['tab_id'] ) {
		return $data;
	}
	$data['name'] = sprintf( '%s <span class="usp-menu-notice usps__line-1">%s</span>', $data['name'], $args['ballon_value'] );

	return $data;
}

// Additional classes in body tag in user account page
add_filter( 'body_class', 'usp_add_office_class_body' );
function usp_add_office_class_body( $classes ) {
	if ( usp_is_office() ) {
		$classes[] = 'usp-office';

		if ( is_user_logged_in() ) {
			$classes[] = USP()->office()->is_owner( get_current_user_id() ) ? 'usp-visitor-master' : 'usp-visitor-guest';
		} else {
			$classes[] = 'usp-visitor-guest';
		}
	}

	return $classes;
}
