<?php

class Tabs {

	public ?string $current_id = null;
	private array $tabs = [];
	protected static $_instance = null;

	public static function instance(): ?Tabs {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function get_tabs(): array {
		return $this->tabs;
	}

	public function isset_tab( string $tab_id ): bool {
		return isset( $this->tabs[ $tab_id ] );
	}

	public function add( array $tabData ): ?Tab {

		if ( ! isset( $tabData['id'] ) ) {
			return null;
		}

		$this->tabs[ $tabData['id'] ] = new Tab( $tabData );

		$this->tabs[ $tabData['id'] ]->setup_subtabs();

		return $this->tabs[ $tabData['id'] ];
	}

	public function tab( string $tab_id ): ?Tab {

		if ( ! $this->isset_tab( $tab_id ) ) {
			return null;
		}

		return $this->tabs[ $tab_id ];
	}

	public function remove_tab( string $tab_id ): void {

		if ( ! $this->isset_tab( $tab_id ) ) {
			return;
		}

		unset( $this->tabs[ $tab_id ] );
	}

	public function current(): ?Tab {
		$this->current_id = $this->get_current_id();

		return ( $this->current_id ) ? $this->tab( $this->current_id ) : null;
	}

	public function get_current_id(): ?string {

		if ( isset( $_GET['tab'] ) ) {
			return sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		} else if ( $this->tabs ) {

			$tabs = $this->get_access_menu_items( 'menu' );

			if ( $tabs ) {
				foreach ( $tabs as $tab_id ) {
					return $tab_id;
				}
			} else {
				foreach ( $this->tabs as $tab_id => $tab ) {

					if ( $tab->output == 'menu' ) {
						continue;
					}

					if ( ! $tab->content ) {
						continue;
					}

					if ( ! $tab->is_access() ) {
						continue;
					}

					return $tab_id;
				}
			}
		}

		return null;
	}

	public function get_menu_items( string $menu_id ): ?array {

		if ( ! $this->tabs ) {
			return null;
		}

		$tab_ids = array();
		foreach ( $this->tabs as $tab_id => $tab ) {

			if ( $tab->output != $menu_id ) {
				continue;
			}

			$tab_ids[] = $tab_id;
		}

		return $tab_ids;
	}

	public function get_access_menu_items( string $menu_id ): ?array {
		$tab_ids = $this->get_menu_items( $menu_id );

		if ( ! $tab_ids ) {
			return null;
		}

		$ids = [];
		foreach ( $tab_ids as $tab_id ) {
			$tab = $this->tab( $tab_id );

			if ( ! $tab ) {
				continue;
			}

			if ( ! $tab->is_access() ) {
				continue;
			}

			$ids[] = $tab_id;
		}

		return $ids;
	}

	public function get_menu( string $menu_id, array $args = array() ): ?string {

		$tab_ids = isset( $args['tab_ids'] ) && $args['tab_ids'] ? $args['tab_ids'] : $this->get_access_menu_items( $menu_id );

		if ( ! $tab_ids ) {
			return null;
		}

		if ( ! $this->current_id ) {
			$this->current_id = $this->get_current_id();
		}

		$classes = [ 'usp-nav', 'usps' ];

		if ( isset( $args['class'] ) && $args['class'] ) {
			$classes[] = $args['class'];
		}

		$content = '<div id="usp-nav-' . esc_attr( $menu_id ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		foreach ( $tab_ids as $tab_id ) {
			$tab = $this->tab( $tab_id );
			if ( ! $tab ) {
				continue;
			}

			$content .= $tab->get_button( $this->current_id == $tab_id ? [ 'status' => 'active' ] : [] );
		}

		$content .= '</div>';

		return $content;
	}

	public function init_custom_tabs(): void {

		$areas = usp_get_area_options();

		foreach ( $areas as $area_id => $tabs ) {

			if ( ! $tabs ) {
				continue;
			}

			foreach ( $tabs as $tab ) {

				if ( isset( $tab['default-tab'] ) ) {
					continue;
				}

				$tab_data = [
					'id'         => $tab['slug'],
					'name'       => $tab['title'],
					'public'     => isset( $tab['public-tab'] ) && $tab['public-tab'] ? 1 : 0,
					'icon'       => $tab['icon'] ?: 'fa-cog',
					'output'     => $area_id,
					'supports'   => $tab['supports-tab'] ?? [],
					'custom-tab' => true,
					'content'    => [
						[
							'id'       => 'subtab-1',
							'name'     => $tab['title'],
							'icon'     => ( $tab['icon'] ) ?: 'fa-cog',
							'callback' => [
								'name' => 'usp_custom_tab_content',
								'args' => [ $tab['content'] ]
							]
						]
					]
				];

				$this->add( $tab_data );
			}
		}
	}

	public function order_tabs(): void {

		$areas = usp_get_area_options();

		if ( $areas ) {

			$newArray = [];

			foreach ( $areas as $tabs ) {

				if ( ! $tabs ) {
					continue;
				}

				foreach ( $tabs as $tabData ) {

					$tab = $this->tab( $tabData['slug'] );

					if ( ! $tab ) {
						continue;
					}

					$tab->set_prop( 'name', $tabData['title'] );
					$tab->set_prop( 'hidden', $tabData['hidden'] );
					$tab->set_prop( 'icon', $tabData['icon'] ?: 'fa-cog' );

					if ( isset( $tabData['custom-tab'] ) && $tabData['custom-tab'] ) {
						$tab->set_prop( 'custom_tab', 1 );
						$tab->set_prop( 'supports', $tabData['supports-tab'] ?? [] );
						$tab->set_prop( 'public', isset( $tabData['public-tab'] ) && $tabData['public-tab']);

						$tab->content[0] = new SubTab( [
							'id'        => $tab->id,
							'name'      => $tab->name,
							'icon'      => $tab->icon,
							'parent_id' => $tab->id,
							'callback'  => [
								'name' => $tab->content[0]->callback['name'],
								'args' => [ $tabData['content'] ]
							]
						] );
					}

					$newArray[ $tab->id ] = $tab;
				}
			}

			foreach ( $this->tabs as $tab_id => $tab ) {
				if ( ! isset( $newArray[ $tab_id ] ) ) {
					$newArray[ $tab_id ] = $tab;
				}
			}

			$this->tabs = $newArray;
		}
	}

}
