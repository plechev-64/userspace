<?php

/**
 * Drop-down (menu) component
 *
 * @return string html menu.
 * @since 1.0
 *
 */
class USP_Dropdown {

	/**
	 * Menu position: right|left (optional)
	 *
	 * @var string
	 * @since 1.0
	 */
	public $position = 'right';

	/**
	 * Wrap on border (optional)
	 *
	 * @var bool
	 * @since 1.0
	 */
	public $border = true;

	/**
	 * Unique id for the menu (required)
	 * This id will be registered as the corresponding WordPress filter
	 *
	 * @var string
	 * @since 1.0
	 */
	public $id = false;

	/**
	 * Additional class of menu (optional)
	 *
	 * @var string
	 * @since 1.0
	 */
	public $class = '';

	/**
	 * html|text before icon (optional)
	 *
	 * @var string
	 * @since 1.0
	 */
	public $left = false;

	/**
	 * Drop-down menu icon (optional)
	 *
	 * @var string
	 * @since 1.0
	 */
	public $icon = 'fa-vertical-ellipsis';

	/**
	 * Add to filter arguments (optional)
	 *
	 * @var string
	 * @since 1.0
	 */
	public $filter_arg = false;

	/**
	 * Constructor.
	 *
	 * @param string $id unique id for the menu (required)
	 * @param array $args Optional array of values.
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct( $id, $args ) {
		if ( ! isset( $id ) ) {
			return;
		}

		$this->id = $id;

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

	/**
	 * Get html dropdown menu
	 *
	 * @return string html menu.
	 *
	 * @since 1.0
	 */
	public function get_dropdown() {
		$args     = $this->filter_arg ?? false;
		$dropdown = '<div id="' . $this->id . '" class="' . $this->get_class() . ' usps usps__ai-stretch usps__relative">';
		if ( $this->left ) {
			$dropdown .= '<div class="usp-dropdown__left usps__as-center">' . $this->left . '</div>';
		}
		$dropdown .= '<div class="usp-dropdown__menu usp-menu-has-child usps__relative usps__text-center">';
		$dropdown .= '<a class="usp-dropdown__bttn usps usps__ai-center usps__jc-center usps__focus" href="javascript:void(0);" onclick="usp_dropdown_open(this); return false;">';
		$dropdown .= '<i class="uspi ' . $this->icon . '" aria-hidden="true"></i>';
		$dropdown .= '</a>';
		/**
		 * Filters to add menu items.
		 * filter name - unique $id (first argument) for the menu on __constructor
		 *
		 * @param string menu items
		 * @param string $args additional data
		 *
		 * @since 1.0.0
		 *
		 */
		$dropdown .= '<div class="usp-dropdown__hidden usp-wrap__widget usps usps__column">'
		             . apply_filters( $this->id, '', $args ) .
		             '</div>';
		$dropdown .= '</div>';
		$dropdown .= '</div>';

		return $dropdown;
	}

	/**
	 * Get additional class on wrapper menu.
	 *
	 * @return string additional classes.
	 * @since 1.0
	 *
	 */
	private function get_class() {
		$class = $this->class ? $this->class . ' ' : '';
		$class .= 'usp-dropdown-box';
		$class .= ( $this->border ) ? ' usp-dropdown-border' : '';
		$class .= ( $this->position == 'left' ) ? ' usp-dropdown-left' : '';

		return $class;
	}

}
