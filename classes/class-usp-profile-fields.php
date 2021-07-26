<?php

USP()->use_module( 'fields' );

class USP_Profile_Fields extends USP_Fields {

	private $user = null;
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
	 * @param USP_User $user
	 */
	public function __construct( $user = null ) {

		$this->user = $user;

		$fields = get_site_option( 'usp_profile_fields' );
		$fields = apply_filters( 'usp_profile_fields', $fields, $this->user );

		parent::__construct( $fields );
	}

	public function get_public_fields_values() {

		if ( ! $this->user ) {
			return '';
		}

		$public_fields = $this->search_by( 'public_value', 1 );

		if ( ! $public_fields ) {
			return '';
		}

		$html = '';

		foreach ( $public_fields as $field ) {

			$field->value = $this->user->{$field->slug};

			$html .= $field->get_field_value( true );
		}

		if ( $html ) {
			$html = '<div class="usp-user-fields usps usps__column">' . $html . '</div>';
		}

		return $html;

	}

	function get_public_fields_slugs() {

		$public_fields = $this->search_by( 'public_value', 1 );

		if ( ! $public_fields ) {
			return ['description'];
		}

		$slugs = array_column( $public_fields, 'slug' );

		return array_merge($slugs, ['description']);
	}

	function update_fields( $fields_to_update = [] ) {

		if ( ! $this->user ) {
			return;
		}

		require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/media.php' );

		if ( ! $fields_to_update ) {
			$fields_to_update = $this->fields;
		}

		if ( ! $fields_to_update ) {
			return;
		}

		foreach ( $fields_to_update as $field ) {

			if ( ! $field instanceof USP_Field_Abstract ) {
				continue;
			}

			/**
			 * @var USP_Field_Abstract $field
			 */

			$field = apply_filters( 'usp_pre_update_profile_field', $field, $this->user );

			if ( ! $field || ! $field->slug ) {
				continue;
			}

			$slug          = $field->slug;
			$cur_value     = $this->user->$slug;
			$new_value     = ( isset( $_POST[ $slug ] ) ) ? $_POST[ $slug ] : false;
			$edit_by_admin = $field->get_prop( 'admin' );

			if ( $edit_by_admin && ! USP()->user()->has_role( 'administrator' ) ) {

				/*
				 * if value exist and current user not admin - skip update
				 */
				if ( $cur_value ) {
					continue;
				}
			}

			if ( $field->type == 'file' ) {

				if ( $cur_value && $new_value != $cur_value ) {
					wp_delete_attachment( $cur_value );
					delete_user_meta( $this->user->ID, $slug );
				}
			}

			if ( $field->type != 'editor' ) {

				if ( is_array( $new_value ) ) {
					$new_value = array_map( 'esc_html', $new_value );
				} else {
					$new_value = esc_html( $new_value );
				}
			}

			if ( in_array( $slug, $this->_default_fields ) ) {

				if ( $slug == 'repeat_pass' ) {
					continue;
				}

				if ( $slug == 'primary_pass' && $new_value ) {

					if ( $new_value != $_POST['repeat_pass'] ) {
						continue;
					}

					$slug = 'user_pass';
				}

				if ( $slug == 'user_email' ) {

					if ( ! $new_value ) {
						continue;
					}

					if ( $cur_value == $new_value ) {
						continue;
					}
				}

				wp_update_user( array( 'ID' => $this->user->ID, $slug => $new_value ) );

				continue;
			}

			if ( $field->type == 'checkbox' ) {

				$vals = array();

				if ( is_array( $new_value ) ) {

					$vals = array_intersect( $new_value, $field->values );

				}

				if ( $vals ) {
					update_user_meta( $this->user->ID, $slug, $vals );
				} else {
					delete_user_meta( $this->user->ID, $slug );
				}
			} else {

				if ( $new_value ) {

					update_user_meta( $this->user->ID, $slug, $new_value );
				} else {

					if ( $cur_value ) {
						delete_user_meta( $this->user->ID, $slug, $cur_value );
					}
				}
			}

			if ( $new_value ) {

				if ( $field->type == 'uploader' ) {
					foreach ( $new_value as $val ) {
						usp_delete_temp_media( $val );
					}
				} else if ( $field->type == 'file' ) {
					usp_delete_temp_media( $new_value );
				}
			}
		}

		do_action( 'usp_update_profile_fields', $this->user->ID );
	}

}