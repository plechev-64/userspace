<?php

namespace USP\Core\Module\DropdownMenu;

class Initializer {

	public function init(): void {
		add_action( 'usp_enqueue_scripts', [$this, 'usp_dropdown_menu_scripts'], 10 );
	}

	public function usp_dropdown_menu_scripts() {
		wp_enqueue_style( 'usp-dropdown-menu', USP_URL . 'assets/modules/dropdown-menu/usp-dropdown-menu.css' );
		wp_enqueue_script( 'usp-dropdown-menu', USP_URL . 'assets/modules/dropdown-menu/usp-dropdown-menu.js' );
	}

	public function usp_menu_build_tree_recursive( $items, $parent_id = 0 ): array {

		$menu = [];

		foreach ( $items as $item ) {
			if ( $item['parent'] == $parent_id ) {
				$menu[] = [
					'id'       => $item['id'],
					'url'      => $item['url'],
					'title'    => $item['title'],
					'children' => $this->usp_menu_build_tree_recursive( $items, $item['id'] )
				];
			}
		}

		return $menu;
	}

	public function usp_menu_build_from_tree_recursive( $menu_tree, DropdownMenu $usp_menu ) {

		foreach ( $menu_tree as $item ) {

			if ( $item['children'] ) {
				$child_menu = new DropdownMenu( 'wp-usp-bar-menu-' . $item['id'], [
					'open_button' => [
						'label' => $item['title'],
						'icon'  => 'fa-vertical-ellipsis'
					],
					'show'        => 'on_hover',
					'position'    => 'right-bottom',
					'style'       => $usp_menu->style
				] );

				$usp_menu->add_submenu( $child_menu );

				$this->usp_menu_build_from_tree_recursive( $item['children'], $child_menu );
			} else {

				$usp_menu->add_button( [ 'label' => $item['title'], 'href' => $item['url'] ] );

			}

		}

	}

}