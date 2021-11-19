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
		[
			'id'       => 'profile',
			'name'     => __( 'Profile', 'userspace' ),
			'title'    => __( 'User profile', 'userspace' ),
			'public'   => 1,
			'supports' => [ 'ajax' ],
			'icon'     => 'fa-address-book',
			'content'  => [
				[
					'id'       => 'info',
					'name'     => __( 'User info', 'userspace' ),
					'title'    => __( 'About the user', 'userspace' ),
					'callback' => [ 'name' => 'usp_get_profile_user_info' ],
				],
			],
		]
	);
}

function usp_get_profile_user_info( $user_id ) {
	USP()->use_module( 'users-list' );

	$manager = new USP_Users_Manager( [
		'id__in'      => $user_id,
		'search'      => 0,
		'template'    => 'full',
		'custom_data' => 'posts, comments, user_registered, rating',
	] );

	return $manager->get_manager();
}

add_action( 'usp_setup_tabs', 'usp_tab_profile_info', 10 );
function usp_tab_profile_info() {
	if ( ! usp_is_office( get_current_user_id() ) ) {
		return;
	}

	$subtab = [
		'id'       => 'edit',
		'name'     => __( 'Edit profile', 'userspace' ),
		'title'    => __( 'Personal Options', 'userspace' ),
		'icon'     => 'fa-user-cog',
		'supports' => [ 'ajax' ],
		'callback' => [ 'name' => 'usp_tab_profile_content' ],
	];

	usp_add_sub_tab( 'profile', $subtab );
}

// Updating the user profile
usp_ajax_action( 'usp_user_update_profile' );
function usp_user_update_profile() {
	if ( ! isset( $_POST['submit_user_profile'] ) ) {
		return [
			'error' => __( 'Something has been wrong', 'userspace' ),
		];
	}

	USP()->user()->profile_fields()->update_fields();

	return [
		'notice' => [
			'text'       => __( 'Your profile has been updated', 'userspace' ),
			'type'       => 'success',
			'time_close' => 10000,
		],
	];
}

add_filter( 'usp_profile_fields', 'usp_add_office_profile_fields', 10 );
function usp_add_office_profile_fields( $fields ) {
	if ( ! usp_user_is_access_console() ) {
		return $fields;
	}

	$profileFields = [
		[
			'slug'         => 'show_admin_bar_front',
			'title'        => __( 'Admin toolbar', 'userspace' ),
			'type'         => 'radio',
			'values'       => [
				'false' => __( 'Disabled', 'userspace' ),
				'true'  => __( 'Enabled', 'userspace' ),
			],
			'default'      => 'false',
			'value_in_key' => false,
		],
	];

	return ( $fields ) ? array_merge( $profileFields, $fields ) : $profileFields;
}

function usp_tab_profile_content( $master_id ) {
	return USP()
		->user( $master_id )
		->profile_fields()
		->get_profile_fields_form();
}

add_action( 'init', 'usp_delete_user_account_activate' );
function usp_delete_user_account_activate() {
	if ( isset( $_POST['usp_delete_user_account'] ) ) {
		add_action( 'wp', 'usp_delete_user_account' );
	}
}

// User deletes their profile
function usp_delete_user_account() {
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'delete-user-' . get_current_user_id() ) ) {
		return false;
	}

	require_once( ABSPATH . 'wp-admin/includes/user.php' );

	global $wpdb;

	//phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->delete( USP_PREF . "user_action", [ 'user' => get_current_user_id() ] );

	$delete = wp_delete_user( get_current_user_id() );

	if ( $delete ) {
		$msg = esc_html__( 'We are very sorry but your account has been deleted!', 'userspace' );
		$msg .= '<br><a href="/">' . esc_html__( 'Go back to the main page', 'userspace' ) . '</a>';
		wp_die( wp_kses_post( $msg ) );
	} else {
		wp_die( esc_html__( 'Account deletion failed! Go back and try again.', 'userspace' ) );
	}
}

// save users page option in global array of options
add_action( 'usp_fields_update', 'usp_update_users_page_option', 10, 2 );
function usp_update_users_page_option( $fields, $manager_id ) {

	if ( $manager_id != 'profile' || ! isset( $_POST['usp_users_page'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	usp_update_option( 'usp_users_page', intval( $_POST['usp_users_page'] ) );
}

// add users page value in the time of saving global options of plugin
add_filter( 'usp_global_options_pre_update', 'usp_add_options_users_page_value', 10 );
function usp_add_options_users_page_value( $values ) {
	$values['usp_users_page'] = usp_get_option( 'usp_users_page', 0 );

	return $values;
}
