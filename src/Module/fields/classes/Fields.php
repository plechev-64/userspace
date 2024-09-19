<?php

class Fields extends Field {

	public array $fields = [];
	public array $structure = [];

	public function __construct( array $fields = [], array $structure = [] ) {

		if ( $structure ) {
			$this->structure = $structure;
		}

		if ( $fields ) {

			$this->fields = [];

			foreach ( $fields as $field ) {

				if ( $field instanceof FieldAbstract ) {
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

	public function setup_structure( bool $force = false ) {

		if ( ! $this->structure || ( ! $this->fields ) || $force ) {

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

	public function get_fields(): array {
		return $this->fields;
	}

	public function add_field( string $field_id, array $args ): void {
		$this->fields[ $field_id ] = parent::setup( $args );
	}

	public function remove_field( string $field_id ): void {
		unset( $this->fields[ $field_id ] );
	}

	public function isset_field( string $field_id ): bool {
		return isset( $this->fields[ $field_id ] );
	}

	public function get_field( string $field_id ): ?FieldAbstract {
		return $this->isset_field( $field_id ) ? $this->fields[ $field_id ] : null;
	}

	public function set_field_prop( string $field_id, string $propName, mixed $propValue ): void {

		$field = $this->get_field( $field_id );

		$field->$propName = $propValue;

		$this->fields[ $field_id ] = $field;
	}

	public function isset_field_prop( string $field_id, string $propName ): bool {

		$field = $this->get_field( $field_id );

		if ( ! $field ) {
			return false;
		}

		return isset( $field->$propName );
	}

	public function get_field_prop( string $field_id, string $propName ): bool {

		if ( ! $this->isset_field_prop( $field_id, $propName ) ) {
			return false;
		}

		$field = $this->get_field( $field_id );

		return $field->$propName;
	}

	public function exclude( $fieldIds ): ?Fields {

		if ( ! $this->fields ) {
			return null;
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

	public function search( array $filters ): ?array {

		$fields = [];

		foreach ( $filters as $key => $value ) {
			$fields = $this->search_by( $key, $value, $fields );
			if ( ! $fields ) {
				return null;
			}
		}

		return $fields;
	}

	public function search_by( string $key, int|string $value, array $fields = [] ): array {

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

	public function add_structure_field( string $group_id, string $area_id, array $fields ): void {

		foreach ( $fields as $args ) {
			$this->fields[ $args['slug'] ]                                 = $this::setup( $args );
			$this->structure[ $group_id ]['areas'][ $area_id ]['fields'][] = $args['slug'];
		}
	}

	public function add_structure_group( string $group_id, array $args = [] ) {

		$this->structure[ $group_id ] = wp_parse_args( $args, [
			'title' => ''
		] );
	}

	public function get_content(): ?string {

		$content = '';

		foreach ( $this->structure as $group_id => $group ) {
			$content .= $this->get_group( $group );
		}

		if ( ! $content ) {
			return null;
		}

		return '<div class="usp-content usp-preloader-parent">' . $content . '</div>';

	}

	public function get_loop(): string {

		$content = '';

		foreach ( $this->structure as $group ) {
			$content .= $this->get_group( $group );
		}

		return $content;
	}

	public function get_group( array $group ): ?string {

		if ( ! isset( $group['areas'] ) || ! $group['areas'] ) {
			return null;
		}

		$groupContent = '';

		foreach ( $group['areas'] as $area ) {
			$groupContent .= $this->get_area( $area );
		}

		if ( ! $groupContent ) {
			return null;
		}

		$content = '<div id="usp-group-' . esc_attr( $group['id'] ) . '" class="usp-content-group">';

		if ( $group['title'] ) {
			$content .= '<div class="usp-group-title">' . esc_html( $group['title'] ) . '</div>';
		}

		$content .= '<div class="group-areas usps">';

		$content .= $groupContent;

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public function get_area( array $area ): ?string {

		$areaContent = '';

		if ( ! isset( $area['fields'] ) || ! $area['fields'] ) {
			return null;
		}

		foreach ( $area['fields'] as $field_id ) {
			$areaContent .= $this->get_field_content( $field_id );
		}

		if ( ! $areaContent ) {
			return null;
		}

		$content = '<div class="usp-content-area" style="min-width:' . ( isset( $area['width'] ) ? esc_attr( $area['width'] ) : 100 ) . '%;">';
		$content .= $areaContent;
		$content .= '</div>';

		return $content;
	}

	public function get_field_content( string $field_id ): ?string {

		$field = $this->get_field( $field_id );

		if ( ! $field->value ) {
			return null;
		}

		return $field->get_field_html( $field->value );
	}

	public function get_form( array $args = [] ): string {

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

		$content .= '<form ' . ( $args['form_id'] ? 'id="' . esc_attr( $args['form_id'] ) . '"' : '' ) . ' method="' . esc_attr( $args['method'] ) . '" action="' . esc_attr( $args['action'] ) . '">';

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

	public function get_content_form( array $args = [] ): ?string {

		$content = '';

		foreach ( $this->structure as $group ) {
			$content .= $this->get_group_form( $group, $args );
		}

		if ( ! $content ) {
			return null;
		}

		return '<div class="usp-content usp-preloader-parent">' . $content . '</div>';

	}

	public function get_group_form( array $group, array $args = [] ): ?string {

		if ( ! isset( $group['areas'] ) || ! $group['areas'] ) {
			return null;
		}

		$groupContent = '';

		foreach ( $group['areas'] as $area ) {
			$groupContent .= $this->get_area_form( $area, $args );
		}

		if ( ! $groupContent ) {
			return null;
		}

		if ( ! isset( $group['id'] ) ) {
			$group['id'] = 'no-name';
		}

		$content = '<div id="usp-group-' . esc_attr( $group['id'] ) . '" class="usp-content-group">';

		if ( isset( $group['title'] ) && $group['title'] ) {
			$content .= '<div class="usp-group-title">' . esc_html( $group['title'] ) . '</div>';
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

	public function get_area_form( array $area, array $args = [] ): ?string {

		$areaContent = '';

		if ( ! isset( $area['fields'] ) || ! $area['fields'] ) {
			return null;
		}

		foreach ( $area['fields'] as $field_id ) {
			$areaContent .= $this->get_field_form( $field_id, $args );
		}

		if ( ! $areaContent ) {
			return null;
		}

		$content = '<div class="usp-content-area" style="min-width:' . ( isset( $area['width'] ) ? esc_attr( $area['width'] ) : 100 ) . '%;">';
		$content .= $areaContent;
		$content .= '</div>';

		return $content;
	}

	public function get_field_form( string $field_id, array $args = [] ): ?string {

		$field = $this->get_field( $field_id );

		if ( ! $field ) {
			return null;
		}

		if ( isset( $args['unique_ids'] ) ) {
			$field->set_prop( 'unique_id', true );
		}

		return $field->get_field_html();

	}

}
