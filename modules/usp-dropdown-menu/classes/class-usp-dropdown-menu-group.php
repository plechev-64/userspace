<?php

class USP_Dropdown_Menu_Group {

	/**
	 * Group id
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Group menu
	 *
	 * @var USP_Dropdown_Menu
	 */
	private $menu;

	/**
	 * Group items order
	 *
	 * @var int
	 */
	public $order = 10;

	/**
	 * Group items align
	 *
	 * @var string - vertical, horizontal
	 */
	public $align_content = 'vertical';

	/**
	 * Group title
	 *
	 * @var string
	 */
	public $title = '';
	/**
	 * Group items
	 *
	 * @var array
	 */
	public $items = [];

	public function __construct( string $id, array $params, USP_Dropdown_Menu $menu ) {
		$this->id   = $id;
		$this->menu = $menu;

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( ! empty( $params[ $name ] ) ) {
				$this->$name = $params[ $name ];
			}
		}

		if ( $this->title ) {
			$this->add_title( $this->title );
		}
	}

	public function add_item( string $html, array $params = [] ) {
		$this->_add_item( $html, 'custom', $params );

		return $this;
	}

	public function add_button( array $args, array $params = [] ) {
		$this->_add_item( $args, 'button', $params );

		return $this;
	}

	public function add_title( string $text, array $params = [] ) {
		$this->_add_item( $text, 'title', $params );

		return $this;
	}

	public function add_submenu( USP_Dropdown_Menu $submenu, array $params = [] ) {
		$this->_add_item( $submenu, 'submenu', $params );

		return $this;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_html() {
		if ( ! $this->items ) {
			return '';
		}

		$this->order_items();

		$html = "<div class='usp-menu-group usp-menu-group_" . esc_attr( $this->get_id() ) . " usp-menu-group_content_" . esc_attr( $this->align_content ) . "'>";

		foreach ( $this->items as $item ) {
			$html .= $this->build_item( $item['data'], $item['type'], $item['params'] );
		}

		$html .= '</div>';

		return $html;
	}

	private function build_item( $data, $type, $params ) {

		if ( $type === 'button' ) {
			return $this->build_item_button( $data, $params );
		} else if ( $type === 'submenu' ) {
			return $this->build_item_submenu( $data, $params );
		} else if ( $type === 'title' ) {
			return $this->build_item_title( $data, $params );
		}

		return $this->build_item_custom( $data, $params );

	}

	private function build_item_custom( string $data, array $params ) {
		return "<div class='{$this->get_item_classes('custom')}'>{$data}</div>";
	}

	private function build_item_submenu( USP_Dropdown_Menu $submenu, array $params ) {
		return "<div class='{$this->get_item_classes('submenu')}'>{$submenu->get_content()}</div>";
	}

	private function build_item_button( array $args, array $params ) {

		$buttons_class = $this->get_item_classes( 'button' );

		$args['type'] = $this->menu->base_button_type;
		$args['size'] = $args['size'] ?? $this->menu->size;

		return ( new USP_Button( $args ) )->add_class( $buttons_class )->get_button();
	}

	private function build_item_title( string $text, array $params ) {
		return "<div class='{$this->get_item_classes( 'title' )}'>{$text}</div>";
	}

	private function get_item_classes( string $item_type ) {
		$base = "usp-menu-item usp-menu-item_{$item_type} usp-menu-item_style_{$this->menu->style}";

		if ( $item_type === 'button' ) {
			$base .= ' usps__focus';
		}

		return $base;
	}

	private function _add_item( $data, string $type, array $params = [] ) {

		if ( ! isset( $params['order'] ) ) {
			$params['order'] = ( count( $this->items ) + 1 ) * 10;
		}

		$this->items[] = [
			'type'   => $type,
			'data'   => $data,
			'params' => $params
		];
	}

	private function order_items() {

		usort( $this->items, function ( $a, $b ) {
			return ( $a['params']['order'] ?? 10 ) <=> ( $b['params']['order'] ?? 10 );
		} );

	}

}
