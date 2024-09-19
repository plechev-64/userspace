<?php

add_action( 'admin_menu', 'usp_admin_menu', 19 );
function usp_admin_menu() {
	add_menu_page( __( 'UserSpace', 'userspace' ), __( 'UserSpace', 'userspace' ), 'manage_options', 'manage-userspace', 'usp_global_options' );
	add_submenu_page( 'manage-userspace', __( 'Settings', 'userspace' ), __( 'Settings', 'userspace' ), 'manage_options', 'manage-userspace', 'usp_global_options' );
	add_submenu_page( 'manage-userspace', __( 'Tabs manager', 'userspace' ), __( 'Tabs manager', 'userspace' ), 'manage_options', 'usp-tabs-manager', 'usp_admin_tabs_manager' );
	add_submenu_page( 'manage-userspace', __( 'Registration form', 'userspace' ), __( 'Registration form', 'userspace' ), 'manage_options', 'usp-register-form-manager', 'usp_register_form_manager' );
}

function usp_register_form_manager() {

	require_once USP_PATH . 'src/Admin/RegisterFormManager.php';

	$Manager = new RegisterFormManager();

	$title = __( 'Editing registration form fields', 'userspace' );

	$header = usp_get_admin_header( $title );

	$content = usp_get_admin_content( $Manager->get_manager(), 'no_sidebar' );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $header . $content;
}

// Plugin settings in the admin panel
function usp_global_options() {
	require_once 'pages/options.php';
}

function usp_admin_tabs_manager() {
	require_once 'pages/tabs-manager.php';
}
