<?php

/**
 * Drop-down (menu) component
 *
 * @return string html menu.
 * @since 1.0
 *
 */
class USP_Dropdown_Group {

	private $id;

	public $params = [];
	public $items = [];

	public function __construct( string $id, array $params = [] ) {

		$this->id     = $id;
		$this->params = array_merge( $this->params, $params );

	}

	public function add_item( string $html, array $params = [] ) {

		$this->_add_item( $html, 'custom', $params );

		return $this;

	}

	public function add_button( array $args, array $params = [] ) {

		$this->_add_item( $args, 'button', $params );

		return $this;

	}

	public function get_id() {
		return $this->id;
	}

	public function get_content() {

		if ( ! $this->items ) {
			return '';
		}

		$html = "<div class='usp-menu-group usp-menu-group_{$this->get_id()}'>";

		foreach ( $this->items as $item ) {
			$html .= $this->build_item( $item['data'], $item['type'], $item['params'] );
		}

		$html .= '</div>';

		return $html;
	}

	private function build_item( $data, $type, $params ) {

		$html = "<div class='usp-menu-item usp-menu-item_{$type}'>";
		$html .= $type === 'button' ? ( new USP_Button( $data ) )->get_button() : $data;
		$html .= '</div>';

		return $html;
	}

	private function _add_item( $data, string $type, array $params = [] ) {

		$this->items[] = [
			'type'        => $type,
			'data'        => $data,
			'item_params' => $params
		];
	}

}
