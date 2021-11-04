<?php

class USP_Pager {

	public $current = 1;  // current page
	public $pages = 0;  // number of pages
	public $diff = array( 4, 4 );  // display range of displayed pages
	public $number = 30; // number of elements per page
	public $total = 0;  // total number of elements
	public $id; // navigation id
	public $class; // navigation class
	public $offset = 0;  // offset
	public $key = 'pagenum';
	public $onclick = false;
	public $page_args = array(
		'type' => 'simple'
	);

	function __construct( $args ) {

		$this->init_properties( $args );

		$this->set_current();

		$this->offset = ( $this->current - 1 ) * $this->number;
		$this->pages  = ceil( $this->total / $this->number );
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) & ! empty( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	function set_current() {

		if ( ! empty( $_REQUEST[ $this->key ] ) ) {
			$this->current = absint( $_REQUEST[ $this->key ] );
		}

		if ( $this->current == 0 ) {
			$this->current = 1;
		}
	}

	function get_walker() {
		$walker = array();

		$walker['args']['number_left']  = ( ( $this->current - $this->diff[0] ) <= 0 ) ? $this->current - 1 : $this->diff[0];
		$walker['args']['number_right'] = ( ( $this->current + $this->diff[1] ) > $this->pages ) ? $this->pages - $this->current : $this->diff[1];

		if ( $walker['args']['number_left'] ) {

			$start = $this->current - $walker['args']['number_left'];

			if ( $start > 1 ) {
				$walker['output'][]['page'] = 1;
			}

			if ( $start > 2 ) {
				$walker['output'][]['separator'] = '<i class="uspi fa-horizontal-ellipsis usp-pager__dots usps usps__ai-center" aria-hidden="true"></i>';
			}


			for ( $num = $walker['args']['number_left']; $num > 0; $num -- ) {
				$walker['output'][]['page'] = $this->current - $num;
			}
		}

		$walker['output'][]['current'] = $this->current;

		if ( $walker['args']['number_right'] ) {
			for ( $num = 1; $num <= $walker['args']['number_right']; $num ++ ) {
				$walker['output'][]['page'] = $this->current + $num;
			}
		}

		$end = $this->pages - ( $this->current + $walker['args']['number_right'] );

		if ( $end > 1 ) {
			$walker['output'][]['separator'] = '<i class="uspi fa-horizontal-ellipsis usp-pager__dots usps usps__ai-center" aria-hidden="true"></i>';
		}

		if ( $end > 0 ) {
			$walker['output'][]['page'] = $this->pages;
		}

		return $walker;
	}

	function get_url( $page_id ) {

		if ( empty( $_POST['tab_url'] ) ) {
			return '';
		}

		return add_query_arg( [ $this->key => $page_id ], sanitize_text_field( wp_unslash( $_POST['tab_url'] ) ) );
	}

	function get_page_args( $page_id, $label = false ) {

		$args = array(
			'type'  => 'simple',
			'href'  => $this->get_url( $page_id ),
			'label' => $label ?: $page_id,
			'data'  => array(
				'page' => $page_id
			)
		);

		if ( $this->onclick ) {
			$args['onclick'] = 'return ' . $this->onclick . '(' . $page_id . ', this);';
		}

		return wp_parse_args( $args, $this->page_args );
	}

	function get_navi() {
		return $this->get_pager();
	}

	function get_pager( $typePager = 'numbers' ) {

		if ( ! $this->total || $this->pages == 1 ) {
			return false;
		}

		$walker = $this->get_walker();

		$content = '<div ' . ( $this->id ? 'id="' . $this->id . '"' : '' ) . ' class="' . ( $this->class ? $this->class . ' ' : '' ) . 'usp-pager usps usps__jc-end usps__line-1">';

		foreach ( $walker['output'] as $item ) {

			foreach ( $item as $type => $data ) {

				if ( $typePager == 'numbers' ) {

					if ( $type == 'page' ) {

						$html = usp_get_button( $this->get_page_args( $data ) );
					} else if ( $type == 'current' ) {
						$html = usp_get_button( [
							'type'   => 'simple',
							'label'  => $data,
							'status' => 'active',
							'data'   => array(
								'page' => $data
							)
						] );
					} else {
						$html = $data;
					}
				} else {

					if ( $type == 'page' ) {

						if ( $this->current + 1 == $data ) {
							$label = __( 'Next', 'userspace' );
						} else if ( $this->current - 1 == $data ) {
							$label = __( 'Previous', 'userspace' );
						} else {
							continue;
						}

						$html = usp_get_button( $this->get_page_args( $data, $label ) );
					} else {
						continue;
					}
				}

				$content .= $html;
			}
		}

		$content .= '</div>';

		return $content;
	}

}
