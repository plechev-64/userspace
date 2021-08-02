<?php

class USP_Profile_Fields extends USP_Fields {

	/**
	 * @var string[] fields for update with wp_update_user
	 */
	private $_primary_fields = [
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
	private $_hide_in_admin = [
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

	private $user_id;

	/**
	 * USP_Profile_Fields constructor.
	 *
	 * @param int $user_id
	 */
	public function __construct( $user_id = 0 ) {

		$this->user_id = $user_id;

		$fields    = get_site_option( 'usp_profile_fields' );
		$fields    = apply_filters( 'usp_profile_fields', $fields, $this->user_id );
		$structure = get_site_option( 'usp_fields_profile_structure' );

		parent::__construct( $fields, $structure );
	}

	public function get_primary_fields_slugs() {
		return $this->_primary_fields;
	}

	public function get_hide_admin_fields_slugs() {
		return $this->_hide_in_admin;
	}

	public function get_public_fields() {
		return $this->search_by( 'public_value', 1 );
	}

	public function get_fields_for_admin_page() {

		$fields        = $this->get_fields();
		$hide_in_admin = $this->get_hide_admin_fields_slugs();

		foreach ( $fields as $field_slug => $field ) {
			if ( in_array( $field_slug, $hide_in_admin ) ) {
				unset( $fields[ $field_slug ] );
			}
		}

		return apply_filters( 'usp_admin_profile_fields', $fields, $this->user_id );
	}

	public function get_public_fields_slugs() {

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
	public function get_default_fields() {
		return apply_filters( 'usp_default_profile_fields', array(
				array(
					'slug'  => 'first_name',
					'title' => __( 'Firstname', 'userspace' ),
					'icon'  => 'fa-user',
					'type'  => 'text'
				),
				array(
					'slug'  => 'last_name',
					'title' => __( 'Surname', 'userspace' ),
					'icon'  => 'fa-user',
					'type'  => 'text'
				),
				array(
					'slug'  => 'display_name',
					'title' => __( 'Name to be displayed', 'userspace' ),
					'icon'  => 'fa-user',
					'type'  => 'text'
				),
				array(
					'slug'  => 'user_url',
					'title' => __( 'Website', 'userspace' ),
					'icon'  => 'fa-link',
					'type'  => 'url'
				),
				array(
					'slug'  => 'description',
					'title' => __( 'Status', 'userspace' ),
					'icon'  => 'fa-comment',
					'type'  => 'textarea'
				),
				array(
					'slug'  => 'usp_birthday',
					'title' => __( 'Birthday', 'userspace' ),
					'icon'  => 'fa-birthday-cake',
					'type'  => 'date'
				),
				array(
					'slug'        => 'usp_sex',
					'title'       => __( 'Sex', 'userspace' ),
					'icon'        => 'fa-user',
					'type'        => 'radio',
					'values'      => [ __( 'Man', 'userspace' ), __( 'Woman', 'userspace' ) ],
					'empty_first' => __( 'Not selected', 'userspace' )
				),
				array(
					'slug'     => 'user_email',
					'title'    => __( 'E-mail', 'userspace' ),
					'type'     => 'email',
					'required' => 1
				),
				array(
					'slug'     => 'primary_pass',
					'title'    => __( 'New password', 'userspace' ),
					'type'     => 'password',
					'required' => 0,
					'notice'   => __( 'If you want to change your password - enter a new one', 'userspace' )
				),
				array(
					'slug'     => 'repeat_pass',
					'title'    => __( 'Repeat password', 'userspace' ),
					'type'     => 'password',
					'required' => 0,
					'notice'   => __( 'Repeat the new password', 'userspace' )
				)
			)
		);
	}

	/**
	 * @return array Base options for every profile field
	 */
	public function get_fields_options() {
		return apply_filters( 'usp_profile_field_options', array(
			array(
				'slug'  => 'notice',
				'type'  => 'textarea',
				'title' => __( 'Field description', 'userspace' )
			),
			array(
				'slug'   => 'required',
				'type'   => 'radio',
				'title'  => __( 'Required field', 'userspace' ),
				'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
			),
			array(
				'slug'   => 'public_value',
				'type'   => 'radio',
				'title'  => __( 'Show the content to other users', 'userspace' ),
				'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
			),
			array(
				'slug'   => 'admin',
				'type'   => 'radio',
				'title'  => __( 'Can be changed only by the site administration', 'userspace' ),
				'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
			),
			array(
				'slug'   => 'filter',
				'type'   => 'radio',
				'title'  => __( 'Filter users by this field', 'userspace' ),
				'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
			)
		) );
	}


}