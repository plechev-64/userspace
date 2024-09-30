<?php

namespace USP\Core\Module\Profile;

use USP\Core\Module\Fields\FieldAbstract;
use USP\Core\Module\Profile\Admin\ProfileFieldsManager;

class Initializer {

	const USP_PROFILE_BASE = __FILE__;

	public function init(): void {
		add_action( 'usp_enqueue_scripts', [ $this, 'usp_my_profile_resources'], 10 );
		add_filter( 'usp_init_js_variables', [ $this, 'usp_init_js_profile_variables'], 10 );
		add_action( 'usp_init_tabs', [ $this, 'usp_tab_profile'] );
		add_action( 'usp_setup_tabs', [ $this, 'usp_tab_profile_info'], 10 );

		add_filter( 'usp_profile_fields', [ $this, 'usp_add_office_profile_fields'], 10 );
		add_action( 'init', [ $this, 'usp_delete_user_account_activate'] );
		// save users page option in global array of options
		add_action( 'usp_fields_update', [ $this, 'usp_update_users_page_option'], 10, 2 );
		// add users page value in the time of saving global options of plugin
		add_filter( 'usp_global_options_pre_update', [ $this, 'usp_add_options_users_page_value'], 10 );

		if ( is_admin() ) {
			add_filter( 'usp_options', [ $this, 'usp_profile_options'] );
			// Profile page in WordPress admin
			add_action( 'admin_enqueue_scripts', [ $this, 'usp_admin_profile_style'] );
			add_action( 'admin_menu', [ $this, 'usp_profile_admin_menu'], 30 );
			add_filter( 'usp_field_options', [ $this, 'usp_setup_profile_manager_field_options'], 10, 3 );
			// Save changes in custom profile fields from the admin user's page
			add_action( 'personal_options_update', [ $this, 'usp_save_profile_fields'] );
			add_action( 'edit_user_profile_update', [ $this, 'usp_save_profile_fields'] );
			add_action( 'profile_personal_options', [ $this, 'usp_get_custom_fields_profile'] );
			add_action( 'edit_user_profile', [ $this, 'usp_get_custom_fields_profile'] );
		}

		require_once 'ajax.php';

	}

	public function usp_my_profile_resources() {
		if ( usp_is_office( get_current_user_id() ) ) {
			wp_enqueue_script( 'usp-my-profile-js', USP_URL . '/assets/modules/usp-profile.js' );
		}
	}

	public function usp_init_js_profile_variables( $data ): array {
		if ( usp_is_office( get_current_user_id() ) ) {
			$data['local']['no_repeat_pass'] = __( 'Repeated password not correct!', 'userspace' );
		}

		return $data;
	}

	public function usp_tab_profile() {
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
						'callback' => [ 'name' => get_class($this) . '::usp_get_profile_user_info' ],
					],
				],
			]
		);
	}

	public function usp_get_profile_user_info( $user_id ) {
		return 'Тут надо вывести данные о пользователе, ранее выводилась карточка из userlist';
	}

	public function usp_tab_profile_info() {
		if ( ! usp_is_office( get_current_user_id() ) ) {
			return;
		}

		$subtab = [
			'id'       => 'edit',
			'name'     => __( 'Edit profile', 'userspace' ),
			'title'    => __( 'Personal Options', 'userspace' ),
			'icon'     => 'fa-user-cog',
			'supports' => [ 'ajax' ],
			'callback' => [ 'name' => get_class($this) . '::usp_tab_profile_content' ],
		];

		usp_add_sub_tab( 'profile', $subtab );
	}

	public function usp_add_office_profile_fields( $fields ) {
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

	public function usp_tab_profile_content( $master_id ) {
		return USP()
			->user( $master_id )
			->profile_fields()
			->get_profile_fields_form();
	}

	public function usp_delete_user_account_activate() {
		if ( isset( $_POST['usp_delete_user_account'] ) ) {
			add_action( 'wp', 'usp_delete_user_account' );
		}
	}

	// User deletes their profile
	public function usp_delete_user_account() {
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

	public function usp_update_users_page_option( $fields, $manager_id ) {

		if ( $manager_id != 'profile' || ! isset( $_POST['usp_users_page'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		usp_update_option( 'usp_users_page', intval( $_POST['usp_users_page'] ) );
	}

	public function usp_add_options_users_page_value( $values ) {
		$values['usp_users_page'] = usp_get_option( 'usp_users_page', 0 );

		return $values;
	}

	public function usp_profile_options( $options ) {

		$options->add_box( 'profile', [
			'title' => __( 'Settings profile', 'userspace' ),
			'icon'  => 'fa-user'
		] )->add_group( 'general' )->add_options( [
			[
				'type'    => 'switch',
				'slug'    => 'usp_user_deleting_profile',
				'title'   => __( 'Allow users to delete their account?', 'userspace' ),
				'text'    => [
					'off' => __( 'No', 'userspace' ),
					'on'  => __( 'Yes', 'userspace' )
				],
				'default' => 0,
			]
		] );

		return $options;
	}

	public function usp_admin_profile_style( $page ) {
		if ( $page == 'user-edit.php' || $page == 'profile.php' ) {
			wp_enqueue_style( 'usp-admin-profile', USP_URL . 'admin/assets/usp-admin-profile.css' );
		}
	}

	public function usp_profile_admin_menu() {
		add_submenu_page( 'manage-userspace', __( 'The form of profile', 'userspace' ), __( 'The form of profile', 'userspace' ), 'manage_options', 'manage-userfield', 'usp_profile_fields_manager' );
	}

	public function usp_profile_fields_manager() {

		$Manager = new ProfileFieldsManager();

		$title = __( 'Manage profile fields', 'userspace' );

		$subtitle = __( 'On this page you can create custom fields of the user profile, as well as to manage already created fields', 'userspace' );

		$header = usp_get_admin_header( $title, $subtitle );

		$content = usp_get_admin_content( $Manager->get_manager(), 'no_sidebar' );
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $header . $content;
	}

	public function usp_setup_profile_manager_field_options( $options, $field, $manager_id ) {

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

	public function usp_save_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		USP()->user( $user_id )->profile_fields()->update_fields();
	}

	public function usp_get_custom_fields_profile( $user ) {

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

}