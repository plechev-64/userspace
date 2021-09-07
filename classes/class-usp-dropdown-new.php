<?php

/**
 * Drop-down (menu) component
 *
 * @return string html menu.
 * @since 1.0
 *
 */
class USP_Dropdown_New {

	private $menu_id;

	private $menu_button;

	private $menu_params;

	private $active_group;

	private $menu = [];

	public function __construct( string $menu_id, $menu_button, array $menu_params = [] ) {

		$this->menu_id     = $menu_id;
		$this->menu_button = $menu_button;
		$this->menu_params = array_merge( [
			'border' => 1
		], $menu_params );

		$this->active_group = 'default';

	}

	public function add_group( string $group_id, array $group_params = [] ) {

		$this->menu[ $group_id ] = [
			'group_id' => $group_id,
			'items'    => [],
			'params'   => array_merge( [
				'border' => 1
			], $group_params )
		];

		$this->active_group = $group_id;

		return $this;

	}

	public function add_item( string $item_html, array $item_params = [] ) {

		$this->add_group_item( $this->active_group, $item_html, 'custom', $item_params );

		return $this;

	}

	public function add_button( array $button_args, array $item_params = [] ) {

		$this->add_group_item( $this->active_group, $button_args, 'button', $item_params );

		return $this;

	}

	public function get_menu_id() {
		return $this->menu_id;
	}

	public function get_content() {

		if ( ! $this->menu ) {
			return '';
		}

		$html = '<div class="usp-menu usp-menu_' . $this->menu_id . '">';

		$html .= $this->build_menu_button();
		$html .= $this->build_menu_content();

		$html .= '</div>';

		return $html;

	}

	private function build_menu_button() {

		if ( is_array( $this->menu_button ) ) {
			$menu_button = $this->build_button( $this->menu_button );
		} else {
			$menu_button = $this->menu_button;
		}

		$html = '<div class="usp-menu-button">';
		$html .= $menu_button;
		$html .= '</div>';

		return $html;

	}

	private function build_menu_content() {

		$html = '<div class="usp-menu-items">';

		foreach ( $this->menu as $menu_group ) {
			$html .= $this->build_menu_group( $menu_group );
		}

		$html .= '</div>';

		return $html;

	}

	private function build_menu_group( $menu_group ) {

		$html = '<div class="usp-menu-group usp-menu-group_' . $menu_group['group_id'] . '">';

		foreach ( $menu_group['items'] as $menu_item ) {

			$item = $menu_item['type'] === 'button' ? $this->build_button( $menu_item['item'] ) : $menu_item['item'];

			$html .= $this->build_menu_item( $item, $menu_item['type'], $menu_item['params'] );

		}

		$html .= '</div>';

		return $html;

	}

	private function build_menu_item( $item, $item_type, $item_params ) {

		$html = '<div class="usp-menu-item usp-menu-item_' . $item_type . '">';

		$html .= $item;

		$html .= '</div>';

		return $html;

	}

	private function build_button( $button_args ) {

		$button = new USP_Button( $button_args );

		return $button->get_button();

	}

	private function add_group_item( string $group_id, $item, string $item_type, array $item_params = [] ) {

		if ( ! isset( $this->menu[ $group_id ] ) ) {
			$this->add_group( $group_id );
		}

		$this->menu[ $group_id ] ['items'][] = [
			'type'        => $item_type,
			'item'        => $item,
			'item_params' => $item_params
		];

	}


}
