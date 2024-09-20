<?php

/**
 * Drop-down (menu) component
 *
 * @return string html menu.
 * @since 1.0
 *
 */
class DropdownMenu {

	private ?string $id = null;
	private string $default_group = 'default';

	/**
	 * Base Button type
	 *
	 * @var string
	 */
	public string $base_button_type = 'clear';

	/**
	 * Menu toggle button
	 *
	 * @var array|string - Button args[] or string / html
	 */
	public array|string $open_button = [
		'icon' => 'fa-vertical-ellipsis',
		'size' => 'medium',
	];

	/**
	 * Menu toggle button color
	 *
	 * @var string - 'transparent'
	 */
	public ?string $open_button_style = null;

	/**
	 * Opening type
	 *
	 * @var string - on_click / on_hover
	 */
	public string $show = 'on_click';

	/**
	 * Menu position
	 *
	 * bottom-left, bottom-right, top-left, top-right, left-bottom,
	 * left-top, left-center, right-bottom, right-top, right-center
	 *
	 * @var string
	 */
	public string $position = 'bottom-right';

	/**
	 * Menu style
	 *
	 * @var string - 'white', 'dark', 'primary' or 'none' - if you need to set your own color
	 */
	public string $style = 'white';

	/**
	 * Menu buttons size
	 *
	 * @var string - small, standard, medium, large, big
	 */
	public string $size = 'standard';

	/**
	 * Html before menu button
	 *
	 * @var string
	 */
	public string $before = '';

	/**
	 * Html after menu button
	 *
	 * @var string
	 */
	public string $after = '';

	/**
	 * Custom menu data
	 *
	 * @var mixed
	 */
	public mixed $custom_data = null;

	public array $groups = [];

	public function __construct( string $id, array $params = [] ) {

		$this->id = $id;

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( ! empty( $params[ $name ] ) ) {
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
	 * @return DropdownMenuGroup|null
	 */
	public function add_group( string $id, array $params = [] ): ?DropdownMenuGroup {

		if ( ! isset( $params['order'] ) ) {
			$params['order'] = ( count( $this->groups ) + 1 ) * 10;
		}

		$this->groups[ $id ] = new DropdownMenuGroup( $id, $params, $this );

		return $this->get_group( $id );
	}

	/**
	 * Add custom menu item
	 *
	 * @param string $html
	 * @param array $params
	 *
	 * @return DropdownMenuGroup
	 */
	public function add_item( string $html, array $params = [] ): DropdownMenuGroup {
		return $this->get_group( $this->default_group )->add_item( $html, $params );
	}

	/**
	 * Add menu button
	 *
	 * @param array $args
	 * @param array $params
	 *
	 * @return DropdownMenuGroup
	 */
	public function add_button( array $args, array $params = [] ): DropdownMenuGroup {
		return $this->get_group( $this->default_group )->add_button( $args, $params );
	}

	/**
	 * Add menu title
	 *
	 * @param string $text
	 * @param array $params
	 *
	 * @return DropdownMenuGroup
	 */
	public function add_title( string $text, array $params = [] ): DropdownMenuGroup {
		return $this->get_group( $this->default_group )->add_title( $text, $params );
	}

	/**
	 * Add submenu
	 *
	 * @param DropdownMenu $submenu
	 * @param array $params
	 *
	 * @return DropdownMenuGroup
	 */
	public function add_submenu( DropdownMenu $submenu, array $params = [] ): DropdownMenuGroup {
		return $this->get_group( $this->default_group )->add_submenu( $submenu, $params );
	}

	/**
	 * Get menu group
	 *
	 * @param $group_id
	 *
	 * @return null|DropdownMenuGroup
	 */
	public function get_group( $group_id ): ?DropdownMenuGroup {
		return $this->groups[ $group_id ] ?? null;
	}

	/**
	 * Get menu id
	 *
	 * @return string|null
	 */
	public function get_id(): ?string {
		return $this->id;
	}

	public function has_buttons(): bool {

		foreach ( $this->groups as $group ) {
			if ( $group->items ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Get menu html
	 *
	 * @return string
	 */
	public function get_content(): string {

		do_action( 'usp_dropdown_menu', $this->get_id(), $this );

		if ( ! $this->has_buttons() ) {
			return '';
		}

		$show  = "usp-menu_" . esc_attr( $this->show );
		$style = "usp-menu_style_" . esc_attr( $this->style );

		$html = "<div id='usp-menu_" . esc_attr( $this->get_id() ) . "' class='usp-menu usp-menu_" . esc_attr( $this->get_id() ) . " {$show} {$style}'>";

		$html .= $this->build_menu_button();
		$html .= $this->build_menu_content();

		$html .= '</div>';

		return $this->before || $this->after ? $this->wrap_content( $html ) : $html;

	}

	private function wrap_content( string $content ): string {
		$before = $this->before ? "<div class='usp-menu-before usps__mr-6'>{$this->before}</div>" : '';
		$after  = $this->after ? "<div class='usp-menu-after usps__ml-6'>{$this->after}</div>" : '';

		return "<div class='usp-menu-wrapper usps__inline usps__ai-center usps__wrap'>{$before}{$content}{$after}</div>";
	}

	private function build_menu_button(): string {
		$color = ( $this->open_button_style ) ? esc_attr( $this->open_button_style ) : esc_attr( $this->style );

		$button_class = "usp-menu-button usp-menu-button_style_{$color} usps__focus";
		$open_button  = $this->open_button;

		if ( is_array( $open_button ) ) {

			$open_button['type'] = $this->base_button_type;

			return ( new Button( $open_button ) )->add_class( $button_class )->get_button();
		}

		$html = "<div tabindex='0' class='{$button_class}'>";
		$html .= $open_button;
		$html .= '</div>';

		return $html;

	}

	private function build_menu_content(): string {

		$pos = esc_attr( $this->position );

		$html = "<div tabindex='-1' class='usp-menu-items usps usp-menu-items_pos_{$pos}' data-position='{$pos}'>";

		$this->order_groups();

		foreach ( $this->groups as $group ) {
			$html .= $group->get_html();
		}

		$html .= '</div>';

		return $html;

	}

	private function order_groups(): void {
		usort( $this->groups, function ( $a, $b ) {
			return $a->order <=> $b->order;
		} );
	}

}
