<?php

USP()->use_module( 'fields-manager' );

class USP_Profile_Fields_Manager extends USP_Fields_Manager {
	function __construct() {
		global $wpdb;

		parent::__construct( 'profile', array(
			'option_name'	 => 'usp_profile_fields',
			'empty_field'	 => 0,
			'structure_edit' => 1,
			'meta_delete'	 => array(
				$wpdb->usermeta => 'meta_key'
			),
			'default_fields' => apply_filters( 'usp_default_profile_fields', array(
				array(
					'slug'	 => 'first_name',
					'title'	 => __( 'Firstname', 'usp' ),
					'icon'	 => 'fa-user',
					'type'	 => 'text'
				),
				array(
					'slug'	 => 'last_name',
					'title'	 => __( 'Surname', 'usp' ),
					'icon'	 => 'fa-user',
					'type'	 => 'text'
				),
				array(
					'slug'	 => 'display_name',
					'title'	 => __( 'Name to be displayed', 'usp' ),
					'icon'	 => 'fa-user',
					'type'	 => 'text'
				),
				array(
					'slug'	 => 'user_url',
					'title'	 => __( 'Website', 'usp' ),
					'icon'	 => 'fa-link',
					'type'	 => 'url'
				),
				array(
					'slug'	 => 'description',
					'title'	 => __( 'Status', 'usp' ),
					'icon'	 => 'fa-comment',
					'type'	 => 'textarea'
				),
				array(
					'slug'	 => 'usp_birthday',
					'title'	 => __( 'Birthday', 'usp' ),
					'icon'	 => 'fa-birthday-cake',
					'type'	 => 'date'
				),
				array(
					'slug'		 => 'user_email',
					'title'		 => __( 'E-mail', 'usp' ),
					'type'		 => 'email',
					'required'	 => 1
				),
				array(
					'slug'		 => 'primary_pass',
					'title'		 => __( 'New password', 'usp' ),
					'type'		 => 'password',
					'required'	 => 0,
					'notice'	 => __( 'If you want to change your password - enter a new one', 'usp' )
				),
				array(
					'slug'		 => 'repeat_pass',
					'title'		 => __( 'Repeat password', 'usp' ),
					'type'		 => 'password',
					'required'	 => 0,
					'notice'	 => __( 'Repeat the new password', 'usp' )
				))
			),
			'field_options'	 => apply_filters( 'usp_profile_field_options', array(
				array(
					'slug'	 => 'notice',
					'type'	 => 'textarea',
					'title'	 => __( 'field description', 'usp' )
				),
				array(
					'slug'	 => 'required',
					'type'	 => 'radio',
					'title'	 => __( 'required field', 'usp' ),
					'values' => array( __( 'No', 'usp' ), __( 'Yes', 'usp' ) )
				),
				array(
					'slug'	 => 'public_value',
					'type'	 => 'radio',
					'title'	 => __( 'show the content to other users', 'usp' ),
					'values' => array( __( 'No', 'usp' ), __( 'Yes', 'usp' ) )
				),
				array(
					'slug'	 => 'admin',
					'type'	 => 'radio',
					'title'	 => __( 'can be changed only by the site administration', 'usp' ),
					'values' => array( __( 'No', 'usp' ), __( 'Yes', 'usp' ) )
				),
				array(
					'slug'	 => 'filter',
					'type'	 => 'radio',
					'title'	 => __( 'Filter users by this field', 'usp' ),
					'values' => array( __( 'No', 'usp' ), __( 'Yes', 'usp' ) )
				)
			) )
		) );

		$this->setup_default_fields();
	}

	function get_manager_options_form_fields() {

		$fields = array(
			'users_page_usp' => array(
				'type'		 => 'custom',
				'title'		 => __( 'Users page', 'usp' ),
				'notice'	 => __( 'This page is required to filter users by value of profile fields', 'usp' ),
				'content'	 => wp_dropdown_pages( array(
					'selected'			 => usp_get_option( 'users_page_usp' ),
					'name'				 => 'users_page_usp',
					'show_option_none'	 => __( 'Not selected', 'usp' ),
					'echo'				 => 0
					)
				)
			) );

		return $fields;
	}

}
