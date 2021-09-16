<?php

/**
 * Drop-down (menu) component
 *
 * @return string html menu.
 * @since 1.0
 *
 */
class USP_Dropdown_New {

	private $id;

	public $open_button;

	public $params = [
		//on_click, on_hover
		'show'     => 'on_click',
		// bottom-left, bottom-right, top-left, top-right, left-bottom, left-top, left-center, right-bottom, right-top, right-center
		'position' => 'bottom-left',
		// dark, white, primary, custom
		'style'    => 'dark'
	];

	public $default_group;

	public $groups = [];

	public function __construct( string $id, $open_button, array $params = [] ) {

		$this->id            = $id;
		$this->open_button   = $open_button;
		$this->params        = array_merge( $this->params, $params );
		$this->default_group = 'default';

		$this->add_group( $this->default_group );
	}

	public function add_group( string $id, array $params = [] ) {

		$this->groups[ $id ] = new USP_Dropdown_Group( $id, $params, $this );

		return $this->get_group( $id );
	}

	public function add_item( string $html, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_item( $html, $params );
	}

	public function add_button( array $args, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_button( $args, $params );
	}

	public function add_submenu( USP_Dropdown_New $submenu, array $params = [] ) {
		return $this->get_group( $this->default_group )->add_submenu( $submenu, $params );
	}

	public function get_group( $group_id ) {
		return $this->groups[ $group_id ] ?? false;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_style() {
		return $this->params['style'];
	}

	public function get_content() {

		$show  = "usp-menu_{$this->params['show']}";
		$style = "usp-menu_style_{$this->get_style()}";

		$html = "<div id='usp-menu_{$this->get_id()}' class='usp-menu usp-menu_{$this->get_id()} {$show} {$style}'>";

		$html .= $this->build_menu_button();
		$html .= $this->build_menu_content();

		$html .= '</div>';

		return $html;

	}

	private function build_menu_button() {

		$button_class = "usp-menu-button usp-menu-button_style_{$this->get_style()} usps__focus";

		if ( is_array( $this->open_button ) ) {
			return ( new USP_Button( $this->open_button ) )->add_class( $button_class )->get_button();
		}

		$html = "<div tabindex='0' class='{$button_class}'>";
		$html .= $this->open_button;
		$html .= '</div>';

		return $html;

	}

	private function build_menu_content() {

		$pos = $this->params['position'];

		$html = "<div tabindex='-1' class='usp-menu-items usps usp-menu-items_pos_{$pos}' data-position='{$pos}'>";

		$this->order_groups();

		foreach ( $this->groups as $group ) {
			$html .= $group->get_html();
		}

		$html .= '</div>';

		return $html;

	}

	private function order_groups() {

		usort( $this->groups, function ( $a, $b ) {
			$a_order = $a->get_param( 'order', 0 );
			$b_order = $b->get_param( 'order', 0 );

			return $a_order <=> $b_order;
		} );

	}

}
