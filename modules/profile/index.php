<?php

define( 'USP_PROFILE_BASE', __FILE__ );

if ( is_admin() ) {
	require_once 'admin/index.php';
}

add_action( 'usp_enqueue_scripts', 'usp_my_profile_resources', 10 );
function usp_my_profile_resources() {

	if ( usp_is_office( get_current_user_id() ) ) {
		usp_enqueue_script( 'usp-my-profile-js', plugins_url( 'assets/js/usp-profile.js', __FILE__ ) );
	}
}

add_action( 'usp_enqueue_scripts', 'usp_profile_style', 10 );
function usp_profile_style() {

	if ( usp_is_office() ) {
		usp_enqueue_style( 'usp-profile-css', plugins_url( 'assets/css/usp-profile.css', __FILE__ ) );
	}
}

add_filter( 'usp_init_js_variables', 'usp_init_js_profile_variables', 10 );
function usp_init_js_profile_variables( $data ) {

	if ( usp_is_office( get_current_user_id() ) ) {
		$data['local']['no_repeat_pass'] = __( 'Repeated password not correct!', 'userspace' );
	}

	return $data;
}

add_action( 'usp_init_tabs', 'usp_tab_profile' );
function usp_tab_profile() {
	usp_tab(
		array(
			'id'       => 'profile',
			'name'     => __( 'Profile', 'userspace' ),
			'title'    => __( 'User profile', 'userspace' ),
			'public'   => 1,
			'supports' => [ 'ajax' ],
			'icon'     => 'fa-address-book',
			'content'  => array(
				array(
					'id'       => 'info',
					'name'     => __( 'User info', 'userspace' ),
					'title'    => __( 'About the user', 'userspace' ),
					'callback' => [ 'name' => 'usp_get_profile_user_info' ]
				)
			)
		)
	);
}

function usp_get_profile_user_info( $user_id ) {
	return usp_get_include_template( 'usp-profile-info.php', USP_PROFILE_BASE, [ 'user_id' => $user_id ] );
}

add_action( 'usp_setup_tabs', 'usp_tab_profile_info', 10 );
function usp_tab_profile_info() {

	if ( ! usp_is_office( get_current_user_id() ) ) {
		return;
	}

	$subtab = array(
		'id'       => 'edit',
		'name'     => __( 'Edit profile', 'userspace' ),
		'title'    => __( 'Personal Options', 'userspace' ),
		'icon'     => 'fa-user-cog',
		'supports' => [ 'ajax' ],
		'callback' => [ 'name' => 'usp_tab_profile_content' ]
	);

	usp_add_sub_tab( 'profile', $subtab );
}

add_action( 'usp_bar_profile_menu_buttons', 'usp_bar_add_profile_link', 15 );
function usp_bar_add_profile_link() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	echo usp_get_button( [
		'type'  => 'clear',
		'size'  => 'medium',
		'class' => 'usp-bar-profile__info',
		'href'  => usp_get_tab_permalink( get_current_user_id(), 'profile' ),
		'icon'  => 'fa-address-book',
		'label' => __( 'Profile info', 'userspace' )
	] );

	echo usp_get_button( [
		'type'  => 'clear',
		'size'  => 'medium',
		'class' => 'usp-bar-profile__settings',
		'href'  => usp_get_tab_permalink( get_current_user_id(), 'profile', 'edit' ),
		'icon'  => 'fa-user-cog',
		'label' => __( 'Profile settings', 'userspace' )
	] );
}

if ( ! is_admin() ) {
	add_action( 'wp', 'usp_update_profile_notice' );
}
function usp_update_profile_notice() {
	if ( isset( $_GET['usp-profile-updated'] ) ) {
		add_action( 'usp_area_notice', function () {
			echo usp_get_notice( [
				'type'  => 'success',
				'class' => 'usp_profile_updated',
				'text'  => __( 'Your profile has been updated', 'userspace' )
			] );
		} );
	}
}

// Updating the user profile
add_action( 'wp', 'usp_edit_profile', 10 );
function usp_edit_profile() {

	if ( ! isset( $_POST['submit_user_profile'] ) ) {
		return;
	}

	$user_id = get_current_user_id();

	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-profile_' . $user_id ) ) {
		return;
	}

	USP()->user()->profile_fields()->update_fields();

	/*
	 * TODO Нужен ли этот хук?
	 */
	do_action( 'personal_options_update', $user_id );

	$redirect_url = usp_get_tab_permalink( $user_id, 'profile' ) . '&usp-profile-updated=true';

	wp_redirect( $redirect_url );

	exit;
}

add_filter( 'usp_profile_fields', 'usp_add_office_profile_fields', 10 );
function usp_add_office_profile_fields( $fields ) {

	if ( ! usp_user_is_access_console() ) {
		return $fields;
	}

	$profileFields = [
		[
			'slug'    => 'show_admin_bar_front',
			'title'   => __( 'Admin toolbar', 'userspace' ),
			'type'    => 'radio',
			'values'  => [
				'false' => __( 'Disabled', 'userspace' ),
				'true'  => __( 'Enabled', 'userspace' )
			],
			'default' => 'false',
			'value_in_key' => false
		]
	];

	return ( $fields ) ? array_merge( $profileFields, $fields ) : $profileFields;
}

function usp_tab_profile_content( $master_id ) {

	return USP()->user( $master_id )->profile_fields()->get_profile_fields_form();
}

add_action( 'init', 'usp_delete_user_account_activate' );
function usp_delete_user_account_activate() {
	if ( isset( $_POST['usp_delete_user_account'] ) ) {
		add_action( 'wp', 'usp_delete_user_account' );
	}
}

// User deletes their profile
function usp_delete_user_account() {

	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'delete-user-' . get_current_user_id() ) ) {
		return false;
	}

	require_once( ABSPATH . 'wp-admin/includes/user.php' );

	global $wpdb;

	$wpdb->query( $wpdb->prepare( "DELETE FROM " . USP_PREF . "user_action WHERE user ='%d'", get_current_user_id() ) );

	$delete = wp_delete_user( get_current_user_id() );

	if ( $delete ) {
		wp_die( __( 'We are very sorry but your account has been deleted!', 'userspace' ) );
		echo '<a href="/">' . __( 'Go back to the main page', 'userspace' ) . '</a>';
	} else {
		wp_die( __( 'Account deletion failed! Go back and try again.', 'userspace' ) );
	}
}

add_action( 'usp_info_stats', 'usp_user_count_comments', 20 );
add_action( 'usp_info_stats', 'usp_user_count_publications', 20 );
add_action( 'usp_info_stats', 'usp_user_get_date_registered', 20 );

add_action( 'usp_info_meta', 'usp_user_info_age', 20 );
function usp_user_info_age( $user_id ) {
	echo usp_user_get_age_html( $user_id, 'usp-info__age' );
}

// save users page option in global array of options
add_action( 'usp_fields_update', 'usp_update_users_page_option', 10, 2 );
function usp_update_users_page_option( $fields, $manager_id ) {
	if ( $manager_id != 'profile' || ! isset( $_POST['usp_users_page'] ) ) {
		return;
	}

	usp_update_option( 'usp_users_page', $_POST['usp_users_page'] );
}

// add users page value in the time of saving global options of plugin
add_filter( 'usp_global_options_pre_update', 'usp_add_options_users_page_value', 10 );
function usp_add_options_users_page_value( $values ) {
	$values['usp_users_page'] = usp_get_option( 'usp_users_page', 0 );

	return $values;
}
