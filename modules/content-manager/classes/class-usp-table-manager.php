<?php

class USP_Table_Manager extends USP_Content_Manager {

	public $cols_manager = '';
	public $default_cols = [];
	public $active_cols = [];
	public $disabled_cols = [];
	public $table = false;

	function __construct( $args ) {

		parent::__construct( array_merge( $args, [ 'pagenavi' => 1 ] ) );

		if ( $this->cols_manager ) {

			if ( isset( $_COOKIE[ $this->cols_manager ] ) && $_COOKIE[ $this->cols_manager ] ) {
				$this->active_cols = json_decode( wp_unslash( $_COOKIE[ $this->cols_manager ] ) );
			}

			if ( ! $this->active_cols ) {
				$this->active_cols = $this->default_cols ?: array_keys( $this->get_table_cols() );
			}
		}
	}

	function get_sort_col( $dataKey ) {
		return [
			'onclick' => 'usp_order_table_manager_page(this);return false;',
			'order'   => ( $this->get_param( 'orderby' ) == $dataKey ) ? $this->get_param( 'order' ) : null
		];
	}

	function get_search_col( $dataKey ) {
		return [
			'submit' => 'usp_table_manager_search_by_col'
		];
	}

	function get_table_cols() {
		return [];
	}

	function get_table_row( $item ) {

		$rowData = [];
		foreach ( $this->get_table_cols() as $colData ) {

			if ( ! isset( $colData['get_result'] ) ) {
				continue;
			}

			$args = [];
			if ( isset( $colData['result_args'] ) ) {
				$args = $colData['result_args'];
			}

			$rowData[] = $colData['get_result']( $item, $args );
		}

		return $rowData;
	}

	function get_buttons_args() {

		if ( ! $this->cols_manager ) {
			return [];
		}

		usp_dialog_scripts();
		usp_sortable_scripts();

		$cols    = $this->get_table_cols();
		$allCols = [];
		foreach ( $cols as $colId => $col ) {
			$allCols[ $colId ] = $col['title'];
		}

		return [
			[
				'label'   => __( 'Column manager', 'userspace' ),
				'icon'    => 'fa-bars',
				'onclick' => 'return usp_get_table_manager_cols("' . $this->cols_manager . '",' . json_encode( $allCols ) . ',' . json_encode( $this->active_cols ) . ',' . json_encode( $this->disabled_cols ) . ',this);return false;',
			]
		];
	}

	function get_items_content() {

		$content = '<div class="usp-content-manager-content">';
		$items   = $this->get_items();

		if ( ! $items ) {
			$content .= $this->get_no_result_notice();
		} else {

			$table = new USP_Table( [
				'cols'   => $this->filter_table_cols( $this->get_table_cols() ),
				'border' => [ 'rows', 'cols', 'table' ],
				'zebra'  => true
			] );

			foreach ( $items as $item ) {

				$rowData = $this->get_table_row( $item );

				if ( $this->cols_manager ) {

					$newRowData = [];
					foreach ( $this->active_cols as $colID ) {
						$newRowData[ $colID ] = $rowData[ $colID ];
					}

					$rowData = $newRowData;
				}

				$table->add_row( $rowData );
			}

			$content .= $table->get_table();
		}
		$content .= '</div>';

		return $content;
	}

	function filter_table_cols( $cols ) {

		if ( $this->cols_manager ) {

			$newCols = [];
			foreach ( $this->active_cols as $colId ) {

				if ( ! isset( $cols[ $colId ] ) ) {
					continue;
				}
				$newCols[ $colId ] = $cols[ $colId ];
			}

			$cols = $newCols;
		}

		foreach ( $cols as $colId => $colData ) {

			if ( isset( $colData['sort'] ) && $colData['sort'] ) {
				$cols[ $colId ]['sort'] = $this->get_sort_col( $colId );
			}

			if ( isset( $colData['search'] ) && $colData['search'] ) {
				$cols[ $colId ]['search'] = $this->get_search_col( $colId );
			}
		}

		return $cols;
	}

}
