<?php

USP()->use_module( 'fields-manager' );

class USP_Profile_Fields_Manager extends USP_Fields_Manager {
	function __construct() {
		global $wpdb;

		parent::__construct( 'profile', [
			'option_name'    => 'usp_profile_fields',
			'empty_field'    => 0,
			'structure_edit' => 1,
			'meta_delete'    => [
				$wpdb->usermeta => 'meta_key'
			],
			'default_fields' => USP()->profile_fields()->get_default_fields(),
			'field_options'  => USP()->profile_fields()->get_fields_options()
		] );

		$this->setup_default_fields();
	}

	function get_manager_options_form_fields() {

		$fields = [
			'usp_users_page' => [
				'type'    => 'custom',
				'title'   => __( 'Users page', 'userspace' ),
				'notice'  => __( 'This page is required to filter users by value of profile fields', 'userspace' ),
				'content' => wp_dropdown_pages( [
						'selected'         => intval( usp_get_option( 'usp_users_page' ) ),
						'name'             => 'usp_users_page',
						'show_option_none' => esc_html__( 'Not selected', 'userspace' ),
						'echo'             => 0
					]
				)
			]
		];

		return $fields;
	}

}
