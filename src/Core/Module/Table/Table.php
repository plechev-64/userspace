<?php

namespace USP\Core\Module\Table;

class Table {

	public bool $zebra = false;
	public array $border = [];
	public array $cols = [];
	public int $cols_number = 0;
	public array $rows = [];
	public array $total = [];
	public ?string $table_id = null;
	public array $class = [];
	public array $attr_rows = [];

	public function __construct( array $tableProps = [] ) {

		$this->init_properties( $tableProps );

		if ( ! $this->table_id ) {
			$this->table_id = 'usp-table-' . current_time( 'timestamp' );
		}

		if ( ! $this->cols_number ) {
			$this->cols_number = count( $this->cols );
		}
	}

	private function init_properties( array $args ): void {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	protected function setup_string_attrs( array $attrs ): string {

		$stringAttrs = [];

		foreach ( $attrs as $name => $value ) {

			if ( ! isset( $value ) || $value === '' ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}

			$stringAttrs[] = $name . '="' . $value . '"';
		}

		return implode( ' ', $stringAttrs );
	}

	protected function get_current_number(): int {
		return count( $this->rows ) + 1;
	}

	protected function get_table_attrs(): string {

		$attrs = [
			'id' => esc_attr( $this->table_id )
		];

		if ( $this->class ) {
			$attrs['class'][] = esc_attr( $this->class );
		}

		$attrs['class'][] = 'usp-table usp-preloader-parent';

		if ( $this->cols_number ) {
			$attrs['class'][] = 'usp-table__type-cell-' . esc_attr( $this->cols_number );
		}

		if ( $this->zebra ) {
			$attrs['class'][] = 'usp-table__zebra';
		}

		if ( ! isset( reset( $this->cols )['title'] ) ) {
			$attrs['class'][] = 'usp-table__not-header';
		}

		if ( $this->border ) {

			if ( in_array( 'table', $this->border ) ) {
				$attrs['class'][] = 'usp-table__border';
			}

			if ( in_array( 'cols', $this->border ) ) {
				$attrs['class'][] = 'usp-table__border-row-right';
			}

			if ( in_array( 'rows', $this->border ) ) {
				$attrs['class'][] = 'usp-table__border-row-bottom';
			}
		}

		return $this->setup_string_attrs( $attrs );
	}

	protected function get_header_attrs(): string {

		$attrs            = [];
		$attrs['class'][] = 'usp-table__row';
		$attrs['class'][] = 'usp-table__row-header';

		return $this->setup_string_attrs( $attrs );
	}

	protected function get_row_attrs( array $customAttrs = [] ): string {

		$attrs = [];

		if ( $customAttrs ) {
			$attrs = $customAttrs;
		}

		$attrs['class'][] = 'usp-table__row';

		return $this->setup_string_attrs( $attrs );
	}

	protected function get_cell_attrs( string $idCol, array $cellProps = [], ?string $place = null, ?string $contentCell = null ): string {

		$attrs = [
			'class' => [ 'usp-table__cell', 'usp-table__col-' . esc_attr( $idCol ) ]
		];

		$attrs['data-col'] = esc_attr( $idCol );

		if ( $cellProps ) {

			if ( isset( $cellProps['width'] ) && $cellProps['width'] ) {
				$attrs['class'][] = 'usp-table__cell-w-' . esc_attr( $cellProps['width'] );
			}

			if ( isset( $cellProps['align'] ) && $cellProps['align'] ) {
				$attrs['class'][] = 'usp-table__cell-' . esc_attr( $cellProps['align'] );
			}

			if ( isset( $cellProps['title'] ) && $cellProps['title'] ) {
				$attrs['data-usp-ttitle'] = esc_attr( $cellProps['title'] );
			}

			$attrs['data-value'] = trim( wp_strip_all_tags( $contentCell ) );

			if ( isset( $cellProps['sort'] ) && $cellProps['sort'] ) {
				if ( $place == 'header' ) {

					if ( isset( $cellProps['sort']['onclick'] ) ) {
						$attrs['onclick'] = $cellProps['sort']['onclick'];
					}

					$attrs['class'][]    = 'usp-table__cell-must-sort';
					$attrs['data-sort']  = $cellProps['sort'];
					$attrs['data-order'] = $cellProps['sort']['order'] ?? 'desc';
				} else if ( $place == 'total' ) {
					$attrs['class'][]    = 'usp-table__cell-total';
					$attrs['data-field'] = $cellProps['sort'];
				} else {
					$attrs['class'][]                                 = 'usp-table__cell-sort';
					$attrs[ 'data-' . $cellProps['sort'] . '-value' ] = trim( wp_strip_all_tags( $contentCell ) );
				}
			}
		}

		return $this->setup_string_attrs( $attrs );
	}

	public function add_row( array $row, array $attrs = [] ): void {
		$this->attr_rows[ count( $this->rows ) ] = $attrs;
		$this->rows[]                            = $row;
	}

	public function add_total_row( array $row ) {
		$this->total = $row;
	}

	public function get_table( array $rows = [] ): string {

		if ( $rows ) {
			$this->rows = $rows;
		}

		$content = '<div ' . $this->get_table_attrs() . '>';

		if ( $this->cols ) {

			$titles = [];
			$search = [];
			foreach ( $this->cols as $k => $col ) {

				if ( isset( $col['title'] ) ) {
					$titles[ $k ] = $col['title'];
				}

				if ( isset( $col['search'] ) && $col['search'] ) {
					$search[ $k ] = $col['search'];
				}
			}

			if ( $titles ) {
				$content .= $this->header_row();
			}

			if ( $search ) {
				$content .= $this->search_row();
			}
		}

		foreach ( $this->rows as $k => $cells ) {

			$attrs = [ 'class' => [ 'usp-table__row-must-sort' ] ];

			if ( isset( $this->attr_rows[ $k ] ) ) {
				foreach ( $this->attr_rows[ $k ] as $attr => $value ) {
					if ( isset( $attrs[ $attr ] ) ) {
						$attrs[ $attr ] = array_merge( $attrs[ $attr ], $value );
					} else {
						$attrs[ $attr ] = $value;
					}
				}
			}

			$content .= $this->row( $cells, $attrs );
		}

		if ( $this->total ) {
			$content .= $this->get_total_row();
		}

		$content .= '</div>';

		$content .= "<script>jQuery(function($){usp_init_table('" . esc_attr( $this->table_id ) . "');});</script>";

		return $content;
	}

	protected function get_total_row(): string {

		$total = ( $this->total && is_array( $this->total ) ) ? $this->total : [];

		if ( ! $total ) {

			foreach ( $this->cols as $k => $col ) {
				if ( isset( $col['total'] ) ) {
					$total[] = $col['total'];
				} else if ( isset( $col['totalsum'] ) ) {
					$total[] = 0;
				} else {
					$total[] = '-';
				}
			}

			foreach ( $this->rows as $row ) {
				foreach ( $row as $k => $value ) {
					if ( isset( $this->cols[ $k ]['totalsum'] ) ) {
						$_v          = wp_strip_all_tags( $value );
						$total[ $k ] += is_numeric( $_v ) ? $_v : 0;
					}
				}
			}
		}

		$attrs['class'][] = 'usp-table__row-total';

		return $this->row( $total, $attrs, 'total' );
	}

	protected function search_row(): string {

		$attrs            = [];
		$attrs['class'][] = 'usp-table__row';
		$attrs['class'][] = 'usp-table__row-search';

		$content = '<div ' . $this->setup_string_attrs( $attrs ) . '>';

		foreach ( $this->cols as $idCol => $col ) {

			if ( ! isset( $col['search'] ) || ! $col['search'] ) {
				$contentCell = '';
			} else {

				$name  = $col['search']['name'] ?? $idCol;
				$value = $col['search']['value'] ?? '';

				if ( ! $value && ! empty( $_REQUEST[ $name ] ) ) {
					$value = sanitize_text_field( wp_unslash( $_REQUEST[ $name ] ) );
				}

				$submit = $col['search']['submit'] ?? 0;

				if ( is_string( $submit ) ) {
					$submit = '\'' . $submit . '\'';
				}

				$onkeyup = 'onkeyup="usp_table_search(this, event.key, ' . $submit . ');"';

				if ( isset( $col['search']['onkeyup'] ) ) {

					if ( ! $col['search']['onkeyup'] ) {
						$onkeyup = '';
					} else {
						$onkeyup = 'onkeyup="' . $col['search']['onkeyup'] . '"';
					}
				}

				$datescript = '';
				if ( isset( $col['search']['type'] ) ) {

					if ( $col['search']['type'] == 'date' ) {

						usp_datepicker_scripts();

						$datescript = 'class="usp-datepicker" onclick="usp_show_datepicker(this);" title="' . __( 'Use the format', 'userspace' ) . ': yyyy-mm-dd" pattern="(\d{4}-\d{2}-\d{2})"';
					}
				}

				$contentCell = '<input style="width:100%" type="text" ' . $datescript . ' name="' . esc_attr( $name ) . '" placeholder="' . __( 'Search', 'userspace' ) . '" ' . $onkeyup . ' value="' . esc_attr( $value ) . '">';
			}

			$content .= $this->cell( $idCol, $contentCell, $col, 'search' );
		}

		$content .= '</div>';

		return $content;
	}

	protected function header_row(): string {

		$content = '<div ' . $this->get_header_attrs() . '>';

		foreach ( $this->cols as $idCol => $col ) {

			$content .= $this->cell( $idCol, $col['title'], $col, 'header' );
		}

		$content .= '</div>';

		return $content;
	}

	protected function parse_row_cells( array $cells, bool $place = false ): string {

		$content = '';

		$ncells = array_combine( array_keys( $this->cols ), $cells );

		foreach ( $ncells as $idCol => $contentCell ) {

			$cellProps = false;

			if ( $this->cols && isset( $this->cols[ $idCol ] ) ) {
				$cellProps = $this->cols[ $idCol ];
			}

			$content .= $this->cell( $idCol, $contentCell, $cellProps, $place );
		}

		return $content;
	}

	protected function row( array $cells, array $attrs = [], bool $place = false ): string {

		$content = '<div ' . $this->get_row_attrs( $attrs ) . '>';
		$content .= $this->parse_row_cells( $cells, $place );
		$content .= '</div>';

		return $content;
	}

	protected function cell( string $idCol, string $contentCell, array $cellProps = [], bool $place = false ): string {

		if ( ! isset( $contentCell ) || $contentCell === '' ) {
			$contentCell = '-';
		}

		return '<div ' . $this->get_cell_attrs( $idCol, $cellProps, $place, $contentCell ) . '>' . $contentCell . '</div>';
	}

}
