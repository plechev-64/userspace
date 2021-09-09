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

	public $params = [
		'align_content' => 'vertical' // vertical, horizontal
	];

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

	public function get_html() {

		if ( ! $this->items ) {
			return '';
		}

		$align_content = $this->params['align_content'];
		$id            = $this->params['id'] ?? '';

		$html = "<div id='{$id}' class='usp-menu-group usp-menu-group_{$this->get_id()} usp-menu-group_content_{$align_content}'>";

		foreach ( $this->items as $item ) {
			$html .= $this->build_item( $item['data'], $item['type'], $item['params'] );
		}

		$html .= '</div>';

		return $html;
	}

	private function build_item( $data, $type, $params ) {

		if ( $type === 'custom' ) {
			return $this->build_item_custom( $data, $params );
		}

		return $this->build_item_button( $data, $params );

	}

	private function build_item_custom( $data, $params ) {

		$html = "<div class='usp-menu-item usp-menu-item_custom'>";
		$html .= $data;
		$html .= '</div>';

		return $html;
	}

	private function build_item_button( $data, $params ) {

		$buttons_class = 'usp-menu-item usp-menu-item_button usps__focus';

		if ( isset( $data['class'] ) ) {
			$data['class'] .= ' ' . $buttons_class;
		} else {
			$data['class'] = $buttons_class;
		}

		$html = ( new USP_Button( $data ) )->get_button();

		return $html;
	}

	private function _add_item( $data, string $type, array $params = [] ) {

		$this->items[] = [
			'type'   => $type,
			'data'   => $data,
			'params' => $params
		];
	}

}
