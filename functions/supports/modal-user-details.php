<?php

add_action( 'usp_enqueue_scripts', 'usp_support_user_info_scripts', 10 );
function usp_support_user_info_scripts() {
	if ( ! usp_is_office() ) {
		return;
	}

	usp_dialog_scripts();
	usp_enqueue_script( 'usp-user-info-js', USP_URL . 'functions/supports/assets/js/usp-user-details.js', false, true );
}

add_action( 'usp_enqueue_scripts', 'usp_support_user_info_style', 10 );
function usp_support_user_info_style() {
	if ( ! usp_is_office() ) {
		return;
	}

	usp_enqueue_style( 'usp-user-info-css', USP_URL . 'functions/supports/assets/css/usp-user-details.css' );
}

// add buttons in personal account
add_filter( 'usp_avatar_bttns', 'usp_add_user_info_button', 10 );
function usp_add_user_info_button( $buttons ) {
	usp_dialog_scripts();

	$args    = [
		'type'    => 'simple',
		'size'    => 'medium',
		'class'   => 'usp-ava__info usp-ava__bttn usps__jc-center',
		'title'   => __( 'User info', 'userspace' ),
		'onclick' => 'usp_get_user_info(this);return false;',
		'href'    => '#',
		'icon'    => 'fa-info-circle',
	];
	$buttons .= usp_get_button( $args );

	return $buttons;
}

// Get ajax user details by id
usp_ajax_action( 'usp_return_user_details', true );
function usp_return_user_details() {
	usp_verify_ajax_nonce();

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$user_id = ( isset( $_POST['user_id'] ) ) ? absint( $_POST['user_id'] ) : 0;

	$user          = USP()->user( $user_id );
	$offline_class = ( $user->is_online() ) ? '' : 'usps__column';

	$name = '<div class="usp-user-modal__top usps ' . $offline_class . '">';
	$name .= '<div class="usp-user-modal__name">' . $user->get_username() . '</div>';
	$name .= '<div class="usp-user-modal__action usps">' . $user->get_action( 'mixed' ) . '</div>';
	$name .= '</div>';

	return [
		'content' => usp_get_user_details( $user_id ),
		'name'    => $name,
	];
}

// Get user details by id
function usp_get_user_details( $user_id ) {
	USP()->use_module( 'users-list' );

	$manager = new USP_Users_Manager( [
		'id__in'      => $user_id,
		'search'      => 0,
		'template'    => 'modal',
		'custom_data' => 'posts, comments, user_registered, rating',
	] );

	return $manager->get_manager();
}
