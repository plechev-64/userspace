<?php

// blocking access to the admin panel
add_action( 'init', 'usp_admin_access', 1 );
function usp_admin_access() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) {
		return;
	}

	if ( is_admin() ) {

		global $user_ID;

		$access = usp_user_is_access_console();

		if ( $access ) {
			return true;
		}

		if ( isset( $_POST['short'] ) && intval( $_POST['short'] ) == 1 || isset( $_POST['fetch'] ) && intval( $_POST['fetch'] ) == 1 ) {

			return true;
		} else {

			if ( ! $user_ID ) {
				return true;
			}

			wp_redirect( '/' );
			exit;
		}
	}
}

add_action( 'wp_head', 'usp_hidden_admin_panel' );
function usp_hidden_admin_panel() {
	global $user_ID;

	if ( ! $user_ID ) {
		return show_admin_bar( false );
	}

	$access = usp_user_is_access_console();

	if ( $access ) {
		return true;
	}

	show_admin_bar( false );
}

add_action( 'init', 'usp_banned_user_redirect' );
function usp_banned_user_redirect() {
	global $user_ID;

	if ( ! $user_ID ) {
		return false;
	}

	if ( usp_user_has_role( $user_ID, 'banned' ) ) {
		wp_die( __( 'Congratulations! You have been banned.', 'userspace' ) );
	}
}
