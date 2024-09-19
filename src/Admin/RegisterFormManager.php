<?php

USP()->use_module( 'fields-manager' );

class RegisterFormManager extends USP_Fields_Manager {
	public function __construct() {

		parent::__construct( 'register_form', [
			'empty_field'    => 0,
			'create_field'   => 0,
			'option_name'    => 'usp_register_form_fields',
			'structure_edit' => 1,
			/**
			 * Filter allow add|change default fields.
			 *
			 * @param   $fields    array  Data default fields.
			 *
			 * @see     RegisterFormManager
			 *
			 * @since   1.0.0
			 */
			'default_fields' => apply_filters( 'usp_register_form_default_fields', $this->get_default_fields() ),
			/**
			 * Filter allow added|change fields options.
			 *
			 * @param   $options    array  Fields options.
			 *
			 * @see     RegisterFormManager
			 *
			 * @since   1.0.0
			 */
			'field_options'  => apply_filters( 'usp_register_form_field_options', [
				//                array(
				//                    'type'        => 'text',
				//                    'slug'        => 'icon',
				//                    'class'       => 'usp-iconpicker',
				//                    'title'       => __( 'Icon class', 'userspace' ),
				//                    'placeholder' => __( 'Example: fa-user', 'userspace' )
				//                ),
				[
					'type'   => 'radio',
					'slug'   => 'required',
					'title'  => __( 'Required field', 'userspace' ),
					'values' => [
						__( 'No', 'userspace' ),
						__( 'Yes', 'userspace' )
					]
				],
				[
					'type'  => 'textarea',
					'slug'  => 'notice',
					'title' => __( 'Field description', 'userspace' ),
				]
			] )
		] );

		$this->setup_default_fields();
	}

	public function get_default_fields(): array {

		$fields = get_site_option( 'usp_profile_fields' );

		if ( $fields ) {
			foreach ( $fields as $k => $field ) {
				if ( in_array( $field['slug'], [ 'primary_pass', 'repeat_pass', 'user_email' ] ) ) {
					unset( $fields[ $k ] );
				}
			}
		}

		if ( ! $fields ) {
			$fields = [];
		}

		$fields[] = [
			'type'        => 'text',
			'slug'        => 'user_login',
			'title'       => __( 'Login', 'userspace' ),
			'placeholder' => __( 'Login', 'userspace' ),
			'icon'        => 'fa-user',
			'maxlenght'   => 50,
			'required'    => 1
		];

		$fields[] = [
			'type'     => 'password',
			'slug'     => 'user_pass',
			'icon'     => 'fa-lock',
			'title'    => __( 'Password', 'userspace' ),
			'required' => 1
		];

		$fields[] = [
			'type'     => 'password',
			'slug'     => 'user_pass_repeat',
			'icon'     => 'fa-lock',
			'title'    => __( 'Repeat Password', 'userspace' ),
			'required' => 1
		];

		return $fields;
	}

}
