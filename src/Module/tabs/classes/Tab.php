<?php

class Tab {

	public ?string $id = null;
	public ?string $name = null;
	public ?string $icon = 'fa-cog';
	public int $public = 0; //can be 1,0,-1,-2
	public bool $hidden = false;
	public ?int $counter = null;
	public ?string $output = 'menu';
	public array $supports = [];
	public array $content = [];
	public bool $custom_tab = false;
	public ?string $current_id = null;
	public ?string $url = null;
	public ?string $onclick = null;

	public function __construct( array $tabData ) {
		$this->init_properties( $tabData );
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

	public function setup_subtabs(): void {
		foreach ( $this->content as $k => $subtabData ) {
			$this->content[ $k ] = $this->new_subtab( $subtabData );
		}
	}

	public function add_subtab( array $subtabData ): void {
		$this->content[] = $this->new_subtab( $subtabData );
	}

	public function new_subtab( array $subtabData ): SubTab {
		return new SubTab( wp_parse_args( $subtabData, [
			'id'        => $this->id,
			'name'      => $this->name,
			'icon'      => $this->icon,
			'parent_id' => $this->id
		] ) );
	}

	public function set_prop( string $propName, mixed $value ): void {
		$this->$propName = $value;
	}

	public function is_prop( string $propName ): bool {
		return isset( $this->$propName );
	}

	public function get_prop( string $propName ): mixed {
		return $this->is_prop( $propName ) ? $this->$propName : false;
	}

	public function isset_subtab( string $subtab_id ): ?SubTab {

		if ( ! $this->content ) {
			return null;
		}

		foreach ( $this->content as $k => $subtab ) {
			if ( $subtab->id == $subtab_id ) {
				return $subtab;
			}
		}

		return null;
	}

	public function subtab( string $subtab_id = null ): ?SubTab {

		if ( ! $this->content ) {
			return null;
		}

		foreach ( $this->content as $k => $subtab ) {
			if ( ! $subtab_id || $subtab->id == $subtab_id ) {
				return $subtab;
			}
		}

		return null;
	}

	public function is_active_tab(): bool {

		$active = false;

		if ( isset( $_GET['tab'] ) ) {
			$active = $_GET['tab'] == $this->id;
		} else {
			if ( USP()->tabs()->current_id == $this->id ) {
				$active = true;
			}
		}

		return $active;
	}

	public function get_class_button(): array {

		$classes = apply_filters( 'usp_tab_class_button', [ 'usp-tab-button' ], $this->id );

		if ( in_array( 'dialog', $this->supports ) ) {
			$classes[] = 'usp-dialog';
			//$classes[]	 = 'usp-ajax';
		} else if ( in_array( 'ajax', $this->supports ) ) {
			//$classes[] = 'usp-ajax';
		}

		return $classes;
	}

	public function get_button( array $args = [] ): string {

		$ajaxLoad = false;
		if ( isset( $this->supports ) ) {
			if ( in_array( 'ajax', $this->supports ) ) {
				$ajaxLoad = true;
			}
		}

		$onclick = $ajaxLoad ? 'usp_load_tab("' . $this->id . '", 0, this);return false;' : null;

		if ( $this->onclick ) {
			$onclick = $this->onclick;
		}

		$attr = wp_parse_args( $args, [
			'id'        => 'usp-tab__' . $this->id,
			'class'     => implode( ' ', $this->get_class_button() ),
			'label'     => $this->name,
			'icon'      => $this->icon,
			'counter'   => $this->counter,
			'href'      => $this->get_permalink(),
			'icon_mask' => 1,
			//'status'	 => $status,
			'onclick'   => $this->url ? false : $onclick
		] );

		return usp_get_button( $attr );
	}

	public function get_permalink( int $user_id = null ): string {
		if ( ! $user_id ) {
			$user_id = USP()->office()->get_owner_id();
		}

		return $this->url ?: add_query_arg( [ 'tab' => $this->id ], usp_user_get_url( $user_id ) );
	}

	public function is_access(): bool {
		global $user_ID;

		if ( $this->public == 0 ) {
			if ( ! $user_ID || ! USP()->office()->is_owner( $user_ID ) ) {
				return false;
			}
		} else if ( $this->public == - 1 ) {
			if ( ! $user_ID || USP()->office()->is_owner( $user_ID ) ) {
				return false;
			}
		} else if ( $this->public == - 2 ) {
			if ( $user_ID && USP()->office()->is_owner( $user_ID ) ) {
				return false;
			}
		}

		return true;
	}

	public function get_active_subtab_id(): string {

		if ( isset( $_GET['subtab'] ) ) {

			foreach ( $this->content as $k => $subtab ) {
				if ( $_GET['subtab'] == $subtab->id ) {
					return $subtab->id;
				}
			}
		}

		return $this->content[0]->id;
	}

	public function get_menu(): ?string {

		if ( ! $this->content || count( $this->content ) < 2 ) {
			return null;
		}

		if ( ! $this->current_id ) {
			$this->current_id = $this->get_active_subtab_id();
		}

		$content = '<div class="usps usp-subtabs-menu">';

		foreach ( $this->content as $subtab ) {

			$content .= $subtab->get_button( $this->current_id == $subtab->id ? [ 'status' => 'active' ] : [] );
		}

		$content .= '</div>';

		return $content;
	}

	public function get_content(): ?string {

		if ( ! $this->is_access() ) {
			return null;
		}

		if ( ! $this->current_id ) {
			$this->current_id = $this->get_active_subtab_id();
		}

		$content = '<div id="usp-tab-content" class="usp-tab-' . esc_attr( $this->id ) . ' usps__relative usps__grow">';

		$content .= $this->get_menu();

		$content .= $this->subtab( $this->current_id )->get_content();

		$content .= '</div>';

		return $content;
	}

}
