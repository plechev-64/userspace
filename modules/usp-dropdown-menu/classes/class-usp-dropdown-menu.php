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
	private $default_group = 'default';

	/**
	 * Base USP_Button type
	 *
	 * @var string
	 */
	public $base_button_type = 'clear';

	/**
	 * Menu toggle button
	 *
	 * @var array|string - USP_Button args[] or string / html
	 */
	public $open_button = [
		'icon' => 'fa-vertical-ellipsis'
	];

	/**
	 * Opening type
	 *
	 * @var string - on_click / on_hover
	 */
	public $show = 'on_click';

	/**
	 * Menu position
	 *
	 * bottom-left, bottom-right, top-left, top-right, left-bottom,
	 * left-top, left-center, right-bottom, right-top, right-center
	 *
	 * @var string
	 */
	public $position = 'bottom-left';

	/**
	 * Menu style
	 *
	 * @var string - white, dark, primary
	 */
	public $style = 'white';

	/**
	 * Menu buttons size
	 *
	 * @var string - small, standart, medium, large, big
	 */
	public $size = 'medium';

	/**
	 * Html before menu button
	 *
	 * @var string
	 */
	public $before = '';

	/**
	 * Html after menu button
	 *
	 * @var string
	 */
	public $after = '';

	public $groups = [];

	public function __construct( string $id, array $params = [] ) {

		$this->id = $id;

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $params[ $name ] ) & ! empty( $params[ $name ] ) ) {
				$this->$name = $params[ $name ];
			}
		}

		$this->add_group( $this->default_group );
	}

	/**
	 * Add new menu buttons group
	 *
	 * @param string $id
	 * @param array $params
	 *
	 * @return USP_Dropdown_Menu_Group
	 */
	public function add_group( string $id, array $params = [] ) {

		$this->groups[ $id ] = new USP_Dropdown_Menu_Group( $id, $params, $this );

		return $this->get_group( $id );
	}

	/**
	 * Add custom menu item
	 *
	 * @param string $html
	 * @param array $params
	 *
	 * @return USP_Dropdown_Menu_Group
	 */
	public function add_item( string $html, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_item( $html, $params );
	}

	/**
	 * Add menu button
	 *
	 * @param array $args
	 * @param array $params
	 *
	 * @return USP_Dropdown_Menu_Group
	 */
	public function add_button( array $args, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_button( $args, $params );
	}

	/**
	 * Add menu title
	 *
	 * @param string $text
	 * @param array $params
	 *
	 * @return USP_Dropdown_Menu_Group
	 */
	public function add_title( string $text, array $params = [] ) {

		return $this->get_group( $this->default_group )->add_title( $text, $params );
	}

	/**
	 * Add submenu
	 *
	 * @param USP_Dropdown_Menu $submenu
	 * @param array $params
	 *
	 * @return USP_Dropdown_Menu_Group
	 */
	public function add_submenu( USP_Dropdown_Menu $submenu, array $params = [] ) {
		return $this->get_group( $this->default_group )->add_submenu( $submenu, $params );
	}

	/**
	 * Get menu group
	 *
	 * @param $group_id
	 *
	 * @return false|USP_Dropdown_Menu_Group
	 */
	public function get_group( $group_id ) {
		return $this->groups[ $group_id ] ?? false;
	}

	/**
	 * Get menu id
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get menu html
	 *
	 * @return string
	 */
	public function get_content() {

		do_action( 'usp_dropdown_menu', $this->get_id(), $this );

		$show  = "usp-menu_{$this->show}";
		$style = "usp-menu_style_{$this->style}";

		$html = "<div id='usp-menu_{$this->get_id()}' class='usp-menu usp-menu_{$this->get_id()} {$show} {$style}'>";

		$html .= $this->build_menu_button();
		$html .= $this->build_menu_content();

		$html .= '</div>';

		return $this->before || $this->after ? $this->wrap_content( $html ) : $html;

	}

	private function wrap_content( $content ) {
		$before = $this->before ? "<div class='usp-menu-before usps__mr-6'>{$this->before}</div>" : '';
		$after  = $this->after ? "<div class='usp-menu-after usps__ml-6'>{$this->after}</div>" : '';

		return "<div class='usp-menu-wrapper usps__inline usps__ai-center usps__wrap'>{$before}{$content}{$after}</div>";
	}

	private function build_menu_button() {

		$button_class = "usp-menu-button usp-menu-button_style_{$this->style} usps__focus";
		$open_button  = $this->open_button;

		if ( is_array( $open_button ) ) {

			$open_button['type'] = $this->base_button_type;
			$open_button['size'] = $open_button['size'] ?? $this->size;

			return ( new USP_Button( $open_button ) )->add_class( $button_class )->get_button();
		}

		$html = "<div tabindex='0' class='{$button_class}'>";
		$html .= $open_button;
		$html .= '</div>';

		return $html;

	}

	private function build_menu_content() {

		$pos = $this->position;

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
			return $a->order <=> $b->order;
		} );

	}

}
