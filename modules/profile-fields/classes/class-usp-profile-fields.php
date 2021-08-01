<?php

class USP_Profile_Fields extends USP_Fields {

	private $_default_fields = [
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
	 * USP_Profile_Fields constructor.
	 *
	 * @param int $user_id
	 */
	public function __construct( $user_id = 0 ) {

		$fields = get_site_option( 'usp_profile_fields' );
		$fields = apply_filters( 'usp_profile_fields', $fields, $user_id );

		parent::__construct( $fields );
	}

	public function get_default_fields() {
		return $this->_default_fields;
	}

	public function get_public_fields() {
		return $this->search_by( 'public_value', 1 );
	}

	function get_public_fields_slugs() {

		$public_fields = $this->get_public_fields();

		if ( ! $public_fields ) {
			return [ 'description' ];
		}

		$slugs = array_column( $public_fields, 'slug' );

		return array_merge( $slugs, [ 'description' ] );
	}


}