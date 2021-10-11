<?php

class USP_Fields extends USP_Field {

	public $fields;
	public $structure = [];

	function __construct( $fields = false, $structure = false ) {

		if ( $structure ) {
			$this->structure = $structure;
		}

		if ( $fields ) {

			$this->fields = [];

			foreach ( $fields as $field ) {

				if ( $field instanceof USP_Field_Abstract ) {
					$this->fields[ $field->slug ] = $field;
					continue;
				}

				if ( ! is_array( $field ) || ! isset( $field['slug'] ) ) {
					continue;
				}

				$this->fields[ $field['slug'] ] = parent::setup( $field );
			}
		}

		$this->setup_structure();
	}

	function setup_structure( $force = false ) {

		if ( ! $this->structure || ( $this->structure && ! $this->fields ) || $force ) {

			$fieldIds = [];

			if ( $this->fields ) {
				foreach ( $this->fields as $field_id => $field ) {
					$fieldIds[] = $field_id;
				}
			}

			$this->structure = [
				[
					'areas' => [
						[
							'fields' => $fieldIds
						]
					]
				]
			];
		} else if ( $this->fields ) { // add orphaned fields to the structure
			$structureFields = [];

			foreach ( $this->structure as $group_id => $group ) {
				if ( ! isset( $group['areas'] ) ) {
					continue;
				}
				foreach ( $group['areas'] as $area ) {
					$structureFields = array_merge( $structureFields, $area['fields'] );
				}
			}

			$structure    = [];
			$headerFields = [];
			$footerFields = [];
			$top          = true;
			foreach ( $this->fields as $field_id => $field ) {
				if ( ! in_array( $field_id, $structureFields ) ) {
					if ( $top ) {
						$headerFields[] = $field_id;
					} else {
						$footerFields[] = $field_id;
					}
				} else if ( $top ) {
					$top = false;
				}
			}

			if ( $headerFields ) {
				$structure['header-group'] = [
					'id'    => 'header',
					'areas' => [
						[
							'fields' => $headerFields
						]
					]
				];
			}

			$structure += $this->structure;

			if ( $footerFields ) {
				$structure['footer-group'] = [
					'id'    => 'footer',
					'areas' => [
						[
							'fields' => $footerFields
						]
					]
				];
			}

			$this->structure = $structure;
		}
	}

	function get_fields() {
		return $this->fields;
	}

	function add_field( $field_id, $args ) {
		$this->fields[ $field_id ] = parent::setup( $args );
	}

	function remove_field( $field_id ) {
		unset( $this->fields[ $field_id ] );
	}

	function isset_field( $field_id ) {
		return isset( $this->fields[ $field_id ] );
	}

	function get_field( $field_id ) {
		return $this->isset_field( $field_id ) ? $this->fields[ $field_id ] : false;
	}

	function set_field_prop( $field_id, $propName, $propValue ) {

		$field = $this->get_field( $field_id );

		$field->$propName = $propValue;

		$this->fields[ $field_id ] = $field;
	}

	function isset_field_prop( $field_id, $propName ) {

		$field = $this->get_field( $field_id );

		if ( ! $field ) {
			return false;
		}

		return isset( $field->$propName );
	}

	function get_field_prop( $field_id, $propName ) {

		if ( ! $this->isset_field_prop( $field_id, $propName ) ) {
			return false;
		}

		$field = $this->get_field( $field_id );

		return $field->$propName;
	}

	function exclude( $fieldIds ) {

		if ( ! $this->fields ) {
			return false;
		}

		$fields = [];
		foreach ( $this->fields as $field_id => $field ) {
			if ( in_array( $field_id, $fieldIds ) ) {
				continue;
			}
			$fields[ $field_id ] = $field;
		}

		$this->fields = $fields;

		return $this;
	}

	function search( $filters ) {

		$fields = [];

		foreach ( $filters as $key => $value ) {
			$fields = $this->search_by( $key, $value, $fields );
			if ( ! $fields ) {
				return false;
			}
		}

		return $fields;
	}

	function search_by( $key, $value, $fields = false ) {

		if ( ! $fields ) {
			$fields = $this->fields;
		}

		$search = [];

		foreach ( $fields as $field_id => $field ) {

			if ( ! $field->isset_prop( $key ) ) {
				continue;
			}

			if ( is_array( $value ) ) {

				if ( ! in_array( $field->get_prop( $key ), $value ) ) {
					continue;
				}
			} else {

				if ( $field->get_prop( $key ) != $value ) {
					continue;
				}
			}

			$search[ $field_id ] = $field;
		}

		return $search;
	}

	function add_structure_field( $group_id, $area_id, $fields ) {

		foreach ( $fields as $args ) {
			$this->fields[ $args['slug'] ]                                 = $this::setup( $args );
			$this->structure[ $group_id ]['areas'][ $area_id ]['fields'][] = $args['slug'];
		}
	}

	function add_structure_group( $group_id, $args = false ) {

		$this->structure[ $group_id ] = wp_parse_args( $args, [
			'title' => ''
		] );
	}

	function get_content() {

		$content = '';

		foreach ( $this->structure as $group_id => $group ) {
			$content .= $this->get_group( $group );
		}

		if ( ! $content ) {
			return false;
		}

		$content = '<div class="usp-content usp-preloader-parent">' . $content . '</div>';

		return $content;
	}

	function get_loop() {

		$content = '';

		foreach ( $this->structure as $group_id => $group ) {
			$content .= $this->get_group( $group );
		}

		return $content;
	}

	function get_group( $group ) {

		if ( ! isset( $group['areas'] ) || ! $group['areas'] ) {
			return false;
		}

		$groupContent = '';

		foreach ( $group['areas'] as $area ) {
			$groupContent .= $this->get_area( $area );
		}

		if ( ! $groupContent ) {
			return false;
		}

		$content = '<div id="usp-group-' . $group['id'] . '" class="usp-content-group">';

		if ( $group['title'] ) {
			$content .= '<div class="usp-group-title">' . $group['title'] . '</div>';
		}

		$content .= '<div class="group-areas usps">';

		$content .= $groupContent;

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	function get_area( $area ) {

		$areaContent = '';

		if ( ! isset( $area['fields'] ) || ! $area['fields'] ) {
			return false;
		}

		foreach ( $area['fields'] as $field_id ) {
			$areaContent .= $this->get_field_content( $field_id );
		}

		if ( ! $areaContent ) {
			return false;
		}

		$content = '<div class="usp-content-area" style="min-width:' . ( isset( $area['width'] ) ? $area['width'] : 100 ) . '%;">';
		$content .= $areaContent;
		$content .= '</div>';

		return $content;
	}

	function get_field_content( $field_id ) {

		$field = $this->get_field( $field_id );

		if ( ! $field->value ) {
			return false;
		}

		return $field->get_field_html( $field->value );
	}

	function get_form( $args = [] ) {

		$args = wp_parse_args( $args, [
			'form_id'    => '',
			'unique_ids' => false,
			'action'     => '',
			'method'     => 'post',
			'submit'     => __( 'Save', 'userspace' ),
			'nonce_name' => '_wpnonce',
			'nonce_key'  => '',
			'onclick'    => '',
		] );

		$content = '<div class="usp-form usp-preloader-parent">';

		$content .= '<form ' . ( $args['form_id'] ? 'id="' . $args['form_id'] . '"' : '' ) . ' method="' . $args['method'] . '" action="' . $args['action'] . '">';

		$content .= $this->get_content_form( $args );

		$content .= '<div class="submit-box">';

		$bttnArgs = [
			'label' => $args['submit'],
			'icon'  => 'fa-check-circle'
		];

		if ( $args['onclick'] ) {
			$bttnArgs['onclick'] = $args['onclick'];
		} else {
			$bttnArgs['submit'] = 1;
		}

		$content .= usp_get_button( $bttnArgs );

		$content .= '</div>';

		if ( $args['nonce_key'] ) {
			$content .= wp_nonce_field( $args['nonce_key'], $args['nonce_name'], true, false );
		}

		$content .= '</form>';

		$content .= '</div>';

		return $content;
	}

	function get_content_form( $args = false ) {

		$content = '';

		foreach ( $this->structure as $group_id => $group ) {
			$content .= $this->get_group_form( $group, $args );
		}

		if ( ! $content ) {
			return false;
		}

		$content = '<div class="usp-content usp-preloader-parent">' . $content . '</div>';

		return $content;
	}

	function get_group_form( $group, $args = false ) {

		if ( ! isset( $group['areas'] ) || ! $group['areas'] ) {
			return false;
		}

		$groupContent = '';

		foreach ( $group['areas'] as $area ) {
			$groupContent .= $this->get_area_form( $area, $args );
		}

		if ( ! $groupContent ) {
			return false;
		}

		if ( ! isset( $group['id'] ) ) {
			$group['id'] = 'no-name';
		}

		$content = '<div id="usp-group-' . $group['id'] . '" class="usp-content-group">';

		if ( isset( $group['title'] ) && $group['title'] ) {
			$content .= '<div class="usp-group-title">' . $group['title'] . '</div>';
		}

		if ( isset( $group['notice'] ) && $group['notice'] ) {
			$content .= '<div class="usp-field-notice usps usps__grow">' . nl2br( $group['notice'] ) . '</div>';
		}

		$content .= '<div class="group-areas usps">';

		$content .= $groupContent;

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	function get_area_form( $area, $args = false ) {

		$areaContent = '';

		if ( ! isset( $area['fields'] ) || ! $area['fields'] ) {
			return false;
		}

		foreach ( $area['fields'] as $field_id ) {
			$areaContent .= $this->get_field_form( $field_id, $args );
		}

		if ( ! $areaContent ) {
			return false;
		}

		$content = '<div class="usp-content-area" style="min-width:' . ( isset( $area['width'] ) ? $area['width'] : 100 ) . '%;">';
		$content .= $areaContent;
		$content .= '</div>';

		return $content;
	}

	function get_field_form( $field_id, $args = false ) {

		$field = $this->get_field( $field_id );

		if ( ! $field ) {
			return false;
		}

		if ( isset( $args['unique_ids'] ) ) {
			$field->set_prop( 'unique_id', true );
		}

		$content = $field->get_field_html();

		return $content;
	}

}
