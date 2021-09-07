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

	public $button;

	public $params = [];

	public $default_group;

	public $groups = [];

	public function __construct( string $id, $button, array $params = [] ) {

		$this->id            = $id;
		$this->button        = $button;
		$this->params        = array_merge( $this->params, $params );
		$this->default_group = 'default';

		$this->add_group( $this->default_group );
	}

	public function add_group( string $id, array $params = [] ) {

		$this->groups[ $id ] = new USP_Dropdown_Group( $id, $params );

		return $this->get_group( $id );
	}

	public function add_item( string $html, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_item( $html, $params );
	}

	public function add_button( array $args, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_button( $args, $params );
	}

	public function get_group( $group_id ) {
		return $this->groups[ $group_id ] ?? false;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_content() {

		$html = "<div class='usp-menu usp-menu_{$this->get_id()}'>";

		$html .= $this->build_menu_button();
		$html .= $this->build_menu_content();

		$html .= '</div>';

		return $html;

	}

	private function build_menu_button() {

		$button = is_array( $this->button ) ? ( new USP_Button( $this->button ) )->get_button() : $this->button;

		$html = '<div class="usp-menu-button">';
		$html .= $button;
		$html .= '</div>';

		return $html;

	}

	private function build_menu_content() {

		$html = '<div class="usp-menu-items">';

		foreach ( $this->groups as $group ) {
			$html .= $group->get_html();
		}

		$html .= '</div>';

		return $html;

	}

}
