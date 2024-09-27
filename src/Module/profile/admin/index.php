<?php

require_once 'class-usp-profile-fields-manager.php';
require_once 'profile-settings.php';

// Profile page in WordPress admin
add_action( 'admin_enqueue_scripts', 'usp_admin_profile_style' );
function usp_admin_profile_style( $page ) {
	if ( $page == 'user-edit.php' || $page == 'profile.php' ) {
		wp_enqueue_style( 'usp-admin-profile', USP_URL . 'src/Module/profile/admin/assets/css/usp-admin-profile.css' );
	}
}

add_action( 'admin_menu', 'usp_profile_admin_menu', 30 );
function usp_profile_admin_menu() {
	add_submenu_page( 'manage-userspace', __( 'The form of profile', 'userspace' ), __( 'The form of profile', 'userspace' ), 'manage_options', 'manage-userfield', 'usp_profile_fields_manager' );
}

function usp_profile_fields_manager() {

	$Manager = new USP_Profile_Fields_Manager();

	$title = __( 'Manage profile fields', 'userspace' );

	$subtitle = __( 'On this page you can create custom fields of the user profile, as well as to manage already created fields', 'userspace' );

	$header = usp_get_admin_header( $title, $subtitle );

	$content = usp_get_admin_content( $Manager->get_manager(), 'no_sidebar' );
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $header . $content;
}

add_filter( 'usp_field_options', 'usp_setup_profile_manager_field_options', 10, 3 );
function usp_setup_profile_manager_field_options( $options, $field, $manager_id ) {

	if ( ! $field->id || $manager_id != 'profile' ) {
		return $options;
	}

	$defaultFields = [
		'first_name',
		'last_name',
		'display_name',
		'url',
		'description'
	];

	if ( in_array( $field->id, $defaultFields ) ) {
		unset( $options['filter'] );
		unset( $options['public_value'] );
	} else if ( in_array( $field->type, [ 'editor', 'uploader', 'file' ] ) ) {
		unset( $options['filter'] );
	}

	if ( in_array( $field->type, [ 'uploader', 'file' ] ) ) {
		unset( $options['required'] );
	}

	return $options;
}

// Save changes in custom profile fields from the admin user's page
add_action( 'personal_options_update', 'usp_save_profile_fields' );
add_action( 'edit_user_profile_update', 'usp_save_profile_fields' );
function usp_save_profile_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	USP()->user( $user_id )->profile_fields()->update_fields();
}

// Output custom profile fields on the user's page in the admin panel
if ( is_admin() ) {
	add_action( 'profile_personal_options', 'usp_get_custom_fields_profile' );
	add_action( 'edit_user_profile', 'usp_get_custom_fields_profile' );
}
function usp_get_custom_fields_profile( $user ) {

	$fields = USP()->user( $user->ID )->profile_fields()->get_fields_for_admin_page();

	if ( ! $fields ) {
		return;
	}

	$content = '<h2>' . __( 'Custom Profile Fields', 'userspace' ) . '</h2>';
	$content .= '<div class="usp-admin-profile usp-form usp-preloader-parent">';
	$content .= '<div class="usp-content">';
	$content .= '<div class="usp-content-group">';

	foreach ( $fields as $field ) {
		/**
		 * @var FieldAbstract $field
		 */

		$field->value = USP()->user( $user->ID )->{$field->slug};

		$content .= $field->get_field_html();
	}

	$content .= '</div>';
	$content .= '</div>';
	$content .= '</div>';
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $content;
}
