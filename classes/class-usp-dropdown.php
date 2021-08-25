<?php


class USP_Dropdown {

	public $id;
	public $class = '';
	public $icon = 'fa-vertical-ellipsis';
	public $left = false; // before icon
	public $border = true; // wrap on border

	function __construct( $args ) {
		if ( ! isset( $args['id'] ) ) {
			return false;
		}

		$this->init_properties( $args );
	}

	private function init_properties( $args ) {
		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	public function get_dropdown() {
		$class = ( $this->border ) ? ' usp-dropdown__border' : '';
		$class .= ( $this->class ) ? ' ' . $this->class : '';

		$dropdown = '<div id="' . $this->id . '" class="usp-dropdown-box usps usps__ai-center' . $class . '">';
		if ( $this->left ) {
			$dropdown .= '<div class="usp-dropdown__left">' . $this->left . '</div>';
		}
		$dropdown .= '<div class="usp-dropdown__menu usps usps__ai-center usps__jc-center usps__relative usps__text-center">';
		$dropdown .= '<i class="uspi ' . $this->icon . '" aria-hidden="true"></i>';
		$dropdown .= '<div class="usp-dropdown__hidden usp-wrap__widget usps usps__column">' . apply_filters( $this->id, '' ) . '</div>';
		$dropdown .= '</div>';
		$dropdown .= '</div>';

		return $dropdown;
	}

}
