<?php

class SubTab {

	public ?string $id = null;
	public ?string $parent_id = null;
	public ?string $name = null;
	public ?string $title = null;
	public string $icon = 'fa-cog';
	public array $supports = [];
	public ?int $counter = null;
	public array $callback = [];
	public ?string $url = null;

	public function __construct( array $subtabData ) {
		$this->init_properties( $subtabData );
		$tab            = USP()->tabs()->tab( $this->parent_id );
		$this->supports = $tab->supports;
	}

	private function init_properties( array $args ): void {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( ! isset( $args[ $name ] ) ) {
				continue;
			}
			$this->$name = $args[ $name ];
		}
	}

	public function setup_prop( string $propName, mixed $value ): void {
		$this->$propName = $value;
	}

	public function is_prop( string $propName ): bool {
		return isset( $this->$propName );
	}

	public function get_prop( string $propName ): mixed {
		return $this->is_prop( $propName ) ? $this->$propName : false;
	}

	public function get_permalink( ?int $user_id = null ): string {
		if ( ! $user_id ) {
			$user_id = USP()->office()->get_owner_id();
		}

		return add_query_arg( [ 'tab' => $this->parent_id, 'subtab' => $this->id ], usp_user_get_url( $user_id ) );
	}

	public function get_button( array $args = array() ): string {

		$tab = USP()->tabs()->tab( $this->parent_id );

		$ajaxLoad = false;
		if ( isset( $tab->supports ) ) {
			if ( in_array( 'ajax', $tab->supports ) ) {
				$ajaxLoad = true;
			}
		}

		$attr = wp_parse_args( $args, [
			'id'      => 'usp-tab__' . esc_attr( $this->id ),
			'class'   => 'usp-subtab-button',
			'label'   => $this->name,
			'icon'    => $this->icon,
			'counter' => $this->counter,
			'href'    => $this->get_permalink(),
			'onclick' => $ajaxLoad ? 'usp_load_tab("' . esc_attr( $tab->id ) . '", "' . esc_attr( $this->id ) . '", this);return false;' : null
		] );

		return usp_get_button( $attr );
	}

	public function get_content(): string {
		global $usp_tab;

		$usp_tab = $this;

		$title = $this->title ?: $this->name;

		$content = '<div id="usp-subtab-' . $this->id . '" class="usp-subtab-box">';

		$content .= '<div class="usp-subtab-title usps usps__nowrap usps__ai-center usps__line-1">';
		if ( $this->icon ) {
			$content .= '<i class="uspi ' . esc_attr( $this->icon ) . '" aria-hidden="true"></i> ';
		}
		$content .= '<span>' . apply_filters( 'usp_subtab_title', $title, $this->id ) . '</span>';
		$content .= '</div>';

		if ( $this->callback ) {

			if ( isset( $this->callback['args'] ) ) {
				$args = $this->callback['args'];
			} else {
				$args = array( USP()->office()->get_owner_id() );
			}

			$content .= '<div class="usp-subtab-content">';
			if ( function_exists( $this->callback['name'] ) ) {
				$content .= apply_filters( 'usp_tab_content', call_user_func_array( $this->callback['name'], $args ), $this->parent_id, $this->id );
			} else {
				$content .= usp_get_notice( [ 'text' => __( 'There was an error loading the tab. Function not found.', 'userspace' ) ] );
			}
			$content .= '</div>';
		}

		$content .= '</div>';

		return $content;
	}

}
