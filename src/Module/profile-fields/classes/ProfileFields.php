<?php

class ProfileFields extends Fields {

	/**
	 * @var string[] fields for update with wp_update_user
	 */
	private array $_primary_fields = [
		'user_email',
		'description',
		'user_url',
		'first_name',
		'last_name',
		'display_name',
		'primary_pass',
		'repeat_pass'
	];

	/**
	 * @var string[] fields for hide in wp-admin profile page
	 */
	private array $_hide_in_admin = [
		'user_email',
		'description',
		'user_url',
		'first_name',
		'last_name',
		'display_name',
		'primary_pass',
		'repeat_pass',
		'show_admin_bar_front'
	];

	private int $user_id;

	public function __construct( int $user_id = 0 ) {

		$this->user_id = $user_id;

		$fields = get_site_option( 'usp_profile_fields', [] );

		foreach ( $fields as $k => $field ) {
			if ( ! isset( $field['value_in_key'] ) ) {
				$fields[ $k ]['value_in_key'] = true;
			}
		}

		$fields    = apply_filters( 'usp_profile_fields', $fields, $this->user_id );
		$structure = get_site_option( 'usp_fields_profile_structure' );

		parent::__construct( $fields, $structure );
	}

	public function get_primary_fields_slugs(): array {
		return $this->_primary_fields;
	}

	public function get_hide_admin_fields_slugs(): array {
		return $this->_hide_in_admin;
	}

	public function get_public_fields(): array {
		return $this->search_by( 'public_value', 1 );
	}

	public function get_fields_for_admin_page(): array {

		$fields        = $this->get_fields();
		$hide_in_admin = $this->get_hide_admin_fields_slugs();

		foreach ( $fields as $field_slug => $field ) {
			if ( in_array( $field_slug, $hide_in_admin ) ) {
				unset( $fields[ $field_slug ] );
			}
		}

		return apply_filters( 'usp_admin_profile_fields', $fields, $this->user_id );
	}

	public function get_public_fields_slugs(): array {

		$public_fields = $this->get_public_fields();

		if ( ! $public_fields ) {
			return [ 'description' ];
		}

		$slugs = array_column( $public_fields, 'slug' );

		return array_merge( $slugs, [ 'description' ] );
	}

	/**
	 * @return array default profile fields
	 */
	public function get_default_fields(): array {
		return apply_filters( 'usp_default_profile_fields', [
				[
					'slug'  => 'first_name',
					'title' => __( 'Firstname', 'userspace' ),
					'icon'  => 'fa-user',
					'type'  => 'text'
				],
				[
					'slug'  => 'last_name',
					'title' => __( 'Surname', 'userspace' ),
					'icon'  => 'fa-user',
					'type'  => 'text'
				],
				[
					'slug'  => 'display_name',
					'title' => __( 'Name to be displayed', 'userspace' ),
					'icon'  => 'fa-user',
					'type'  => 'text'
				],
				[
					'slug'  => 'user_url',
					'title' => __( 'Website', 'userspace' ),
					'icon'  => 'fa-link',
					'type'  => 'url'
				],
				[
					'slug'  => 'description',
					'title' => __( 'Status', 'userspace' ),
					'icon'  => 'fa-comment',
					'type'  => 'textarea'
				],
				[
					'slug'  => 'usp_birthday',
					'title' => __( 'Birthday', 'userspace' ),
					'icon'  => 'fa-birthday-cake',
					'type'  => 'date'
				],
				[
					'slug'        => 'usp_sex',
					'title'       => __( 'Sex', 'userspace' ),
					'icon'        => 'fa-user',
					'type'        => 'radio',
					'values'      => [ __( 'Man', 'userspace' ), __( 'Woman', 'userspace' ) ],
					'empty_first' => __( 'Not selected', 'userspace' )
				],
				[
					'slug'     => 'user_email',
					'title'    => __( 'E-mail', 'userspace' ),
					'type'     => 'email',
					'required' => 1
				],
				[
					'slug'     => 'primary_pass',
					'title'    => __( 'New password', 'userspace' ),
					'type'     => 'password',
					'required' => 0,
					'notice'   => __( 'If you want to change your password - enter a new one', 'userspace' )
				],
				[
					'slug'     => 'repeat_pass',
					'title'    => __( 'Repeat password', 'userspace' ),
					'type'     => 'password',
					'required' => 0,
					'notice'   => __( 'Repeat the new password', 'userspace' )

				]
			]
		);
	}

	/**
	 * @return array Base options for every profile field
	 */
	public function get_fields_options(): array {
		return apply_filters( 'usp_profile_field_options', [
			[
				'slug'  => 'notice',
				'type'  => 'textarea',
				'title' => __( 'Field description', 'userspace' )
			],
			[
				'slug'   => 'required',
				'type'   => 'radio',
				'title'  => __( 'Required field', 'userspace' ),
				'values' => [ __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) ]
			],
			[
				'slug'   => 'public_value',
				'type'   => 'radio',
				'title'  => __( 'Show the content to other users', 'userspace' ),
				'values' => [ __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) ]
			],
			[
				'slug'   => 'admin',
				'type'   => 'radio',
				'title'  => __( 'Can be changed only by the site administration', 'userspace' ),
				'values' => [ __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) ]
			],
			[
				'slug'   => 'filter',
				'type'   => 'radio',
				'title'  => __( 'Filter users by this field', 'userspace' ),
				'values' => [ __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) ]
			]
		] );
	}


}