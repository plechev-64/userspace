<?php

/* проверяем подтверждение емейла, если такая настройка включена */
add_filter( 'wp_authenticate_user', 'usp_chek_user_authenticate', 10 );
function usp_chek_user_authenticate( $user ) {

	if ( isset( $user->ID ) && usp_get_option( 'confirm_register_recall' ) == 1 ) {

		if ( usp_is_user_role( $user->ID, 'need-confirm' ) ) {

			$wp_errors = new WP_Error();
			$wp_errors->add( 'need-confirm', __( 'Your account is unconfirmed! Confirm your account by clicking on the link in the email', 'usp' ) );
			return $wp_errors;
		}
	}

	return $user;
}

/**
 * получаем путь на возврат пользователя после авторизации
 *
 * @param int $user_id идентификатор пользователя
 */
function usp_get_authorize_url( $user_id ) {

	$redirect = false;

	if ( $autPage = usp_get_option( 'authorize_page' ) ) {

		if ( $autPage == 1 )
			$redirect	 = $_POST['redirect_to'];
		else if ( $autPage == 2 )
			$redirect	 = usp_get_option( 'custom_authorize_page' );
	}

	if ( ! $redirect )
		$redirect = usp_get_user_url( $user_id );

	return apply_filters( 'usp_redirect_after_login', $redirect, $user_id );
}

if ( function_exists( 'limit_login_add_error_message' ) )
	add_action( 'usp_login_form_head', 'usp_limit_login_add_error_message' );
function usp_limit_login_add_error_message() {
	global $wp_errors, $limit_login_my_error_shown;

	if ( ! should_limit_login_show_msg() || $limit_login_my_error_shown ) {
		return;
	}

	$msg = limit_login_get_message();

	if ( $msg != '' ) {
		$limit_login_my_error_shown				 = true;
		$wp_errors->errors['usp_limit_login'][]	 = $msg;
	}

	return;
}
