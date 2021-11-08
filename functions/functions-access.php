<?php

// blocking access to the admin panel
add_action( 'init', 'usp_admin_access', 1 );
function usp_admin_access() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return false;
	}

	if ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) {
		return false;
	}

	if ( is_admin() ) {
		$access = usp_user_is_access_console();

		if ( $access ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['short'] ) && 1 == intval( $_POST['short'] ) || isset( $_POST['fetch'] ) && 1 == intval( $_POST['fetch'] ) ) {
			return true;
		} else {
			if ( ! is_user_logged_in() ) {
				return true;
			}

			wp_safe_redirect( '/' );
			exit;
		}
	}
}

add_action( 'wp_head', 'usp_hidden_admin_panel' );
function usp_hidden_admin_panel() {
	if ( ! is_user_logged_in() ) {
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
	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( usp_user_has_role( get_current_user_id(), 'banned' ) ) {
		wp_die( esc_html__( 'Congratulations! You have been banned.', 'userspace' ) );
	}
}
