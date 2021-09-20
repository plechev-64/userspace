<?php

/**
 * Drop-down (menu) component
 *
 * @return string html menu.
 * @since 1.0
 *
 */
class USP_Dropdown_Menu {

	private $id;

	public $params = [
		'open_button' => [
			'icon' => 'fa-vertical-ellipsis'
		],
		//on_click, on_hover
		'show'        => 'on_click',
		// bottom-left, bottom-right, top-left, top-right, left-bottom, left-top, left-center, right-bottom, right-top, right-center
		'position'    => 'bottom-left',
		// dark, white, primary, custom
		'style'       => 'white',
		//small, standart, medium, large, big
		'size'        => 'medium'
	];

	public $groups = [];

	private $button_type = 'clear';
	private $default_group = 'default';

	public function __construct( string $id, array $params = [] ) {

		$this->id     = $id;
		$this->params = array_merge( $this->params, $params );

		$this->add_group( $this->default_group );
	}

	public function add_group( string $id, array $params = [] ) {

		$this->groups[ $id ] = new USP_Dropdown_Menu_Group( $id, $params, $this );

		return $this->get_group( $id );
	}

	public function add_item( string $html, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_item( $html, $params );
	}

	public function add_button( array $args, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_button( $args, $params );
	}

	public function add_title( string $text, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_title( $text, $params );
	}

	public function add_submenu( USP_Dropdown_Menu $submenu, array $params = [] ) {
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

	public function get_size() {
		return $this->params['size'];
	}

	public function get_button_type() {
		return $this->button_type;
	}

	public function get_open_button() {
		return $this->params['open_button'];
	}

	public function get_content() {

		do_action( 'usp_dropdown_menu', $this->get_id(), $this );

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
		$open_button  = $this->get_open_button();

		if ( is_array( $open_button ) ) {
			$open_button['type'] = $this->get_button_type();
			$open_button['size'] = $this->get_size();

			return ( new USP_Button( $open_button ) )->add_class( $button_class )->get_button();
		}

		$html = "<div tabindex='0' class='{$button_class}'>";
		$html .= $open_button;
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
