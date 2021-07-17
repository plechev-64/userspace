<?php

// user authorization
function usp_login_user() {
	global $wp_errors;

	$pass   = sanitize_text_field( $_POST['user_pass'] );
	$login  = sanitize_user( $_POST['user_login'] );
	$member = ( isset( $_POST['rememberme'] ) ) ? intval( $_POST['rememberme'] ) : 0;
	$url    = esc_url( $_POST['redirect_to'] );

	$wp_errors = new WP_Error();

	if ( ! $pass || ! $login ) {
		$wp_errors->add( 'usp_login_empty', __( 'Fill in the required fields!', 'userspace' ) );

		return $wp_errors;
	}

	$creds                  = array();
	$creds['user_login']    = $login;
	$creds['user_password'] = $pass;
	$creds['remember']      = $member;
	$userdata               = wp_signon( $creds );

	if ( is_wp_error( $userdata ) ) {
		$wp_errors = $userdata;

		return $wp_errors;
	}

	wp_redirect( apply_filters( 'login_redirect', $url, '', $userdata ) );
	exit;
}

// accept data for user authorization from the UserSpace form
add_action( 'init', 'usp_get_login_user_activate' );
function usp_get_login_user_activate() {
	if ( isset( $_POST['login_wpnonce'] ) ) {
		if ( ! wp_verify_nonce( $_POST['login_wpnonce'], 'login-key-usp' ) ) {
			return false;
		}
		add_action( 'wp', 'usp_login_user', 10 );
	}
}

function usp_get_current_url( $typeform = false, $unset = false ) {

	$args = array(
		'register'        => false,
		'login'           => false,
		'remember'        => false,
		'success'         => false,
		'usp-confirmdata' => false
	);

	$args['action-usp'] = $typeform;

	if ( $typeform == 'remember' ) {
		$args['remember'] = 'success';
	}

	return add_query_arg( $args );
}

function usp_referer_url( $typeform = false ) {
	echo usp_get_current_url( $typeform );
}

function usp_form_action( $typeform ) {
	echo usp_get_current_url( $typeform, true );
}
