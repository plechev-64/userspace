<?php

class TableColsManager {

	public string $manager_id = '';
	public array $cols = [];
	public array $active_cols = [];
	public array $disabled_cols = [];

	public function __construct( string $manager_id, array $args ) {
		$args['manager_id'] = $manager_id;
		$this->init_properties( $args );

		$this->active_cols = array_unique( array_merge( $this->disabled_cols, $this->active_cols ) );
	}

	private function init_properties( array $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	public function get_manager(): string {

		$content = '<div id="usp-cols-manager">';

		$content .= '<div class="active-cols cols-box">';

		$content .= '<div class="list-title">' . __( 'Active columns', 'userspace' ) . '</div>';

		$content .= '<input type="hidden" name="manager_id" value="' . $this->manager_id . '">';

		$content .= '<div class="cols-list">';
		if ( $this->active_cols ) {
			foreach ( $this->active_cols as $colId ) {
				if ( ! isset( $this->cols[ $colId ] ) ) {
					continue;
				}
				$content .= $this->get_col_html( $colId, $this->cols[ $colId ] );
			}
		}
		$content .= '</div>';

		$content .= '</div>';

		$content .= '<div class="unactive-cols cols-box">';

		$content .= '<div class="list-title">' . __( 'Inactive columns', 'userspace' ) . '</div>';

		$content .= '<div class="cols-list">';
		foreach ( $this->cols as $colId => $name ) {
			if ( in_array( $colId, $this->active_cols ) ) {
				continue;
			}
			$content .= $this->get_col_html( $colId, $name );
		}
		$content .= '</div>';

		$content .= '</div>';

		$content .= '<script>
			jQuery(function(){
				jQuery("#usp-cols-manager .cols-list").sortable({
					items: ".single-col:not(.disabled)",
					connectWith: ".cols-list",
					cursor: "move",
					placeholder: "ui-sortable-placeholder",
					distance: 5
				});
				return false;
			});
		</script>';

		$content .= '</div>';

		return usp_get_form( array(
			'submit'  => __( 'Save', 'userspace' ),
			'onclick' => 'usp_save_table_manager_cols(this);return false;',
			'fields'  => array(
				array(
					'type'    => 'custom',
					'slug'    => 'manager',
					'content' => $content
				)
			)
		) );
	}

	public function get_col_html( int|string $colId, string $name ): string {
		$content = '<div class="single-col ' . ( in_array( $colId, $this->disabled_cols ) ? 'disabled' : 'enabled' ) . ' ' . ( $name == '-' ? 'hidden' : '' ) . '">';
		$content .= '<span class="col-name">' . $name . '</span>';
		$content .= '<input type="hidden" name="col_ids[]" value="' . $colId . '">';
		$content .= '</div>';

		return $content;
	}

}
